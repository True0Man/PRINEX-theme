<?php
/**
 * PRINEX — Checkout: podsumowanie (prawa kolumna).
 * Override checkout/review-order.php — to jest fragment AJAX
 * (selektor .woocommerce-checkout-review-order-table, odświeżany przez checkout.js).
 *
 * Zawiera realny selektor metody wysyłki (wc_cart_totals_shipping_html) UKRYTY w CSS,
 * żeby shipping_method[0] zawsze trafił do POST (single free rate). Widoczna "DARMOWA"
 * karta wysyłki jest osobno w lewej kolumnie (form-checkout.php).
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

$netto    = (float) WC()->cart->get_subtotal();
$tax      = (float) WC()->cart->get_total_tax();
$brutto   = $netto + $tax;
$ship_tot = (float) WC()->cart->get_shipping_total();
$ship_free = ( 0.0 === $ship_tot );
?>
<div class="woocommerce-checkout-review-order-table pxc-sum-fragment">

  <div class="pxc-sum-head"><span class="pxc-sum-title">Podsumowanie</span></div>

  <div class="pxc-sum-rows">
    <div class="pxc-sum-row">
      <span class="k">Wartość netto</span>
      <span class="v"><?php echo wp_kses_post( wc_price( $netto ) ); ?></span>
    </div>
    <div class="pxc-sum-row">
      <span class="k">VAT 23%</span>
      <span class="v"><?php echo wp_kses_post( wc_price( $tax ) ); ?></span>
    </div>
    <div class="pxc-sum-row">
      <span class="k">Wartość brutto</span>
      <span class="v"><?php echo wp_kses_post( wc_price( $brutto ) ); ?></span>
    </div>
    <div class="pxc-sum-row">
      <span class="k">Dostawa</span>
      <span class="v"><?php echo $ship_free ? '<span class="pxc-ship-free">DARMOWA</span>' : wp_kses_post( wc_price( $ship_tot ) ); ?></span>
    </div>
  </div>

  <div class="pxc-sum-total">
    <div class="pxc-tlabel">Do zapłaty</div>
    <div class="pxc-tval"><?php echo wp_kses_post( WC()->cart->get_total() ); ?></div>
    <div class="pxc-tsub">brutto, z VAT 23%</div>
  </div>

  <?php /* realny selektor wysyłki — ukryty (CSS), żeby shipping_method[] trafił do POST */ ?>
  <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
    <table class="pxc-hidden-shipping" aria-hidden="true"><tfoot>
      <?php wc_cart_totals_shipping_html(); ?>
    </tfoot></table>
  <?php endif; ?>

</div>
