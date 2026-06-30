<?php
/**
 * PRINEX — Panel klienta /moje-konto/ (My Account) — CSS/JS + integracja WC (#29).
 * Scope: front-end. Logika guardowana is_account_page() / filtrami konta.
 *
 * Współpracuje z override'ami child theme woocommerce/myaccount/*.php.
 * NIE dotyka: variable.php, #13/#14 (produkt), #21 (koszyk), #28 (checkout).
 * Warstwa 1 (wygląd). Warstwa 2 (social login, GUS, wiele adresów, faktura) — osobno.
 */

defined( 'ABSPATH' ) || exit;

/* ── Menu konta: relabel + ukryj Pobrania (mapowanie 1:1 do mockupu) ── */
add_filter( 'woocommerce_account_menu_items', function ( $items ) {
	unset( $items['downloads'] );
	if ( isset( $items['dashboard'] ) ) {
		$items['dashboard'] = 'Pulpit';
	}
	if ( isset( $items['orders'] ) ) {
		$items['orders'] = 'Zamówienia';
	}
	if ( isset( $items['edit-address'] ) ) {
		$items['edit-address'] = 'Dane do wysyłki';
	}
	if ( isset( $items['edit-account'] ) ) {
		$items['edit-account'] = 'Dane konta';
	}
	if ( isset( $items['customer-logout'] ) ) {
		$items['customer-logout'] = 'Wyloguj';
	}
	return $items;
}, 20 );

/* ── view-order: własne sekcje w view-order.php → bez domyślnej tabeli ── */
add_action( 'init', function () {
	remove_action( 'woocommerce_view_order', 'woocommerce_order_details_table', 10 );
} );

/* ── "Zamów ponownie" dostępne też dla nieopłaconych/w realizacji ── */
add_filter( 'woocommerce_valid_order_statuses_for_order_again', function ( $statuses ) {
	return array_unique( array_merge( (array) $statuses, array( 'completed', 'processing', 'on-hold', 'pending' ) ) );
} );

/* ── Helpery dla szablonów ── */
if ( ! function_exists( 'prinex_status_chip' ) ) {
	function prinex_status_chip( $order ) {
		$s   = $order->get_status();
		$map = array(
			'pending'        => 'amber',
			'on-hold'        => 'amber',
			'processing'     => 'navy',
			'completed'      => 'green',
			'checkout-draft' => 'grey',
			'cancelled'      => 'grey',
			'refunded'       => 'grey',
			'failed'         => 'red',
		);
		$cls  = $map[ $s ] ?? 'grey';
		$name = wc_get_order_status_name( $s );
		$name = function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $name, 'UTF-8' ) : strtoupper( $name );
		return '<span class="pxc-ostat pxc-ostat-' . esc_attr( $cls ) . '"><span class="pxc-ostat-dot"></span>' . esc_html( $name ) . '</span>';
	}
}
if ( ! function_exists( 'prinex_order_again_url' ) ) {
	function prinex_order_again_url( $order ) {
		return wp_nonce_url( add_query_arg( 'order_again', $order->get_id(), wc_get_cart_url() ), 'woocommerce-order_again' );
	}
}

/* ─────────────────────────────────────────────────────────────────────────
 * CSS — scoped body.woocommerce-account. Wzorzec wizualny = checkout/koszyk.
 * ──────────────────────────────────────────────────────────────────────── */
