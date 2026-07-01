<?php
/**
 * PRINEX — Faktura na inne dane (Warstwa 2d).
 * Scope: global (ma handlery wp_ajax + hooki checkout).
 *
 * Dwa miejsca:
 *  A) KONTO ("Dane do wysyłki"): sekcja "Faktura" — checkbox "Chcę fakturę na inne dane
 *     niż adres dostawy" + dane faktury; zapis AJAX do user_meta _prinex_invoice_data.
 *  B) CHECKOUT: DODATEK przez hook (woocommerce_checkout_after_customer_details) — ten sam
 *     checkbox + pola; zapis danych faktury do meta ZAMÓWIENIA przy tworzeniu.
 *
 * DYSCYPLINA #28 (nietykalna warstwa): B to DODATEK, NIE zmiana rdzenia. Pola/walidacja/zapis
 * billing #28 bez zmian. Nowa walidacja (NIP faktury) i zapis meta faktury odpalają się TYLKO
 * gdy checkbox zaznaczony → domyślny checkout (bez faktury) IDENTYCZNY (parytet process_checkout).
 *
 * BEZPIECZEŃSTWO: nonce (prinex_addr) na AJAX; operacje na user_meta bieżącego usera;
 * sanityzacja wejścia + escaping wyjścia; NIP prinex_nip_checksum_valid (2e); user_meta (HPOS-safe).
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

/* ─────────────────────────────── MODEL ─────────────────────────────── */

function prinex_invoice_defaults() {
	return array( 'enabled' => false, 'type' => 'firma', 'company' => '', 'nip' => '', 'first_name' => '', 'last_name' => '', 'address_1' => '', 'address_2' => '', 'postcode' => '', 'city' => '', 'country' => 'PL' );
}
function prinex_get_invoice( $uid ) {
	$d = get_user_meta( (int) $uid, '_prinex_invoice_data', true );
	return is_array( $d ) ? array_merge( prinex_invoice_defaults(), $d ) : prinex_invoice_defaults();
}
function prinex_save_invoice( $uid, $arr ) {
	update_user_meta( (int) $uid, '_prinex_invoice_data', $arr );
}
function prinex_sanitize_invoice( $in ) {
	$type = ( isset( $in['type'] ) && 'osoba' === $in['type'] ) ? 'osoba' : 'firma';
	return array(
		'enabled'    => ! empty( $in['enabled'] ),
		'type'       => $type,
		'company'    => 'firma' === $type ? sanitize_text_field( $in['company'] ?? '' ) : '',
		'nip'        => 'firma' === $type ? prinex_nip_normalize( $in['nip'] ?? '' ) : '',
		'first_name' => sanitize_text_field( $in['first_name'] ?? '' ),
		'last_name'  => sanitize_text_field( $in['last_name'] ?? '' ),
		'address_1'  => sanitize_text_field( $in['address_1'] ?? '' ),
		'address_2'  => sanitize_text_field( $in['address_2'] ?? '' ),
		'postcode'   => sanitize_text_field( $in['postcode'] ?? '' ),
		'city'       => sanitize_text_field( $in['city'] ?? '' ),
		'country'    => ( isset( $in['country'] ) && preg_match( '/^[A-Z]{2}$/', $in['country'] ) ) ? $in['country'] : 'PL',
	);
}
/** Walidacja danych faktury — tylko gdy enabled. Zwraca mapę pole=>komunikat. */
function prinex_validate_invoice( $inv, $prefix = 'invoice_' ) {
	$err = array();
	if ( empty( $inv['enabled'] ) ) {
		return $err;
	}
	if ( 'firma' === $inv['type'] ) {
		if ( '' === trim( $inv['company'] ) )            { $err[ $prefix . 'company' ] = 'Podaj nazwę firmy do faktury.'; }
		if ( ! prinex_nip_checksum_valid( $inv['nip'] ) ) { $err[ $prefix . 'nip' ]    = 'Nieprawidłowy NIP do faktury (10 cyfr + suma kontrolna).'; }
	} else {
		if ( '' === trim( $inv['first_name'] ) ) { $err[ $prefix . 'first_name' ] = 'Podaj imię do faktury.'; }
		if ( '' === trim( $inv['last_name'] ) )  { $err[ $prefix . 'last_name' ]  = 'Podaj nazwisko do faktury.'; }
	}
	if ( '' === trim( $inv['address_1'] ) ) { $err[ $prefix . 'address_1' ] = 'Podaj adres do faktury.'; }
	if ( '' === trim( $inv['postcode'] ) )  { $err[ $prefix . 'postcode' ]  = 'Podaj kod pocztowy.'; }
	if ( '' === trim( $inv['city'] ) )      { $err[ $prefix . 'city' ]      = 'Podaj miejscowość.'; }
	return $err;
}

