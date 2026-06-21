<?php
/**
 * PRINEX — eksport Code Snippet (mirror do wersjonowania, NIE zrodlo prawdy)
 *
 * ID snippetu (wp_snippets.id): 13
 * Tytul:                       PRINEX — Strona produktu: layout 58/42 + hooki konfiguratora (Etap 1)
 * Typ:                         PHP (snippet typu code-snippets; echo CSS w wp_head, zaznaczone w kodzie)
 * Scope:                       front-end — wykonuje sie WYLACZNIE na froncie (nie w wp-admin)
 * Status:                      AKTYWNY
 *
 * ROZBUDOWANY 2026-06-21: CSS przepisany 1:1 ze wzoru Strona Produktu PRINEX.html
 * (klasy cfg-block/qty-list/sum-card/dlv/btn-cta...), reskin zywych swatchy
 * woo-variation-swatches (checkmark CSS, plakietka Popularny CSS ::after).
 *
 * UWAGA: zrodlem prawdy jest baza WP (wtyczka Code Snippets). Ten plik to
 * mirror do wersjonowania/code review. Edycja tego pliku NIE zmienia
 * dzialania strony — trzeba wkleic zmiany z powrotem do wp-admin > Code Snippets,
 * lub zaktualizowac wp_snippets.code (np. przez wp-cli/wp eval).
 */

/**
 * PRINEX — Strona produktu: layout 58/42 + hooki konfiguratora (Etap 1)
 *
 * Dziala wylacznie na is_product(). Etap 1 = warstwa wizualna 1:1 ze wzoru
 * 04-mockupy/02-strona-produktu/Strona Produktu PRINEX.html — bez kalkulatora cen.
 * Klasy CSS przeniesione 1:1 ze wzoru (cfg-block, qty-list, sum-card, dlv, btn-cta...).
 */

// Uwaga: GP Premium dokleja .featured-image.page-header-image-single nad trescia na
// kazdej stronie single — ALE GeneratePress (motyw rodzic) ma wlasna regule CSS
// '.woocommerce .page-header-image-single { display:none; }' (inc/plugin-compat.php),
// ktora juz globalnie to chowa na wszystkich stronach WooCommerce. Sprawdzone w DOM +
// CSS cascade (brak nadpisania o wyzszej specyficznosci) — nie wymaga dodatkowej akcji.

add_action( 'wp', function() {

	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	// Breadcrumb: z domyslnego miejsca nad .product do prawej kolumny (konfigurator)
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
	add_action( 'woocommerce_single_product_summary', 'prinex_product_breadcrumb', 4 );

	// Zdejmujemy natywny box ceny — zastapiony statyczna karta "Podsumowanie" w variable.php
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );

	// Opis: krotki opis produktu + link "opis produktu" do sekcji O materiale (zamiast goleg excerpt)
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
	add_action( 'woocommerce_single_product_summary', 'prinex_product_description_with_link', 20 );

	// SKU/Kategoria/Tagi i social sharing — nie sa czescia nowego projektu
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

	// Domyslne zakladki "Informacje dodatkowe / Opinie" — redundantne z konfiguratorem;
	// "Opinie" dostaje wlasna sekcje nizej na stronie.
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );

}, 20 );

function prinex_product_breadcrumb() {
	woocommerce_breadcrumb( array(
		'wrap_before' => '<nav class="cfg-crumb">',
		'wrap_after'  => '</nav>',
		'delimiter'   => ' <span class="sep">/</span> ',
	) );
}

function prinex_product_description_with_link() {
	global $product;
	if ( ! $product ) {
		return;
	}
	$desc = $product->get_short_description();
	if ( ! $desc ) {
		return;
	}
	echo '<div class="cfg-desc">' . wp_kses_post( wpautop( $desc ) ) . ' <a href="#prinex-opis" class="prinex-desc-link">' . esc_html__( 'opis produktu', 'prinex' ) . ' &darr;</a></div>';
}

