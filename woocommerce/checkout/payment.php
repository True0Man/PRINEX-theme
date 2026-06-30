<?php
/**
 * PRINEX — Checkout: bramki płatności TYLKO (lewa kolumna).
 * Override checkout/payment.php.
 *
 * UWAGA: terms + #place_order + nonce NIE są tutaj — renderowane w prawym podsumowaniu
 * (form-checkout.php), POZA fragmentem AJAX .woocommerce-checkout-payment.
 * Hook woocommerce_checkout_payment jest zdjęty z order_review w snippecie #28;
 * tę funkcję wywołujemy ręcznie w lewej kolumnie. AJAX nadal odświeża #payment w miejscu.
 *
 * @package generatepress-child
 * @version 9.8.0-prinex
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="payment" class="woocommerce-checkout-payment">
	<?php if ( WC()->cart && WC()->cart->needs_payment() ) : ?>
		<ul class="wc_payment_methods payment_methods methods">
			<?php
			if ( ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway ) {
					wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
				}
			} else {
				echo '<li class="woocommerce-info">';
				wc_print_notice(
					apply_filters(
						'woocommerce_no_available_payment_methods_message',
						WC()->customer->get_billing_country() ? esc_html__( 'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) : esc_html__( 'Please fill in your details above to see available payment methods.', 'woocommerce' )
					),
					'notice'
				);
				echo '</li>';
			}
			?>
		</ul>
	<?php else : ?>
		<p class="pxc-no-payment">Ta pozycja nie wymaga płatności online.</p>
	<?php endif; ?>

	<noscript>
		<button type="submit" class="button alt" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update totals', 'woocommerce' ); ?>"><?php esc_html_e( 'Update totals', 'woocommerce' ); ?></button>
	</noscript>
</div>
