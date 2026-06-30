<?php
/**
 * PRINEX — Dane do wysyłki: edycja adresu (Warstwa 1: styl natywny pojedynczego adresu).
 * Override myaccount/form-edit-address.php.
 *
 * Wiele adresów + przełącznik Osoba/Firma + faktura = WARSTWA 2 (custom CRUD na meta klienta).
 * Tu: natywny adres WC w systemie wizualnym PRINEX.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

$page_title = ( 'billing' === $load_address ) ? 'Adres rozliczeniowy' : 'Adres dostawy';

do_action( 'woocommerce_before_edit_account_address_form' );

if ( ! $load_address ) :
	wc_get_template( 'myaccount/my-address.php' );
else :
?>
	<form method="post" novalidate class="pxc-form pxc-acc-form">
		<div class="pxc-card">
			<h2 class="pxc-card-title"><?php echo esc_html( apply_filters( 'woocommerce_my_account_edit_address_title', $page_title, $load_address ) ); ?></h2>

			<div class="woocommerce-address-fields">
				<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

				<div class="woocommerce-address-fields__field-wrapper pxc-addr-grid">
					<?php
					foreach ( $address as $key => $field ) {
						woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
					}
					?>
				</div>

				<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>
			</div>
		</div>

		<div class="pxc-form-submit">
			<?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
			<button type="submit" class="pxc-btn-cta" name="save_address" value="<?php esc_attr_e( 'Save address', 'woocommerce' ); ?>">
				<span class="pxc-btn-label">Zapisz adres</span>
				<span class="pxc-btn-cube"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span>
			</button>
			<input type="hidden" name="action" value="edit_address" />
		</div>
	</form>
<?php
endif;

do_action( 'woocommerce_after_edit_account_address_form' );