/* ───────────────────── wspólny render pól faktury ───────────────────── */
function prinex_invoice_fields_html( $inv, $prefix = 'invoice_', $countries = null ) {
	if ( null === $countries ) {
		$countries = WC()->countries->get_allowed_countries();
	}
	$firma = 'firma' === $inv['type'];
	ob_start(); ?>
	<div class="pxc-inv-fields<?php echo $firma ? ' is-firma' : ''; ?>">
		<div class="pxc-inv-toggle-grp" role="group">
			<button type="button" class="pxc-inv-pill<?php echo ! $firma ? ' is-active' : ''; ?>" data-type="osoba">Osoba prywatna</button>
			<button type="button" class="pxc-inv-pill<?php echo $firma ? ' is-active' : ''; ?>" data-type="firma">Firma</button>
		</div>
		<input type="hidden" name="<?php echo esc_attr( $prefix ); ?>type" class="pxc-inv-type" value="<?php echo esc_attr( $inv['type'] ); ?>">
		<div class="pxc-form-grid">
			<p class="pxc-form-row pxc-fr-wide pxc-firma-only"><label>Nazwa firmy</label><input type="text" class="input-text" name="<?php echo esc_attr( $prefix ); ?>company" value="<?php echo esc_attr( $inv['company'] ); ?>"></p>
			<p class="pxc-form-row pxc-fr-wide pxc-firma-only"><label>NIP</label><input type="text" class="input-text" name="<?php echo esc_attr( $prefix ); ?>nip" maxlength="15" value="<?php echo esc_attr( $inv['nip'] ); ?>" placeholder="np. 1234563218"></p>
			<p class="pxc-form-row pxc-osoba-only"><label>Imię</label><input type="text" class="input-text" name="<?php echo esc_attr( $prefix ); ?>first_name" value="<?php echo esc_attr( $inv['first_name'] ); ?>"></p>
			<p class="pxc-form-row pxc-osoba-only"><label>Nazwisko</label><input type="text" class="input-text" name="<?php echo esc_attr( $prefix ); ?>last_name" value="<?php echo esc_attr( $inv['last_name'] ); ?>"></p>
			<p class="pxc-form-row pxc-fr-wide"><label>Ulica i numer</label><input type="text" class="input-text" name="<?php echo esc_attr( $prefix ); ?>address_1" value="<?php echo esc_attr( $inv['address_1'] ); ?>"></p>
			<p class="pxc-form-row"><label>Kod pocztowy</label><input type="text" class="input-text" name="<?php echo esc_attr( $prefix ); ?>postcode" value="<?php echo esc_attr( $inv['postcode'] ); ?>"></p>
			<p class="pxc-form-row"><label>Miejscowość</label><input type="text" class="input-text" name="<?php echo esc_attr( $prefix ); ?>city" value="<?php echo esc_attr( $inv['city'] ); ?>"></p>
			<p class="pxc-form-row pxc-fr-wide"><label>Kraj</label>
				<select class="input-text" name="<?php echo esc_attr( $prefix ); ?>country">
					<?php foreach ( $countries as $code => $cn ) : ?><option value="<?php echo esc_attr( $code ); ?>" <?php selected( $code, $inv['country'] ?: 'PL' ); ?>><?php echo esc_html( $cn ); ?></option><?php endforeach; ?>
				</select>
			</p>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/* ─────────────────────── A) KONTO — sekcja Faktura ──────────────────── */
add_action( 'woocommerce_after_edit_account_address_form', function () {
	if ( ! is_user_logged_in() || is_wc_endpoint_url( 'edit-account' ) ) {
		return;
	}
	// tylko na liście adresów (bez konkretnego adresu w edycji)
	global $wp;
	if ( ! empty( $wp->query_vars['edit-address'] ) ) {
		return;
	}
	$inv = prinex_get_invoice( get_current_user_id() );
	?>
	<div class="pxc-card pxc-inv-card" id="pxc-inv-account">
		<h2 class="pxc-card-title">Faktura</h2>
		<label class="pxc-inv-check">
			<input type="checkbox" id="pxc-inv-enabled" <?php checked( $inv['enabled'] ); ?>>
			<span class="pxc-inv-box"><svg viewBox="0 0 24 24" width="12" height="12"><path d="M5 12l5 5 9-10"/></svg></span>
			<span>Chcę fakturę na inne dane niż adres dostawy</span>
		</label>
		<div class="pxc-inv-note" id="pxc-inv-note"<?php echo $inv['enabled'] ? ' hidden' : ''; ?>>Faktura zostanie wystawiona na dane z domyślnego adresu dostawy.</div>
		<form class="pxc-form pxc-inv-form" id="pxc-inv-form"<?php echo $inv['enabled'] ? '' : ' hidden'; ?>>
			<?php echo prinex_invoice_fields_html( $inv, 'invoice_' ); // phpcs:ignore ?>
			<div class="pxc-abook-form-msg" id="pxc-inv-msg" role="alert"></div>
			<div class="pxc-abook-form-actions">
				<button type="submit" class="pxc-btn-cta"><span class="pxc-btn-label">Zapisz dane faktury</span><span class="pxc-btn-cube"><svg viewBox="0 0 24 24" width="22" height="22"><path d="M5 12l5 5 9-10"/></svg></span></button>
			</div>
		</form>
	</div>
	<?php
} );

/* AJAX zapis faktury (konto) */
add_action( 'wp_ajax_prinex_invoice_save', function () {
	if ( ! check_ajax_referer( 'prinex_addr', 'nonce', false ) ) { wp_send_json_error( array( 'message' => 'Sesja wygasła — odśwież stronę.' ), 403 ); }
	if ( ! is_user_logged_in() ) { wp_send_json_error( array( 'message' => 'Wymagane logowanie.' ), 401 ); }
	$uid = get_current_user_id();
	$inv = prinex_sanitize_invoice( $_POST );
	if ( $inv['enabled'] ) {
		$errs = prinex_validate_invoice( $inv, 'invoice_' );
		// mapuj klucze błędów na nazwy pól formularza (bez prefixu 'invoice_')
		if ( $errs ) {
			$fields = array();
			foreach ( $errs as $k => $v ) { $fields[ str_replace( 'invoice_', '', $k ) ] = $v; }
			wp_send_json_error( array( 'message' => 'Sprawdź dane faktury.', 'fields' => $fields ), 422 );
		}
	}
	prinex_save_invoice( $uid, $inv );
	wp_send_json_success( array( 'enabled' => $inv['enabled'] ) );
} );

/* ─────────────────── B) CHECKOUT — dodatek przez hook ───────────────── */
add_action( 'woocommerce_checkout_after_customer_details', function () {
	$inv = is_user_logged_in() ? prinex_get_invoice( get_current_user_id() ) : prinex_invoice_defaults();
	// na checkoucie startowo zwinięte, chyba że user ma zapisaną włączoną fakturę
	$open = ! empty( $inv['enabled'] );
	?>
	<div class="pxc-card pxc-inv-card pxc-inv-checkout" id="pxc-inv-checkout">
		<label class="pxc-inv-check">
			<input type="checkbox" name="prinex_invoice_enabled" id="pxc-inv-enabled" value="1" <?php checked( $open ); ?>>
			<span class="pxc-inv-box"><svg viewBox="0 0 24 24" width="12" height="12"><path d="M5 12l5 5 9-10"/></svg></span>
			<span>Chcę fakturę na inne dane niż adres dostawy</span>
		</label>
		<div class="pxc-inv-form" id="pxc-inv-form"<?php echo $open ? '' : ' hidden'; ?>>
			<?php echo prinex_invoice_fields_html( $inv, 'invoice_' ); // phpcs:ignore ?>
		</div>
	</div>
	<?php
} );

/* Walidacja faktury na checkoucie — TYLKO gdy checkbox zaznaczony (inaczej brak wpływu na #28) */
add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
	if ( empty( $_POST['prinex_invoice_enabled'] ) ) {
		return;
	}
	$inv = prinex_sanitize_invoice( array_merge( $_POST, array( 'enabled' => 1, 'type' => $_POST['invoice_type'] ?? 'firma', 'company' => $_POST['invoice_company'] ?? '', 'nip' => $_POST['invoice_nip'] ?? '', 'first_name' => $_POST['invoice_first_name'] ?? '', 'last_name' => $_POST['invoice_last_name'] ?? '', 'address_1' => $_POST['invoice_address_1'] ?? '', 'postcode' => $_POST['invoice_postcode'] ?? '', 'city' => $_POST['invoice_city'] ?? '', 'country' => $_POST['invoice_country'] ?? 'PL' ) ) );
	$errs = prinex_validate_invoice( $inv, 'invoice_' );
	foreach ( $errs as $code => $msg ) { $errors->add( $code, $msg ); }
}, 20, 2 );

