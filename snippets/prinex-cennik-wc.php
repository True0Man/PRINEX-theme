/**
 * PRINEX — WC filtry cennika (#25, Etap 2)
 *
 * Podpina ceny z silnika (#23 prinex_sale_net) pod mechanikę WooCommerce:
 *  1. woocommerce_available_variation — dane wariantu wysyłane do JS
 *  2. woocommerce_before_calculate_totals — cena w koszyku/kasie
 *
 * NIE dotyka variable.php, snippet #13, #14, nie dodaje JS.
 * Wymaga aktywnego snippetu #23 (silnik cennika).
 */

// 1. Dane wariantu wysyłane do JS (wyświetlanie)
add_filter( 'woocommerce_available_variation', function( $data, $product, $variation ) {
	if ( ! function_exists( 'prinex_sale_net' ) || ! function_exists( 'prinex_rozmiar_to_dims' ) ) {
		return $data;
	}

	$foil_id = get_post_meta( $product->get_id(), '_prinex_folia', true );
	$rodzaj  = get_post_meta( $product->get_id(), '_prinex_rodzaj', true ) ?: '3d';

	if ( empty( $foil_id ) ) {
		return $data;
	}

	$rozmiar_slug = $variation->get_attribute( 'pa_rozmiar' );
	$naklad_slug  = $variation->get_attribute( 'pa_naklad' );

	if ( empty( $rozmiar_slug ) || empty( $naklad_slug ) ) {
		return $data;
	}

	list( $w, $h ) = prinex_rozmiar_to_dims( $rozmiar_slug );
	$qty = max( 1, (int) preg_replace( '/[^0-9]/', '', $naklad_slug ) );

	if ( $w <= 0 || $h <= 0 ) {
		return $data;
	}

	$net = prinex_sale_net( $foil_id, $rodzaj, $w, $h, $qty );

	$data['display_price']         = wc_get_price_to_display( $variation, array( 'price' => $net ) );
	$data['display_regular_price'] = $data['display_price'];
	$data['price_html']            = wc_price( $net );

	return $data;
}, 20, 3 );

// 2. Cena w koszyku i kasie
add_action( 'woocommerce_before_calculate_totals', function( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}
	if ( ! function_exists( 'prinex_sale_net' ) || ! function_exists( 'prinex_rozmiar_to_dims' ) ) {
		return;
	}
	if ( $cart->is_empty() ) {
		return;
	}

	foreach ( $cart->get_cart() as $cart_item ) {
		$product = $cart_item['data'];
		if ( ! ( $product instanceof WC_Product_Variation ) ) {
			continue;
		}

		$parent_id = $product->get_parent_id();
		$foil_id   = get_post_meta( $parent_id, '_prinex_folia', true );
		$rodzaj    = get_post_meta( $parent_id, '_prinex_rodzaj', true ) ?: '3d';

		if ( empty( $foil_id ) ) {
			continue;
		}

		$rozmiar_slug = $product->get_attribute( 'pa_rozmiar' );
		$naklad_slug  = $product->get_attribute( 'pa_naklad' );

		if ( empty( $rozmiar_slug ) || empty( $naklad_slug ) ) {
			continue;
		}

		list( $w, $h ) = prinex_rozmiar_to_dims( $rozmiar_slug );
		$qty = max( 1, (int) preg_replace( '/[^0-9]/', '', $naklad_slug ) );

		if ( $w <= 0 || $h <= 0 ) {
			continue;
		}

		$net = prinex_sale_net( $foil_id, $rodzaj, $w, $h, $qty );
		$product->set_price( $net );
	}
}, 10, 1 );
