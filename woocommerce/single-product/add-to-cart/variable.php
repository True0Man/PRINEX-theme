<?php
/**
 * PRINEX — override: konfigurator wariantow (Format / Naklad) + Podsumowanie + ZAMAWIAM
 *
 * Bazuje na woocommerce/templates/single-product/add-to-cart/variable.php (WC, wersja bazowa 9.6.0).
 * ETAP 1 — warstwa wizualna 1:1 ze wzoru 04-mockupy/02-strona-produktu/Strona Produktu PRINEX.html
 * (klasy: cfg-block, cfg-label, cfg-num, qty-list, cfg-label-nak, sum-card, dlv, btn-cta...).
 * DANE: rozmiary/ceny/rabaty z bazy WooCommerce, NIE ze wzoru (wzor mial fikcyjne 70x100/100x150
 * i fikcyjne ceny — ignorowane).
 *
 * Zachowane bez zmian: wc_dropdown_variation_attribute_options() per atrybut (woo-variation-swatches
 * filtruje jego output), .variations jako rodzic <select> oraz .single_variation/.single_variation_wrap
 * (wymagane przez assets/js/frontend/add-to-cart-variation.js) — bez tego dopasowywanie wariantu
 * i poprawne dodanie do koszyka przestaje dzialac.
 *
 * "Wlasny format" / "Wlasny naklad" (custom-panel ze wzoru) — POMINIETE w Etapie 1 (nie sa realnymi
 * wartosciami atrybutow, to funkcja Etapu 2/indywidualnego zamowienia). TODO Etap 2: dodac wiersz +
 * panel z polami, walidacja.
 */

defined( 'ABSPATH' ) || exit;

global $product;

$attribute_keys  = array_keys( $attributes );
$variations_json = wp_json_encode( $available_variations );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

// Etykiety UI per atrybut (FORMAT zamiast nazwy taksonomii "Rozmiar" — decyzja z briefu wdrozenia; slug pa_rozmiar pozostaje bez zmian)
$prinex_attr_labels = array(
	'pa_rozmiar' => __( 'Format', 'prinex' ),
	'pa_naklad'  => __( 'Nakład', 'prinex' ),
);
// ID listy per atrybut — zgodne ze wzorem (#sizeList / #qtyList), wykorzystywane rowniez jako
// kotwica specyficznosci CSS (patrz snippet #13: #sizeList/#qtyList .variable-item...).
$prinex_attr_ids = array(
	'pa_rozmiar' => 'sizeList',
	'pa_naklad'  => 'qtyList',
);

