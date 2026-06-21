# Code Snippets — mirror do wersjonowania

**Źródłem prawdy jest baza WordPressa** (tabela `wp_snippets`, wtyczka Code
Snippets). Pliki w tym katalogu to **mirror** wyeksportowany do code review i
historii zmian w Git — edycja pliku tutaj **nie zmienia działania strony**.
Żeby zmiana zadziałała na żywo, trzeba ją wkleić z powrotem do wp-admin →
Snippets (albo zaktualizować `wp_snippets.code` przez wp-cli/`wp eval`), a
potem ponownie wyeksportować ten katalog dla zgodności.

Każdy plik ma na górze nagłówek z ID snippetu, scope i statusem
(aktywny/nieaktywny) — zgodny z `manifest.json` w tym katalogu.

## Lista snippetów

| ID | Plik | Scope | Status | Co robi |
|---|---|---|---|---|
| 1 | `make-upload-filenames-lowercase.php` | global | nieaktywny | Przykładowy snippet z instalacji wtyczki — wymusza małe litery w nazwach wgrywanych plików. Nieużywany w PRINEX. |
| 2 | `disable-admin-bar.php` | front-end | nieaktywny | Przykładowy snippet z instalacji wtyczki — wyłącza górną belkę WP-admina dla wszystkich oprócz adminów. Nieużywany w PRINEX. |
| 3 | `allow-smilies.php` | global | nieaktywny | Przykładowy snippet z instalacji wtyczki — emotikony w nietypowych miejscach. Nieużywany w PRINEX. |
| 4 | `current-year.php` | content (shortcode) | nieaktywny | Przykładowy shortcode wstawiający aktualny rok. Nieużywany w PRINEX. |
| 5 | `prinex-ukryj-wybierz-opcje-w-dropdownach.php` | front-end | **aktywny** | Usuwa opcję "Wybierz opcję" (`show_option_none`) z domyślnych dropdownów wariantów WooCommerce. |
| 6 | `prinex-radio-buttons-zamiast-dropdownow-dla-wariantow.php` | front-end | nieaktywny | Zamienia dropdowny wariantów na radio buttony — zastąpione wtyczką Variation Swatches, zostawione jako odłożony kod. |
| 7 | `prinex-dodaj-cene-i-rabat-do-swatchow-nakladu.php` | front-end | **aktywny** | PRZEPISANY 2026-06-21 z JS na filtr PHP `woocommerce_dropdown_variation_attribute_options_html` — wstrzykuje realne ceny netto/brutto + "Taniej o X%" do wierszy Nakładu. Zero JS. |
| 8 | `prinex-dodaj-radio-i-popularny-do-swatchow-rozmiaru.php` | front-end | nieaktywny | NIEUŻYWANY od 2026-06-21 — plakietka "Popularny" zrobiona czystym CSS w snippecie #13 (`::after` na klasie generowanej przez wtyczkę swatchy). Zostawiony jako odłożony kod. |
| 9 | `prinex-figtree-google-font.php` | front-end | **aktywny** | Ładuje font Figtree (400/600/700) z Google Fonts dla całego serwisu. |
| 10 | `prinex-svg-upload-support.php` | **global** | **aktywny** | Zezwala na wgrywanie SVG/SVGZ do biblioteki mediów. Naprawiony 2026-06-19 — patrz niżej. |
| 11 | `prinex-strona-glowna-css-js-z-eksportu-claude-design.php` | front-end | **aktywny** | Pełny CSS/JS strony głównej (hero, kafle, FAQ, opinie, responsywność) — przeniesiony 1:1 z makiety `04-mockupy/01-strona-glowna/strona-glowna-unpacked.html`. Ładuje się tylko na `is_front_page()`. |
| 12 | `prinex-kategoria-naklejki-3d-premium-layout-css-js.php` | front-end | **aktywny** | CSS/JS strony kategorii "Naklejki 3D Premium" (breadcrumb, filtry, siatka kafli, sekcja SEO) — wyłącznie dla `product_cat = naklejki-3d-premium`. |
| 13 | `prinex-strona-produktu-layout-i-hooki-etap1.php` | front-end | **aktywny** | Strona produktu, Etap 1: layout 58/42, hooki WooCommerce, CSS 1:1 ze wzoru (qty-list/opt-row/sum-card/dlv/btn-cta), reskin żywych swatchy, plakietka Popularny — wyłącznie dla `is_product()`. Patrz niżej. |

