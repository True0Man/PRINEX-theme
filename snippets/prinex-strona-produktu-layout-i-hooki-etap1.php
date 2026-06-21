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
 * UWAGA: zrodlem prawdy jest baza WP (wtyczka Code Snippets). Ten plik to
 * mirror do wersjonowania/code review. Edycja tego pliku NIE zmienia
 * dzialania strony — trzeba wkleic zmiany z powrotem do wp-admin > Code Snippets,
 * lub zaktualizowac wp_snippets.code (np. przez wp-cli/wp eval).
 */

/**
 * PRINEX — Strona produktu: layout 58/42 + hooki konfiguratora (Etap 1)
 *
 * Dziala wylacznie na is_product(). Etap 1 = warstwa wizualna, bez kalkulatora cen.
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
	// "Opinie" dostaje wlasna sekcje w Bloku E. DECYZJA do potwierdzenia w raporcie.
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );

}, 20 );

function prinex_product_breadcrumb() {
	woocommerce_breadcrumb( array(
		'wrap_before' => '<nav class="prinex-cfg-crumb">',
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
	echo '<div class="prinex-cfg-desc">' . wp_kses_post( wpautop( $desc ) ) . ' <a href="#prinex-opis" class="prinex-desc-link">' . esc_html__( 'opis produktu', 'prinex' ) . ' &darr;</a></div>';
}

// Przycisk "Zamawiam" zamiast domyslnego tekstu WooCommerce (Blok C)
add_filter( 'woocommerce_product_single_add_to_cart_text', function() {
	return __( 'Zamawiam', 'prinex' );
} );
add_filter( 'woocommerce_product_add_to_cart_text', function() {
	return __( 'Zamawiam', 'prinex' );
} );

/* ===================== CSS — layout 58/42 + elementy Etapu 1 ===================== */
add_action( 'wp_head', function() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	?>
	<style>
	/* ---- layout 58/42 (na istniejacej strukturze WC .images/.summary, bez forka content-single-product.php) ----
	   UWAGA (BUG 1, naprawione): ".product.type-product" bez kwalifikatora tagu lapalo TRZY rozne elementy —
	   <article class="post-17 product type-product..."> (wrapper GP), <div id="product-17" class="product
	   type-product..."> (prawdziwy kontener WC) ORAZ kazdy <li class="product type-product..."> w "Podobne
	   produkty". Efekt: grid 58/42 nakladal sie podwojnie (article->1 dziecko w kolumnie 1, w nim div->wlasny
	   grid 58/42 z 58% szerokosci) — konfigurator wychodzil na ~42% z 58% = ~24% strony. Selektor zwezony do
	   "div.product.type-product" (tag div) — dopasowuje WYLACZNIE prawdziwy kontener WC (sprawdzone w DOM:
	   dokladnie 1 <div> ma obie klasy razem). */
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
	/* "Podobne produkty" (woocommerce_after_single_product_summary) zostaje jako pelna szerokosc pod konfiguratorem — nie usuwamy, poza zakresem Etapu 1 */
	div.product.type-product > .related.products{ grid-column:1 / -1; }
	div.product.type-product .woocommerce-product-gallery{
		border-radius:14px;
		overflow:hidden;
		box-shadow:0 2px 14px rgba(11,69,125,.08);
	}

	/* ---- breadcrumb w prawej kolumnie ---- */
	.prinex-cfg-crumb{
		display:flex; align-items:center; gap:8px; flex-wrap:wrap;
		font-size:14px; font-weight:600; letter-spacing:.02em;
		color:#78B833; margin-bottom:20px; text-transform:uppercase;
	}
	.prinex-cfg-crumb a{ color:#78B833; text-decoration:none; }
	.prinex-cfg-crumb a:hover{ color:#62992a; }
	.prinex-cfg-crumb .sep{ color:#78B833; opacity:.5; }

	/* ---- tytul / ocena (stylujemy natywne elementy WC, bez zmiany markupu) ---- */
	div.product.type-product .summary .product_title{
		font-family:'Figtree',sans-serif; color:#0B457D; font-weight:700;
		font-size:36px; line-height:1.12; margin:0 0 14px;
	}
	div.product.type-product .summary .star-rating{ font-size:14px; }

	/* ---- opis + link ---- */
	.prinex-cfg-desc{
		font-size:16px; color:#5a6570; line-height:1.55; margin-bottom:30px; max-width:480px;
	}
	.prinex-cfg-desc p{ margin:0 0 8px; }
	.prinex-desc-link{
		color:#62992a; font-weight:700; text-decoration:none;
		border-bottom:1px solid #b9d89a; white-space:nowrap;
	}
	.prinex-desc-link:hover{ border-bottom-color:#62992a; }

	/* ---- bloki numerowane 1/2/3 ---- */
	.prinex-cfg-block{ margin-bottom:30px; }
	.prinex-cfg-label{
		font-size:16px; font-weight:700; letter-spacing:.02em; text-transform:uppercase;
		color:#0B457D; margin-bottom:14px; display:flex; align-items:center; gap:12px;
	}
	.prinex-cfg-num{
		flex:none; width:23px; height:23px; border-radius:50%; background:#78B833; color:#fff;
		font-size:13px; font-weight:700; display:inline-flex; align-items:center; justify-content:center;
		text-transform:none; letter-spacing:0;
	}
	.prinex-lbl-val{
		font-size:13px; font-weight:600; color:#8a939c; letter-spacing:0; text-transform:none;
	}

	/* ---- karta Podsumowanie ---- */
	.prinex-sum-card{
		display:flex; align-items:stretch; justify-content:space-between;
		background:#fff; border:1px solid #E2E8EC; border-radius:12px; padding:24px;
	}
	.prinex-sum-half{ display:flex; flex-direction:column; gap:6px; min-width:0; }
	.prinex-sum-half.prinex-sum-right{ text-align:right; align-items:flex-end; }
	.prinex-sum-lab{ font-size:11px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:#8A939C; }
	.prinex-sum-val{ font-size:28px; font-weight:700; color:#0B457D; line-height:1.1; white-space:nowrap; }
	.prinex-sum-brutto{ font-size:14px; font-weight:500; color:#8A939C; white-space:nowrap; }
	.prinex-sum-div{ width:1px; background:#EEF2F4; margin:2px 22px; flex:none; }

	/* ---- pasek darmowej dostawy (statyczny, Etap 1) ---- */
	.prinex-dlv{ background:#fff; border:1px solid #E2E8EC; border-radius:12px; padding:15px 18px 18px; margin-top:14px; }
	.prinex-dlv-row{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:13px; }
	.prinex-dlv-left{ font-size:12px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:#62992a; }
	.prinex-dlv-left.prinex-dlv-done{ color:#0B457D; }
	.prinex-dlv-right{ font-size:13px; font-weight:700; text-transform:uppercase; color:#0B457D; white-space:nowrap; }
	.prinex-track{ position:relative; height:9px; background:#E9EEF2; border-radius:50px; }
	.prinex-fill{ position:absolute; top:0; left:0; height:100%; background:#78B833; border-radius:50px; }

	/* ---- ukrycie natywnego boxu ceny wariantu + pola Ilosc (mamy wlasna karte Podsumowanie) ----
	   UWAGA (BUG 2a, naprawione): WC JS (add-to-cart-variation.js) robi po wybraniu wariantu
	   .single_variation.slideDown()/.show() — jQuery ustawia INLINE style="display:...", ktory bije
	   zwykly CSS bez !important. Stad cena "349,00 zl (429,27 brutto)" wracala mimo display:none w arkuszu.
	   Pole Ilosc NIE jest ruszane przez ten JS (PHP-only render) — !important tu tylko dla bezpieczenstwa/
	   spojnosci. POTWIERDZONE: ukrycie .quantity przez CSS nie usuwa pola z formularza (input zostaje w DOM,
	   wartosc "1" i tak leci w POST) — dodawanie do koszyka dziala bez zmian, nakl ad i tak wybierany wariantem. */
	div.product.type-product .single_variation_wrap .woocommerce-variation.single_variation{ display:none !important; }
	div.product.type-product .single_variation_wrap .quantity{ display:none !important; }

	/* ---- przycisk ZAMAWIAM (Blok C) ----
	   UWAGA (BUG 2b, naprawione): WooCommerce ma wbudowana regule
	   ".woocommerce:where(body:not(...)) button.button.alt{background-color:#7f54b3}" — :where() ma
	   zerowa specyficznosc, ale tag "button" + klasy "button.alt" dawaly (0,3,1), wyzsze niz nasze
	   (0,3,0) bez tagu — fiolet wygrywal. Dodane !important na background-color (obie reguly, w tym hover). */
	div.product.type-product .single_add_to_cart_button{
		display:block; width:100%; text-align:center;
		background:#78B833 !important; color:#fff !important; border:none; border-radius:50px;
		font-family:'Figtree',sans-serif; font-weight:700; font-size:19px;
		text-transform:uppercase; letter-spacing:.03em; padding:18px 28px;
		cursor:pointer; transition:background .2s ease, box-shadow .2s ease;
	}
	div.product.type-product .single_add_to_cart_button:hover{
		background:#62992a !important; box-shadow:0 10px 24px rgba(120,184,51,.34);
	}

	/* ---- "Nastepny krok: wgraj pliki" ---- */
	.prinex-next-step{
		display:flex; align-items:center; justify-content:center; gap:10px; margin-top:16px;
		font-size:14px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:#0B457D;
	}
	.prinex-next-ic{ width:19px; height:19px; stroke:#78B833; fill:none; stroke-width:1.9; stroke-linecap:round; stroke-linejoin:round; }
	</style>
	<?php
}, 20 );