// Domyslny wariant — do statycznej karty "Podsumowanie" i paska dostawy (Etap 1)
$prinex_default_attrs     = $product->get_default_attributes();
$prinex_default_variation = null;
foreach ( $available_variations as $prinex_v ) {
	$match = true;
	foreach ( $prinex_default_attrs as $prinex_attr_name => $prinex_attr_value ) {
		$prinex_key = 'attribute_' . $prinex_attr_name;
		if ( ! isset( $prinex_v['attributes'][ $prinex_key ] ) || $prinex_v['attributes'][ $prinex_key ] !== $prinex_attr_value ) {
			$match = false;
			break;
		}
	}
	if ( $match ) {
		$prinex_default_variation = $prinex_v;
		break;
	}
}

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="variations_form cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. ?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?></p>
	<?php else : ?>

		<div class="variations prinex-variations">
			<?php
			$prinex_block_num = 1;
			foreach ( $attributes as $attribute_name => $options ) :
				$prinex_label  = isset( $prinex_attr_labels[ $attribute_name ] ) ? $prinex_attr_labels[ $attribute_name ] : wc_attribute_label( $attribute_name );
				$prinex_is_nak = ( 'pa_naklad' === $attribute_name );
				$prinex_list_id = isset( $prinex_attr_ids[ $attribute_name ] ) ? $prinex_attr_ids[ $attribute_name ] : sanitize_title( $attribute_name ) . 'List';

				// Aktualna (domyslna) etykieta wartosci do .lbl-val — statyczna w Etapie 1, TODO Etap 2: zywa na zmiane wyboru.
				$prinex_cur_label = '';
				if ( isset( $prinex_default_attrs[ $attribute_name ] ) && taxonomy_exists( $attribute_name ) ) {
					$prinex_term = get_term_by( 'slug', $prinex_default_attrs[ $attribute_name ], $attribute_name );
					if ( $prinex_term ) {
						$prinex_cur_label = $prinex_term->name . ( $prinex_is_nak ? ' szt.' : '' );
					}
				}
				?>
				<div class="cfg-block">
					<?php if ( $prinex_is_nak ) : ?>
						<div class="cfg-label cfg-label-nak">
							<span class="nak-head-left"><span class="cfg-num"><?php echo esc_html( $prinex_block_num ); ?></span><?php echo esc_html( $prinex_label ); ?> <span class="lbl-val"><?php echo esc_html( $prinex_cur_label ); ?></span></span>
							<span class="nak-col-desc"><?php esc_html_e( 'netto / brutto', 'prinex' ); ?></span>
							<span></span>
						</div>
					<?php else : ?>
						<div class="cfg-label"><span class="cfg-num"><?php echo esc_html( $prinex_block_num ); ?></span><?php echo esc_html( $prinex_label ); ?> <span class="lbl-val"><?php echo esc_html( $prinex_cur_label ); ?></span></div>
					<?php endif; ?>
					<div class="qty-list" id="<?php echo esc_attr( $prinex_list_id ); ?>">
						<?php
						wc_dropdown_variation_attribute_options(
							array(
								'options'   => $options,
								'attribute' => $attribute_name,
								'product'   => $product,
							)
						);
						?>
					</div>
				</div>
				<?php
				$prinex_block_num++;
			endforeach;
			?>
		</div>

		<div class="reset_variations_alert screen-reader-text" role="alert" aria-live="polite" aria-relevant="all"></div>
		<?php do_action( 'woocommerce_after_variations_table' ); ?>

		<!-- 3. PODSUMOWANIE — Etap 1: wartosci statyczne domyslnego wariantu (zrodlo: WC, w tym wc_get_price_including_tax). -->
		<!-- TODO Etap 2 (kalkulator): podmienic na live-update przy zmianie Formatu/Nakladu. -->
		<?php if ( $prinex_default_variation ) :
			$prinex_vp   = wc_get_product( $prinex_default_variation['variation_id'] );
			$prinex_qty  = 1;
			foreach ( $prinex_default_attrs as $prinex_a => $prinex_v_val ) {
				if ( false !== strpos( $prinex_a, 'naklad' ) ) {
					$prinex_qty = max( 1, (int) preg_replace( '/[^0-9]/', '', $prinex_v_val ) );
				}
			}
			$prinex_total_net   = (float) $prinex_vp->get_price();
			$prinex_total_gross = (float) wc_get_price_including_tax( $prinex_vp, array( 'price' => $prinex_total_net ) );
			$prinex_unit_net    = $prinex_total_net / $prinex_qty;
			$prinex_unit_gross  = $prinex_total_gross / $prinex_qty;

			$prinex_free_ship_threshold = 200; // zgodnie z topbar "Darmowa dostawa już od 200 zł"
			$prinex_pct                 = min( 100, round( ( $prinex_total_net / $prinex_free_ship_threshold ) * 100 ) );
			$prinex_missing             = max( 0, $prinex_free_ship_threshold - $prinex_total_net );
			?>
			<div class="cfg-block">
				<div class="cfg-label"><span class="cfg-num">3</span><?php esc_html_e( 'Podsumowanie', 'prinex' ); ?></div>

				<div class="sum-card">
					<div class="sum-half">
						<span class="sum-lab grey"><?php esc_html_e( 'Cena za sztukę', 'prinex' ); ?></span>
						<span class="sum-val"><?php echo wp_kses_post( wc_price( $prinex_unit_net ) ); ?></span>
						<span class="sum-brutto"><?php esc_html_e( 'brutto', 'prinex' ); ?> <?php echo wp_kses_post( wc_price( $prinex_unit_gross ) ); ?></span>
					</div>
					<div class="sum-div"></div>
					<div class="sum-half right">
						<span class="sum-lab navy"><?php esc_html_e( 'Suma', 'prinex' ); ?></span>
						<span class="sum-val"><?php echo wp_kses_post( wc_price( $prinex_total_net ) ); ?></span>
						<span class="sum-brutto"><?php esc_html_e( 'brutto', 'prinex' ); ?> <?php echo wp_kses_post( wc_price( $prinex_total_gross ) ); ?></span>
					</div>
				</div>

				<div class="dlv">
					<div class="dlv-row">
						<?php if ( $prinex_total_net >= $prinex_free_ship_threshold ) : ?>
							<span class="dlv-left"><?php esc_html_e( 'Masz darmową dostawę', 'prinex' ); ?></span>
							<span class="dlv-right"><svg viewBox="0 0 24 24" fill="none" stroke="#78B833" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"></path></svg><?php esc_html_e( 'Gratis', 'prinex' ); ?></span>
						<?php else : ?>
							<span class="dlv-left"><?php esc_html_e( 'Jeszcze tylko troszkę..', 'prinex' ); ?></span>
							<span class="dlv-right"><?php esc_html_e( 'brakuje', 'prinex' ); ?> <?php echo wp_kses_post( wc_price( $prinex_missing ) ); ?></span>
						<?php endif; ?>
					</div>
					<div class="track">
						<div class="fill" style="width:<?php echo esc_attr( $prinex_pct ); ?>%"></div>
						<div class="truck" style="left:<?php echo esc_attr( $prinex_pct ); ?>%">
							<svg viewBox="0 0 24 24" fill="none" stroke="#0B457D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7" cy="17" r="2"></circle><circle cx="17" cy="17" r="2"></circle><path d="M5 17H3V6a1 1 0 0 1 1-1h9v12M9 17h6m4 0h2v-6h-6m0-3h3l3 4"></path></svg>
						</div>
					</div>
				</div>
				<p class="screen-reader-text">Wartości statyczne dla domyślnego wariantu — Etap 1 (warstwa wizualna). Etap 2: kalkulator na zmianę wyboru.</p>
			</div>
		<?php endif; ?>

		<div class="cfg-order">
			<div class="single_variation_wrap">
				<?php
				do_action( 'woocommerce_before_single_variation' );
				do_action( 'woocommerce_single_variation' );
				do_action( 'woocommerce_after_single_variation' );
				?>
			</div>

			<div class="next-step">
				<svg viewBox="0 0 24 24"><path d="M12 15V4"/><path d="M8 8l4-4 4 4"/><path d="M4 14v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"/></svg>
				<?php esc_html_e( 'Następny krok: wgraj pliki', 'prinex' ); ?>
			</div>
		</div>

	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );
