<?php
/**
 * PRINEX — eksport Code Snippet (mirror do wersjonowania, NIE zrodlo prawdy)
 *
 * ID snippetu (wp_snippets.id): 7
 * Tytul:                       PRINEX — Naklad: ceny/rabaty per wiersz w PHP (Etap 1, bez JS)
 * Typ:                         PHP (filtr na woocommerce_dropdown_variation_attribute_options_html)
 * Scope:                       front-end — wykonuje sie WYLACZNIE na froncie (nie w wp-admin)
 * Status:                      AKTYWNY
 *
 * PRZEPISANY 2026-06-21: poprzednia wersja (JS, wp_footer DOM-injection) mialaby
 * zahardkodowane ceny tylko dla Formatu 30x60 i kolidowala wizualnie z malym
 * kwadratowym swatchem (Bug 3 z poprzedniej iteracji). Nowa wersja liczy ceny w
 * PHP z realnych wariantow i wstrzykuje je w te sama operacje, ktora buduje
 * wiersz Nakladu (zob. woocommerce/single-product/add-to-cart/variable.php).
 *
 * UWAGA: zrodlem prawdy jest baza WP (wtyczka Code Snippets). Ten plik to
 * mirror do wersjonowania/code review. Edycja tego pliku NIE zmienia
 * dzialania strony — trzeba wkleic zmiany z powrotem do wp-admin > Code Snippets,
 * lub zaktualizowac wp_snippets.code (np. przez wp-cli/wp eval).
 */

/**
 * PRINEX — Nakład: ceny/rabaty per wiersz, wstrzykiwane w PHP (Etap 1, bez JS)
 *
 * Hook: woocommerce_dropdown_variation_attribute_options_html, priorytet 21
 * (PO wtyczce woo-variation-swatches, priorytet 20) — wzbogaca juz-swatchowy
 * HTML o cene netto/brutto + "Taniej o X%" dla atrybutu pa_naklad, liczone
 * z realnych wariantow domyslnego Formatu (Rozmiaru). Selekcja .selected nie
 * jest ruszana (atrybuty <li> bez zmian) — modyfikujemy tylko wnetrze <li>.
 *
 * TODO Etap 2 (kalkulator): wartosci sa statyczne dla domyslnego Formatu
 * (30x60). Po zmianie Formatu wiersze Nakladu NIE przeliczaja sie — to
 * wymaga kalkulatora, poza zakresem Etapu 1.
 */

add_filter( 'woocommerce_dropdown_variation_attribute_options_html', function( $html, $args ) {
	if ( empty( $args['attribute'] ) || 'pa_naklad' !== $args['attribute'] ) {
		return $html;
	}
	$product = isset( $args['product'] ) ? $args['product'] : null;
	if ( ! $product instanceof WC_Product ) {
		return $html;
	}

	$default_attrs   = $product->get_default_attributes();
	$default_rozmiar = isset( $default_attrs['pa_rozmiar'] ) ? $default_attrs['pa_rozmiar'] : '';

	if ( ! $product->is_type( 'variable' ) ) {
		return $html;
	}

	$variations = $product->get_available_variations();
	$by_naklad  = array();
	foreach ( $variations as $v ) {
		$attrs = isset( $v['attributes'] ) ? $v['attributes'] : array();
		if ( ! isset( $attrs['attribute_pa_rozmiar'], $attrs['attribute_pa_naklad'] ) ) {
			continue;
		}
		if ( $attrs['attribute_pa_rozmiar'] !== $default_rozmiar ) {
			continue;
		}
		$vp = wc_get_product( $v['variation_id'] );
		if ( ! $vp ) {
			continue;
		}
		$net = (float) $vp->get_price();
		$qty = max( 1, (int) preg_replace( '/[^0-9]/', '', $attrs['attribute_pa_naklad'] ) );
		$by_naklad[ $attrs['attribute_pa_naklad'] ] = array(
			'net'   => $net,
			'gross' => (float) wc_get_price_including_tax( $vp, array( 'price' => $net ) ),
			'qty'   => $qty,
		);
	}
	if ( empty( $by_naklad ) ) {
		return $html;
	}

	// Baseline (najmniejszy naklad, zwykle 100 szt.) — punkt odniesienia dla "Taniej o X%" wzgledem ceny za sztuke.
	$baseline_slug = null;
	$baseline_qty  = null;
	foreach ( $by_naklad as $slug => $row ) {
		if ( null === $baseline_qty || $row['qty'] < $baseline_qty ) {
			$baseline_qty  = $row['qty'];
			$baseline_slug = $slug;
		}
	}
	$baseline_unit = ( $baseline_slug && $by_naklad[ $baseline_slug ]['qty'] ) ? ( $by_naklad[ $baseline_slug ]['net'] / $by_naklad[ $baseline_slug ]['qty'] ) : 0;

	$html = preg_replace_callback(
		'/(<li[^>]*data-value="([^"]+)"[^>]*>)(.*?)(<\/li>)/s',
		function( $m ) use ( $by_naklad, $baseline_slug, $baseline_unit ) {
			$slug = $m[2];
			if ( ! isset( $by_naklad[ $slug ] ) ) {
				return $m[0];
			}
			$row     = $by_naklad[ $slug ];
			$qty_num = $row['qty'];
			$unit    = $qty_num ? ( $row['net'] / $qty_num ) : 0;
			$disc    = ( $baseline_unit > 0 && $slug !== $baseline_slug ) ? (int) round( ( 1 - ( $unit / $baseline_unit ) ) * 100 ) : 0;

			$hit = ( '250-szt' === $slug )
				? '<span class="pill pill-opt" style="background-color:#F39200">HIT</span>'
				: '';
			$disc_html = $disc > 0
				? '<span class="nak-disc">' . esc_html( sprintf( __( 'Taniej o %d%%', 'prinex' ), $disc ) ) . '</span>'
				: '<span class="nak-disc"></span>';

			$inner = '<span class="nak-left">'
				. '<span class="opt-check"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span>'
				. '<span class="opt-row-title">' . esc_html( $qty_num ) . '</span>'
				. $hit
				. '</span>'
				. '<span class="nak-price"><b class="net">' . wp_kses_post( wc_price( $row['net'] ) ) . '</b><span class="sl">/</span><span class="br">' . wp_kses_post( wc_price( $row['gross'] ) ) . '</span></span>'
				. $disc_html;

			return $m[1] . $inner . $m[4];
		},
		$html
	);

	return $html;
}, 21, 2 );
