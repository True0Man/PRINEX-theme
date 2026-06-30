<?php
/**
 * PRINEX — Historia zamówień.
 * Override myaccount/orders.php.
 *
 * Statusy kolorowe (prinex_status_chip — snippet #29), "zamów ponownie", stan pusty.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders );

if ( $has_orders ) :
?>
<div class="pxc-orders">
  <div class="pxc-orders-head">
    <span class="pxc-oh-sort">Numer <svg viewBox="0 0 24 24"><path d="M8 9l4-4 4 4M8 15l4 4 4-4"/></svg></span>
    <span class="pxc-oh-sort">Data <svg viewBox="0 0 24 24"><path d="M8 9l4-4 4 4M8 15l4 4 4-4"/></svg></span>
    <span>Status</span>
    <span>Kwota</span>
    <span class="pxc-oh-act"></span>
  </div>

  <?php
  foreach ( $customer_orders->orders as $customer_order ) :
    $order = wc_get_order( $customer_order );
    if ( ! $order ) {
      continue;
    }
    $item_count = $order->get_item_count() - $order->get_item_count_refunded();
  ?>
  <div class="pxc-order-row">
    <span class="pxc-or-num" data-l="Numer"><a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">#<?php echo esc_html( $order->get_order_number() ); ?></a></span>
    <span class="pxc-or-date" data-l="Data"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></span>
    <span class="pxc-or-stat" data-l="Status"><?php echo function_exists( 'prinex_status_chip' ) ? prinex_status_chip( $order ) : esc_html( wc_get_order_status_name( $order->get_status() ) ); // phpcs:ignore ?></span>
    <span class="pxc-or-total" data-l="Kwota"><?php echo wp_kses_post( wc_price( $order->get_total() ) ); ?></span>
    <span class="pxc-or-actions">
      <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="pxc-btn-outline">
        <svg viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
        Zobacz
      </a>
      <?php
      // Faktura — WIZUALNIE wg mockupu; generowanie faktur = Warstwa 2 (brak systemu fakturowania).
      $pxc_inv_url = apply_filters( 'prinex_account_invoice_url', '', $order );
      ?>
      <a href="<?php echo $pxc_inv_url ? esc_url( $pxc_inv_url ) : '#'; ?>" class="pxc-btn-outline pxc-btn-faktura<?php echo $pxc_inv_url ? '' : ' is-soon'; ?>"<?php echo $pxc_inv_url ? '' : ' title="Faktura dostępna wkrótce" aria-disabled="true"'; ?>>
        <svg viewBox="0 0 24 24"><path d="M6 2h9l4 4v16H6z"/><path d="M14 2v5h5"/><path d="M9 13h6M9 17h4"/></svg>
        Faktura
      </a>
      <?php if ( function_exists( 'prinex_order_again_url' ) && in_array( $order->get_status(), array( 'completed', 'processing', 'on-hold', 'pending' ), true ) ) : ?>
        <a href="<?php echo esc_url( prinex_order_again_url( $order ) ); ?>" class="pxc-btn-sm">Zamów ponownie</a>
      <?php endif; ?>
    </span>
  </div>
  <?php endforeach; ?>
</div>

<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
  <div class="pxc-orders-pag">
    <?php if ( 1 !== $current_page ) : ?>
      <a class="pxc-btn-sm" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>">← Poprzednie</a>
    <?php endif; ?>
    <?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
      <a class="pxc-btn-sm" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>">Następne →</a>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php else : ?>

<div class="pxc-empty">
  <div class="pxc-empty-ic"><svg viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg></div>
  <h2>Nie masz jeszcze żadnych zamówień</h2>
  <p>Złóż pierwsze zamówienie — naklejki 3D Premium z wyceną na żywo.</p>
  <a href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>" class="pxc-btn-cta">
    <span class="pxc-btn-label">Przejdź do sklepu</span>
    <span class="pxc-btn-cube"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></span>
  </a>
</div>

<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
