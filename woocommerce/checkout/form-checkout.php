<?php
/**
 * PRINEX — Checkout (Dostawa i płatność) — classic [woocommerce_checkout] override.
 * Layout 1:1 wg mockupu 04-mockupy/06-zamowienie/mockup-2.png.
 *
 * Mechanika WC nietknięta:
 *  - <form name="checkout" class="checkout woocommerce-checkout"> verbatim (checkout.js selektor).
 *  - Payment renderowany w LEWEJ kolumnie przez woocommerce_checkout_payment()
 *    (hook ...order_review/woocommerce_checkout_payment ZDJĘTY w snippecie #28),
 *    AJAX nadal odświeża .woocommerce-checkout-payment w miejscu.
 *  - #order_review (review-order.php) = nasze podsumowanie w PRAWEJ kolumnie (fragment AJAX).
 *  - terms + #place_order + nonce = w prawym podsumowaniu, POZA fragmentem (stabilne po AJAX).
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

/* ── dane do listy "Twoje naklejki" (ten sam mechanizm co koszyk) ── */
$label_map = [ 'pa_rozmiar' => 'Format', 'pa_naklad' => 'Nakład' ];
$unit_map  = [ 'pa_rozmiar' => ' mm', 'pa_naklad' => '' ];

/* ── etykieta wybranej metody wysyłki (prezentacyjna karta "Opcje wysyłki") ── */
$ship_label = 'Dostawa';
$ship_free  = false;
foreach ( WC()->shipping()->get_packages() as $pkg ) {
	$chosen = WC()->session->get( 'chosen_shipping_methods' );
	foreach ( $pkg['rates'] as $rid => $rate ) {
		$is_chosen = is_array( $chosen ) && in_array( $rid, $chosen, true );
		if ( $is_chosen || ! $ship_free ) {
			$ship_label = $rate->get_label();
			$ship_free  = ( 0.0 === (float) $rate->get_cost() );
		}
	}
}

/* ── adres czy strona płatności? bramki tylko gdy needs_payment ── */
?>

