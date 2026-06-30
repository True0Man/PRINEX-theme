<?php
/**
 * PRINEX — Strona "Dostawa i płatność" (checkout) — CSS/JS + integracja WC (#28).
 * Scope: front-end. Cała logika guard'owana is_checkout() / filtrami checkout.
 *
 * Współpracuje z override'ami szablonów child theme:
 *   woocommerce/checkout/{form-checkout,form-billing,review-order,payment}.php
 *
 * NIE dotyka strony produktu (#13/#14/variable.php) ani koszyka (#21).
 */

defined( 'ABSPATH' ) || exit;

/* ─────────────────────────────────────────────────────────────────────────
 * 1) MECHANIKA: payment renderujemy w lewej kolumnie (form-checkout.php),
 *    więc zdejmujemy domyślny hook z order_review (inaczej dubel w prawej).
 *    AJAX update_order_review nadal odświeża .woocommerce-checkout-payment.
 * ──────────────────────────────────────────────────────────────────────── */
add_action( 'init', function () {
	remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
	// Czyste UI wg mockupu — bez bannerów logowania i kuponu na górze checkoutu.
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
} );

/* ─────────────────────────────────────────────────────────────────────────
 * 2) POLA: NIP + typ odbiorcy (osoba/firma). Firma+NIP chowane JS-em.
 * ──────────────────────────────────────────────────────────────────────── */
add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
	// Nazwa firmy — ZAPEWNIJ obecność (w ustawieniach WC bywa ukryta) + klasa toggle.
	$company             = isset( $fields['billing']['billing_company'] ) ? $fields['billing']['billing_company'] : array();
	$company['label']    = 'Nazwa firmy';
	$company['required'] = false;
	$company['priority'] = 32;
	$company['class']    = array( 'form-row-wide', 'pxc-firma-only' );
	$fields['billing']['billing_company'] = $company;
	// NIP — nowe pole, firma only.
	$fields['billing']['billing_nip'] = array(
		'label'       => 'NIP',
		'required'    => false,
		'class'       => array( 'form-row-wide', 'pxc-firma-only' ),
		'priority'    => 34,
		'maxlength'   => 15,
		'placeholder' => 'np. 1234563218',
	);
	return $fields;
} );

/* Walidacja NIP gdy wybrana Firma. */
add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
	$type = isset( $_POST['billing_customer_type'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_customer_type'] ) ) : 'osoba';
	if ( 'firma' !== $type ) {
		return;
	}
	$nip = isset( $_POST['billing_nip'] ) ? preg_replace( '/[^0-9]/', '', wp_unslash( $_POST['billing_nip'] ) ) : '';
	if ( '' === $nip ) {
		$errors->add( 'billing_nip', 'Podaj NIP firmy.' );
	} elseif ( 10 !== strlen( $nip ) ) {
		$errors->add( 'billing_nip', 'NIP powinien mieć 10 cyfr.' );
	}
	if ( empty( $_POST['billing_company'] ) ) {
		$errors->add( 'billing_company', 'Podaj nazwę firmy.' );
	}
}, 10, 2 );

/* Zapis typu odbiorcy + NIP do zamówienia. HPOS-safe: na obiekcie order
 * (woocommerce_checkout_create_order — WC zapisuje order PO tym hooku). */
add_action( 'woocommerce_checkout_create_order', function ( $order, $data ) {
	if ( isset( $_POST['billing_customer_type'] ) ) {
		$order->update_meta_data( '_billing_customer_type', sanitize_text_field( wp_unslash( $_POST['billing_customer_type'] ) ) );
	}
	if ( isset( $_POST['billing_nip'] ) ) {
		$order->update_meta_data( '_billing_nip', sanitize_text_field( wp_unslash( $_POST['billing_nip'] ) ) );
	}
}, 10, 2 );

