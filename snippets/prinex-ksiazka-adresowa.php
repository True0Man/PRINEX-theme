<?php
/**
 * PRINEX — Książka adresowa klienta (Warstwa 2c).
 * Scope: front-end. Wiele zapisanych adresów dostawy (custom CRUD na meta klienta).
 *
 * BEZPIECZEŃSTWO (część zakresu, nie dodatek):
 *  - nonce na KAŻDEJ operacji AJAX (check_ajax_referer 'prinex_addr').
 *  - autoryzacja: operacje WYŁĄCZNIE na adresach get_current_user_id() — obcy ID
 *    nie istnieje w tablicy bieżącego usera → odrzucony (brak dostępu do cudzych).
 *  - sanityzacja wejścia (sanitize_*) + escaping wyjścia (esc_*).
 *  - NIP: prinex_nip_checksum_valid (suma kontrolna — ostrzejszy niż checkout #28).
 *  - dane klienta = user_meta (_prinex_addresses), NIE post_meta.
 *
 * Reuse: prinex_nip_normalize / prinex_nip_checksum_valid / Osoba-Firma (inc/prinex-customer-fields.php).
 * Renderowane na "Dane do wysyłki" (override my-address.php → prinex_render_address_book).
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

/* ─────────────────────────────── MODEL ─────────────────────────────── */

function prinex_get_addresses( $uid ) {
	$a = get_user_meta( (int) $uid, '_prinex_addresses', true );
	return is_array( $a ) ? array_values( $a ) : array();
}

function prinex_save_addresses( $uid, $arr ) {
	update_user_meta( (int) $uid, '_prinex_addresses', array_values( $arr ) );
}

/** Sanityzacja jednego adresu z surowego wejścia. Zwraca czystą tablicę. */
function prinex_sanitize_address( $in ) {
	$type = ( isset( $in['type'] ) && 'firma' === $in['type'] ) ? 'firma' : 'osoba';
	return array(
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
		'phone'      => sanitize_text_field( $in['phone'] ?? '' ),
	);
}

/** Walidacja adresu. Zwraca [] gdy OK, albo mapę pole=>komunikat. */
function prinex_validate_address( $a ) {
	$err = array();
	if ( '' === trim( $a['first_name'] ) ) { $err['first_name'] = 'Podaj imię.'; }
	if ( '' === trim( $a['last_name'] ) )  { $err['last_name']  = 'Podaj nazwisko.'; }
	if ( '' === trim( $a['address_1'] ) )  { $err['address_1']  = 'Podaj ulicę i numer.'; }
	if ( '' === trim( $a['postcode'] ) )   { $err['postcode']   = 'Podaj kod pocztowy.'; }
	if ( '' === trim( $a['city'] ) )       { $err['city']       = 'Podaj miejscowość.'; }
	if ( 'firma' === $a['type'] ) {
		if ( '' === trim( $a['company'] ) )            { $err['company'] = 'Podaj nazwę firmy.'; }
		if ( ! prinex_nip_checksum_valid( $a['nip'] ) ) { $err['nip']    = 'Nieprawidłowy NIP (10 cyfr + suma kontrolna).'; }
	}
	return $err;
}

/* ─────────────────────────────── RENDER ────────────────────────────── */

function prinex_address_card_html( $a, $idx ) {
	$is_firma = 'firma' === ( $a['type'] ?? 'osoba' );
	$name = $is_firma && $a['company'] ? $a['company'] : trim( ( $a['first_name'] ?? '' ) . ' ' . ( $a['last_name'] ?? '' ) );
	$lines = array();
	if ( $is_firma && trim( ( $a['first_name'] ?? '' ) . ( $a['last_name'] ?? '' ) ) ) {
		$lines[] = trim( $a['first_name'] . ' ' . $a['last_name'] );
	}
	if ( $is_firma && ! empty( $a['nip'] ) ) { $lines[] = 'NIP: ' . $a['nip']; }
	$street = trim( $a['address_1'] . ( ! empty( $a['address_2'] ) ? ' / ' . $a['address_2'] : '' ) );
	if ( $street ) { $lines[] = $street; }
	$lines[] = trim( ( $a['postcode'] ?? '' ) . ' ' . ( $a['city'] ?? '' ) );
	$cc = $a['country'] ?? 'PL';
	$cname = isset( WC()->countries->get_countries()[ $cc ] ) ? WC()->countries->get_countries()[ $cc ] : $cc;
	$lines[] = $cname;
	if ( ! empty( $a['phone'] ) ) { $lines[] = $a['phone']; }

	$is_def = ! empty( $a['is_default'] );
	$id = esc_attr( $a['id'] );
	ob_start(); ?>
	<div class="pxc-card pxc-abook-card<?php echo $is_def ? ' is-default' : ''; ?>" data-id="<?php echo $id; ?>" data-addr="<?php echo esc_attr( wp_json_encode( $a ) ); ?>">
		<div class="pxc-abook-head">
			<span class="pxc-abook-name"><?php echo esc_html( $name ); ?></span>
			<?php if ( $is_def ) : ?><span class="pxc-abook-badge">Domyślny</span><?php else : ?><span class="pxc-abook-type"><?php echo $is_firma ? 'Firma' : 'Osoba prywatna'; ?></span><?php endif; ?>
		</div>
		<div class="pxc-abook-body">
			<?php foreach ( array_filter( $lines ) as $ln ) : ?><div><?php echo esc_html( $ln ); ?></div><?php endforeach; ?>
		</div>
		<div class="pxc-abook-actions">
			<button type="button" class="pxc-abook-edit" data-id="<?php echo $id; ?>"><svg viewBox="0 0 24 24" width="14" height="14"><path d="M4 20h4l10-10-4-4L4 16z"/><path d="M13.5 6.5l4 4"/></svg>Edytuj</button>
			<button type="button" class="pxc-abook-del" data-id="<?php echo $id; ?>"><svg viewBox="0 0 24 24" width="14" height="14"><path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/></svg>Usuń</button>
			<?php if ( ! $is_def ) : ?><button type="button" class="pxc-abook-def" data-id="<?php echo $id; ?>"><svg viewBox="0 0 24 24" width="14" height="14"><path d="M12 3l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 18.8 6.1 21l1.2-6.5L2.5 9.9 9.1 9z"/></svg>Ustaw jako domyślny</button><?php endif; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

function prinex_render_address_cards( $uid ) {
	$addrs = prinex_get_addresses( $uid );
	if ( empty( $addrs ) ) {
		return '<div class="pxc-abook-empty">Nie masz jeszcze zapisanych adresów. Dodaj pierwszy poniżej.</div>';
	}
	$html = '';
	foreach ( $addrs as $i => $a ) {
		$html .= prinex_address_card_html( $a, $i );
	}
	return $html;
}

/** Cała sekcja "Dane do wysyłki" (wołane z my-address.php). */
function prinex_render_address_book( $uid ) {
	$countries = WC()->countries->get_allowed_countries();
	ob_start(); ?>
	<div class="pxc-abook" id="pxc-abook">
		<div class="pxc-abook-top">
			<h2 class="pxc-abook-h">Adresy dostawy</h2>
			<span class="pxc-abook-sub">Zapisane adresy podpowiemy przy kolejnym zamówieniu.</span>
		</div>

		<div class="pxc-abook-cards" id="pxc-abook-list"><?php echo prinex_render_address_cards( $uid ); // phpcs:ignore ?></div>

		<button type="button" class="pxc-abook-add" id="pxc-abook-add">
			<svg viewBox="0 0 24 24" width="16" height="16"><path d="M12 5v14M5 12h14"/></svg> Dodaj nowy adres
		</button>

		<form class="pxc-card pxc-abook-form pxc-form" id="pxc-abook-form" hidden>
			<input type="hidden" name="id" value="">
			<h3 class="pxc-abook-form-h" id="pxc-abook-form-h">Nowy adres dostawy</h3>

			<div class="pxc-type-toggle" role="group">
				<button type="button" class="pxc-type-pill is-active" data-type="osoba">Osoba prywatna</button>
				<button type="button" class="pxc-type-pill" data-type="firma">Firma</button>
			</div>
			<input type="hidden" name="type" id="pxc-abook-type" value="osoba">

			<div class="pxc-form-grid">
				<p class="pxc-form-row pxc-fr-wide pxc-firma-only"><label>Nazwa firmy</label><input type="text" class="input-text" name="company"></p>
				<p class="pxc-form-row pxc-fr-wide pxc-firma-only"><label>NIP</label><input type="text" class="input-text" name="nip" maxlength="15" placeholder="np. 1234563218"></p>
				<p class="pxc-form-row"><label>Imię</label><input type="text" class="input-text" name="first_name"></p>
				<p class="pxc-form-row"><label>Nazwisko</label><input type="text" class="input-text" name="last_name"></p>
				<p class="pxc-form-row pxc-fr-wide"><label>Ulica i numer</label><input type="text" class="input-text" name="address_1" placeholder="Nazwa ulicy, numer budynku"></p>
				<p class="pxc-form-row"><label>Nr lokalu <span class="pxc-lbl-hint">(opcjonalnie)</span></label><input type="text" class="input-text" name="address_2"></p>
				<p class="pxc-form-row"><label>Kod pocztowy</label><input type="text" class="input-text" name="postcode"></p>
				<p class="pxc-form-row"><label>Miejscowość</label><input type="text" class="input-text" name="city"></p>
				<p class="pxc-form-row"><label>Kraj</label>
					<select class="input-text" name="country">
						<?php foreach ( $countries as $code => $cname ) : ?>
							<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $code, 'PL' ); ?>><?php echo esc_html( $cname ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<p class="pxc-form-row pxc-fr-wide"><label>Telefon <span class="pxc-lbl-hint">(opcjonalnie)</span></label><input type="text" class="input-text" name="phone"></p>
			</div>

			<label class="pxc-check-inline pxc-abook-defcheck">
				<input type="checkbox" name="is_default" value="1">
				<span class="pxc-checkbox-box"><svg viewBox="0 0 24 24" width="12" height="12"><path d="M5 12l5 5 9-10"/></svg></span>
				<span>Ustaw jako domyślny adres</span>
			</label>

			<div class="pxc-abook-form-msg" id="pxc-abook-msg" role="alert"></div>

			<div class="pxc-abook-form-actions">
				<button type="submit" class="pxc-btn-cta"><span class="pxc-btn-label">Zapisz adres</span><span class="pxc-btn-cube"><svg viewBox="0 0 24 24" width="22" height="22"><path d="M5 12l5 5 9-10"/></svg></span></button>
				<button type="button" class="pxc-abook-cancel" id="pxc-abook-cancel">Anuluj</button>
			</div>
		</form>
	</div>
	<?php
	return ob_get_clean();
}

/* ─────────────────────────────── AJAX ──────────────────────────────── */

/** Wspólny guard: nonce + zalogowany. Zwraca uid lub kończy żądanie. */
function prinex_addr_guard() {
	if ( ! check_ajax_referer( 'prinex_addr', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => 'Sesja wygasła — odśwież stronę.' ), 403 );
	}
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'Wymagane logowanie.' ), 401 );
	}
	return get_current_user_id();
}

/** Dodaj / edytuj adres. Edycja tylko gdy ID istnieje w adresach BIEŻĄCEGO usera. */
add_action( 'wp_ajax_prinex_addr_save', function () {
	$uid   = prinex_addr_guard();
	$addrs = prinex_get_addresses( $uid );

	$clean = prinex_sanitize_address( $_POST );
	$errs  = prinex_validate_address( $clean );
	if ( $errs ) {
		wp_send_json_error( array( 'message' => 'Sprawdź pola formularza.', 'fields' => $errs ), 422 );
	}

	$req_id   = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
	$make_def = ! empty( $_POST['is_default'] );

	if ( '' !== $req_id ) {
		// EDYCJA — wyłącznie jeśli ID należy do bieżącego usera (autoryzacja per-ID).
		$found = false;
		foreach ( $addrs as $i => $a ) {
			if ( (string) $a['id'] === $req_id ) {
				$clean['id'] = $a['id'];
				$clean['is_default'] = ! empty( $a['is_default'] );
				$addrs[ $i ] = $clean;
				$found = true;
				break;
			}
		}
		if ( ! $found ) {
			wp_send_json_error( array( 'message' => 'Adres nie istnieje lub nie należy do Ciebie.' ), 404 );
		}
	} else {
		// DODANIE
		$clean['id'] = uniqid( 'a', true );
		$clean['is_default'] = empty( $addrs ); // pierwszy = domyślny
		$addrs[] = $clean;
	}

	if ( $make_def ) {
		$target = $clean['id'];
		foreach ( $addrs as $i => $a ) {
			$addrs[ $i ]['is_default'] = ( (string) $a['id'] === (string) $target );
		}
	}

	prinex_save_addresses( $uid, $addrs );
	wp_send_json_success( array( 'html' => prinex_render_address_cards( $uid ) ) );
} );

