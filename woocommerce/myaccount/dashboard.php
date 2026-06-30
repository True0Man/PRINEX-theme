<?php
/**
 * PRINEX — Pulpit konta (kafle) — 1:1 wg mockup-konto-1 (ekran 2).
 * Override myaccount/dashboard.php.
 *
 * Kafel ostatniego zamówienia POZIOMY (miniatura | info | Zobacz + Zamów ponownie),
 * pod nim 2 kolumny: navy CTA "Złóż nowe zamówienie" + amber "pliki do dostarczenia".
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

$pxc_uid    = get_current_user_id();
$pxc_orders = wc_get_orders( array(
	'customer' => $pxc_uid,
	'limit'    => 8,
	'orderby'  => 'date',
	'order'    => 'DESC',
	'status'   => array_keys( wc_get_order_statuses() ),
) );
$pxc_last = ! empty( $pxc_orders ) ? $pxc_orders[0] : null;

$pxc_missing = 0;
$pxc_missing_order = null;
foreach ( $pxc_orders as $o ) {
	if ( in_array( $o->get_status(), array( 'completed', 'cancelled', 'refunded', 'failed' ), true ) ) {
		continue;
	}
	foreach ( $o->get_items() as $it ) {
		$f  = $it->get_meta( '_prinex_upload_files' );
		$pj = $it->get_meta( '_prinex_upload_projekt' );
		if ( ! ( ( is_array( $f ) && $f ) || '1' === $pj ) ) {
			$pxc_missing++;
			if ( ! $pxc_missing_order ) {
				$pxc_missing_order = $o;
			}
		}
	}
}

if ( ! function_exists( 'pxc_pozycje' ) ) {
	function pxc_pozycje( $n ) {
		$n = (int) $n;
		if ( 1 === $n ) {
			return 'pozycja';
		}
		$mod = $n % 10;
		$mod100 = $n % 100;
		if ( $mod >= 2 && $mod <= 4 && ! ( $mod100 >= 12 && $mod100 <= 14 ) ) {
			return 'pozycje';
		}
		return 'pozycji';
	}
}
if ( ! function_exists( 'pxc_pliki' ) ) {
	function pxc_pliki( $n ) {
		$n = (int) $n;
		if ( 1 === $n ) {
			return 'plik';
		}
		$mod = $n % 10;
		$mod100 = $n % 100;
		if ( $mod >= 2 && $mod <= 4 && ! ( $mod100 >= 12 && $mod100 <= 14 ) ) {
			return 'pliki';
		}
		return 'plików';
	}
}
?>
<div class="pxc-dash">

  <?php
  if ( $pxc_last ) :
	$li_items = $pxc_last->get_items();
	$li_first = $li_items ? reset( $li_items ) : null;
	$li_prod  = $li_first ? $li_first->get_product() : null;
	$li_thumb = $li_prod ? $li_prod->get_image( 'thumbnail', array( 'class' => 'pxc-tile-thumb-img' ) ) : '';
	$li_count = $pxc_last->get_item_count();
  ?>
  <div class="pxc-tile pxc-tile-order">
    <div class="pxc-tile-thumb"><?php echo $li_thumb; // phpcs:ignore ?></div>
    <div class="pxc-tile-order-body">
      <div class="pxc-tile-lbl">Ostatnie zamówienie</div>
      <div class="pxc-tile-order-row">
        <span class="pxc-tile-onum">#<?php echo esc_html( $pxc_last->get_order_number() ); ?></span>
        <?php echo function_exists( 'prinex_status_chip' ) ? prinex_status_chip( $pxc_last ) : ''; // phpcs:ignore ?>
      </div>
      <div class="pxc-tile-meta">Złożone <strong><?php echo esc_html( wc_format_datetime( $pxc_last->get_date_created() ) ); ?></strong> &middot; <?php echo (int) $li_count . ' ' . esc_html( pxc_pozycje( $li_count ) ); ?> &middot; <strong><?php echo wp_kses_post( wc_price( $pxc_last->get_total() ) ); ?></strong></div>
    </div>
    <div class="pxc-tile-order-actions">
      <a href="<?php echo esc_url( $pxc_last->get_view_order_url() ); ?>" class="pxc-btn-outline">
        <svg viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
        Zobacz
      </a>
      <?php if ( function_exists( 'prinex_order_again_url' ) ) : ?>
        <a href="<?php echo esc_url( prinex_order_again_url( $pxc_last ) ); ?>" class="pxc-btn-sm">Zamów ponownie</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="pxc-dash-2col<?php echo $pxc_missing > 0 ? '' : ' pxc-dash-1'; ?>">
    <div class="pxc-tile pxc-tile-cta">
      <span class="pxc-tile-eyebrow">Gotowy na kolejne?</span>
      <strong class="pxc-tile-cta-h">Złóż nowe zamówienie naklejek 3D Premium</strong>
      <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="pxc-btn-greenpill">
        Do sklepu <span class="pxc-btn-greenpill-cube"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></span>
      </a>
    </div>

    <?php if ( $pxc_missing > 0 && $pxc_missing_order ) : ?>
    <div class="pxc-tile pxc-tile-warn">
      <div class="pxc-tile-warn-ic"><svg viewBox="0 0 24 24"><path d="M12 3l9.5 16.5a1 1 0 0 1-.9 1.5H3.4a1 1 0 0 1-.9-1.5L12 3z"/><path d="M12 9v5"/><path d="M12 17h.01"/></svg></div>
      <strong class="pxc-tile-warn-h">Masz <?php echo (int) $pxc_missing; ?> <?php echo esc_html( pxc_pliki( $pxc_missing ) ); ?> do dostarczenia</strong>
      <p class="pxc-tile-warn-p">Bez plików graficznych nie rozpoczniemy realizacji zamówienia #<?php echo esc_html( $pxc_missing_order->get_order_number() ); ?>.</p>
      <a href="<?php echo esc_url( $pxc_missing_order->get_view_order_url() ); ?>" class="pxc-warn-link">
        <svg viewBox="0 0 24 24"><path d="M12 16V5"/><path d="M7 10l5-5 5 5"/><path d="M4 16v3h16v-3"/></svg>
        Wgraj pliki teraz
      </a>
    </div>
    <?php endif; ?>
  </div>

  <?php do_action( 'woocommerce_account_dashboard' ); ?>
</div>
