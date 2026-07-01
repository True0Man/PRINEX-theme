<?php
/**
 * PRINEX — Dane do wysyłki: KSIĄŻKA ADRESOWA (Warstwa 2c).
 * Override myaccount/my-address.php.
 *
 * Renderuje custom książkę adresową (wiele adresów na meta klienta _prinex_addresses)
 * przez prinex_render_address_book() (snippet #30 — CRUD AJAX + bezpieczeństwo).
 * Fallback: gdy snippet #30 nieaktywny — natywne adresy WC.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'prinex_render_address_book' ) ) {
	echo prinex_render_address_book( get_current_user_id() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

/* ── Fallback (snippet #30 nieaktywny): natywne adresy WC w kartach ── */
$customer_id = get_current_user_id();
$get_addresses = apply_filters(
	'woocommerce_my_account_get_addresses',
	array( 'shipping' => 'Adres dostawy', 'billing' => 'Adres rozliczeniowy' ),
	$customer_id
);
?>
<div class="pxc-addr-cards">
	<?php foreach ( $get_addresses as $name => $address_title ) : ?>
		<?php $address = wc_get_account_formatted_address( $name ); ?>
		<div class="pxc-card pxc-addr-card">
			<div class="pxc-addr-card-head">
				<span class="pxc-addr-card-title"><?php echo esc_html( $address_title ); ?></span>
				<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $name ) ); ?>" class="pxc-addr-edit">Edytuj</a>
			</div>
			<address class="pxc-addr-body"><?php echo $address ? wp_kses_post( $address ) : 'Nie ustawiono jeszcze tego adresu.'; ?></address>
		</div>
	<?php endforeach; ?>
</div>
