<?php
/**
 * PRINEX — Dane konta (dane osobowe + zmiana hasła).
 * Override myaccount/form-edit-account.php.
 *
 * Pola/nonce/akcje natywne WC. Display name ukryty (CSS) — utrzymany dla walidacji WC.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_edit_account_form' );
?>
<form class="woocommerce-EditAccountForm edit-account pxc-form pxc-acc-form" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?>>
  <?php do_action( 'woocommerce_edit_account_form_start' ); ?>

  <div class="pxc-card">
    <h2 class="pxc-card-title">Dane osobowe</h2>
    <div class="pxc-form-grid">
      <p class="pxc-form-row">
        <label for="account_first_name">Imię</label>
        <input type="text" class="input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" />
      </p>
      <p class="pxc-form-row">
        <label for="account_last_name">Nazwisko</label>
        <input type="text" class="input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" />
      </p>
      <p class="pxc-form-row pxc-fr-wide pxc-fr-hidden">
        <label for="account_display_name">Nazwa wyświetlana</label>
        <input type="text" class="input-text" name="account_display_name" id="account_display_name" value="<?php echo esc_attr( $user->display_name ); ?>" />
      </p>
      <p class="pxc-form-row pxc-fr-wide">
        <label for="account_email">Adres e-mail</label>
        <input type="email" class="input-text" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" />
      </p>
    </div>
    <?php do_action( 'woocommerce_edit_account_form_fields' ); ?>
  </div>

  <div class="pxc-card">
    <h2 class="pxc-card-title">Zmiana hasła</h2>
    <div class="pxc-form-grid">
      <p class="pxc-form-row pxc-fr-wide">
        <label for="password_current">Obecne hasło <span class="pxc-lbl-hint">(zostaw puste, by nie zmieniać)</span></label>
        <input type="password" class="input-text" name="password_current" id="password_current" autocomplete="current-password" />
      </p>
      <p class="pxc-form-row">
        <label for="password_1">Nowe hasło</label>
        <input type="password" class="input-text" name="password_1" id="password_1" autocomplete="new-password" />
      </p>
      <p class="pxc-form-row">
        <label for="password_2">Powtórz nowe hasło</label>
        <input type="password" class="input-text" name="password_2" id="password_2" autocomplete="new-password" />
      </p>
    </div>
  </div>

  <?php do_action( 'woocommerce_edit_account_form' ); ?>

  <div class="pxc-form-submit">
    <?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
    <button type="submit" class="pxc-btn-cta" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>">
      <span class="pxc-btn-label">Zapisz zmiany</span>
      <span class="pxc-btn-cube"><svg viewBox="0 0 24 24"><path d="M5 12l5 5 9-10"/></svg></span>
    </button>
    <input type="hidden" name="action" value="save_account_details" />
  </div>

  <?php do_action( 'woocommerce_edit_account_form_end' ); ?>
</form>
<?php
do_action( 'woocommerce_after_edit_account_form' );
