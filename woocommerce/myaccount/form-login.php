<?php
/**
 * PRINEX — Logowanie / rejestracja (Model C: logowanie główne, rejestracja subtelna).
 * Override myaccount/form-login.php.
 *
 * Taby Logowanie / Nowe konto (JS w snippecie #29). "Zapamiętaj mnie" DOMYŚLNIE OFF.
 * Przyciski social = WARSTWA wizualna (OAuth = Warstwa 2a). Hooki WC zachowane
 * (woocommerce_login_form_*, woocommerce_register_form_*) dla kompatybilności z wtyczką OAuth.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_customer_login_form' );

$pxc_reg_on  = ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) );
$pxc_gen_usr = ( 'yes' === get_option( 'woocommerce_registration_generate_username' ) );
$pxc_gen_pwd = ( 'yes' === get_option( 'woocommerce_registration_generate_password' ) );
?>
<div class="pxc-login-wrap">
  <div class="pxc-login-card" id="pxc-login-card">

    <?php if ( $pxc_reg_on ) : ?>
    <div class="pxc-login-tabs" role="tablist">
      <button type="button" class="pxc-login-tab is-active" data-tab="login" role="tab">Logowanie</button>
      <button type="button" class="pxc-login-tab" data-tab="register" role="tab">Nowe konto</button>
    </div>
    <?php endif; ?>

    <!-- PANEL LOGOWANIE -->
    <div class="pxc-login-panel" data-panel="login">
      <p class="pxc-login-intro">Witaj ponownie — zaloguj się do swojego konta.</p>

      <form class="woocommerce-form woocommerce-form-login login pxc-form" method="post" novalidate>
        <?php do_action( 'woocommerce_login_form_start' ); ?>

        <p class="pxc-form-row">
          <label for="username">Adres e-mail</label>
          <input type="text" class="woocommerce-Input input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required />
        </p>
        <p class="pxc-form-row">
          <label for="password">Hasło</label>
          <input class="woocommerce-Input input-text" type="password" name="password" id="password" autocomplete="current-password" required />
        </p>

        <?php do_action( 'woocommerce_login_form' ); ?>

        <div class="pxc-login-meta">
          <label class="pxc-check-inline">
            <input class="woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
            <span class="pxc-checkbox-box"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span>
            <span>Zapamiętaj mnie</span>
          </label>
          <a class="pxc-login-lost" href="<?php echo esc_url( wp_lostpassword_url() ); ?>">Nie pamiętasz hasła?</a>
        </div>

        <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
        <button type="submit" class="pxc-btn-cta pxc-btn-full" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>">
          <span class="pxc-btn-label">Zaloguj się</span>
          <span class="pxc-btn-cube"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></span>
        </button>

        <?php do_action( 'woocommerce_login_form_end' ); ?>
      </form>

      <div class="pxc-social">
        <div class="pxc-social-sep"><span>lub kontynuuj przez</span></div>
        <div class="pxc-social-btns">
          <button type="button" class="pxc-social-btn" data-social="facebook" aria-label="Zaloguj przez Facebook">
            <svg viewBox="0 0 24 24" fill="#1877F2"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.2c-1.2 0-1.6.8-1.6 1.5V12h2.7l-.4 2.9h-2.3v7A10 10 0 0 0 22 12z"/></svg>
            Facebook
          </button>
          <button type="button" class="pxc-social-btn" data-social="google" aria-label="Zaloguj przez Google">
            <svg viewBox="0 0 24 24"><path fill="#4285F4" d="M21.8 12.2c0-.7-.1-1.4-.2-2H12v3.8h5.5a4.7 4.7 0 0 1-2 3.1v2.6h3.2c1.9-1.7 3-4.3 3-7.5z"/><path fill="#34A853" d="M12 22c2.7 0 5-.9 6.7-2.4l-3.2-2.6c-.9.6-2 1-3.5 1-2.7 0-5-1.8-5.8-4.3H2.9v2.7A10 10 0 0 0 12 22z"/><path fill="#FBBC05" d="M6.2 13.7a6 6 0 0 1 0-3.8V7.2H2.9a10 10 0 0 0 0 9z"/><path fill="#EA4335" d="M12 5.9c1.5 0 2.8.5 3.8 1.5l2.8-2.8A10 10 0 0 0 2.9 7.2L6.2 9.9C7 7.6 9.3 5.9 12 5.9z"/></svg>
            Google
          </button>
        </div>
      </div>
    </div>

    <?php if ( $pxc_reg_on ) : ?>
    <!-- PANEL NOWE KONTO -->
    <div class="pxc-login-panel" data-panel="register" hidden>
      <p class="pxc-login-intro">Nowy w PRINEX? Konto przyspieszy kolejne zamówienia.</p>

      <form method="post" class="woocommerce-form woocommerce-form-register register pxc-form" <?php do_action( 'woocommerce_register_form_tag' ); ?>>
        <?php do_action( 'woocommerce_register_form_start' ); ?>

        <?php if ( ! $pxc_gen_usr ) : ?>
        <p class="pxc-form-row">
          <label for="reg_username">Nazwa użytkownika</label>
          <input type="text" class="woocommerce-Input input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required />
        </p>
        <?php endif; ?>

        <p class="pxc-form-row">
          <label for="reg_email">Adres e-mail</label>
          <input type="email" class="woocommerce-Input input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required />
        </p>

        <?php if ( ! $pxc_gen_pwd ) : ?>
        <p class="pxc-form-row">
          <label for="reg_password">Hasło</label>
          <input type="password" class="woocommerce-Input input-text" name="password" id="reg_password" autocomplete="new-password" required />
        </p>
        <?php else : ?>
        <p class="pxc-login-note">Link do ustawienia hasła wyślemy na podany adres e-mail.</p>
        <?php endif; ?>

        <?php do_action( 'woocommerce_register_form' ); ?>

        <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
        <button type="submit" class="pxc-btn-cta pxc-btn-full" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">
          <span class="pxc-btn-label">Załóż konto</span>
          <span class="pxc-btn-cube"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></span>
        </button>

        <?php do_action( 'woocommerce_register_form_end' ); ?>
      </form>
    </div>
    <?php endif; ?>

  </div>
</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
