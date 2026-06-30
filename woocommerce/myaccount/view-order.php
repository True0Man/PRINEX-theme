<?php
/**
 * PRINEX — Widok pojedynczego zamówienia — 1:1 wg mockup-konto-1 (ekran 4).
 * Override myaccount/view-order.php.
 *
 * Pozycje + status plików; OSOBNE karty: pozycje / adresy / Podsumowanie (z Wartością brutto)
 * / Dane do przelewu (etykieta-nad-wartością + KOPIUJ). Domyślna tabela odpięta w #29.
 *
 * @package generatepress-child
 * @var WC_Order $order
 * @var int $order_id
 */

defined( 'ABSPATH' ) || exit;

if ( ! $order ) {
	return;
}

$label_map = array( 'pa_rozmiar' => '', 'pa_naklad' => '' );
$unit_map  = array( 'pa_rozmiar' => ' mm', 'pa_naklad' => '' );

$netto  = (float) $order->get_subtotal();
$tax    = (float) $order->get_total_tax();
$ship   = (float) $order->get_shipping_total();
$brutto = (float) $order->get_total();

$status    = $order->get_status();
$awaiting  = in_array( $status, array( 'pending', 'on-hold' ), true );
$is_bacs   = ( 'bacs' === $order->get_payment_method() );
$show_bank = $awaiting && $is_bacs;

$ctype = $order->get_meta( '_billing_customer_type' );
$nip   = $order->get_meta( '_billing_nip' );

$bacs_accounts = (array) get_option( 'woocommerce_bacs_accounts', array() );
$acc0          = ! empty( $bacs_accounts ) ? (array) $bacs_accounts[0] : array();
$bacs_legacy   = (array) get_option( 'woocommerce_bacs_settings', array() );
$acc_name = $acc0['account_name'] ?? ( $bacs_legacy['account_name'] ?? '' );
$acc_disp = ! empty( $acc0['account_number'] ) ? $acc0['account_number'] : ( ! empty( $acc0['iban'] ) ? $acc0['iban'] : ( $bacs_legacy['iban'] ?? ( $bacs_legacy['account_number'] ?? '' ) ) );
$acc_copy = preg_replace( '/\s+/', '', $acc_disp );

