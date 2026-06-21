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
| 7 | `prinex-dodaj-cene-i-rabat-do-swatchow-nakladu.php` | front-end | **aktywny** | JS na stronie produktu: dokleja cenę i marker "Taniej o X%" do swatchów atrybutu Nakład. |
| 8 | `prinex-dodaj-radio-i-popularny-do-swatchow-rozmiaru.php` | front-end | nieaktywny | JS na stronie produktu: marker "POPULARNY" na swatchu Rozmiaru 50×80. Wyłączony — sprawdzić czy wzorzec produktu (Srebrna Szlifowana) tego jeszcze potrzebuje. |
| 9 | `prinex-figtree-google-font.php` | front-end | **aktywny** | Ładuje font Figtree (400/600/700) z Google Fonts dla całego serwisu. |
| 10 | `prinex-svg-upload-support.php` | **global** | **aktywny** | Zezwala na wgrywanie SVG/SVGZ do biblioteki mediów. Naprawiony 2026-06-19 — patrz niżej. |
| 11 | `prinex-strona-glowna-css-js-z-eksportu-claude-design.php` | front-end | **aktywny** | Pełny CSS/JS strony głównej (hero, kafle, FAQ, opinie, responsywność) — przeniesiony 1:1 z makiety `04-mockupy/01-strona-glowna/strona-glowna-unpacked.html`. Ładuje się tylko na `is_front_page()`. |
| 12 | `prinex-kategoria-naklejki-3d-premium-layout-css-js.php` | front-end | **aktywny** | CSS/JS strony kategorii "Naklejki 3D Premium" (breadcrumb, filtry, siatka kafli, sekcja SEO) — wyłącznie dla `product_cat = naklejki-3d-premium`. |
| 13 | `prinex-strona-produktu-layout-i-hooki-etap1.php` | front-end | **aktywny** | Strona produktu, Etap 1 (warstwa wizualna): layout 58/42 na `.images`/`.summary`, breadcrumb przeniesiony do prawej kolumny, usunięcie domyślnego price/meta/sharing/tabs, filtr przycisku na "Zamawiam" — wyłącznie dla `is_product()`. Współpracuje z override `woocommerce/single-product/add-to-cart/variable.php` (patrz niżej — to plik motywu, nie snippet). |

## 🧩 Strona produktu — Etap 1 (warstwa wizualna), 2026-06-21

Konfigurator FORMAT/NAKŁAD + Podsumowanie + ZAMAWIAM zbudowany na **dwóch
mechanizmach naraz**, oba potrzebne razem:

- **Snippet #13** (ten katalog) — layout 58/42, hooki WooCommerce, CSS.
- **`../woocommerce/single-product/add-to-cart/variable.php`** — override
  szablonu WooCommerce (plik motywu, **nie** Code Snippet) — numerowane bloki
  "1 FORMAT" / "2 NAKŁAD" / "3 Podsumowanie", pasek dostawy, przycisk, podpis
  "Następny krok: wgraj pliki". Zachowuje `wc_dropdown_variation_attribute_options()`
  per atrybut (wymagane przez wtyczkę woo-variation-swatches) oraz `.variations`/
  `.single_variation`/`.single_variation_wrap` (wymagane przez
  `assets/js/frontend/add-to-cart-variation.js` — bez tego dopasowywanie
  wariantu i dodanie do koszyka przestaje działać).

**Decyzje podjęte przy wdrożeniu (do potwierdzenia):**
- Etykieta atrybutu `pa_rozmiar` wyświetlana jako "Format" (UI), nie "Rozmiar"
  (nazwa taksonomii bez zmian) — zgodnie z briefem wdrożenia.
- Domyślne zakładki WC "Informacje dodatkowe / Opinie" wyłączone (redundantne
  z konfiguratorem; "Opinie" dostaną własną sekcję w Bloku E).
- "Podobne produkty" (related products) — pozostawione, tylko rozciągnięte na
  pełną szerokość gridu (poza zakresem makiety, ale działająca funkcja, nie
  usuwana bez powodu).
- Karta "Podsumowanie" i pasek dostawy: wartości **statyczne** dla domyślnego
  wariantu (30×60 + 100 szt., realne dane z `wc_get_price_including_tax`) —
  TODO w kodzie przy Etapie 2 (kalkulator na zmianę wyboru).
- Próg darmowej dostawy na pasku: 200 zł (zgodnie z topbar, nie 250 zł jak w
  oryginalnej makiecie Claude Design).

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
