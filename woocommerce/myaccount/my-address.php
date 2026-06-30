<?php
/**
 * PRINEX — Dane do wysyłki: lista adresów (karty).
 * Override myaccount/my-address.php. (Warstwa 1: natywne adresy billing/shipping w kartach PRINEX.)
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();

if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'shipping' => 'Adres dostawy',
			'billing'  => 'Adres rozliczeniowy',
		),
		$customer_id
	);
} else {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array( 'billing' => 'Adres rozliczeniowy' ),
		$customer_id
	);
}
?>
<div class="pxc-addr-cards">
	<?php foreach ( $get_addresses as $name => $address_title ) : ?>
		<?php $address = wc_get_account_formatted_address( $name ); ?>
		<div class="pxc-card pxc-addr-card">
			<div class="pxc-addr-card-head">
				<span class="pxc-addr-card-title"><?php echo esc_html( $address_title ); ?></span>
				<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $name ) ); ?>" class="pxc-addr-edit">
					<svg viewBox="0 0 24 24"><path d="M4 20h4l10-10-4-4L4 16z"/><path d="M13.5 6.5l4 4"/></svg>
					<?php echo $address ? 'Edytuj' : 'Dodaj'; ?>
				</a>
			</div>
			<address class="pxc-addr-body">
				<?php
				if ( $address ) {
					echo wp_kses_post( $address );
				} else {
					echo '<span class="pxc-addr-empty">Nie ustawiono jeszcze tego adresu.</span>';
				}
				do_action( 'woocommerce_my_account_after_my_address', $name );
				?>
			</address>
		</div>
	<?php endforeach; ?>
</div>
<?php /* Warstwa 2: „Dodaj nowy adres" + wiele adresów + Osoba/Firma + faktura (custom CRUD na meta klienta). */ ?>
