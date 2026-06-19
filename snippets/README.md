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
| 10 | `prinex-svg-upload-support.php` | front-end | **aktywny** | Ma zezwalać na wgrywanie SVG/SVGZ do biblioteki mediów. **⚠️ ZNANY BUG — patrz niżej.** |
| 11 | `prinex-strona-glowna-css-js-z-eksportu-claude-design.php` | front-end | **aktywny** | Pełny CSS/JS strony głównej (hero, kafle, FAQ, opinie, responsywność) — przeniesiony 1:1 z makiety `04-mockupy/01-strona-glowna/strona-glowna-unpacked.html`. Ładuje się tylko na `is_front_page()`. |
| 12 | `prinex-kategoria-naklejki-3d-premium-layout-css-js.php` | front-end | **aktywny** | CSS/JS strony kategorii "Naklejki 3D Premium" (breadcrumb, filtry, siatka kafli, sekcja SEO) — wyłącznie dla `product_cat = naklejki-3d-premium`. |

## ⚠️ Znany bug — snippet #10 (SVG upload support)

Eksport (i `php -l`) wykrył, że **kod w bazie jest uszkodzony**: każda
zmienna (`$mimes`, `$file`, `$filename`, `$type`) jest zapisana jako goły
backslash `\` bez nazwy zmiennej — np. `function( \ ) { \["svg"] = ... }`
zamiast `function( $mimes ) { $mimes["svg"] = ... }`. To nie jest artefakt
eksportu — sprawdzone bezpośrednio w bazie (`wp eval` na żywo daje ten sam,
uszkodzony tekst).

Snippet jest oznaczony jako **aktywny**, ale ponieważ jego scope to
`front-end`, a filtry `upload_mimes`/`wp_check_filetype_and_ext` są wywoływane
w kontekście wp-admin (biblioteka mediów), kod **prawdopodobnie nigdy się nie
wykonuje** — co tłumaczy, dlaczego uszkodzona składnia nie wywołała
widocznego błędu na stronie. To prawdopodobnie oznacza, że **wgrywanie SVG do
biblioteki mediów dziś nie działa** (mimo że snippet istnieje i jest
"aktywny"). Wymaga osobnej naprawy: poprawić nazwy zmiennych i rozważyć zmianę
scope na `global` lub `admin`, żeby filtr faktycznie zadziałał przy uploadzie
w dashboardzie.

Eksport zachowuje ten kod **wiernie, z błędem włącznie** — celem mirrora jest
dokładne odzwierciedlenie tego, co jest w bazie, żeby takie problemy było
łatwo wychwycić w review.