<section class="pxc-co-wrap">
  <div class="prinex-container">

    <div class="pxc-steps-outer">
      <nav class="pxc-steps" aria-label="Etapy zamówienia">
        <div class="pxc-step pxc-done"><span class="pxc-dot"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span>Produkt</div>
        <span class="pxc-step-line pxc-done"></span>
        <div class="pxc-step pxc-done"><span class="pxc-dot"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span>Pliki</div>
        <span class="pxc-step-line pxc-done"></span>
        <div class="pxc-step pxc-done"><span class="pxc-dot"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span>Koszyk</div>
        <span class="pxc-step-line pxc-done"></span>
        <div class="pxc-step pxc-now" aria-current="step"><span class="pxc-dot">4</span>Dostawa i płatność</div>
      </nav>
    </div>

    <div class="pxc-head">
      <div class="pxc-sig"></div>
      <h1>Dostawa i płatność</h1>
    </div>

    <form name="checkout" method="post" class="checkout woocommerce-checkout pxc-co-form" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

      <div class="pxc-co-grid">

        <div class="pxc-co-main">

          <?php if ( $checkout->get_checkout_fields() ) : ?>
            <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

            <!-- DANE ODBIORCY (form-billing.php) -->
            <?php do_action( 'woocommerce_checkout_billing' ); ?>

            <!-- checkbox: ten sam adres do rozliczeń (prezentacyjny, domyślnie ON) -->
            <label class="pxc-same-addr">
              <input type="checkbox" id="pxc-same-addr" checked>
              <span class="pxc-checkbox-box"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span>
              <span class="pxc-same-addr-txt">Użyj tego samego adresu do rozliczeń płatności</span>
            </label>

            <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
          <?php endif; ?>

          <!-- OPCJE WYSYŁKI (prezentacyjna; realny radio jest ukryty w review-order.php) -->
          <div class="pxc-card pxc-ship-card">
            <h2 class="pxc-card-title">Opcje wysyłki</h2>
            <div class="pxc-opt-row pxc-opt-selected">
              <span class="pxc-opt-check"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span>
              <span class="pxc-opt-name"><?php echo esc_html( $ship_label ); ?></span>
              <span class="pxc-opt-right"><?php echo $ship_free ? '<span class="pxc-free-lbl">BEZPŁATNIE</span>' : esc_html( wp_strip_all_tags( wc_price( 0 ) ) ); ?></span>
            </div>
          </div>

          <!-- TWOJE NAKLEJKI (statyczna lista; status plików = ten sam mechanizm co koszyk) -->
          <div class="pxc-card pxc-items-card">
            <h2 class="pxc-card-title">Twoje naklejki</h2>
            <div class="pxc-items">
              <?php
              foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
                $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                if ( ! $_product || ! $_product->exists() || 0 === $cart_item['quantity'] ) {
                  continue;
                }
                $parent = wc_get_product( $cart_item['product_id'] );
                $name   = $parent ? $parent->get_name() : $_product->get_name();

                $params_parts = [];
                if ( ! empty( $cart_item['variation'] ) ) {
                  foreach ( $cart_item['variation'] as $attr_key => $attr_val ) {
                    $tax   = str_replace( 'attribute_', '', $attr_key );
                    $term  = get_term_by( 'slug', $attr_val, $tax );
                    $tname = $term ? $term->name : str_replace( '-', ' ', $attr_val );
                    $unit  = $unit_map[ $tax ] ?? '';
                    $dlbl  = $label_map[ $tax ] ?? wc_attribute_label( $tax, $_product );
                    $params_parts[] = esc_html( $dlbl ) . ': ' . esc_html( $tname . $unit );
                  }
                }
                $params_html = implode( '<span class="pxc-param-sep"> | </span>', $params_parts );

                $has_files  = ! empty( $cart_item['prinex_upload_files'] ) && is_array( $cart_item['prinex_upload_files'] );
                $is_projekt = isset( $cart_item['prinex_upload_projekt'] ) && '1' === $cart_item['prinex_upload_projekt'];

                $line_netto  = (float) $cart_item['line_total'];
                $line_tax    = isset( $cart_item['line_tax'] ) ? (float) $cart_item['line_tax'] : $line_netto * 0.23;
                $line_brutto = $line_netto + $line_tax;
              ?>
              <div class="pxc-it">
                <div class="pxc-it-thumb">
                  <?php if ( $_product->get_image_id() ) { echo $_product->get_image( 'thumbnail', [ 'alt' => esc_attr( $name ), 'class' => 'pxc-it-img' ] ); } ?>
                </div>
                <div class="pxc-it-info">
                  <div class="pxc-it-name"><?php echo esc_html( $name ); ?></div>
                  <div class="pxc-it-params"><?php echo $params_html; ?></div>
                  <div class="pxc-it-status">
                    <?php if ( $is_projekt ) : ?>
                      <span class="pxc-file-status pxc-st-ok"><span class="pxc-st-dot"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span>PROJEKT INDYWIDUALNY</span>
                    <?php elseif ( $has_files ) : ?>
                      <span class="pxc-file-status pxc-st-ok"><span class="pxc-st-dot"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span>DODANO PLIKI</span>
                    <?php else : ?>
                      <?php $upload_url = add_query_arg( 'cart_key', $cart_item_key, get_permalink( 170 ) ); ?>
                      <a href="<?php echo esc_url( $upload_url ); ?>" class="pxc-upload-btn">
                        <svg viewBox="0 0 24 24"><path d="M12 16V5"/><path d="M7 10l5-5 5 5"/><path d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                        Wgraj pliki
                      </a>
                      <span class="pxc-meta-sep"> | </span>
                      <span class="pxc-file-status pxc-st-warn">○ pliki później</span>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="pxc-it-price">
                  <span class="pxc-it-net"><?php echo wp_strip_all_tags( wc_price( $line_netto ) ); ?>&nbsp;<span class="pxc-it-net-lbl">netto</span></span>
                  <span class="pxc-it-gross"><?php echo wp_strip_all_tags( wc_price( $line_brutto ) ); ?> brutto</span>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- OPCJE PŁATNOŚCI (bramki; place_order/terms NIE tutaj — payment.php override) -->
          <div class="pxc-card pxc-pay-card">
            <h2 class="pxc-card-title">Opcje płatności</h2>
            <?php woocommerce_checkout_payment(); ?>
          </div>

          <!-- DODAJ NOTATKĘ -->
          <div class="pxc-card pxc-note-card">
            <label class="pxc-note-toggle">
              <input type="checkbox" id="pxc-note-toggle">
              <span class="pxc-checkbox-box"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span>
              <span>Dodaj notatkę do zamówienia</span>
            </label>
            <div class="pxc-note-body" id="pxc-note-body" hidden>
              <textarea name="order_comments" id="order_comments" class="pxc-note-area" rows="3" placeholder="Uwagi do zamówienia, np. szczegóły dostawy."></textarea>
            </div>
          </div>

        </div><!-- /pxc-co-main -->

        <aside class="pxc-co-aside">
          <div class="pxc-summary">

            <div id="order_review" class="woocommerce-checkout-review-order">
              <?php do_action( 'woocommerce_checkout_order_review' ); ?>
            </div>

            <div class="pxc-terms">
              <label class="pxc-terms-label">
                <input type="checkbox" name="terms" id="terms" class="pxc-terms-cb">
                <span class="pxc-checkbox-box"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span>
                <span class="pxc-terms-txt">Akceptuję <a href="<?php echo esc_url( wc_get_page_permalink( 'terms' ) ? wc_get_page_permalink( 'terms' ) : '#' ); ?>" target="_blank" rel="noopener">Warunki i zasady</a> oraz <a href="<?php echo esc_url( get_privacy_policy_url() ? get_privacy_policy_url() : '#' ); ?>" target="_blank" rel="noopener">Politykę prywatności</a></span>
              </label>
              <input type="hidden" name="terms-field" value="1">
            </div>

            <?php echo apply_filters(
              'woocommerce_order_button_html',
              '<button type="submit" class="pxc-btn-cta pxc-btn-full" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr__( 'Place order', 'woocommerce' ) . '" data-value="' . esc_attr__( 'Place order', 'woocommerce' ) . '" disabled aria-disabled="true">'
                . '<span class="pxc-btn-label">Kupuję i płacę</span>'
                . '<span class="pxc-btn-cube"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></span>'
              . '</button>'
            ); ?>

            <div class="pxc-cs-trust">
              <svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
              Bezpieczne zakupy
            </div>
            <div class="pxc-cs-pay">
              <span class="pxc-pay-badge">BLIK</span>
              <span class="pxc-pay-badge">Przelewy24</span>
              <span class="pxc-pay-badge">PayU</span>
            </div>

          </div>
        </aside>

      </div><!-- /pxc-co-grid -->

      <?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
    </form>

  </div>
</section>
<?php get_template_part( 'inc/prinex-trust' ); ?>
<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
