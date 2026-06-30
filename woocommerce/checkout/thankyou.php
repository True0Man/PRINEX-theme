<?php
/**
 * PRINEX — Ekran "Zamówienie otrzymane" (order-received) w systemie wizualnym PRINEX.
 * Override checkout/thankyou.php.
 *
 * Reużywa komponentów checkoutu (snippet #28): stepper, .pxc-card, .pxc-summary,
 * .pxc-it (pozycje), .pxc-file-status, .pxc-btn-cta. Domyślna tabela zamówienia
 * (woocommerce_order_details_table) jest ODPIĘTA od woocommerce_thankyou w #28 —
 * renderujemy własne sekcje. Hook woocommerce_thankyou nadal odpalamy (kompatybilność
 * z pluginami/analityką). Domyślny blok bacs (woocommerce_thankyou_bacs) POMIJAMY —
 * mamy własny "Dane do przelewu".
 *
 * Status plików per pozycja = meta order item `_prinex_upload_files` / `_prinex_upload_projekt`
 * (przeniesione z koszyka przez inc/prinex-upload.php). Brak ponownego uploadu po zamówieniu
 * (/wgraj-pliki/ działa tylko na żywym koszyku) → dla braków komunikat informacyjny, nie CTA.
 *
 * @package generatepress-child
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_thankyou', $order ? $order->get_id() : 0 );

if ( ! $order ) {
	echo '<section class="pxc-co-wrap"><div class="prinex-container"><div class="pxc-card pxc-ty-fallback"><h1 class="pxc-card-title">Dziękujemy</h1><p>Twoje zamówienie zostało przyjęte.</p></div></div></section>';
	return;
}

$oid = $order->get_id();

if ( $order->has_status( 'failed' ) ) : ?>
	<section class="pxc-co-wrap"><div class="prinex-container">
		<div class="pxc-card pxc-ty-failed">
			<h1 class="pxc-card-title">Płatność nie powiodła się</h1>
			<p>Niestety nie udało się przetworzyć płatności. Spróbuj ponownie.</p>
			<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="pxc-btn-cta"><span class="pxc-btn-label">Ponów płatność</span><span class="pxc-btn-cube"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></span></a>
		</div>
	</div></section>
	<?php
	do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $oid );
	do_action( 'woocommerce_thankyou', $oid );
	return;
endif;

$label_map = [ 'pa_rozmiar' => 'Format', 'pa_naklad' => 'Nakład' ];
$unit_map  = [ 'pa_rozmiar' => ' mm', 'pa_naklad' => '' ];

$netto  = (float) $order->get_subtotal();
$tax    = (float) $order->get_total_tax();
$ship   = (float) $order->get_shipping_total();
$brutto = (float) $order->get_total();

$is_bacs = ( 'bacs' === $order->get_payment_method() );
// Kanoniczne źródło WC = woocommerce_bacs_accounts (to samo, co maile WC); legacy settings jako fallback.
$bacs_accounts = (array) get_option( 'woocommerce_bacs_accounts', [] );
$acc0          = ! empty( $bacs_accounts ) ? (array) $bacs_accounts[0] : [];
$bacs_legacy   = (array) get_option( 'woocommerce_bacs_settings', [] );
$acc_name = $acc0['account_name'] ?? ( $bacs_legacy['account_name'] ?? '' );
$acc_disp = ! empty( $acc0['account_number'] )
	? $acc0['account_number']
	: ( ! empty( $acc0['iban'] ) ? $acc0['iban'] : ( $bacs_legacy['iban'] ?? ( $bacs_legacy['account_number'] ?? '' ) ) );
$bank_nm  = $acc0['bank_name'] ?? ( $bacs_legacy['bank_name'] ?? '' );

$ctype = $order->get_meta( '_billing_customer_type' );
$nip   = $order->get_meta( '_billing_nip' );

$missing = 0;
foreach ( $order->get_items() as $it ) {
	$f  = $it->get_meta( '_prinex_upload_files' );
	$pj = $it->get_meta( '_prinex_upload_projekt' );
	if ( ! ( ( is_array( $f ) && $f ) || '1' === $pj ) ) {
		$missing++;
	}
}
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
        <div class="pxc-step pxc-done"><span class="pxc-dot"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span>Dostawa i płatność</div>
      </nav>
    </div>

    <div class="pxc-ty-hero">
      <div class="pxc-ty-check"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></div>
      <h1>Dziękujemy! Zamówienie przyjęte</h1>
      <p class="pxc-ty-sub">Numer zamówienia <strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong> &middot; <?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></p>
      <p class="pxc-ty-mail">Potwierdzenie wyślemy na <strong><?php echo esc_html( $order->get_billing_email() ); ?></strong>.</p>
    </div>

    <div class="pxc-co-grid">

      <div class="pxc-co-main">

        <!-- CO DALEJ -->
        <div class="pxc-card">
          <h2 class="pxc-card-title">Co dalej?</h2>
          <ol class="pxc-timeline">
            <?php if ( $is_bacs ) : ?>
            <li class="pxc-tl-step"><span class="pxc-tl-num">1</span><div><strong>Opłać zamówienie</strong><span>Przelew na konto (dane obok). W tytule podaj numer zamówienia.</span></div></li>
            <li class="pxc-tl-step"><span class="pxc-tl-num">2</span><div><strong>Weryfikacja plików</strong><span>Sprawdzamy projekt i przygotowanie do druku.</span></div></li>
            <li class="pxc-tl-step"><span class="pxc-tl-num">3</span><div><strong>Produkcja</strong><span>Druk + zalewanie żywicą PU.</span></div></li>
            <li class="pxc-tl-step"><span class="pxc-tl-num">4</span><div><strong>Wysyłka</strong><span>4–5 dni roboczych od akceptacji projektu.</span></div></li>
            <?php else : ?>
            <li class="pxc-tl-step"><span class="pxc-tl-num">1</span><div><strong>Weryfikacja plików</strong><span>Sprawdzamy projekt i przygotowanie do druku.</span></div></li>
            <li class="pxc-tl-step"><span class="pxc-tl-num">2</span><div><strong>Produkcja</strong><span>Druk + zalewanie żywicą PU.</span></div></li>
            <li class="pxc-tl-step"><span class="pxc-tl-num">3</span><div><strong>Wysyłka</strong><span>4–5 dni roboczych od akceptacji projektu.</span></div></li>
            <?php endif; ?>
          </ol>
          <?php if ( $missing > 0 ) : ?>
          <div class="pxc-ty-files-note">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v5"/><path d="M12 16h.01"/></svg>
            <span>Do <strong><?php echo (int) $missing; ?></strong> pozycji nie dołączono jeszcze plików. Skontaktujemy się mailowo, aby je dostarczyć przed produkcją.</span>
          </div>
          <?php endif; ?>
        </div>

        <!-- TWOJE ZAMÓWIENIE -->
        <div class="pxc-card">
          <h2 class="pxc-card-title">Twoje zamówienie</h2>
          <div class="pxc-items">
            <?php
            foreach ( $order->get_items() as $item_id => $item ) :
              $prod   = $item->get_product();
              $parent = wc_get_product( $item->get_product_id() );
              $name   = $parent ? $parent->get_name() : $item->get_name();

              $params_parts = [];
              if ( $prod && $prod->is_type( 'variation' ) ) {
                foreach ( $prod->get_variation_attributes() as $akey => $aval ) {
                  $tax_name = str_replace( 'attribute_', '', $akey );
                  $term     = get_term_by( 'slug', $aval, $tax_name );
                  $tname    = $term ? $term->name : str_replace( '-', ' ', $aval );
                  $dlbl     = $label_map[ $tax_name ] ?? wc_attribute_label( $tax_name );
                  $unit     = $unit_map[ $tax_name ] ?? '';
                  $params_parts[] = esc_html( $dlbl ) . ': ' . esc_html( $tname . $unit );
                }
              }
              $params_html = implode( '<span class="pxc-param-sep"> | </span>', $params_parts );

              $f  = $item->get_meta( '_prinex_upload_files' );
              $pj = $item->get_meta( '_prinex_upload_projekt' );
              $has_files = is_array( $f ) && $f;

              $line_net   = (float) $item->get_total();
              $line_gross = $line_net + (float) $item->get_total_tax();
              $img = $prod ? $prod->get_image( 'thumbnail', [ 'class' => 'pxc-it-img', 'alt' => esc_attr( $name ) ] ) : ( $parent ? $parent->get_image( 'thumbnail', [ 'class' => 'pxc-it-img' ] ) : '' );
            ?>
            <div class="pxc-it">
              <div class="pxc-it-thumb"><?php echo $img; ?></div>
              <div class="pxc-it-info">
                <div class="pxc-it-name"><?php echo esc_html( $name ); ?></div>
                <div class="pxc-it-params"><?php echo $params_html; ?></div>
                <div class="pxc-it-status">
                  <?php if ( '1' === $pj ) : ?>
                    <span class="pxc-file-status pxc-st-ok"><span class="pxc-st-dot"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span>PROJEKT INDYWIDUALNY</span>
                  <?php elseif ( $has_files ) : ?>
                    <span class="pxc-file-status pxc-st-ok"><span class="pxc-st-dot"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span>DODANO PLIKI</span>
                  <?php else : ?>
                    <span class="pxc-file-status pxc-st-warn">○ pliki do dostarczenia</span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="pxc-it-price">
                <span class="pxc-it-net"><?php echo wp_strip_all_tags( wc_price( $line_net ) ); ?>&nbsp;<span class="pxc-it-net-lbl">netto</span></span>
                <span class="pxc-it-gross"><?php echo wp_strip_all_tags( wc_price( $line_gross ) ); ?> brutto</span>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- DANE ODBIORCY -->
        <div class="pxc-card">
          <h2 class="pxc-card-title">Dane odbiorcy</h2>
          <div class="pxc-rs-name"><?php echo esc_html( trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) ); ?></div>
          <?php if ( 'firma' === $ctype && $order->get_billing_company() ) : ?>
            <div class="pxc-rs-addr"><?php echo esc_html( $order->get_billing_company() ); ?><?php echo $nip ? ' &middot; NIP ' . esc_html( $nip ) : ''; ?></div>
          <?php endif; ?>
          <div class="pxc-rs-addr">
            <?php
            echo esc_html( implode( ', ', array_filter( [
              $order->get_billing_address_1(),
              trim( $order->get_billing_postcode() . ' ' . $order->get_billing_city() ),
              $order->get_billing_phone(),
            ] ) ) );
            ?>
          </div>
          <div class="pxc-rs-addr"><?php echo esc_html( $order->get_billing_email() ); ?></div>
        </div>

        <div class="pxc-ty-cta-row">
          <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="pxc-btn-cta">
            <span class="pxc-btn-label">Wróć do sklepu</span>
            <span class="pxc-btn-cube"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></span>
          </a>
          <?php if ( is_user_logged_in() ) : ?>
            <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="pxc-ty-link-acc">Moje zamówienia</a>
          <?php endif; ?>
        </div>

      </div><!-- /pxc-co-main -->

      <aside class="pxc-co-aside">
        <div class="pxc-summary">
          <div class="pxc-sum-head"><span class="pxc-sum-title">Podsumowanie</span></div>
          <div class="pxc-sum-rows">
            <div class="pxc-sum-row"><span class="k">Wartość netto</span><span class="v"><?php echo wp_kses_post( wc_price( $netto ) ); ?></span></div>
            <div class="pxc-sum-row"><span class="k">VAT 23%</span><span class="v"><?php echo wp_kses_post( wc_price( $tax ) ); ?></span></div>
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

          <?php if ( $is_bacs ) : ?>
          <div class="pxc-ty-bank">
            <div class="pxc-ty-bank-title">Dane do przelewu</div>
            <div class="pxc-ty-bank-row"><span>Kwota</span><strong><?php echo wp_kses_post( wc_price( $brutto ) ); ?></strong></div>
            <div class="pxc-ty-bank-row"><span>Odbiorca</span><strong><?php echo $acc_name ? esc_html( $acc_name ) : '<span class="pxc-ty-todo">[nazwa odbiorcy — do uzupełnienia]</span>'; ?></strong></div>
            <div class="pxc-ty-bank-row"><span>Nr konta</span><strong><?php echo $acc_disp ? esc_html( $acc_disp ) : '<span class="pxc-ty-todo">[numer konta — do uzupełnienia]</span>'; ?></strong></div>
            <?php if ( $bank_nm ) : ?><div class="pxc-ty-bank-row"><span>Bank</span><strong><?php echo esc_html( $bank_nm ); ?></strong></div><?php endif; ?>
            <div class="pxc-ty-bank-row"><span>Tytuł przelewu</span><strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong></div>
          </div>
          <?php endif; ?>

          <div class="pxc-cs-trust">
            <svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
            Bezpieczne zakupy
          </div>
        </div>
      </aside>

    </div><!-- /pxc-co-grid -->

  </div>
</section>
<?php get_template_part( 'inc/prinex-trust' ); ?>
<?php
// Kompatybilność: bramki inne niż bacs renderują swój blok; bacs pomijamy (mamy własny).
if ( ! $is_bacs ) {
	do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $oid );
}
// woocommerce_order_details_table odpięte od tego hooka w snippecie #28 (brak dubla tabeli).
do_action( 'woocommerce_thankyou', $oid );