/* Zapis danych faktury do ZAMÓWIENIA (HPOS-safe) — tylko gdy zaznaczone */
add_action( 'woocommerce_checkout_create_order', function ( $order, $data ) {
	if ( empty( $_POST['prinex_invoice_enabled'] ) ) {
		return;
	}
	$inv = prinex_sanitize_invoice( array( 'enabled' => 1, 'type' => $_POST['invoice_type'] ?? 'firma', 'company' => $_POST['invoice_company'] ?? '', 'nip' => $_POST['invoice_nip'] ?? '', 'first_name' => $_POST['invoice_first_name'] ?? '', 'last_name' => $_POST['invoice_last_name'] ?? '', 'address_1' => $_POST['invoice_address_1'] ?? '', 'address_2' => $_POST['invoice_address_2'] ?? '', 'postcode' => $_POST['invoice_postcode'] ?? '', 'city' => $_POST['invoice_city'] ?? '', 'country' => $_POST['invoice_country'] ?? 'PL' ) );
	$order->update_meta_data( '_prinex_invoice', $inv );
}, 20, 2 );

/* podgląd w adminie zamówienia */
add_action( 'woocommerce_admin_order_data_after_billing_address', function ( $order ) {
	$inv = $order->get_meta( '_prinex_invoice' );
	if ( ! is_array( $inv ) || empty( $inv['enabled'] ) ) { return; }
	echo '<p><strong>Faktura na inne dane:</strong><br>';
	echo 'firma' === $inv['type'] ? esc_html( $inv['company'] ) . ' (NIP ' . esc_html( $inv['nip'] ) . ')<br>' : esc_html( $inv['first_name'] . ' ' . $inv['last_name'] ) . '<br>';
	echo esc_html( $inv['address_1'] . ', ' . $inv['postcode'] . ' ' . $inv['city'] ) . '</p>';
}, 20 );

