/**
 * PRINEX — Nakład: ceny/rabaty per wiersz w PHP (Etap 2 — silnik cennika)
 *
 * Hook: woocommerce_dropdown_variation_attribute_options_html, priorytet 21
 * (PO wtyczce woo-variation-swatches, priorytet 20) — wzbogaca już-swatchowy
 * HTML o cenę netto/brutto + "Taniej o X%" dla atrybutu pa_naklad.
 * Ceny z prinex_sale_net() (#23); fallback na get_price() gdy silnik niedostępny.
 * Badge OPTYMALNY z _prinex_optymalny (meta produktu).
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

	// Dane silnika cennika
	$foil_id = get_post_meta( $product->get_id(), '_prinex_folia', true );
	$rodzaj  = get_post_meta( $product->get_id(), '_prinex_rodzaj', true ) ?: '3d';
	$dims    = ( $default_rozmiar && function_exists( 'prinex_rozmiar_to_dims' ) ) ? prinex_rozmiar_to_dims( $default_rozmiar ) : null;
	$w       = $dims ? (float) $dims[0] : 0.0;
	$h       = $dims ? (float) $dims[1] : 0.0;

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
		$qty = max( 1, (int) preg_replace( '/[^0-9]/', '', $attrs['attribute_pa_naklad'] ) );
		if ( $foil_id && function_exists( 'prinex_sale_net' ) && $w > 0 && $h > 0 ) {
			$net = prinex_sale_net( $foil_id, $rodzaj, $w, $h, $qty );
		} else {
			$net = (float) $vp->get_price();
		}
		$by_naklad[ $attrs['attribute_pa_naklad'] ] = array(
			'net'   => $net,
			'gross' => (float) wc_get_price_including_tax( $vp, array( 'price' => $net ) ),
			'qty'   => $qty,
		);
	}
	if ( empty( $by_naklad ) ) {
		return $html;
	}

	// Baseline (najmniejszy nakład) — punkt odniesienia dla "Taniej o X%"
	$baseline_slug = null;
	$baseline_qty  = null;
	foreach ( $by_naklad as $slug => $row ) {
		if ( null === $baseline_qty || $row['qty'] < $baseline_qty ) {
			$baseline_qty  = $row['qty'];
			$baseline_slug = $slug;
		}
	}
	$baseline_unit = ( $baseline_slug && $by_naklad[ $baseline_slug ]['qty'] ) ? ( $by_naklad[ $baseline_slug ]['net'] / $by_naklad[ $baseline_slug ]['qty'] ) : 0;

	$optymalny_slug = get_post_meta( $product->get_id(), '_prinex_optymalny', true );

	$html = preg_replace_callback(
		'/(<li[^>]*data-value="([^"]+)"[^>]*>)(.*?)(<\/li>)/s',
		function( $m ) use ( $by_naklad, $baseline_slug, $baseline_unit, $optymalny_slug ) {
			$slug = $m[2];
			if ( ! isset( $by_naklad[ $slug ] ) ) {
				return $m[0];
			}
			$row     = $by_naklad[ $slug ];
			$qty_num = $row['qty'];
			$unit    = $qty_num ? ( $row['net'] / $qty_num ) : 0;
			$disc    = ( $baseline_unit > 0 && $slug !== $baseline_slug ) ? (int) round( ( 1 - ( $unit / $baseline_unit ) ) * 100 ) : 0;

			$hit = ( ! empty( $optymalny_slug ) && $slug === $optymalny_slug )
				? '<span class="pill pill-opt" style="background-color:#78B833">OPTYMALNY</span>'
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