## 🧩 Strona produktu — Etap 1 (warstwa wizualna 1:1 ze wzoru), 2026-06-21

Konfigurator Format/Nakład + Podsumowanie + ZAMAWIAM zbudowany na **czterech
mechanizmach naraz**, wszystkie potrzebne razem:

- **Snippet #13** (ten katalog) — layout 58/42, hooki WooCommerce, CSS (klasy
  1:1 ze wzoru: `cfg-block`, `cfg-label`, `cfg-num`, `qty-list`, `cfg-label-nak`,
  `sum-card`, `dlv`, `btn-cta`...).
- **Snippet #7** — filtr PHP `woocommerce_dropdown_variation_attribute_options_html`
  (priorytet 21, po wtyczce woo-variation-swatches na priorytecie 20) —
  wstrzykuje ceny netto/brutto + "Taniej o X%" do wierszy Nakładu, liczone z
  realnych wariantów domyślnego Formatu. Zero JS.
- **`../woocommerce/single-product/add-to-cart/variable.php`** — override
  szablonu WooCommerce (plik motywu, **nie** Code Snippet) — bloki "1 Format" /
  "2 Nakład" / "3 Podsumowanie", pasek dostawy, `cfg-order` z przyciskiem +
  podpisem. Zachowuje `wc_dropdown_variation_attribute_options()` per atrybut
  (wymagane przez wtyczkę woo-variation-swatches) oraz `.variations`/
  `.single_variation`/`.single_variation_wrap` (wymagane przez
  `assets/js/frontend/add-to-cart-variation.js` — bez tego dopasowywanie
  wariantu i dodanie do koszyka przestaje działać).
- **`../woocommerce/single-product/add-to-cart/variation-add-to-cart-button.php`**
  — override (plik motywu) — dodaje `span.btn-cta-ic`/`btn-cta-label`/`btn-cta-cube`
  do przycisku (ikona + strzałka + hover wg wzoru). Potrzebny, bo
  `$product->single_add_to_cart_text()` jest escapowany w oryginale — sam filtr
  tekstu nie może wstrzyknąć HTML.

