<?php
/**
 * PRINEX — Odzyskiwanie hasła.
 * Override myaccount/form-lost-password.php.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_lost_password_form' );
?>
<div class="pxc-login-wrap">
  <div class="pxc-login-card">
    <h2 class="pxc-login-h">Odzyskiwanie hasła</h2>
    <form method="post" class="woocommerce-ResetPassword lost_reset_password pxc-form">
      <p class="pxc-login-intro">Podaj adres e-mail powiązany z kontem — wyślemy link do ustawienia nowego hasła.</p>

      <p class="pxc-form-row">
        <label for="user_login">Adres e-mail</label>
        <input class="woocommerce-Input input-text" type="text" name="user_login" id="user_login" autocomplete="username" required />
      </p>

      <?php do_action( 'woocommerce_lostpassword_form' ); ?>

      <input type="hidden" name="wc_reset_password" value="true" />
      <button type="submit" class="pxc-btn-cta pxc-btn-full" value="<?php esc_attr_e( 'Reset password', 'woocommerce' ); ?>">
        <span class="pxc-btn-label">Wyślij link</span>
        <span class="pxc-btn-cube"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></span>
      </button>
      <?php wp_nonce_field( 'lost_password' ); ?>

      <p class="pxc-login-back"><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">← Wróć do logowania</a></p>
    </form>
  </div>
</div>
<?php
do_action( 'woocommerce_after_lost_password_form' );