/** Usuń adres (tylko własny). */
add_action( 'wp_ajax_prinex_addr_delete', function () {
	$uid   = prinex_addr_guard();
	$id    = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
	$addrs = prinex_get_addresses( $uid );
	$before = count( $addrs );
	$was_default = false;
	$addrs = array_values( array_filter( $addrs, function ( $a ) use ( $id, &$was_default ) {
		if ( (string) $a['id'] === $id ) {
			$was_default = ! empty( $a['is_default'] );
			return false;
		}
		return true;
	} ) );
	if ( count( $addrs ) === $before ) {
		wp_send_json_error( array( 'message' => 'Adres nie istnieje lub nie należy do Ciebie.' ), 404 );
	}
	if ( $was_default && ! empty( $addrs ) ) {
		$addrs[0]['is_default'] = true; // przekaż domyślność pierwszemu
	}
	prinex_save_addresses( $uid, $addrs );
	wp_send_json_success( array( 'html' => prinex_render_address_cards( $uid ) ) );
} );

/** Ustaw domyślny (tylko własny). */
add_action( 'wp_ajax_prinex_addr_set_default', function () {
	$uid   = prinex_addr_guard();
	$id    = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
	$addrs = prinex_get_addresses( $uid );
	$found = false;
	foreach ( $addrs as $a ) {
		if ( (string) $a['id'] === $id ) { $found = true; break; }
	}
	if ( ! $found ) {
		wp_send_json_error( array( 'message' => 'Adres nie istnieje lub nie należy do Ciebie.' ), 404 );
	}
	foreach ( $addrs as $i => $a ) {
		$addrs[ $i ]['is_default'] = ( (string) $a['id'] === $id );
	}
	prinex_save_addresses( $uid, $addrs );
	wp_send_json_success( array( 'html' => prinex_render_address_cards( $uid ) ) );
} );

/* nonce + ajaxurl dla JS (tylko na koncie, zalogowany) */
add_action( 'wp_footer', function () {
	if ( ! is_account_page() || ! is_user_logged_in() ) {
		return;
	}
	?>
<script>window.PRINEX_ADDR={url:"<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>",nonce:"<?php echo esc_js( wp_create_nonce( 'prinex_addr' ) ); ?>"};</script>
	<?php
}, 5 );