**Architektura "Popularny" / "HIT" — czysty CSS, bez JS:**
Wtyczka woo-variation-swatches generuje per-wartość klasę
`button-variable-item-{slug}` na każdym `<li>`. Plakietka "Popularny" (Format
50×80) to `#sizeList .variable-item.button-variable-item-50-x-80
.variable-item-contents::after{content:"Popularny";...}` — zero JS, zero
ryzyka błędnego sluga (jak w starym snippecie #8, teraz nieużywanym). "HIT"
(Nakład 250 szt.) jest wstrzykiwane przez snippet #7 jako prawdziwy
`<span class="pill pill-opt">`, bo leci razem z realną ceną tego wiersza.

**Checkmark swatchy Formatu — czysty CSS:** `::before` na
`.variable-item-contents` z tłem `background-image: url("data:image/svg+xml,...")`
(SVG inline w data-URI) — bez wstrzykiwania markupu, bez JS.

**Decyzje podjęte przy wdrożeniu (do potwierdzenia):**
- Etykieta atrybutu `pa_rozmiar` wyświetlana jako "Format" (UI), nie "Rozmiar"
  (nazwa taksonomii bez zmian) — zgodnie z briefem wdrożenia.
- Domyślne zakładki WC "Informacje dodatkowe / Opinie" wyłączone (redundantne
  z konfiguratorem; "Opinie" mają własną sekcję niżej na stronie).
- "Podobne produkty" (related products) — pozostawione, tylko rozciągnięte na
  pełną szerokość gridu (poza zakresem makiety, ale działająca funkcja, nie
  usuwana bez powodu).
- Karta "Podsumowanie" i pasek dostawy: wartości **statyczne** dla domyślnego
  wariantu (30×60 + 100 szt., realne dane z `wc_get_price_including_tax`) —
  TODO w kodzie przy Etapie 2 (kalkulator na zmianę wyboru).
- Próg darmowej dostawy na pasku: 200 zł (zgodnie z topbar, nie 250 zł jak w
  oryginalnej makiecie Claude Design).
- "Własny format" / "Własny nakład" (wiersz z chevronem + panel pól) —
  POMINIĘTE w Etapie 1. To nie są realne wartości atrybutów WooCommerce, tylko
  funkcja indywidualnego zamówienia (Etap 2/etap dalszy). TODO w kodzie
  `variable.php`.
- Rabaty "Taniej o X%" przy Nakładzie liczone wg tego samego wzoru co
  poprzednio (spadek ceny za sztukę względem najmniejszego nakładu) — dla
  Formatu 30×60 wychodzi 57/78/89%, NIE 15/26/40% z oryginalnej makiety Claude
  Design (te liczby były przykładowe, nie z bazy).

## 🐛 Naprawione bugi layoutu po wizualnym przeglądzie, 2026-06-21

**Bug 1 — konfigurator skompresowany do ~150px.** Selektor `.product.type-product`
(bez kwalifikatora tagu) dopasowywał TRZY różne elementy: `<article class="post-17
product type-product...">` (wrapper GeneratePress), `<div id="product-17"
class="product type-product...">` (prawdziwy kontener WooCommerce) i każdy `<li
class="product type-product...">` w sekcji "Podobne produkty". Grid 58/42
nakładał się podwójnie (article → 1 dziecko w kolumnie 1 → w nim div ze swoim
własnym gridem 58/42, ale liczonym z 58% szerokości, nie z całości) — stąd
kolumna konfiguratora ~42% z 58% ≈ 24% strony. **Naprawione:** selektor zwężony
do `div.product.type-product` (sprawdzone w DOM: dokładnie 1 `<div>` ma obie
klasy razem).

**Bug 2a — natywna cena wariantu wracała mimo `display:none`.** WooCommerce JS
(`assets/js/frontend/add-to-cart-variation.js`) robi po dopasowaniu wariantu
`$singleVariation.slideDown()`/`.show()` — jQuery ustawia **inline**
`style="display:..."`, które bije zwykłą regułę CSS. **Naprawione:** `display:none
!important` na `.woocommerce-variation.single_variation`.

**Bug 2b — fioletowy przycisk ZAMAWIAM.** WooCommerce ma wbudowaną regułę
`.woocommerce:where(body:not(...)) button.button.alt{background-color:#7f54b3}`
— `:where()` ma zerową specyficzność, ale tag `button` + klasy `button`+`alt`
dawały specyficzność (0,3,1), wyższą niż nasza (0,3,0) bez kwalifikatora tagu.
**Naprawione:** `!important` na `background-color` (reguła bazowa + `:hover`).

**Bug 3 — kolizja ceny/rabatu w wierszach Nakładu — ODŁOŻONY do Bloku B
(decyzja, nie przeoczenie).** Przyczyna: snippet #7 dokleja blokowy
`<div class="prinex-swatch-enhanced">` do małego, ciasnego kwadratowego
swatcha (`wvs-style-squared`) bez żadnego CSS ograniczającego — treść nie ma
gdzie się zmieścić. To NIE jest `position:absolute` (sprawdzone), tylko
przepełnienie małego kontenera. Właściwa naprawa = przebudowa wiersza Nakładu
na pełną szerokość w Bloku B — łatka teraz zostałaby wyrzucona przy
przebudowie.

## ✅ Naprawiony bug — snippet #10 (SVG upload support), 2026-06-19

Kod w bazie był uszkodzony: każda zmienna (`$mimes`, `$file`, `$filename`,
`$type`) była zapisana jako goły backslash `\` bez nazwy — np.
`function( \ ) { \["svg"] = ... }` zamiast `function( $mimes ) { $mimes["svg"] = ... }`.
Scope był `front-end`, a filtry `upload_mimes`/`wp_check_filetype_and_ext`
wywołują się w kontekście wp-admin (biblioteka mediów) — więc uszkodzony kod
nigdy się nie wykonywał i wgrywanie SVG faktycznie nie działało, mimo że
snippet był oznaczony jako "aktywny".

**Naprawione:**
- kod zaktualizowany w `wp_snippets.code` (poprawne nazwy zmiennych)
- scope zmieniony z `front-end` na **`global`** — filtr teraz faktycznie
  działa też w wp-admin, gdzie się wykonuje upload
- `php -l` na realnej treści z bazy: bez błędów
- przetestowany prawdziwy upload (`wp media import` testowego SVG) —
  zaimportowany jako `image/svg+xml`, publicznie dostępny pod realnym URL,
  testowy załącznik usunięty po weryfikacji