$can_again = in_array( $status, array( 'completed', 'processing', 'on-hold', 'pending' ), true );
?>
<div class="pxc-vo">

  <div class="pxc-vo-top">
    <div class="pxc-vo-onum">
      <span class="pxc-vo-onum-h">Zamówienie #<?php echo esc_html( $order->get_order_number() ); ?></span>
      <?php echo function_exists( 'prinex_status_chip' ) ? prinex_status_chip( $order ) : ''; // phpcs:ignore ?>
    </div>
    <div class="pxc-vo-date">Złożone <?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></div>
  </div>

  <div class="pxc-vo-grid">

    <div class="pxc-vo-main">
      <div class="pxc-card">
        <div class="pxc-items">
          <?php
          foreach ( $order->get_items() as $item_id => $item ) :
            $prod   = $item->get_product();
            $parent = wc_get_product( $item->get_product_id() );
            $name   = $parent ? $parent->get_name() : $item->get_name();

            $params_parts = array();
            if ( $prod && $prod->is_type( 'variation' ) ) {
              foreach ( $prod->get_variation_attributes() as $akey => $aval ) {
                $tax_name = str_replace( 'attribute_', '', $akey );
                $term     = get_term_by( 'slug', $aval, $tax_name );
                $tname    = $term ? $term->name : str_replace( '-', ' ', $aval );
                $unit     = $unit_map[ $tax_name ] ?? '';
                $params_parts[] = esc_html( $tname . $unit );
              }
            }
            $params_html = implode( '<span class="pxc-param-sep"> &middot; </span>', $params_parts );

            $f  = $item->get_meta( '_prinex_upload_files' );
            $pj = $item->get_meta( '_prinex_upload_projekt' );
            $has_files = is_array( $f ) && $f;

            $line_net   = (float) $item->get_total();
            $line_gross = $line_net + (float) $item->get_total_tax();
            $img = $prod ? $prod->get_image( 'thumbnail', array( 'class' => 'pxc-it-img', 'alt' => esc_attr( $name ) ) ) : ( $parent ? $parent->get_image( 'thumbnail', array( 'class' => 'pxc-it-img' ) ) : '' );
          ?>
          <div class="pxc-it">
            <div class="pxc-it-thumb"><?php echo $img; // phpcs:ignore ?></div>
            <div class="pxc-it-info">
              <div class="pxc-it-name"><?php echo esc_html( $name ); ?></div>
              <div class="pxc-it-params"><?php echo $params_html; // phpcs:ignore ?></div>
              <div class="pxc-it-status">
                <?php if ( '1' === $pj ) : ?>
                  <span class="pxc-fstat pxc-fstat-ok"><span class="pxc-fstat-ic"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span>Projekt indywidualny</span>
                <?php elseif ( $has_files ) : ?>
                  <span class="pxc-fstat pxc-fstat-ok"><span class="pxc-fstat-ic"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span>Dodano pliki</span>
                <?php else : ?>
                  <span class="pxc-fstat pxc-fstat-warn"><span class="pxc-fstat-circ"></span>Pliki do dostarczenia</span>
                <?php endif; ?>
              </div>
            </div>
            <div class="pxc-it-price">
              <span class="pxc-it-net"><?php echo wp_strip_all_tags( wc_price( $line_net ) ); ?></span>
              <span class="pxc-it-gross"><?php echo wp_strip_all_tags( wc_price( $line_gross ) ); ?> brutto</span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="pxc-card pxc-vo-addrcard">
        <div class="pxc-vo-addr">
          <div class="pxc-vo-addr-col">
            <div class="pxc-vo-addr-lbl">Dane odbiorcy</div>
            <div class="pxc-rs-name"><?php echo esc_html( trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) ); ?></div>
            <?php if ( 'firma' === $ctype && $order->get_billing_company() ) : ?>
              <div class="pxc-rs-addr"><?php echo esc_html( $order->get_billing_company() ); ?></div>
              <?php if ( $nip ) : ?><div class="pxc-rs-addr">NIP: <?php echo esc_html( $nip ); ?></div><?php endif; ?>
            <?php endif; ?>
            <div class="pxc-rs-addr"><?php echo esc_html( $order->get_billing_email() ); ?></div>
            <?php if ( $order->get_billing_phone() ) : ?><div class="pxc-rs-addr"><?php echo esc_html( $order->get_billing_phone() ); ?></div><?php endif; ?>
          </div>
          <div class="pxc-vo-addr-col">
            <div class="pxc-vo-addr-lbl">Adres dostawy</div>
            <div class="pxc-rs-addr"><?php echo wp_kses_post( $order->get_formatted_shipping_address() ? $order->get_formatted_shipping_address() : $order->get_formatted_billing_address() ); ?></div>
          </div>
        </div>
      </div>

      <?php if ( $can_again && function_exists( 'prinex_order_again_url' ) ) : ?>
      <a href="<?php echo esc_url( prinex_order_again_url( $order ) ); ?>" class="pxc-btn-cta">
        <span class="pxc-btn-label">Zamów ponownie</span>
        <span class="pxc-btn-cube"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></span>
      </a>
      <?php endif; ?>
    </div>

    <aside class="pxc-vo-aside">
      <div class="pxc-card pxc-vo-sum">
        <div class="pxc-sum-head"><span class="pxc-sum-title">Podsumowanie</span></div>
        <div class="pxc-sum-rows">
          <div class="pxc-sum-row"><span class="k">Wartość netto</span><span class="v"><?php echo wp_kses_post( wc_price( $netto ) ); ?></span></div>
          <div class="pxc-sum-row"><span class="k">VAT 23%</span><span class="v"><?php echo wp_kses_post( wc_price( $tax ) ); ?></span></div>
          <div class="pxc-sum-row"><span class="k">Wartość brutto</span><span class="v"><?php echo wp_kses_post( wc_price( $brutto ) ); ?></span></div>
          <div class="pxc-sum-row"><span class="k">Dostawa</span><span class="v"><?php echo 0.0 === $ship ? '<span class="pxc-ship-free">DARMOWA</span>' : wp_kses_post( wc_price( $ship ) ); ?></span></div>
        </div>
        <div class="pxc-sum-total">
          <div class="pxc-tlabel">Do zapłaty</div>
          <div class="pxc-tval"><?php echo wp_kses_post( wc_price( $brutto ) ); ?></div>
          <div class="pxc-tsub">brutto, z VAT 23%</div>
        </div>
        <div class="pxc-ty-pay">
          <span class="pxc-ty-pay-k">Metoda płatności</span>
          <span class="pxc-ty-pay-v"><?php echo esc_html( $order->get_payment_method_title() ); ?></span>
        </div>
      </div>

      <?php if ( $show_bank ) : ?>
      <div class="pxc-card pxc-vo-bank">
        <div class="pxc-card-title pxc-bank-title">Dane do przelewu</div>
        <div class="pxc-bank-field">
          <span class="pxc-bank-lbl">Odbiorca</span>
          <div class="pxc-bank-val"><strong><?php echo $acc_name ? esc_html( $acc_name ) : '<span class="pxc-ty-todo">[do uzupełnienia]</span>'; ?></strong></div>
        </div>
        <div class="pxc-bank-field">
          <span class="pxc-bank-lbl">Numer konta</span>
          <div class="pxc-bank-val">
            <strong><?php echo $acc_disp ? esc_html( $acc_disp ) : '<span class="pxc-ty-todo">[do uzupełnienia]</span>'; ?></strong>
            <?php if ( $acc_copy ) : ?><button type="button" class="pxc-copy" data-copy="<?php echo esc_attr( $acc_copy ); ?>" aria-label="Kopiuj numer konta"><svg viewBox="0 0 24 24" width="17" height="17"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg></button><?php endif; ?>
          </div>
        </div>
        <div class="pxc-bank-field">
          <span class="pxc-bank-lbl">Tytuł przelewu</span>
          <div class="pxc-bank-val">
            <strong>Zamówienie #<?php echo esc_html( $order->get_order_number() ); ?></strong>
            <button type="button" class="pxc-copy" data-copy="Zamówienie #<?php echo esc_attr( $order->get_order_number() ); ?>" aria-label="Kopiuj tytuł"><svg viewBox="0 0 24 24" width="17" height="17"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg></button>
          </div>
        </div>
        <div class="pxc-bank-field">
          <span class="pxc-bank-lbl">Kwota</span>
          <div class="pxc-bank-val">
            <strong><?php echo wp_kses_post( wc_price( $brutto ) ); ?></strong>
            <button type="button" class="pxc-copy" data-copy="<?php echo esc_attr( number_format( $brutto, 2, '.', '' ) ); ?>" aria-label="Kopiuj kwotę"><svg viewBox="0 0 24 24" width="17" height="17"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg></button>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </aside>

  </div>
</div>
<?php
do_action( 'woocommerce_view_order', $order_id );
