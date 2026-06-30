<?php
/**
 * PRINEX — Panel klienta (My Account) wrapper.
 * Override myaccount/my-account.php.
 *
 * Layout: kontener 1400 → nagłówek (H1 per endpoint) → grid [nawigacja | treść] → trust.
 * Treść/nawigacja renderowane natywnymi akcjami WC (mechanika kont nietknięta).
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

$pxc_user  = wp_get_current_user();
$pxc_first = $pxc_user->first_name ? $pxc_user->first_name : $pxc_user->display_name;

// Bieżący endpoint → tytuł + podtytuł.
$pxc_ep = 'dashboard';
foreach ( array( 'orders', 'view-order', 'edit-address', 'edit-account', 'downloads', 'customer-logout' ) as $e ) {
	if ( is_wc_endpoint_url( $e ) ) {
		$pxc_ep = $e;
		break;
	}
}
$pxc_titles = array(
	'dashboard'    => array( 'Cześć, ' . $pxc_first . '!', 'Stąd zarządzasz zamówieniami i danymi konta.' ),
	'orders'       => array( 'Zamówienia', 'Przeglądaj historię i ponawiaj zamówienia jednym kliknięciem.' ),
	'view-order'   => array( 'Szczegóły zamówienia', '' ),
	'edit-address' => array( 'Dane do wysyłki', 'Adresy używane domyślnie przy kolejnych zamówieniach.' ),
	'edit-account' => array( 'Dane konta', 'Zaktualizuj dane logowania i hasło.' ),
);
$pxc_title = $pxc_titles[ $pxc_ep ] ?? array( 'Moje konto', '' );
?>
<section class="pxc-acc-wrap">
  <div class="prinex-container">

    <div class="pxc-acc-head">
      <div class="pxc-sig"></div>
      <h1 class="pxc-acc-title"><?php echo esc_html( $pxc_title[0] ); ?></h1>
      <?php if ( 'view-order' === $pxc_ep ) : ?>
        <a href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>" class="pxc-acc-back">
          <svg viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg> Wróć do listy zamówień
        </a>
      <?php elseif ( $pxc_title[1] ) : ?>
        <p class="pxc-acc-sub"><?php echo esc_html( $pxc_title[1] ); ?></p>
      <?php endif; ?>
    </div>

    <div class="pxc-acc-grid">
      <aside class="pxc-acc-nav">
        <?php do_action( 'woocommerce_account_navigation' ); ?>
      </aside>
      <div class="pxc-acc-content woocommerce-MyAccount-content">
        <?php do_action( 'woocommerce_account_content' ); ?>
      </div>
    </div>

  </div>
</section>
<?php get_template_part( 'inc/prinex-trust' ); ?>