/* Podgląd w adminie zamówienia. */
add_action( 'woocommerce_admin_order_data_after_billing_address', function ( $order ) {
	$type = $order->get_meta( '_billing_customer_type' );
	$nip  = $order->get_meta( '_billing_nip' );
	if ( $type ) {
		echo '<p><strong>Typ odbiorcy:</strong> ' . esc_html( 'firma' === $type ? 'Firma' : 'Osoba prywatna' ) . '</p>';
	}
	if ( $nip ) {
		echo '<p><strong>NIP:</strong> ' . esc_html( $nip ) . '</p>';
	}
} );

/* ─────────────────────────────────────────────────────────────────────────
 * 3) CSS — scoped body.woocommerce-checkout. Wzorzec wizualny = koszyk (#21).
 * ──────────────────────────────────────────────────────────────────────── */
add_action( 'wp_head', function () {
	if ( ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}
	?>
<style>
body.woocommerce-checkout{--pxc-navy:#0B457D;--pxc-green:#78B833;--pxc-green-dark:#62992a;--pxc-amber:#F39200;--pxc-line:#e1e6ea;--pxc-muted:#8a939c;--pxc-ibrd:#d0d5db;background:#E8ECEF;}
body.woocommerce-checkout .inside-article{background:transparent !important;padding-top:0 !important;padding-bottom:0 !important;}
body.woocommerce-checkout .entry-header,
body.woocommerce-checkout .woocommerce-breadcrumb,
body.woocommerce-checkout .breadcrumb-trail{display:none !important;}
body.woocommerce-checkout #order_review_heading{display:none !important;}
body.woocommerce-checkout .woocommerce-notices-wrapper:empty{display:none;}

body.woocommerce-checkout .pxc-co-wrap{padding:6px 0 56px;}

/* stepper — identyczny komponent jak koszyk */
body.woocommerce-checkout .pxc-steps-outer{max-width:760px;margin:0 auto 34px;}
body.woocommerce-checkout .pxc-steps{display:flex;align-items:center;width:100%;}
body.woocommerce-checkout .pxc-step{display:flex;align-items:center;gap:9px;font-size:14px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:var(--pxc-muted);flex:none;}
body.woocommerce-checkout .pxc-dot{width:26px;height:26px;border-radius:50%;flex:none;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;background:#d4dbe1;color:#fff;}
body.woocommerce-checkout .pxc-dot svg{width:13px;height:13px;stroke:#fff;fill:none;stroke-width:3.2;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-checkout .pxc-step.pxc-done .pxc-dot{background:var(--pxc-green);}
body.woocommerce-checkout .pxc-step.pxc-done,
body.woocommerce-checkout .pxc-step.pxc-now{color:var(--pxc-navy);}
body.woocommerce-checkout .pxc-step.pxc-now .pxc-dot{background:var(--pxc-navy);}
body.woocommerce-checkout .pxc-step-line{flex:1 1 auto;height:2px;background:#d4dbe1;margin:0 14px;}
body.woocommerce-checkout .pxc-step-line.pxc-done{background:var(--pxc-green);}

/* head */
body.woocommerce-checkout .pxc-head{margin-bottom:28px;text-align:center;}
body.woocommerce-checkout .pxc-sig{width:52px;height:2px;background:var(--pxc-green);margin:0 auto 14px;}
body.woocommerce-checkout .pxc-head h1{font-size:38px;font-weight:700;color:var(--pxc-navy);line-height:1.15;margin:0;}

/* grid */
body.woocommerce-checkout .pxc-co-grid{display:grid;grid-template-columns:minmax(0,1fr) 366px;gap:38px;align-items:start;max-width:1180px;margin:0 auto;}
body.woocommerce-checkout .pxc-co-main{display:flex;flex-direction:column;gap:24px;min-width:0;}

/* karty */
body.woocommerce-checkout .pxc-card{background:#fff;border:1px solid var(--pxc-line);border-radius:16px;padding:28px 30px;box-shadow:0 2px 14px rgba(11,69,125,.06);}
body.woocommerce-checkout .pxc-card-title{font-size:21px;font-weight:700;color:var(--pxc-navy);line-height:1.2;margin:0 0 20px;}
body.woocommerce-checkout .pxc-card-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;}
body.woocommerce-checkout .pxc-card-head .pxc-card-title{margin:0;}

/* Dane odbiorcy — zwinięte */
body.woocommerce-checkout .pxc-edit-link{font-size:15px;font-weight:600;color:var(--pxc-navy);text-decoration:underline;cursor:pointer;}
body.woocommerce-checkout .pxc-edit-link:hover{color:var(--pxc-green-dark);}
body.woocommerce-checkout .pxc-recipient-summary .pxc-rs-name{font-size:17px;font-weight:700;color:var(--pxc-navy);}
body.woocommerce-checkout .pxc-recipient-summary .pxc-rs-addr{font-size:15px;color:#5a6570;margin-top:6px;line-height:1.5;}

/* Dane odbiorcy — formularz */
body.woocommerce-checkout .pxc-type-toggle{display:inline-flex;background:#eef1f3;border-radius:8px;padding:4px;gap:4px;margin-bottom:20px;grid-column:1 / -1;justify-self:start;}
body.woocommerce-checkout #billing_customer_type{display:none !important;}
body.woocommerce-checkout .pxc-type-pill{border:none;background:none;font-family:inherit;font-size:14px;font-weight:600;color:#5a6570;padding:8px 18px;border-radius:6px;cursor:pointer;transition:background .15s,color .15s;}
body.woocommerce-checkout .pxc-type-pill.is-active{background:var(--pxc-navy);color:#fff;}
body.woocommerce-checkout .pxc-recipient-form .form-row.pxc-firma-only{display:none;}
body.woocommerce-checkout .pxc-recipient-form.pxc-type-firma .form-row.pxc-firma-only{display:flex;}
body.woocommerce-checkout .woocommerce-billing-fields__field-wrapper{display:grid;grid-template-columns:1fr 1fr;gap:14px 18px;}
body.woocommerce-checkout .pxc-recipient-form .form-row{margin:0;display:flex;flex-direction:column;}
body.woocommerce-checkout .pxc-recipient-form .form-row-wide,
body.woocommerce-checkout .pxc-recipient-form #billing_company_field,
body.woocommerce-checkout .pxc-recipient-form #billing_nip_field,
body.woocommerce-checkout .pxc-recipient-form #billing_address_1_field,
body.woocommerce-checkout .pxc-recipient-form #billing_email_field,
body.woocommerce-checkout .pxc-recipient-form #billing_phone_field,
body.woocommerce-checkout .pxc-recipient-form #billing_country_field{grid-column:1 / -1;}
body.woocommerce-checkout .pxc-recipient-form #billing_state_field,
body.woocommerce-checkout .pxc-recipient-form #billing_address_2_field{display:none !important;}
body.woocommerce-checkout .pxc-recipient-form label{font-size:13px;font-weight:600;color:var(--pxc-navy);margin-bottom:6px;}
body.woocommerce-checkout .pxc-recipient-form label .required{color:#d34a4a;border:0;}
body.woocommerce-checkout .pxc-recipient-form label .optional{color:var(--pxc-muted);font-weight:400;}
body.woocommerce-checkout .pxc-recipient-form .input-text,
body.woocommerce-checkout .pxc-recipient-form select,
body.woocommerce-checkout .pxc-recipient-form .select2-selection{width:100%;border:1px solid var(--pxc-ibrd);border-radius:0;padding:12px 14px;font-size:15px;font-family:inherit;color:#333;background:#fff;line-height:1.3;box-shadow:none;}
body.woocommerce-checkout .pxc-recipient-form .input-text:focus,
body.woocommerce-checkout .pxc-recipient-form select:focus{outline:none;border-color:var(--pxc-green);box-shadow:0 0 0 2px rgba(120,184,51,.16);}
body.woocommerce-checkout .pxc-recipient-form .woocommerce-input-wrapper{display:block;width:100%;}

/* checkboxy brandowe (same-addr / note / terms) */
body.woocommerce-checkout .pxc-same-addr,
body.woocommerce-checkout .pxc-note-toggle,
body.woocommerce-checkout .pxc-terms-label{display:flex;align-items:flex-start;gap:11px;cursor:pointer;font-size:15px;color:#444;line-height:1.45;}
body.woocommerce-checkout .pxc-same-addr{padding:2px 4px 0;}
body.woocommerce-checkout .pxc-same-addr input,
body.woocommerce-checkout .pxc-note-toggle input,
body.woocommerce-checkout .pxc-terms-label input{position:absolute;opacity:0;width:0;height:0;}
body.woocommerce-checkout .pxc-checkbox-box{flex:none;width:22px;height:22px;border-radius:5px;border:2px solid var(--pxc-ibrd);background:#fff;display:flex;align-items:center;justify-content:center;transition:background .15s,border-color .15s;}
body.woocommerce-checkout .pxc-checkbox-box svg{width:13px;height:13px;stroke:#fff;fill:none;stroke-width:3;stroke-linecap:round;stroke-linejoin:round;opacity:0;}
body.woocommerce-checkout input:checked + .pxc-checkbox-box{background:var(--pxc-navy);border-color:var(--pxc-navy);}
body.woocommerce-checkout input:checked + .pxc-checkbox-box svg{opacity:1;}

/* karta z opcją (wysyłka) — zielona kreska sygnaturowa jak nakład #7 */
body.woocommerce-checkout .pxc-opt-row{position:relative;display:flex;align-items:center;gap:14px;padding:18px 20px;border:1px solid var(--pxc-line);border-radius:10px;}
body.woocommerce-checkout .pxc-opt-selected{border-color:var(--pxc-green);box-shadow:inset 4px 0 0 var(--pxc-green);}
body.woocommerce-checkout .pxc-opt-check{flex:none;width:22px;height:22px;border-radius:50%;background:var(--pxc-green);display:flex;align-items:center;justify-content:center;}
body.woocommerce-checkout .pxc-opt-check svg{width:12px;height:12px;stroke:#fff;fill:none;stroke-width:3;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-checkout .pxc-opt-name{font-size:16px;font-weight:600;color:var(--pxc-navy);}
body.woocommerce-checkout .pxc-opt-right{margin-left:auto;}
body.woocommerce-checkout .pxc-free-lbl{font-size:13px;font-weight:700;letter-spacing:.05em;color:var(--pxc-green-dark);}

/* Twoje naklejki */
body.woocommerce-checkout .pxc-items{display:flex;flex-direction:column;}
body.woocommerce-checkout .pxc-it{display:grid;grid-template-columns:60px 1fr auto;align-items:center;gap:18px;padding:18px 0;border-top:1px solid var(--pxc-line);}
body.woocommerce-checkout .pxc-it:first-child{border-top:none;padding-top:2px;}
body.woocommerce-checkout .pxc-it-thumb{width:60px;height:60px;border-radius:9px;background-color:#eef1f3;background-image:repeating-linear-gradient(45deg,rgba(11,69,125,.05) 0 9px,rgba(11,69,125,.09) 9px 18px);overflow:hidden;}
body.woocommerce-checkout .pxc-it-img{width:100%;height:100%;object-fit:cover;display:block;}
body.woocommerce-checkout .pxc-it-name{font-size:16px;font-weight:700;color:var(--pxc-navy);line-height:1.3;}
body.woocommerce-checkout .pxc-it-params{font-size:13px;color:var(--pxc-muted);margin-top:3px;}
body.woocommerce-checkout .pxc-param-sep{color:#c2cad3;padding:0 2px;}
body.woocommerce-checkout .pxc-it-status{margin-top:7px;display:flex;align-items:center;flex-wrap:wrap;}
body.woocommerce-checkout .pxc-file-status{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;}
body.woocommerce-checkout .pxc-st-dot{flex:none;width:16px;height:16px;border-radius:50%;background:var(--pxc-green);display:flex;align-items:center;justify-content:center;}
body.woocommerce-checkout .pxc-st-dot svg{width:9px;height:9px;stroke:#fff;fill:none;stroke-width:3;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-checkout .pxc-st-ok{color:var(--pxc-navy);}
body.woocommerce-checkout .pxc-st-warn{font-size:12px;color:var(--pxc-muted);text-transform:none;letter-spacing:0;}
body.woocommerce-checkout .pxc-upload-btn{display:inline-flex;align-items:center;gap:5px;font-size:13px;font-weight:700;color:var(--pxc-green);text-decoration:none;}
body.woocommerce-checkout .pxc-upload-btn svg{width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-checkout .pxc-upload-btn:hover{color:var(--pxc-green-dark);}
body.woocommerce-checkout .pxc-meta-sep{color:#c2cad3;padding:0 7px;font-size:12px;}
body.woocommerce-checkout .pxc-it-price{text-align:right;}
body.woocommerce-checkout .pxc-it-net{display:block;font-size:17px;font-weight:700;color:var(--pxc-navy);white-space:nowrap;}
body.woocommerce-checkout .pxc-it-net-lbl{font-size:inherit;font-weight:inherit;}
body.woocommerce-checkout .pxc-it-gross{display:block;font-size:13px;color:var(--pxc-muted);margin-top:2px;white-space:nowrap;}

/* Opcje płatności — bramki jako karty */
body.woocommerce-checkout .pxc-pay-card #payment.woocommerce-checkout-payment{background:transparent !important;border:none !important;border-radius:0;}
body.woocommerce-checkout .pxc-pay-card #payment.woocommerce-checkout-payment .wc_payment_methods{list-style:none;margin:0 !important;padding:0 !important;border:0 !important;display:flex;flex-direction:column;gap:0;}
body.woocommerce-checkout .pxc-pay-card .wc_payment_methods{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:0;}
body.woocommerce-checkout .pxc-pay-card .wc_payment_method{position:relative;border:1px solid var(--pxc-line);border-top:none;padding:0;}
body.woocommerce-checkout .pxc-pay-card .wc_payment_method:first-child{border-top:1px solid var(--pxc-line);border-radius:10px 10px 0 0;}
body.woocommerce-checkout .pxc-pay-card .wc_payment_method:last-child{border-radius:0 0 10px 10px;}
body.woocommerce-checkout .pxc-pay-card .wc_payment_method:only-child{border-radius:10px;}
body.woocommerce-checkout .pxc-pay-card .wc_payment_method > input{position:absolute;opacity:0;width:0;height:0;}
body.woocommerce-checkout .pxc-pay-card .wc_payment_method > label{display:flex;align-items:center;gap:12px;padding:18px 20px;font-size:16px;font-weight:600;color:var(--pxc-navy);cursor:pointer;margin:0;}
body.woocommerce-checkout .pxc-pay-card .wc_payment_method > label::before{content:"";flex:none;width:22px;height:22px;border-radius:50%;border:2px solid var(--pxc-ibrd);transition:border-color .15s;}
body.woocommerce-checkout .pxc-pay-card .wc_payment_method:has(input:checked){box-shadow:inset 4px 0 0 var(--pxc-green);}
body.woocommerce-checkout .pxc-pay-card .wc_payment_method:has(input:checked) > label::before{border:7px solid var(--pxc-green);}
body.woocommerce-checkout #payment.woocommerce-checkout-payment .payment_box{padding:0 20px 18px 54px;margin:0;font-size:14px;color:#5a6570;line-height:1.55;background:transparent !important;border:none !important;border-radius:0;box-shadow:none !important;}
body.woocommerce-checkout #payment.woocommerce-checkout-payment .payment_box p{margin:0;padding:0;}
body.woocommerce-checkout #payment.woocommerce-checkout-payment .payment_box::before{display:none !important;}

/* notatka */
body.woocommerce-checkout .pxc-note-body{margin-top:16px;}
body.woocommerce-checkout .pxc-note-area{width:100%;border:1px solid var(--pxc-ibrd);border-radius:0;padding:12px 14px;font-family:inherit;font-size:15px;resize:vertical;}
body.woocommerce-checkout .pxc-note-area:focus{outline:none;border-color:var(--pxc-green);}

/* podsumowanie (prawa kolumna) */
body.woocommerce-checkout .pxc-summary{position:sticky;top:120px;background:#fff;border:1px solid var(--pxc-line);border-radius:16px;padding:28px;box-shadow:0 2px 14px rgba(11,69,125,.06);}
body.woocommerce-checkout .pxc-sum-head{margin-bottom:20px;}
body.woocommerce-checkout .pxc-sum-title{font-size:20px;font-weight:700;color:var(--pxc-navy);}
body.woocommerce-checkout .pxc-sum-rows{display:flex;flex-direction:column;gap:13px;padding-bottom:18px;border-bottom:1px solid var(--pxc-line);}
body.woocommerce-checkout .pxc-sum-row{display:flex;justify-content:space-between;gap:16px;font-size:15px;}
body.woocommerce-checkout .pxc-sum-row .k{color:#5a6570;}
body.woocommerce-checkout .pxc-sum-row .v{color:var(--pxc-navy);font-weight:600;white-space:nowrap;}
body.woocommerce-checkout .pxc-ship-free{font-size:12px;font-weight:700;letter-spacing:.05em;color:#fff;background:var(--pxc-green);border-radius:5px;padding:4px 10px;}
body.woocommerce-checkout .pxc-sum-total{margin-top:18px;display:flex;flex-direction:column;}
body.woocommerce-checkout .pxc-tlabel{font-size:15px;color:#5a6570;}
body.woocommerce-checkout .pxc-tval{font-size:34px;font-weight:700;color:var(--pxc-navy);line-height:1.1;margin-top:4px;}
body.woocommerce-checkout .pxc-tval .woocommerce-Price-currencySymbol{font-size:20px;}
body.woocommerce-checkout .pxc-tsub{font-size:13px;color:var(--pxc-muted);margin-top:4px;}
body.woocommerce-checkout .pxc-hidden-shipping{display:none !important;}

/* terms */
body.woocommerce-checkout .pxc-terms{margin:18px 0;}
body.woocommerce-checkout .pxc-terms-txt{font-size:13.5px;color:#5a6570;line-height:1.5;}
body.woocommerce-checkout .pxc-terms-txt a{color:var(--pxc-navy);text-decoration:underline;}
body.woocommerce-checkout .pxc-terms-label .pxc-checkbox-box{width:20px;height:20px;}

/* CTA — zielona pigułka z kostką (jak koszyk) */
body.woocommerce-checkout .pxc-btn-cta{position:relative;display:inline-flex;align-items:center;justify-content:flex-start;background:var(--pxc-green);color:#fff;border-radius:50px;font-family:inherit;font-weight:700;font-size:18px;text-transform:uppercase;letter-spacing:.03em;padding:17px 68px 17px 26px;cursor:pointer;overflow:hidden;user-select:none;border:none;text-decoration:none;transition:transform .5s ease,box-shadow .5s ease,background .3s ease;}
body.woocommerce-checkout .pxc-btn-label{transition:opacity .35s ease;white-space:nowrap;position:relative;z-index:1;}
body.woocommerce-checkout .pxc-btn-cube{position:absolute;top:4px;right:4px;bottom:4px;width:48px;background:rgba(255,255,255,.22);border-radius:50px;display:flex;align-items:center;justify-content:center;transition:width .5s ease,background .5s ease;}
body.woocommerce-checkout .pxc-btn-cube svg{width:24px;height:24px;stroke:#fff;stroke-width:2.4;fill:none;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-checkout .pxc-btn-cta:not(:disabled):hover .pxc-btn-label{opacity:0;}
body.woocommerce-checkout .pxc-btn-cta:not(:disabled):hover .pxc-btn-cube{width:calc(100% - 8px);background:rgba(255,255,255,.30);}
body.woocommerce-checkout .pxc-btn-cta:not(:disabled):hover{box-shadow:0 10px 24px rgba(120,184,51,.34);}
body.woocommerce-checkout .pxc-btn-cta:not(:disabled):active{transform:scale(.98);}
body.woocommerce-checkout .pxc-btn-full{width:100%;}
body.woocommerce-checkout .pxc-btn-cta:disabled{background:#c2cad3;color:#fff;cursor:not-allowed;}
body.woocommerce-checkout .pxc-btn-cta:disabled .pxc-btn-cube{background:rgba(255,255,255,.28);}

/* secure + pay badges */
body.woocommerce-checkout .pxc-cs-trust{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:16px;font-size:13px;color:var(--pxc-muted);}
body.woocommerce-checkout .pxc-cs-trust svg{width:15px;height:15px;stroke:var(--pxc-green);fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-checkout .pxc-cs-pay{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:12px;}
body.woocommerce-checkout .pxc-pay-badge{font-size:11px;font-weight:700;letter-spacing:.03em;color:#5a6570;background:#eef1f3;border:1px solid var(--pxc-line);border-radius:6px;padding:5px 10px;}

/* WC processing overlay nie psuje layoutu */
body.woocommerce-checkout .woocommerce-checkout .blockUI.blockOverlay{border-radius:16px;}

/* responsywność */
@media(max-width:980px){
  body.woocommerce-checkout .pxc-co-grid{grid-template-columns:1fr;gap:24px;}
  body.woocommerce-checkout .pxc-summary{position:static;}
}
@media(max-width:560px){
  body.woocommerce-checkout .woocommerce-billing-fields__field-wrapper{grid-template-columns:1fr;}
  body.woocommerce-checkout .pxc-head h1{font-size:30px;}
  body.woocommerce-checkout .pxc-step{font-size:0;gap:0;}
  body.woocommerce-checkout .pxc-step .pxc-dot{font-size:13px;}
}
</style>
	<?php
} );

/* ─────────────────────────────────────────────────────────────────────────
 * 4) JS — Edytuj toggle, Osoba/Firma, notatka, terms→przycisk.
 * ──────────────────────────────────────────────────────────────────────── */
add_action( 'wp_footer', function () {
	if ( ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}
	?>
<script>
(function(){
  function ready(fn){ if(document.readyState!=='loading'){fn();} else {document.addEventListener('DOMContentLoaded',fn);} }
  ready(function(){
    // Edytuj — inline toggle
    var editLink = document.getElementById('pxc-edit-link');
    var form     = document.getElementById('pxc-recipient-form');
    var summary  = document.getElementById('pxc-recipient-summary');
    if(editLink && form){
      editLink.addEventListener('click', function(e){
        e.preventDefault();
        var hidden = form.hasAttribute('hidden');
        if(hidden){ form.removeAttribute('hidden'); if(summary){summary.setAttribute('hidden','');} editLink.textContent='Zwiń'; }
        else { form.setAttribute('hidden',''); if(summary){summary.removeAttribute('hidden');} editLink.textContent='Edytuj'; }
      });
    }

    // Osoba / Firma
    var pills = document.querySelectorAll('.pxc-type-pill');
    var typeInput = document.getElementById('billing_customer_type');
    function applyType(t){
      if(form){ form.classList.toggle('pxc-type-firma', t==='firma'); }
      if(typeInput){ typeInput.value=t; }
      pills.forEach(function(p){ p.classList.toggle('is-active', p.getAttribute('data-type')===t); });
    }
    pills.forEach(function(p){ p.addEventListener('click', function(){ applyType(p.getAttribute('data-type')); }); });
    if(typeInput){ applyType(typeInput.value || 'osoba'); }

    // Notatka
    var noteCb = document.getElementById('pxc-note-toggle');
    var noteBody = document.getElementById('pxc-note-body');
    if(noteCb && noteBody){
      noteCb.addEventListener('change', function(){ if(noteCb.checked){noteBody.removeAttribute('hidden');} else {noteBody.setAttribute('hidden','');} });
    }

    // Terms -> przycisk
    var terms = document.getElementById('terms');
    var place = document.getElementById('place_order');
    function syncBtn(){ if(!place) return; if(terms && terms.checked){ place.disabled=false; place.removeAttribute('aria-disabled'); } else { place.disabled=true; place.setAttribute('aria-disabled','true'); } }
    if(terms){ terms.addEventListener('change', syncBtn); }
    syncBtn();
    // checkout.js odświeża fragmenty — przycisk jest poza fragmentem, więc stan trzyma.
    document.body.addEventListener('updated_checkout', syncBtn);
  });
})();
</script>
	<?php
} );