// Przycisk "Zamawiam" zamiast domyslnego tekstu WooCommerce
add_filter( 'woocommerce_product_single_add_to_cart_text', function() {
	return __( 'Zamawiam', 'prinex' );
} );
add_filter( 'woocommerce_product_add_to_cart_text', function() {
	return __( 'Zamawiam', 'prinex' );
} );

/* ===================== CSS — layout 58/42 + konfigurator wg wzoru (Etap 1) ===================== */
add_action( 'wp_head', function() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	?>
	<style>
	:root{
		--navy:#0B457D;
		--green:#78B833;
		--green-dark:#62992a;
	}

	/* ---- layout 58/42 (na istniejacej strukturze WC .images/.summary, bez forka content-single-product.php) ----
	   UWAGA (BUG 1, naprawione 2026-06-21): ".product.type-product" bez kwalifikatora tagu lapalo TRZY
	   rozne elementy — <article class="post-17 product type-product..."> (wrapper GP), <div id="product-17"
	   class="product type-product..."> (prawdziwy kontener WC) ORAZ kazdy <li class="product type-product...">
	   w "Podobne produkty". Selektor zwezony do "div.product.type-product" (tag div) — dopasowuje WYLACZNIE
	   prawdziwy kontener WC (sprawdzone w DOM: dokladnie 1 <div> ma obie klasy razem). */
	div.product.type-product{
		display:grid;
		grid-template-columns:minmax(0,58fr) minmax(0,42fr);
		gap:56px;
		align-items:start;
	}
	div.product.type-product > .images,
	div.product.type-product > .summary{
		float:none;
		width:auto;
		margin:0;
	}
	div.product.type-product > .images{ grid-column:1; grid-row:1; position:sticky; top:128px; }
	div.product.type-product > .summary{ grid-column:2; grid-row:1; }
	/* "Podobne produkty" zostaje jako pelna szerokosc pod konfiguratorem — dzialajaca funkcja, nie usuwamy */
	div.product.type-product > .related.products{ grid-column:1 / -1; }
	div.product.type-product .woocommerce-product-gallery{
		border-radius:14px;
		overflow:hidden;
		box-shadow:0 2px 14px rgba(11,69,125,.08);
	}

	/* ===================== KONFIGURATOR — klasy 1:1 ze wzoru ===================== */

	/* breadcrumb */
	.cfg-crumb{
		display:flex; align-items:center; gap:11px; flex-wrap:wrap;
		font-size:16px; font-weight:600; letter-spacing:.02em;
		color:var(--green); margin-bottom:20px;
	}
	.cfg-crumb a{ color:var(--green); text-decoration:none; }
	.cfg-crumb a:hover{ color:var(--green-dark); }
	.cfg-crumb .sep{ color:var(--green); opacity:.5; }

	/* tytul / ocena (stylujemy natywne elementy WC, markup bez zmian) */
	div.product.type-product .summary .product_title{
		font-family:'Figtree',sans-serif; color:var(--navy); font-weight:700;
		font-size:36px; line-height:1.12; margin:0 0 14px;
	}
	div.product.type-product .summary .star-rating{ font-size:14px; }

	/* opis + link "opis produktu" */
	.cfg-desc{
		font-size:16px; color:#5a6570; line-height:1.55; margin-bottom:30px; max-width:480px;
	}
	.cfg-desc p{ margin:0 0 8px; }
	.prinex-desc-link{
		color:var(--green-dark); font-weight:700; text-decoration:none;
		border-bottom:1px solid #b9d89a; white-space:nowrap;
	}
	.prinex-desc-link:hover{ border-bottom-color:var(--green-dark); }

	/* bloki numerowane 1/2/3 */
	.cfg-block{ margin-bottom:30px; }
	.cfg-label{
		font-size:16px; font-weight:700; letter-spacing:.02em; text-transform:uppercase;
		color:var(--navy); margin-bottom:14px; display:flex; align-items:center; gap:12px;
	}
	.cfg-num{
		flex:none; width:23px; height:23px; border-radius:50%; background:var(--green); color:#fff;
		font-size:13px; font-weight:700; display:inline-flex; align-items:center; justify-content:center;
		text-transform:none; letter-spacing:0;
	}
	.lbl-val{ font-size:13px; font-weight:500; color:#8a939c; letter-spacing:0; text-transform:none; }

	/* nagłowek Nakladu — grid 1fr auto 112px, "2 Nakład" do lewej / "netto / brutto" do prawej */
	.cfg-label-nak{ display:grid; grid-template-columns:1fr auto 112px; gap:12px; align-items:center; padding:0 20px 0 0; }
	.cfg-label-nak .nak-head-left{ display:flex; align-items:center; gap:12px; font-size:16px; font-weight:700; text-transform:uppercase; color:var(--navy); }
	.cfg-label-nak .nak-col-desc{ justify-self:end; font-size:14px; font-weight:500; letter-spacing:0; text-transform:none; color:#9AA6AF; }

	/* ===== qty-list — kontener listy (wlasciwy <ul> wtyczki jest WEWNATRZ tego diva) ===== */
	.qty-list{ display:flex; flex-direction:column; border:1.5px solid #e1e6ea; border-radius:12px; overflow:hidden; background:#fff; }
	#qtyList.qty-list{ border:1px solid #E2E8EC; border-radius:14px; }

	/* ===== reskin zywych swatchy woo-variation-swatches na .opt-row (bez zmiany logiki/JS wtyczki) ===== */
	#sizeList .variable-items-wrapper,
	#qtyList .variable-items-wrapper{
		display:flex; flex-direction:column; list-style:none; margin:0; padding:0; width:100%;
	}
	#sizeList .variable-item,
	#qtyList .variable-item{
		position:relative; display:flex; align-items:center; gap:14px; width:100%; height:auto;
		padding:17px 20px; margin:0; border:none; border-top:1.5px solid #e1e6ea; background:#fff;
		cursor:pointer; float:none; box-sizing:border-box; transition:background .15s; text-align:left;
	}
	#sizeList .variable-item:first-child,
	#qtyList .variable-item:first-child{ border-top:none; }
	#sizeList .variable-item:hover,
	#qtyList .variable-item:hover{ background:#f7faf2; }
	/* zaznaczony: tlo wg wzoru, BEZ granatowego paska (wzor: .opt-row.sel::before{content:none}) — nie dodajemy ::before w ogole */
	#sizeList .variable-item.selected{ background:#103d8b0d; }
	#qtyList .variable-item.selected{ background:#F5F8FA; }
	#qtyList.qty-list .variable-item{ border-top:1px solid #EEF2F4; }

	/* checkmark — czysty CSS (::before na variable-item-contents), zero JS/markupu */
	#sizeList .variable-item-contents{
		display:flex; align-items:center; gap:14px; width:100%; min-width:0;
	}
	#sizeList .variable-item-contents::before{
		content:""; flex:none; width:24px; height:24px; border-radius:50px; border:2px solid #dce0e5;
		background-color:transparent; background-repeat:no-repeat; background-position:center; background-size:14px;
		transition:border-color .15s, background-color .15s;
	}
	#sizeList .variable-item.selected .variable-item-contents::before{
		border-color:var(--green); background-color:var(--green);
		background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12l5 5 9-10'/%3E%3C/svg%3E");
	}
	#sizeList .variable-item-span{
		font-size:17px; font-weight:500; color:var(--navy); white-space:nowrap;
	}
	#sizeList .variable-item.selected .variable-item-span{ font-weight:700; }

	/* "Popularny" — czysty CSS ::after na klasie per-wartosc generowanej przez wtyczke (button-variable-item-{slug}); zero JS, zero ryzyka zlego sluga */
	#sizeList .variable-item.button-variable-item-50-x-80 .variable-item-contents::after{
		content:"Popularny"; margin-left:auto; flex:none;
		font-size:12px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:#fff;
		background-color:#F39200; padding:5px 14px; border-radius:50px; line-height:1;
	}

	/* ===== #qtyList — wiersze Nakladu, wnetrze <li> wstrzykniete w PHP (snippet #7), grid 1fr auto 112px ===== */
	#qtyList .variable-item{
		display:grid; grid-template-columns:1fr auto 112px; gap:12px; align-items:center; align-content:center;
		height:48px; padding:17px 20px; line-height:1;
	}
	#qtyList .variable-item *{ line-height:1; }
	.nak-left{ display:flex; align-items:center; gap:14px; min-width:0; }
	.nak-left .opt-check{
		flex:none; width:24px; height:24px; border-radius:50px; border:2px solid #dce0e5;
		display:flex; align-items:center; justify-content:center; transition:border-color .15s, background-color .15s;
	}
	.nak-left .opt-check svg{ width:15px; height:15px; stroke:#fff; fill:none; stroke-width:3; stroke-linecap:round; stroke-linejoin:round; opacity:0; }
	#qtyList .variable-item.selected .nak-left .opt-check{ border-color:var(--green); background-color:var(--green); }
	#qtyList .variable-item.selected .nak-left .opt-check svg{ opacity:1; }
	.nak-left .opt-row-title{ font-size:17px; font-weight:700; color:var(--navy); white-space:nowrap; }
	.nak-price{ display:flex; align-items:center; justify-content:flex-end; justify-self:end; text-align:right; white-space:nowrap; }
	.nak-price .net{ font-size:17px; font-weight:700; color:var(--navy); }
	.nak-price .sl{ color:#B8C2CA; margin:0 5px; }
	.nak-price .br{ font-size:17px; font-weight:500; color:var(--navy); }
	.nak-disc{ justify-self:end; text-align:right; font-size:14px; font-weight:700; color:var(--green-dark); white-space:nowrap; }

	/* plakietki (pill) — uzywane przez snippet #7 (HIT) inline ze wzoru */
	.pill{ font-size:12px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:#fff; padding:5px 14px; border-radius:50px; line-height:1; white-space:nowrap; flex:none; }
	.pill-opt{ background:#F39200; }

	/* ===== karta Podsumowanie ===== */
	.sum-card{
		display:flex; align-items:stretch; justify-content:space-between;
		background:#fff; border:1px solid #E2E8EC; border-radius:12px; padding:24px;
	}
	.sum-half{ display:flex; flex-direction:column; gap:6px; min-width:0; }
	.sum-half.right{ text-align:right; align-items:flex-end; }
	.sum-lab{ font-size:11px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; white-space:nowrap; }
	.sum-lab.grey{ color:#6B7680; }
	.sum-lab.navy{ color:var(--navy); }
	.sum-val{ font-size:28px; font-weight:700; color:var(--navy); line-height:1.1; white-space:nowrap; }
	.sum-brutto{ font-size:14px; font-weight:500; color:#8A939C; white-space:nowrap; }
	.sum-div{ width:1px; background:#EEF2F4; margin:2px 22px; flex:none; }

	/* ===== pasek darmowej dostawy (statyczny, Etap 1) ===== */
	.dlv{ background:#fff; border:1px solid #E2E8EC; border-radius:12px; padding:15px 18px 18px; margin-top:14px; }
	.dlv-row{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:13px; }
	.dlv-left{ font-size:12px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:var(--navy); }
	.dlv-right{ font-size:13px; font-weight:700; text-transform:uppercase; display:inline-flex; align-items:center; gap:6px; color:var(--navy); white-space:nowrap; }
	.dlv-right svg{ width:16px; height:16px; flex:none; }
	.track{ position:relative; height:9px; background:#E9EEF2; border-radius:50px; }
	.fill{ position:absolute; top:0; left:0; height:100%; background:var(--green); border-radius:50px; }
	.truck{
		position:absolute; top:50%; width:40px; height:40px; border-radius:50%; background:#fff;
		border:1px solid var(--green); display:flex; align-items:center; justify-content:center;
		transform:translate(-50%,-50%);
	}
	.truck svg{ width:26px; height:26px; }

	/* ===== ukrycie natywnego boxu ceny wariantu + pola Ilosc (mamy wlasna karte Podsumowanie) ====
	   UWAGA (BUG 2a, naprawione 2026-06-21): WC JS (add-to-cart-variation.js) robi po wybraniu wariantu
	   .single_variation.slideDown()/.show() — jQuery ustawia INLINE style="display:...", ktory bije
	   zwykly CSS bez !important. Pole Ilosc NIE jest ruszane przez ten JS — !important tu dla spojnosci.
	   POTWIERDZONE: ukrycie .quantity przez CSS nie usuwa pola z formularza (input zostaje w DOM,
	   wartosc "1" i tak leci w POST) — dodawanie do koszyka dziala bez zmian. */
	div.product.type-product .single_variation_wrap .woocommerce-variation.single_variation{ display:none !important; }
	div.product.type-product .single_variation_wrap .quantity{ display:none !important; }

	/* ===== cfg-order + przycisk ZAMAWIAM (btn-cta, wg wzoru — ikona + cube/strzalka + hover) ====
	   UWAGA (BUG 2b, naprawione 2026-06-21): WooCommerce ma wbudowana regule
	   ".woocommerce:where(body:not(...)) button.button.alt{background-color:#7f54b3}" — :where() ma
	   zerowa specyficznosc, ale tag "button" + klasy "button.alt" dawaly (0,3,1), wyzsze niz nasz
	   selektor klasowy bez tagu — fiolet wygrywal. Stad !important na tle (reguła + :hover) ponizej. */
	.cfg-order{ margin-top:24px; }
	div.product.type-product .btn-cta{
		position:relative; display:inline-flex; align-items:center; justify-content:flex-start;
		width:100%; background:var(--green) !important; color:#fff !important; border:none; border-radius:50px;
		font-family:'Figtree',sans-serif; font-weight:700; font-size:19px; text-transform:uppercase; letter-spacing:.03em;
		padding:18px 70px 18px 28px; cursor:pointer; overflow:hidden; user-select:none;
		transition:transform .5s ease, background .5s ease, box-shadow .5s ease;
	}
	div.product.type-product .btn-cta-ic{ display:inline-flex; align-items:center; margin-right:13px; position:relative; z-index:1; }
	div.product.type-product .btn-cta-ic svg{ width:23px; height:23px; stroke:#fff; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
	div.product.type-product .btn-cta-label{ transition:opacity .35s ease; white-space:nowrap; position:relative; z-index:1; }
	div.product.type-product .btn-cta:hover .btn-cta-label,
	div.product.type-product .btn-cta:hover .btn-cta-ic{ opacity:0; }
	div.product.type-product .btn-cta-cube{
		position:absolute; top:4px; right:4px; bottom:4px; width:50px;
		background:rgba(255,255,255,.22); border-radius:50px;
		display:flex; align-items:center; justify-content:center;
		transition:width .5s ease, background .5s ease;
	}
	div.product.type-product .btn-cta:hover .btn-cta-cube{ width:calc(100% - 8px); background:rgba(255,255,255,.30); }
	div.product.type-product .btn-cta-cube svg{ width:24px; height:24px; stroke:#fff; stroke-width:2.4; fill:none; }
	div.product.type-product .btn-cta:hover{ background:var(--green-dark) !important; box-shadow:0 10px 24px rgba(120,184,51,.34); }
	div.product.type-product .btn-cta:active{ transform:scale(.98); }

	/* "Następny krok: wgraj pliki" */
	.next-step{
		display:flex; align-items:center; justify-content:center; gap:10px; margin-top:16px;
		font-size:14px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:var(--navy);
	}
	.next-step svg{ width:19px; height:19px; stroke:var(--green); fill:none; stroke-width:1.9; stroke-linecap:round; stroke-linejoin:round; }
	</style>
	<?php
}, 20 );