/* ─────────────────────────────── CSS ───────────────────────────────── */
add_action( 'wp_head', function () {
	if ( ! is_account_page() && ! is_checkout() ) { return; }
	?>
<style>
/* checkbox self-contained (nie zależy od #28/#29 scope) */
.pxc-inv-check{display:flex;align-items:center;gap:9px;cursor:pointer;font-size:15px;color:#444;}
.pxc-inv-check input{position:absolute;opacity:0;width:0;height:0;}
.pxc-inv-box{flex:none;width:20px;height:20px;border-radius:5px;border:2px solid #d0d5db;background:#fff;display:flex;align-items:center;justify-content:center;}
.pxc-inv-box svg{opacity:0;stroke:#fff;fill:none;stroke-width:3;stroke-linecap:round;stroke-linejoin:round;}
.pxc-inv-check input:checked + .pxc-inv-box{background:#0B457D;border-color:#0B457D;}
.pxc-inv-check input:checked + .pxc-inv-box svg{opacity:1;}
.pxc-inv-note{font-size:14px;color:#8a939c;margin-top:10px;}
.pxc-inv-form{margin-top:18px;}
/* toggle Osoba/Firma faktury — WŁASNE klasy (odcięte od #28 .pxc-type-pill, by JS #28 nie ruszał) */
.pxc-inv-toggle-grp{display:inline-flex;background:#eef1f3;border-radius:8px;padding:4px;gap:4px;margin-bottom:18px;}
.pxc-inv-pill{border:none;background:none;font-family:inherit;font-size:14px;font-weight:600;color:#5a6570;padding:8px 18px;border-radius:6px;cursor:pointer;}
.pxc-inv-pill.is-active{background:#78B833;color:#fff;}
/* pokaż/ukryj per typ — celujemy w .pxc-form-row.X + !important (bije .pxc-form-row{display:flex}) */
.pxc-inv-fields .pxc-form-row.pxc-firma-only,
.pxc-inv-fields .pxc-form-row.pxc-osoba-only{display:none !important;}
.pxc-inv-fields.is-firma .pxc-form-row.pxc-firma-only{display:flex !important;}
.pxc-inv-fields:not(.is-firma) .pxc-form-row.pxc-osoba-only{display:flex !important;}
body.woocommerce-checkout .pxc-inv-checkout{margin-top:24px;}
body.woocommerce-checkout .pxc-inv-checkout .pxc-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px 18px;}
body.woocommerce-checkout .pxc-inv-checkout .pxc-fr-wide{grid-column:1/-1;}
body.woocommerce-checkout .pxc-inv-checkout label{font-size:13px;font-weight:600;color:#0B457D;margin-bottom:6px;display:block;}
body.woocommerce-checkout .pxc-inv-checkout .input-text,body.woocommerce-checkout .pxc-inv-checkout select{width:100%;border:1px solid #d0d5db;border-radius:8px;padding:11px 14px;font-size:15px;font-family:inherit;}
body.woocommerce-checkout .pxc-inv-checkout .input-text:focus,body.woocommerce-checkout .pxc-inv-checkout select:focus{outline:none;border-color:#78B833;box-shadow:0 0 0 2px rgba(120,184,51,.16);}
body.woocommerce-checkout .pxc-inv-checkout .pxc-form-row{margin:0;display:flex;flex-direction:column;}
</style>
	<?php
} );

/* ─────────────────────────────── JS ────────────────────────────────── */
add_action( 'wp_footer', function () {
	if ( ! is_account_page() && ! is_checkout() ) { return; }
	?>
<script>
(function(){
  function ready(fn){ if(document.readyState!=='loading'){fn();} else {document.addEventListener('DOMContentLoaded',fn);} }
  ready(function(){
    // toggle Osoba/Firma w polach faktury
    document.querySelectorAll('.pxc-inv-fields').forEach(function(box){
      var hidden=box.querySelector('.pxc-inv-type');
      box.querySelectorAll('.pxc-inv-pill').forEach(function(pill){
        pill.addEventListener('click',function(){
          var t=pill.getAttribute('data-type');
          box.classList.toggle('is-firma',t==='firma');
          if(hidden) hidden.value=t;
          box.querySelectorAll('.pxc-inv-pill').forEach(function(x){x.classList.toggle('is-active',x.getAttribute('data-type')===t);});
        });
      });
    });
    // checkbox → pokaż/ukryj formularz
    var card=document.getElementById('pxc-inv-account')||document.getElementById('pxc-inv-checkout');
    if(card){
      var cb=card.querySelector('#pxc-inv-enabled'), form=card.querySelector('#pxc-inv-form'), note=document.getElementById('pxc-inv-note');
      if(cb&&form){ cb.addEventListener('change',function(){ if(cb.checked){form.removeAttribute('hidden'); if(note)note.setAttribute('hidden','');} else {form.setAttribute('hidden',''); if(note)note.removeAttribute('hidden');} }); }
    }
    // KONTO: zapis AJAX
    var accForm=document.querySelector('#pxc-inv-account #pxc-inv-form');
    if(accForm && window.PRINEX_ADDR){
      accForm.addEventListener('submit',function(e){
        e.preventDefault();
        var msg=document.getElementById('pxc-inv-msg'); if(msg){msg.className='pxc-abook-form-msg';msg.textContent='';}
        accForm.querySelectorAll('.pxc-has-err').forEach(function(x){x.classList.remove('pxc-has-err');});
        var body=new URLSearchParams(); body.append('action','prinex_invoice_save'); body.append('nonce',window.PRINEX_ADDR.nonce);
        body.append('enabled', document.getElementById('pxc-inv-enabled').checked?'1':'');
        new FormData(accForm).forEach(function(v,k){ body.append(k.replace(/^invoice_/,''),v); });
        fetch(window.PRINEX_ADDR.url,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString()})
          .then(function(r){return r.json().then(function(j){return {ok:r.ok,j:j};});})
          .then(function(res){
            if(res.j&&res.j.success){ if(msg){msg.className='pxc-abook-form-msg';msg.style.display='block';msg.style.background='#eaf4dc';msg.style.color='#4e7d18';msg.textContent='Dane faktury zapisane.';} }
            else { var d=res.j&&res.j.data?res.j.data:{}; if(msg){msg.className='pxc-abook-form-msg is-err';msg.textContent=d.message||'Sprawdź dane.';} if(d.fields){Object.keys(d.fields).forEach(function(k){var f=accForm.querySelector('[name=invoice_'+k+']');if(f){var row=f.closest('.pxc-form-row');if(row)row.classList.add('pxc-has-err');}});} }
          }).catch(function(){ if(msg){msg.className='pxc-abook-form-msg is-err';msg.textContent='Błąd połączenia.';} });
      });
    }
  });
})();
</script>
	<?php
} );