add_action( 'wp_head', function () {
	if ( ! is_account_page() ) {
		return;
	}
	?>
<style>
body.woocommerce-account{--pxc-navy:#0B457D;--pxc-green:#78B833;--pxc-green-dark:#62992a;--pxc-amber:#F39200;--pxc-line:#e1e6ea;--pxc-muted:#8a939c;--pxc-ibrd:#d0d5db;background:#E8ECEF;}
body.woocommerce-account .inside-article{background:transparent !important;padding-top:0 !important;padding-bottom:0 !important;}
body.woocommerce-account .entry-header,
body.woocommerce-account .woocommerce-breadcrumb,
body.woocommerce-account .breadcrumb-trail{display:none !important;}
body.woocommerce-account .woocommerce-notices-wrapper:empty{display:none;}
body.woocommerce-account .woocommerce{margin:0;}

body.woocommerce-account .pxc-acc-wrap{padding:8px 0 56px;}
body.woocommerce-account .pxc-acc-head{margin-bottom:26px;}
body.woocommerce-account .pxc-sig{width:48px;height:2px;background:var(--pxc-green);margin:0 0 14px;}
body.woocommerce-account .pxc-acc-title{font-size:34px;font-weight:700;color:var(--pxc-navy);line-height:1.15;margin:0;}
body.woocommerce-account .pxc-acc-sub{font-size:16px;color:#5a6570;margin:8px 0 0;}
body.woocommerce-account .pxc-acc-back{display:inline-flex;align-items:center;gap:6px;margin-top:10px;font-size:14px;font-weight:700;color:var(--pxc-green-dark);text-decoration:none;}
body.woocommerce-account .pxc-acc-back svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-account .pxc-acc-back:hover{color:var(--pxc-green);}

/* grid nav | content */
body.woocommerce-account .pxc-acc-grid{display:grid;grid-template-columns:248px minmax(0,1fr);gap:32px;align-items:start;}
/* neutralizacja domyślnego floatu/szerokości WC (25%/75%) — psuje grid */
body.woocommerce-account .woocommerce-MyAccount-navigation,
body.woocommerce-account .woocommerce-MyAccount-content{float:none !important;width:100% !important;margin:0 !important;}

/* nawigacja */
body.woocommerce-account .woocommerce-MyAccount-navigation{background:#fff;border:1px solid var(--pxc-line);border-radius:14px;padding:10px;box-shadow:0 2px 14px rgba(11,69,125,.06);position:sticky;top:120px;}
body.woocommerce-account .woocommerce-MyAccount-navigation ul{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:2px;}
body.woocommerce-account .woocommerce-MyAccount-navigation li{margin:0;}
body.woocommerce-account .woocommerce-MyAccount-navigation a{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:9px;font-size:15px;font-weight:600;color:#5a6570;text-decoration:none;position:relative;transition:background .15s,color .15s;}
body.woocommerce-account .pxc-acc-nav-ic{flex:none;width:20px;height:20px;display:flex;}
body.woocommerce-account .pxc-acc-nav-ic svg{width:20px;height:20px;stroke:var(--pxc-green);fill:none;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-account .woocommerce-MyAccount-navigation a:hover{background:#f4f7fb;color:var(--pxc-navy);}
body.woocommerce-account .woocommerce-MyAccount-navigation li.is-active a{background:#f1f4f7;color:var(--pxc-navy);}
body.woocommerce-account .woocommerce-MyAccount-navigation li.is-active a::before{content:"";position:absolute;left:0;top:8px;bottom:8px;width:3px;border-radius:3px;background:var(--pxc-green);}
/* Wyloguj — szary + separator (wg mockupu, NIE czerwony) */
body.woocommerce-account .woocommerce-MyAccount-navigation li.woocommerce-MyAccount-navigation-link--customer-logout{border-top:1px solid var(--pxc-line);margin-top:6px;padding-top:6px;}
body.woocommerce-account .woocommerce-MyAccount-navigation li.woocommerce-MyAccount-navigation-link--customer-logout a{color:var(--pxc-muted);}
body.woocommerce-account .woocommerce-MyAccount-navigation li.woocommerce-MyAccount-navigation-link--customer-logout .pxc-acc-nav-ic svg{stroke:var(--pxc-muted);}

/* karty wspólne */
body.woocommerce-account .pxc-card{background:#fff;border:1px solid var(--pxc-line);border-radius:14px;padding:26px 28px;box-shadow:0 2px 14px rgba(11,69,125,.06);}
body.woocommerce-account .pxc-card + .pxc-card{margin-top:22px;}
body.woocommerce-account .pxc-card-title{font-size:19px;font-weight:700;color:var(--pxc-navy);margin:0 0 18px;}

/* przyciski */
body.woocommerce-account .pxc-btn-cta{position:relative;display:inline-flex;align-items:center;justify-content:flex-start;background:var(--pxc-green);color:#fff;border-radius:50px;font-family:inherit;font-weight:700;font-size:16px;text-transform:uppercase;letter-spacing:.03em;padding:15px 60px 15px 24px;cursor:pointer;overflow:hidden;border:none;text-decoration:none;transition:transform .5s ease,box-shadow .5s ease;}
body.woocommerce-account .pxc-btn-label{transition:opacity .35s ease;white-space:nowrap;position:relative;z-index:1;}
body.woocommerce-account .pxc-btn-cube{position:absolute;top:4px;right:4px;bottom:4px;width:44px;background:rgba(255,255,255,.22);border-radius:50px;display:flex;align-items:center;justify-content:center;transition:width .5s ease,background .5s ease;}
body.woocommerce-account .pxc-btn-cube svg{width:22px;height:22px;stroke:#fff;stroke-width:2.4;fill:none;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-account .pxc-btn-cta:hover .pxc-btn-label{opacity:0;}
body.woocommerce-account .pxc-btn-cta:hover .pxc-btn-cube{width:calc(100% - 8px);background:rgba(255,255,255,.30);}
body.woocommerce-account .pxc-btn-cta:hover{box-shadow:0 10px 24px rgba(120,184,51,.32);}
body.woocommerce-account .pxc-btn-full{width:100%;justify-content:flex-start;}
body.woocommerce-account .pxc-btn-sm{display:inline-flex;align-items:center;gap:6px;background:var(--pxc-green);color:#fff;border-radius:50px;font-weight:700;font-size:13px;padding:8px 16px;text-decoration:none;border:none;cursor:pointer;transition:background .15s;}
body.woocommerce-account .pxc-btn-sm:hover{background:var(--pxc-green-dark);}
body.woocommerce-account .pxc-btn-navy{display:inline-flex;align-items:center;gap:8px;background:var(--pxc-navy);color:#fff;border-radius:50px;font-weight:700;font-size:14px;text-transform:uppercase;letter-spacing:.03em;padding:13px 22px;text-decoration:none;transition:background .15s;}
body.woocommerce-account .pxc-btn-navy svg{width:18px;height:18px;stroke:#fff;fill:none;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-account .pxc-btn-navy:hover{background:#0a3d6e;}
body.woocommerce-account .pxc-tile-link{font-size:14px;font-weight:600;color:var(--pxc-navy);text-decoration:underline;}
body.woocommerce-account .pxc-tile-link:hover{color:var(--pxc-green-dark);}

/* statusy zamówień (uppercase + kropka, wg mockupu) */
body.woocommerce-account .pxc-ostat{display:inline-flex;align-items:center;gap:7px;font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;border-radius:50px;padding:6px 13px;white-space:nowrap;}
body.woocommerce-account .pxc-ostat-dot{flex:none;width:7px;height:7px;border-radius:50%;background:currentColor;}
body.woocommerce-account .pxc-ostat-amber{background:#fdf0db;color:#9a6207;}
body.woocommerce-account .pxc-ostat-navy{background:#e3edf7;color:var(--pxc-navy);}
body.woocommerce-account .pxc-ostat-green{background:#eaf4dc;color:#4e7d18;}
body.woocommerce-account .pxc-ostat-grey{background:#eef1f3;color:#5a6570;}
body.woocommerce-account .pxc-ostat-red{background:#fbe9e9;color:#b23b32;}

/* ── PULPIT (1:1 mockup) ── */
body.woocommerce-account .pxc-dash{display:flex;flex-direction:column;gap:18px;}
body.woocommerce-account .pxc-tile{background:#fff;border:1px solid var(--pxc-line);border-radius:14px;padding:22px 24px;box-shadow:0 2px 14px rgba(11,69,125,.06);}
body.woocommerce-account .pxc-tile-lbl{font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--pxc-muted);margin-bottom:10px;}
/* kafel ostatniego zamówienia — POZIOMY */
body.woocommerce-account .pxc-tile-order{display:grid;grid-template-columns:88px minmax(0,1fr) auto;align-items:center;gap:22px;}
body.woocommerce-account .pxc-tile-thumb{width:88px;height:88px;border-radius:10px;background-color:#eef1f3;background-image:repeating-linear-gradient(45deg,rgba(11,69,125,.05) 0 9px,rgba(11,69,125,.09) 9px 18px);overflow:hidden;}
body.woocommerce-account .pxc-tile-thumb-img{width:100%;height:100%;object-fit:cover;display:block;}
body.woocommerce-account .pxc-tile-order-body{min-width:0;}
body.woocommerce-account .pxc-tile-order-row{display:flex;align-items:center;gap:14px;}
body.woocommerce-account .pxc-tile-onum{font-size:22px;font-weight:700;color:var(--pxc-navy);}
body.woocommerce-account .pxc-tile-meta{font-size:14px;color:#5a6570;margin-top:8px;}
body.woocommerce-account .pxc-tile-meta strong{color:var(--pxc-navy);font-weight:700;}
body.woocommerce-account .pxc-tile-order-actions{display:flex;align-items:center;gap:12px;flex:none;}
/* 2 kolumny: CTA + warn */
body.woocommerce-account .pxc-dash-2col{display:grid;grid-template-columns:1fr 1fr;gap:18px;align-items:stretch;}
body.woocommerce-account .pxc-dash-2col.pxc-dash-1{grid-template-columns:1fr;}
body.woocommerce-account .pxc-tile-cta{background:var(--pxc-navy);border-color:var(--pxc-navy);display:flex;flex-direction:column;align-items:flex-start;}
body.woocommerce-account .pxc-tile-eyebrow{font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:rgba(255,255,255,.55);}
body.woocommerce-account .pxc-tile-cta-h{display:block;font-size:18px;font-weight:700;color:#fff;line-height:1.3;margin:8px 0 18px;}
body.woocommerce-account .pxc-btn-greenpill{display:inline-flex;align-items:center;gap:10px;background:var(--pxc-green);color:#fff;border-radius:50px;font-weight:700;font-size:14px;text-transform:uppercase;letter-spacing:.03em;padding:11px 8px 11px 22px;text-decoration:none;margin-top:auto;transition:background .15s;}
body.woocommerce-account .pxc-btn-greenpill:hover{background:var(--pxc-green-dark);}
body.woocommerce-account .pxc-btn-greenpill-cube{flex:none;width:30px;height:30px;border-radius:50%;background:rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;}
body.woocommerce-account .pxc-btn-greenpill-cube svg{width:18px;height:18px;stroke:#fff;fill:none;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-account .pxc-tile-warn{background:#fff7ec;border-color:#f3d9ad;display:flex;flex-direction:column;align-items:flex-start;}
body.woocommerce-account .pxc-tile-warn-ic{flex:none;width:42px;height:42px;border-radius:10px;background:#fceccf;display:flex;align-items:center;justify-content:center;margin-bottom:12px;}
body.woocommerce-account .pxc-tile-warn-ic svg{width:22px;height:22px;stroke:var(--pxc-amber);fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-account .pxc-tile-warn-h{display:block;font-size:16px;font-weight:700;color:#5a3c0e;}
body.woocommerce-account .pxc-tile-warn-p{font-size:14px;color:#7a5a1e;line-height:1.45;margin:4px 0 14px;}
body.woocommerce-account .pxc-warn-link{display:inline-flex;align-items:center;gap:7px;font-size:14px;font-weight:700;color:var(--pxc-green-dark);text-decoration:none;margin-top:auto;}
body.woocommerce-account .pxc-warn-link svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-account .pxc-warn-link:hover{color:var(--pxc-green);}
/* outline button (Zobacz/Faktura) */
body.woocommerce-account .pxc-btn-outline{display:inline-flex;align-items:center;gap:8px;border:1px solid var(--pxc-ibrd);background:#fff;border-radius:50px;font-family:inherit;font-size:14px;font-weight:600;color:var(--pxc-navy);padding:9px 18px;text-decoration:none;cursor:pointer;transition:border-color .15s,background .15s;}
body.woocommerce-account .pxc-btn-outline svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-account .pxc-btn-outline:hover{border-color:#c2cad3;background:#f7f9fb;}
body.woocommerce-account .pxc-btn-faktura.is-soon{color:var(--pxc-muted);cursor:default;}
body.woocommerce-account .pxc-btn-faktura.is-soon:hover{border-color:var(--pxc-ibrd);background:#fff;}

/* ── HISTORIA ZAMÓWIEŃ ── */
body.woocommerce-account .pxc-orders{background:#fff;border:1px solid var(--pxc-line);border-radius:14px;overflow:hidden;box-shadow:0 2px 14px rgba(11,69,125,.06);}
body.woocommerce-account .pxc-orders-head,
body.woocommerce-account .pxc-order-row{display:grid;grid-template-columns:0.7fr 0.9fr 1.25fr 0.8fr auto;align-items:center;gap:14px;padding:15px 22px;}
body.woocommerce-account .pxc-orders-head{background:#f7f9fb;font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--pxc-muted);}
body.woocommerce-account .pxc-oh-sort{display:inline-flex;align-items:center;gap:5px;}
body.woocommerce-account .pxc-oh-sort svg{width:13px;height:13px;stroke:var(--pxc-muted);fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;opacity:.6;}
body.woocommerce-account .pxc-order-row{border-top:1px solid var(--pxc-line);font-size:15px;}
body.woocommerce-account .pxc-or-num a{font-weight:700;color:var(--pxc-navy);text-decoration:none;}
body.woocommerce-account .pxc-or-num a:hover{color:var(--pxc-green-dark);}
body.woocommerce-account .pxc-or-date{color:#5a6570;}
body.woocommerce-account .pxc-or-total{font-weight:700;color:var(--pxc-navy);white-space:nowrap;}
body.woocommerce-account .pxc-or-total .woocommerce-Price-amount{font-weight:700;}
body.woocommerce-account .pxc-or-actions{display:flex;align-items:center;gap:14px;justify-content:flex-end;}
body.woocommerce-account .pxc-orders-pag{display:flex;gap:12px;margin-top:18px;}

/* stan pusty */
body.woocommerce-account .pxc-empty{background:#fff;border:1px solid var(--pxc-line);border-radius:14px;box-shadow:0 2px 14px rgba(11,69,125,.06);display:flex;flex-direction:column;align-items:center;text-align:center;padding:64px 24px;}
body.woocommerce-account .pxc-empty-ic{width:72px;height:72px;border-radius:50%;background:#eef1f3;display:flex;align-items:center;justify-content:center;margin-bottom:20px;}
body.woocommerce-account .pxc-empty-ic svg{width:34px;height:34px;stroke:var(--pxc-muted);fill:none;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-account .pxc-empty h2{font-size:24px;font-weight:700;color:var(--pxc-navy);margin:0 0 10px;}
body.woocommerce-account .pxc-empty p{font-size:15px;color:#5a6570;margin:0 0 24px;}

/* ── WIDOK ZAMÓWIENIA ── */
body.woocommerce-account .pxc-vo-top{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:18px;flex-wrap:wrap;}
body.woocommerce-account .pxc-vo-onum{display:flex;align-items:center;gap:14px;}
body.woocommerce-account .pxc-vo-onum-h{font-size:20px;font-weight:700;color:var(--pxc-navy);}
body.woocommerce-account .pxc-vo-date{font-size:14px;color:var(--pxc-muted);}
body.woocommerce-account .pxc-vo-grid{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:24px;align-items:start;}
body.woocommerce-account .pxc-vo-main{display:flex;flex-direction:column;gap:20px;}

/* pozycje (reuse) */
body.woocommerce-account .pxc-items{display:flex;flex-direction:column;}
body.woocommerce-account .pxc-it{display:grid;grid-template-columns:56px 1fr auto;align-items:center;gap:16px;padding:16px 0;border-top:1px solid var(--pxc-line);}
body.woocommerce-account .pxc-it:first-child{border-top:none;padding-top:2px;}
body.woocommerce-account .pxc-it-thumb{width:56px;height:56px;border-radius:9px;background-color:#eef1f3;background-image:repeating-linear-gradient(45deg,rgba(11,69,125,.05) 0 9px,rgba(11,69,125,.09) 9px 18px);overflow:hidden;}
body.woocommerce-account .pxc-it-img{width:100%;height:100%;object-fit:cover;display:block;}
body.woocommerce-account .pxc-it-name{font-size:15px;font-weight:700;color:var(--pxc-navy);line-height:1.3;}
body.woocommerce-account .pxc-it-params{font-size:13px;color:var(--pxc-muted);margin-top:3px;}
body.woocommerce-account .pxc-param-sep{color:#c2cad3;padding:0 2px;}
body.woocommerce-account .pxc-it-status{margin-top:7px;}
body.woocommerce-account .pxc-file-status{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;}
body.woocommerce-account .pxc-st-dot{flex:none;width:16px;height:16px;border-radius:50%;background:var(--pxc-green);display:flex;align-items:center;justify-content:center;}
body.woocommerce-account .pxc-st-dot svg{width:9px;height:9px;stroke:#fff;fill:none;stroke-width:3;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-account .pxc-st-ok{color:var(--pxc-navy);}
body.woocommerce-account .pxc-st-warn{font-size:12px;color:var(--pxc-muted);text-transform:none;letter-spacing:0;}
body.woocommerce-account .pxc-it-price{text-align:right;}
body.woocommerce-account .pxc-it-net{display:block;font-size:15px;font-weight:700;color:var(--pxc-navy);white-space:nowrap;}
body.woocommerce-account .pxc-it-gross{display:block;font-size:13px;color:var(--pxc-muted);margin-top:2px;white-space:nowrap;}

/* status plików per pozycja (mixed-case wg mockupu) */
body.woocommerce-account .pxc-fstat{display:inline-flex;align-items:center;gap:7px;font-size:13px;font-weight:600;}
body.woocommerce-account .pxc-fstat-ok{color:var(--pxc-green-dark);}
body.woocommerce-account .pxc-fstat-ic{flex:none;width:16px;height:16px;border-radius:50%;background:var(--pxc-green);display:flex;align-items:center;justify-content:center;}
body.woocommerce-account .pxc-fstat-ic svg{width:9px;height:9px;stroke:#fff;fill:none;stroke-width:3;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-account .pxc-fstat-warn{color:#b07a1e;}
body.woocommerce-account .pxc-fstat-circ{flex:none;width:14px;height:14px;border-radius:50%;border:2px solid var(--pxc-amber);}

/* dane odbiorcy / adres — osobna karta */
body.woocommerce-account .pxc-vo-addr{display:grid;grid-template-columns:1fr 1fr;gap:24px;}
body.woocommerce-account .pxc-vo-addr-lbl{font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--pxc-muted);margin-bottom:8px;}
body.woocommerce-account .pxc-rs-name{font-size:15px;font-weight:700;color:var(--pxc-navy);}
body.woocommerce-account .pxc-rs-addr{font-size:14px;color:#5a6570;line-height:1.55;margin-top:4px;font-style:normal;}

/* podsumowanie + przelew (reuse) */
body.woocommerce-account .pxc-vo-sum{position:sticky;top:120px;padding:24px;}
body.woocommerce-account .pxc-sum-head{margin-bottom:16px;}
body.woocommerce-account .pxc-sum-title{font-size:18px;font-weight:700;color:var(--pxc-navy);}
body.woocommerce-account .pxc-sum-rows{display:flex;flex-direction:column;gap:11px;padding-bottom:16px;border-bottom:1px solid var(--pxc-line);}
body.woocommerce-account .pxc-sum-row{display:flex;justify-content:space-between;gap:14px;font-size:14px;}
body.woocommerce-account .pxc-sum-row .k{color:#5a6570;}
body.woocommerce-account .pxc-sum-row .v{color:var(--pxc-navy);font-weight:600;white-space:nowrap;}
body.woocommerce-account .pxc-ship-free{font-size:11px;font-weight:700;letter-spacing:.05em;color:#fff;background:var(--pxc-green);border-radius:5px;padding:3px 9px;}
body.woocommerce-account .pxc-sum-total{margin-top:16px;}
body.woocommerce-account .pxc-tlabel{font-size:14px;color:#5a6570;}
body.woocommerce-account .pxc-tval{font-size:30px;font-weight:700;color:var(--pxc-navy);line-height:1.1;margin-top:4px;}
body.woocommerce-account .pxc-tval .woocommerce-Price-currencySymbol{font-size:18px;}
body.woocommerce-account .pxc-tsub{font-size:13px;color:var(--pxc-muted);margin-top:4px;}
body.woocommerce-account .pxc-ty-pay{display:flex;justify-content:space-between;gap:14px;margin-top:16px;padding-top:14px;border-top:1px solid var(--pxc-line);font-size:14px;}
body.woocommerce-account .pxc-ty-pay-k{color:#5a6570;}
body.woocommerce-account .pxc-ty-pay-v{color:var(--pxc-navy);font-weight:600;}
body.woocommerce-account .pxc-ty-bank{margin-top:16px;padding:14px 16px;background:#f4f7fb;border:1px solid #dbe6f2;border-radius:10px;}
body.woocommerce-account .pxc-ty-bank-title{font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--pxc-navy);margin-bottom:10px;}
body.woocommerce-account .pxc-ty-bank-row{display:flex;justify-content:space-between;gap:12px;font-size:13px;padding:4px 0;}
body.woocommerce-account .pxc-ty-bank-row span{color:#5a6570;}
body.woocommerce-account .pxc-ty-bank-row strong{color:var(--pxc-navy);font-weight:600;text-align:right;}
body.woocommerce-account .pxc-ty-todo{color:var(--pxc-amber);font-weight:700;}

/* Dane do przelewu — OSOBNA karta (etykieta nad wartością + KOPIUJ) */
body.woocommerce-account .pxc-vo-bank{padding:24px;}
body.woocommerce-account .pxc-bank-title{margin-bottom:18px;}
body.woocommerce-account .pxc-bank-field{padding:11px 0;border-top:1px solid var(--pxc-line);}
body.woocommerce-account .pxc-bank-field:first-of-type{border-top:none;padding-top:0;}
body.woocommerce-account .pxc-bank-lbl{display:block;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--pxc-muted);margin-bottom:5px;}
body.woocommerce-account .pxc-bank-val{display:flex;align-items:center;justify-content:space-between;gap:12px;}
body.woocommerce-account .pxc-bank-val strong{font-size:15px;font-weight:700;color:var(--pxc-navy);line-height:1.4;}
body.woocommerce-account .pxc-copy{flex:none;width:34px;height:34px;border-radius:8px;border:1px solid var(--pxc-line);background:#f7f9fb;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--pxc-navy);transition:border-color .15s,color .15s,background .15s;}
body.woocommerce-account .pxc-copy svg{width:17px !important;height:17px !important;flex:none;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-account .pxc-copy:hover{border-color:var(--pxc-green);color:var(--pxc-green-dark);}
body.woocommerce-account .pxc-copy.pxc-copied{border-color:var(--pxc-green);color:#fff;background:var(--pxc-green);}

/* ── FORMULARZE (dane konta / adres) — input radius 8px, label uppercase ── */
body.woocommerce-account .pxc-acc-form .pxc-card + .pxc-card{margin-top:22px;}
body.woocommerce-account .pxc-form-grid,
body.woocommerce-account .pxc-addr-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px 18px;}
body.woocommerce-account .pxc-form-row,
body.woocommerce-account .woocommerce-address-fields .form-row{margin:0;display:flex;flex-direction:column;}
body.woocommerce-account .pxc-fr-wide,
body.woocommerce-account .woocommerce-address-fields .form-row-wide{grid-column:1 / -1;}
body.woocommerce-account .pxc-fr-hidden{display:none !important;}
body.woocommerce-account .woocommerce-address-fields #shipping_state_field,
body.woocommerce-account .woocommerce-address-fields #billing_state_field,
body.woocommerce-account .woocommerce-address-fields #shipping_address_2_field,
body.woocommerce-account .woocommerce-address-fields #billing_address_2_field{display:none !important;}
body.woocommerce-account .pxc-form label,
body.woocommerce-account .woocommerce-address-fields label{font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:var(--pxc-muted);margin-bottom:6px;}
body.woocommerce-account .pxc-lbl-hint{font-weight:400;text-transform:none;letter-spacing:0;color:var(--pxc-muted);}
body.woocommerce-account .pxc-form label .required,
body.woocommerce-account .woocommerce-address-fields label .required{color:#d34a4a;border:0;}
body.woocommerce-account .pxc-form .input-text,
body.woocommerce-account .pxc-form input[type=email],
body.woocommerce-account .pxc-form input[type=password],
body.woocommerce-account .woocommerce-address-fields .input-text,
body.woocommerce-account .woocommerce-address-fields select,
body.woocommerce-account .woocommerce-address-fields input{width:100%;border:1px solid var(--pxc-ibrd);border-radius:8px;padding:12px 14px;font-size:15px;font-family:inherit;color:#333;background:#fff;line-height:1.3;}
body.woocommerce-account .pxc-form .input-text:focus,
body.woocommerce-account .pxc-form input:focus,
body.woocommerce-account .woocommerce-address-fields input:focus,
body.woocommerce-account .woocommerce-address-fields select:focus{outline:none;border-color:var(--pxc-green);box-shadow:0 0 0 2px rgba(120,184,51,.16);}
body.woocommerce-account .woocommerce-address-fields .woocommerce-input-wrapper{display:block;width:100%;}
body.woocommerce-account fieldset{border:none;margin:0;padding:0;}
body.woocommerce-account .pxc-form-submit{margin-top:22px;}

/* karty adresów */
body.woocommerce-account .pxc-addr-cards{display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:stretch;}
/* nie pozwól, by ogólna reguła .pxc-card+.pxc-card spychała drugą kartę w gridzie */
body.woocommerce-account .pxc-addr-cards .pxc-card{margin-top:0 !important;height:100%;}
body.woocommerce-account .pxc-addr-card-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;}
body.woocommerce-account .pxc-addr-card-title{font-size:16px;font-weight:700;color:var(--pxc-navy);}
body.woocommerce-account .pxc-addr-edit{display:inline-flex;align-items:center;gap:6px;font-size:14px;font-weight:600;color:var(--pxc-navy);text-decoration:none;}
body.woocommerce-account .pxc-addr-edit:hover{color:var(--pxc-green-dark);}
body.woocommerce-account .pxc-addr-edit svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round;}
body.woocommerce-account .pxc-addr-card{border-radius:12px;}
body.woocommerce-account .pxc-addr-body{font-style:normal;font-size:14px;color:#5a6570;line-height:1.6;}
body.woocommerce-account .pxc-addr-empty{color:var(--pxc-muted);}

/* ── LOGOWANIE / REJESTRACJA ── */
body.woocommerce-account.woocommerce-account:not(.logged-in) .pxc-acc-wrap{display:none;}
body.woocommerce-account .pxc-login-wrap{max-width:460px;margin:30px auto 64px;}
body.woocommerce-account .pxc-login-card{background:#fff;border:1px solid var(--pxc-line);border-radius:16px;padding:32px;box-shadow:0 6px 30px rgba(11,69,125,.08);}
body.woocommerce-account .pxc-login-tabs{display:flex;background:#eef1f3;border-radius:10px;padding:4px;gap:4px;margin-bottom:24px;}
body.woocommerce-account .pxc-login-tab{flex:1;border:none;background:none;font-family:inherit;font-size:15px;font-weight:700;color:#5a6570;padding:11px;border-radius:7px;cursor:pointer;transition:background .15s,color .15s;}
body.woocommerce-account .pxc-login-tab.is-active{background:#fff;color:var(--pxc-navy);box-shadow:0 1px 4px rgba(11,69,125,.1);}
body.woocommerce-account .pxc-login-h{font-size:24px;font-weight:700;color:var(--pxc-navy);margin:0 0 8px;}
body.woocommerce-account .pxc-login-intro{font-size:15px;color:#5a6570;margin:0 0 20px;}
body.woocommerce-account .pxc-login-note{font-size:13px;color:var(--pxc-muted);margin:0 0 14px;}
body.woocommerce-account .pxc-form-row{margin:0 0 16px;}
body.woocommerce-account .pxc-login-meta{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:4px 0 20px;flex-wrap:wrap;}
body.woocommerce-account .pxc-check-inline{display:flex;align-items:center;gap:9px;cursor:pointer;font-size:14px;color:#5a6570;}
body.woocommerce-account .pxc-check-inline input{position:absolute;opacity:0;width:0;height:0;}
body.woocommerce-account .pxc-checkbox-box{flex:none;width:20px;height:20px;border-radius:5px;border:2px solid var(--pxc-ibrd);background:#fff;display:flex;align-items:center;justify-content:center;transition:background .15s,border-color .15s;}
body.woocommerce-account .pxc-checkbox-box svg{width:12px;height:12px;stroke:#fff;fill:none;stroke-width:3;stroke-linecap:round;stroke-linejoin:round;opacity:0;}
body.woocommerce-account .pxc-check-inline input:checked + .pxc-checkbox-box{background:var(--pxc-navy);border-color:var(--pxc-navy);}
body.woocommerce-account .pxc-check-inline input:checked + .pxc-checkbox-box svg{opacity:1;}
body.woocommerce-account .pxc-login-lost{font-size:14px;font-weight:600;color:var(--pxc-navy);text-decoration:none;}
body.woocommerce-account .pxc-login-lost:hover{color:var(--pxc-green-dark);text-decoration:underline;}
body.woocommerce-account .pxc-login-back{margin:18px 0 0;text-align:center;}
body.woocommerce-account .pxc-login-back a{font-size:14px;font-weight:600;color:var(--pxc-navy);text-decoration:none;}
body.woocommerce-account .pxc-social{margin-top:24px;}
body.woocommerce-account .pxc-social-sep{display:flex;align-items:center;gap:12px;color:var(--pxc-muted);font-size:13px;margin-bottom:16px;}
body.woocommerce-account .pxc-social-sep::before,
body.woocommerce-account .pxc-social-sep::after{content:"";flex:1;height:1px;background:var(--pxc-line);}
body.woocommerce-account .pxc-social-btns{display:flex;gap:12px;}
body.woocommerce-account .pxc-social-btn{flex:1;display:inline-flex;align-items:center;justify-content:center;gap:9px;border:1px solid var(--pxc-line);background:#fff;border-radius:10px;padding:12px;font-family:inherit;font-size:14px;font-weight:600;color:#333;cursor:pointer;transition:border-color .15s,background .15s;}
body.woocommerce-account .pxc-social-btn svg{width:18px;height:18px;}
body.woocommerce-account .pxc-social-btn:hover{border-color:#c2cad3;background:#f7f9fb;}

/* notices brandowe */
body.woocommerce-account .woocommerce-message,
body.woocommerce-account .woocommerce-error,
body.woocommerce-account .woocommerce-info{border-radius:10px;border-left-width:3px;}

/* responsywność */
@media(max-width:900px){
  body.woocommerce-account .pxc-acc-grid{grid-template-columns:1fr;gap:20px;}
  body.woocommerce-account .woocommerce-MyAccount-navigation{position:static;}
  body.woocommerce-account .woocommerce-MyAccount-navigation ul{flex-direction:row;flex-wrap:wrap;}
  body.woocommerce-account .pxc-vo-grid{grid-template-columns:1fr;}
  body.woocommerce-account .pxc-vo-sum{position:static;}
}
@media(max-width:680px){
  body.woocommerce-account .pxc-orders-head{display:none;}
  body.woocommerce-account .pxc-order-row{grid-template-columns:1fr 1fr;gap:8px 16px;}
  body.woocommerce-account .pxc-or-actions{grid-column:1 / -1;justify-content:flex-start;}
  body.woocommerce-account .pxc-form-grid,
  body.woocommerce-account .pxc-addr-grid,
  body.woocommerce-account .pxc-addr-cards,
  body.woocommerce-account .pxc-vo-addr{grid-template-columns:1fr;}
  body.woocommerce-account .pxc-acc-title{font-size:27px;}
}
</style>
	<?php
} );

/* ─────────────────────────────────────────────────────────────────────────
 * JS — taby logowania/rejestracji.
 * ──────────────────────────────────────────────────────────────────────── */
add_action( 'wp_footer', function () {
	if ( ! is_account_page() ) {
		return;
	}
	?>
<script>
(function(){
  function ready(fn){ if(document.readyState!=='loading'){fn();} else {document.addEventListener('DOMContentLoaded',fn);} }
  ready(function(){
    // KOPIUJ — dane do przelewu
    document.querySelectorAll('.pxc-copy').forEach(function(btn){
      btn.addEventListener('click', function(){
        var val = btn.getAttribute('data-copy') || '';
        var done = function(){ btn.classList.add('pxc-copied'); setTimeout(function(){ btn.classList.remove('pxc-copied'); }, 1400); };
        if(navigator.clipboard && navigator.clipboard.writeText){ navigator.clipboard.writeText(val).then(done).catch(function(){}); }
        else { var t=document.createElement('textarea'); t.value=val; document.body.appendChild(t); t.select(); try{document.execCommand('copy');}catch(e){} document.body.removeChild(t); done(); }
      });
    });

    // Taby logowania/rejestracji
    var tabs = document.querySelectorAll('.pxc-login-tab');
    if(!tabs.length) return;
    var panels = document.querySelectorAll('.pxc-login-panel');
    function show(which){
      tabs.forEach(function(t){ t.classList.toggle('is-active', t.getAttribute('data-tab')===which); });
      panels.forEach(function(p){ if(p.getAttribute('data-panel')===which){ p.removeAttribute('hidden'); } else { p.setAttribute('hidden',''); } });
    }
    tabs.forEach(function(t){ t.addEventListener('click', function(){ show(t.getAttribute('data-tab')); }); });
    if(document.querySelector('.woocommerce-form-register .woocommerce-error, .woocommerce-form-register .woocommerce-invalid')){ show('register'); }
  });
})();
</script>
	<?php
} );
