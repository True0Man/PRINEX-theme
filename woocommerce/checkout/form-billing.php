<?php
/**
 * PRINEX — Checkout: "Dane odbiorcy" (billing) — zwijana karta + inline Edytuj.
 * Override checkout/form-billing.php.
 *
 * - Zwinięty: zapisany adres jako tekst + link "Edytuj".
 * - Rozwinięty (JS toggle): pełny formularz pól WooCommerce + przełącznik Osoba/Firma.
 * - Pola Firma + NIP (klasa .pxc-firma-only) widoczne tylko dla typu "firma" (JS).
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

$fields = $checkout->get_checkout_fields( 'billing' );

$fn   = $checkout->get_value( 'billing_first_name' );
$ln   = $checkout->get_value( 'billing_last_name' );
$a1   = $checkout->get_value( 'billing_address_1' );
$pc   = $checkout->get_value( 'billing_postcode' );
$ci   = $checkout->get_value( 'billing_city' );
$ph   = $checkout->get_value( 'billing_phone' );
$cc   = $checkout->get_value( 'billing_country' );
$cnam = $cc && isset( WC()->countries->get_countries()[ $cc ] ) ? WC()->countries->get_countries()[ $cc ] : '';

$has_saved = ( $fn || $ln ) && $a1;

$type_val  = $checkout->get_value( 'billing_customer_type' );
$type_val  = $type_val ? $type_val : 'osoba';

$addr_bits = array_filter( [
	trim( "$a1" ),
	trim( "$pc $ci" ),
	$cnam,
	$ph,
] );
?>
<div class="woocommerce-billing-fields pxc-card pxc-recipient<?php echo $has_saved ? ' is-collapsed' : ''; ?>" id="pxc-recipient">

	<div class="pxc-card-head">
		<h2 class="pxc-card-title">Dane odbiorcy</h2>
		<a href="#" class="pxc-edit-link" id="pxc-edit-link" role="button"><?php echo $has_saved ? 'Edytuj' : 'Zwiń'; ?></a>
	</div>

	<?php if ( $has_saved ) : ?>
	<div class="pxc-recipient-summary" id="pxc-recipient-summary">
		<div class="pxc-rs-name"><?php echo esc_html( trim( "$fn $ln" ) ); ?></div>
		<div class="pxc-rs-addr"><?php echo esc_html( implode( ', ', $addr_bits ) ); ?></div>
	</div>
	<?php endif; ?>

	<div class="woocommerce-billing-fields__field-wrapper pxc-recipient-form" id="pxc-recipient-form"<?php echo $has_saved ? ' hidden' : ''; ?>>

		<div class="pxc-type-toggle" role="group" aria-label="Typ odbiorcy">
			<button type="button" class="pxc-type-pill<?php echo 'osoba' === $type_val ? ' is-active' : ''; ?>" data-type="osoba">Osoba prywatna</button>
			<button type="button" class="pxc-type-pill<?php echo 'firma' === $type_val ? ' is-active' : ''; ?>" data-type="firma">Firma</button>
		</div>
		<input type="hidden" name="billing_customer_type" id="billing_customer_type" value="<?php echo esc_attr( $type_val ); ?>">

		<?php
		foreach ( $fields as $key => $field ) {
			woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
		}
		?>
	</div>

	<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
</div>
