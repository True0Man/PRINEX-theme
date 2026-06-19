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