/* ─────────────────────────────── CSS ───────────────────────────────── */
add_action( 'wp_head', function () {
	if ( ! is_account_page() ) {
		return;
	}
	?>
<style>
body.woocommerce-account .pxc-abook-top{margin-bottom:18px;}
body.woocommerce-account .pxc-abook-h{font-size:20px;font-weight:700;color:var(--pxc-navy);margin:0;}
body.woocommerce-account .pxc-abook-sub{display:block;font-size:14px;color:#5a6570;margin-top:4px;}
body.woocommerce-account .pxc-abook-cards{display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:stretch;}
body.woocommerce-account .pxc-abook-cards .pxc-card{margin-top:0 !important;border-radius:12px;position:relative;}
body.woocommerce-account .pxc-abook-card{display:flex;flex-direction:column;}
body.woocommerce-account .pxc-abook-card.is-default{box-shadow:inset 4px 0 0 var(--pxc-green),0 2px 14px rgba(11,69,125,.06);}
body.woocommerce-account .pxc-abook-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;}
body.woocommerce-account .pxc-abook-name{font-size:16px;font-weight:700;color:var(--pxc-navy);}
body.woocommerce-account .pxc-abook-badge{flex:none;font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#fff;background:var(--pxc-green);border-radius:50px;padding:4px 11px;}
body.woocommerce-account .pxc-abook-type{flex:none;font-size:13px;color:var(--pxc-muted);}
body.woocommerce-account .pxc-abook-body{font-size:14px;color:#5a6570;line-height:1.6;flex:1;}
body.woocommerce-account .pxc-abook-actions{display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-top:16px;padding-top:14px;border-top:1px solid var(--pxc-line);}
body.woocommerce-account .pxc-abook-actions button{display:inline-flex;align-items:center;gap:6px;background:none;border:none;padding:0;font-family:inherit;font-size:13px;font-weight:600;color:var(--pxc-navy);cursor:pointer;}
body.woocommerce-account .pxc-abook-actions button svg{stroke:currentColor;fill:none;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-account .pxc-abook-def svg{fill:currentColor;stroke:none;}
body.woocommerce-account .pxc-abook-actions button:hover{color:var(--pxc-green-dark);}
body.woocommerce-account .pxc-abook-del:hover{color:#b23b32;}
body.woocommerce-account .pxc-abook-empty{background:#fff;border:1px solid var(--pxc-line);border-radius:12px;padding:28px;text-align:center;color:#5a6570;font-size:15px;}
body.woocommerce-account .pxc-abook-add{display:inline-flex;align-items:center;gap:8px;margin-top:18px;background:#fff;border:1px solid var(--pxc-ibrd);border-radius:50px;padding:11px 20px;font-family:inherit;font-size:14px;font-weight:700;color:var(--pxc-navy);cursor:pointer;transition:border-color .15s,background .15s;}
body.woocommerce-account .pxc-abook-add svg{stroke:var(--pxc-green);fill:none;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-account .pxc-abook-add:hover{border-color:var(--pxc-green);background:#f7faf2;}
body.woocommerce-account .pxc-abook-form{margin-top:22px;}
body.woocommerce-account .pxc-abook-form-h{font-size:17px;font-weight:700;color:var(--pxc-navy);margin:0 0 18px;}
body.woocommerce-account .pxc-abook-form .pxc-type-toggle{display:inline-flex;background:#eef1f3;border-radius:8px;padding:4px;gap:4px;margin-bottom:18px;}
body.woocommerce-account .pxc-abook-form .pxc-type-pill{border:none;background:none;font-family:inherit;font-size:14px;font-weight:600;color:#5a6570;padding:8px 18px;border-radius:6px;cursor:pointer;}
body.woocommerce-account .pxc-abook-form .pxc-type-pill.is-active{background:var(--pxc-green);color:#fff;}
body.woocommerce-account .pxc-abook-form .pxc-firma-only{display:none;}
body.woocommerce-account .pxc-abook-form.is-firma .pxc-firma-only{display:flex;}
body.woocommerce-account .pxc-abook-defcheck{margin-top:14px;}
body.woocommerce-account .pxc-abook-form-msg{display:none;margin-top:14px;padding:10px 14px;border-radius:8px;font-size:14px;}
body.woocommerce-account .pxc-abook-form-msg.is-err{display:block;background:#fbe9e9;color:#b23b32;}
body.woocommerce-account .pxc-form-row.pxc-has-err input{border-color:#d34a4a;}
body.woocommerce-account .pxc-abook-form-actions{display:flex;align-items:center;gap:18px;margin-top:20px;}
body.woocommerce-account .pxc-abook-cancel{background:none;border:none;font-family:inherit;font-size:14px;font-weight:600;color:var(--pxc-muted);cursor:pointer;}
body.woocommerce-account .pxc-abook-cancel:hover{color:var(--pxc-navy);}
@media(max-width:680px){body.woocommerce-account .pxc-abook-cards{grid-template-columns:1fr;}}
</style>
	<?php
} );

/* ─────────────────────────────── JS ────────────────────────────────── */
add_action( 'wp_footer', function () {
	if ( ! is_account_page() || ! is_user_logged_in() ) {
		return;
	}
	?>
<script>
(function(){
  function ready(fn){ if(document.readyState!=='loading'){fn();} else {document.addEventListener('DOMContentLoaded',fn);} }
  ready(function(){
    var book=document.getElementById('pxc-abook'); if(!book||!window.PRINEX_ADDR) return;
    var list=document.getElementById('pxc-abook-list'),
        form=document.getElementById('pxc-abook-form'),
        addBtn=document.getElementById('pxc-abook-add'),
        cancel=document.getElementById('pxc-abook-cancel'),
        formH=document.getElementById('pxc-abook-form-h'),
        typeInput=document.getElementById('pxc-abook-type'),
        msg=document.getElementById('pxc-abook-msg');

    function setType(t){ form.classList.toggle('is-firma',t==='firma'); typeInput.value=t; form.querySelectorAll('.pxc-type-pill').forEach(function(p){p.classList.toggle('is-active',p.getAttribute('data-type')===t);}); }
    function clearErrs(){ msg.className='pxc-abook-form-msg'; msg.textContent=''; form.querySelectorAll('.pxc-has-err').forEach(function(e){e.classList.remove('pxc-has-err');}); }
    function resetForm(){ form.reset(); form.querySelector('[name=id]').value=''; setType('osoba'); clearErrs(); }
    function openForm(edit){ form.removeAttribute('hidden'); formH.textContent=edit?'Edytuj adres':'Nowy adres dostawy'; form.scrollIntoView({behavior:'smooth',block:'center'}); }
    function closeForm(){ form.setAttribute('hidden',''); resetForm(); }

    form.querySelectorAll('.pxc-type-pill').forEach(function(p){ p.addEventListener('click',function(){ setType(p.getAttribute('data-type')); }); });
    addBtn.addEventListener('click',function(){ resetForm(); openForm(false); });
    cancel.addEventListener('click',closeForm);

    function bindCards(){
      list.querySelectorAll('.pxc-abook-edit').forEach(function(b){ b.addEventListener('click',function(){
        var card=b.closest('.pxc-abook-card'); var a=JSON.parse(card.getAttribute('data-addr'));
        resetForm();
        form.querySelector('[name=id]').value=a.id||'';
        setType(a.type||'osoba');
        ['company','nip','first_name','last_name','address_1','address_2','postcode','city','phone'].forEach(function(k){ var f=form.querySelector('[name='+k+']'); if(f) f.value=a[k]||''; });
        var cc=form.querySelector('[name=country]'); if(cc) cc.value=a.country||'PL';
        var dc=form.querySelector('[name=is_default]'); if(dc) dc.checked=!!a.is_default;
        openForm(true);
      }); });
      list.querySelectorAll('.pxc-abook-del').forEach(function(b){ b.addEventListener('click',function(){ if(!confirm('Usunąć ten adres?')) return; send('prinex_addr_delete',{id:b.getAttribute('data-id')}); }); });
      list.querySelectorAll('.pxc-abook-def').forEach(function(b){ b.addEventListener('click',function(){ send('prinex_addr_set_default',{id:b.getAttribute('data-id')}); }); });
    }

    function send(action,data,onErr){
      var body=new URLSearchParams(); body.append('action',action); body.append('nonce',window.PRINEX_ADDR.nonce);
      Object.keys(data).forEach(function(k){ body.append(k,data[k]); });
      return fetch(window.PRINEX_ADDR.url,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString()})
        .then(function(r){ return r.json().then(function(j){ return {ok:r.ok,j:j}; }); })
        .then(function(res){
          if(res.j&&res.j.success){ list.innerHTML=res.j.data.html; bindCards(); if(action==='prinex_addr_save'){ closeForm(); } }
          else if(onErr){ onErr(res.j&&res.j.data?res.j.data:{}); }
          else { alert((res.j&&res.j.data&&res.j.data.message)||'Wystąpił błąd.'); }
        }).catch(function(){ if(onErr){onErr({});} else {alert('Błąd połączenia.');} });
    }

    form.addEventListener('submit',function(e){
      e.preventDefault(); clearErrs();
      var data={}; new FormData(form).forEach(function(v,k){ data[k]=v; });
      send('prinex_addr_save',data,function(err){
        msg.className='pxc-abook-form-msg is-err'; msg.textContent=err.message||'Sprawdź pola.';
        if(err.fields){ Object.keys(err.fields).forEach(function(k){ var f=form.querySelector('[name='+k+']'); if(f){ var row=f.closest('.pxc-form-row'); if(row) row.classList.add('pxc-has-err'); } }); }
      });
    });

    setType('osoba');
    bindCards();
  });
})();
</script>
	<?php
} );
