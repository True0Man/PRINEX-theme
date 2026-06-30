<?php
/**
 * PRINEX — Nawigacja konta (pionowa, aktywna pozycja z zieloną kreską).
 * Override myaccount/navigation.php.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_navigation' );

// Ikony per endpoint (inline SVG, kolor dziedziczony).
$pxc_icons = array(
	'dashboard'       => '<path d="M3 10l9-7 9 7"/><path d="M5 9v11h14V9"/><path d="M9 20v-6h6v6"/>',
	'orders'          => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
	'edit-address'    => '<path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
	'edit-account'    => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
	'customer-logout' => '<path d="M15 17l5-5-5-5"/><path d="M20 12H9"/><path d="M9 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h3"/>',
	'downloads'       => '<path d="M12 16V5"/><path d="M7 11l5 5 5-5"/><path d="M5 19h14"/>',
);
?>

<nav class="woocommerce-MyAccount-navigation" aria-label="<?php esc_attr_e( 'Account pages', 'woocommerce' ); ?>">
	<ul>
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="<?php echo esc_attr( wc_get_account_menu_item_classes( $endpoint ) ); ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" <?php echo wc_is_current_account_menu_item( $endpoint ) ? 'aria-current="page"' : ''; ?>>
					<span class="pxc-acc-nav-ic"><svg viewBox="0 0 24 24"><?php echo $pxc_icons[ $endpoint ] ?? $pxc_icons['edit-account']; // phpcs:ignore ?></svg></span>
					<span class="pxc-acc-nav-lbl"><?php echo esc_html( $label ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
