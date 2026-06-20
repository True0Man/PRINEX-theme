# PRINEX — sklep WooCommerce

Repozytorium kodu produkcyjnego sklepu PRINEX (child theme GeneratePress + Code Snippets).
Ten plik to **mapa projektu i tracker postępów** — jedno miejsce, z którego widać,
jak zbudowana jest strona, co już działa, co jest zepsute i co dalej.

> **Status na dziś:** infrastruktura gotowa, audyt zamknięty, dwa bugi rozwiązane.
> Wchodzimy w pracę nad wyglądem poszczególnych stron.

---

## 1. Jak pracujemy (pętla współpracy)

```
Claude Design  ──►  Claude Code  ──►  serwer (SSH)  ──►  GitHub  ──►  Claude.ai
  makiety           wdrożenie         kod żyje tu       mirror        review
```

- **Claude Design** — projektuje makiety (HTML/CSS).
- **Claude Code** — wdraża na serwer przez `ssh prinex`, commituje i pushuje.
- **Serwer** — hosting współdzielony (jailshell CloudLinux), WP-CLI, child theme + Code Snippets.
- **GitHub** — repo publiczne, mirror kodu z serwera.
- **Claude.ai (review)** — czyta kod z GitHuba przez `raw.githubusercontent.com`, daje feedback. Zero kopiuj/wklej.

---

## 2. Architektura — gdzie co mieszka

| Warstwa | Lokalizacja | Źródło prawdy |
|---|---|---|
| **HTML ramy** (header, stopka) | Element 161, GeneratePress Theme Builder | baza WP |
| **CSS ramy** (topbar, header, stopka, tokeny) | `style.css` child theme | ✅ ładowany przez core GeneratePress (`generate-child-css`) |
| **CSS/JS poszczególnych stron** | Code Snippets (warunkowane `is_front_page()` / kategorią) | baza WP (`wp_snippets`) |
| **Szablon kategorii** | `woocommerce/taxonomy-product_cat-naklejki-3d-premium.php` | plik w repo |
| **Font Figtree** | Code Snippet #9 | baza WP |
| **Logo** | `assets/prinex-logo.svg` | plik w repo |

> **Ważne:** dla Code Snippets **źródłem prawdy jest baza WP**, nie pliki.
> Pliki w `snippets/` to mirror do review i historii. Edycja pliku **nie zmienia
> działania strony** — zmianę trzeba wprowadzić w wp-admin / przez WP-CLI, a potem
> ponownie wyeksportować mirror.

### Tokeny brandowe

| Token | Hex | Użycie |
|---|---|---|
| Granat PRINEX | `#0B457D` | nagłówki, nawigacja, brand |
| Zieleń PRINEX | `#78B833` | akcenty, hover, kreska sygnaturowa |
| Zieleń ciemna | `#62992A` | hover na zielonych elementach |
| Tło jasne | `#E8ECEF` | topbar, stopka |
| Tekst | `#333333` | treść |

Czcionka: **Figtree** (400 / 600 / 700, Google Fonts).

---

## 3. Repozytoria

| Repo | Zawartość | Branch | Widoczność |
|---|---|---|---|
| **PRINEX** | briefy, makiety, logo, ikony, czcionki, `CLAUDE.md` | `master` | prywatne |
| **PRINEX-theme** | kod produkcyjny (child theme + snippety) | `main` | publiczne |

---

## 4. Struktura repo (PRINEX-theme)

```
PRINEX-theme/
├── functions.php                 # enqueue, paleta kolorów edytora
├── style.css                     # tokeny + CSS globalnej ramy
├── .gitignore                    # *.bak, *.bak-*
├── assets/
│   └── prinex-logo.svg
├── woocommerce/
│   └── taxonomy-product_cat-naklejki-3d-premium.php
└── snippets/                     # mirror Code Snippets (źródło prawdy = baza WP)
    ├── README.md
    ├── manifest.json
    └── *.php
```

---

## 5. Inwentarz Code Snippets

Legenda: ✅ aktywny i używany · 💤 nieaktywny / odłożony · 🗑️ przykład z instalacji (do usunięcia) · ⚠️ bug

| # | Snippet | Scope | Status | Opis |
|---|---|---|---|---|
| 1 | make-upload-filenames-lowercase | global | 🗑️ | Przykład z wtyczki, nieużywany |
| 2 | disable-admin-bar | front-end | 🗑️ | Przykład z wtyczki, nieużywany |
| 3 | allow-smilies | global | 🗑️ | Przykład z wtyczki, nieużywany |
| 4 | current-year (shortcode) | content | 🗑️ | Przykład z wtyczki, nieużywany |
| 5 | ukryj „Wybierz opcję" w dropdownach | front-end | ✅ | Usuwa `show_option_none` z wariantów WooCommerce |
| 6 | radio buttons zamiast dropdownów | front-end | 💤 | Zastąpione wtyczką Variation Swatches |
| 7 | cena + rabat na swatchach Nakładu | front-end | ✅ | JS: cena i „Taniej o X%" na swatchach |
| 8 | „POPULARNY" na swatchu Rozmiaru | front-end | 💤 | Wyłączony — do weryfikacji |
| 9 | Figtree Google Font | front-end | ✅ | Ładuje font dla całego serwisu |
| 10 | SVG upload support | global | ✅ | Zezwala na upload SVG/SVGZ do mediów. *Naprawiony — patrz §6* |
| 11 | Strona główna — CSS/JS | front-end | ✅ | Hero, kafle, FAQ, opinie; tylko `is_front_page()`. *Oczyszczony z martwego nagłówka makiety — patrz §6* |
| 12 | Kategoria 3D Premium — CSS/JS | front-end | ✅ | Breadcrumb, filtry, siatka, SEO; tylko ta kategoria |

---

## 6. Problemy — rozwiązane i otwarte

### ✅ Snippet #10 — upload SVG (NAPRAWIONY)
- **Był:** kod w bazie uszkodzony (każda zmienna jako goły `\`) + scope `front-end`, podczas gdy `upload_mimes` działa w wp-admin → upload SVG nie działał.
- **Naprawione:** odtworzono nazwy zmiennych, scope `front-end` → `global`. Zweryfikowane: `php -l` czysty, realny `wp media import` SVG → HTTP 200, `content-type: image/svg+xml`. Mirror re-eksportowany.
- ℹ️ *Uwaga bezpieczeństwa na przyszłość:* surowy SVG to wektor XSS. Upload tylko dla admina = niskie ryzyko, ale wtyczka Safe SVG (sanityzacja) byłaby bezpieczniejsza.

### ✅ Nagłówek strony głównej — duplikat makiety (USUNIĘTY)
- **Był:** snippet #11 niósł martwy CSS/JS nagłówka z makiety (generyczne `.topbar`/`.header`/`.nav` + sticky na `.header.stuck`) — pozostałość po przeniesieniu makiety „1:1".
- **Naprawione:** usunięto martwy CSS i JS ze snippetu #11 (po backupie). Zweryfikowano na żywo (jako admin): strona główna renderuje wyłącznie globalny `<header id="prinex-header">` (Element 161), identycznie jak kategoria; sticky `.is-stuck` działa sitewide (JS zaszyty w Elemencie 161). Zero pozostałości po makiecie.
- **Wniosek:** globalny nagłówek (Element 161 + `style.css`) to jedyne źródło prawdy dla menu — spójny na każdej stronie.

### ✅ Ładowanie `style.css` (CSS globalnej ramy) — WYJAŚNIONE
- Child `style.css` jest enqueue'owany **bezwarunkowo przez core GeneratePress** (`generate_load_child_theme_stylesheet`, handle `generate-child-css`) — na każdym żądaniu, niezależnie od snippetów.
- Potwierdzone na stronie głównej i koszyku (strona bez dedykowanego snippetu): `.prinex-header` obecny w realnie wczytanym CSS.
- **Wniosek:** `style.css` to żywe, jedyne źródło prawdy dla ramy. Żadnej konsolidacji nie trzeba.

### 🧹 `functions.php` — redundantny enqueue (do sprzątnięcia)
- `prinex_child_enqueue_styles()` ładuje styl rodzica trzeci raz pod osobnym handlem — GeneratePress już to robi. Nieszkodliwe, ale zbędne. Całą funkcję można usunąć.
- **Status:** drobny cleanup, niepilny.

### 🔍 Nawigacja mobilna
- Poniżej 1023px `.prinex-nav { display:none }`, brak widocznego zamiennika (hamburger).
- **Status:** do weryfikacji, czy menu mobilne jest w osobnym snippecie/elemencie.

### 🧹 Martwe snippety
- Snippety 1–4 (przykłady z wtyczki) i 6, 8 (odłożone) zaśmiecają listę.
- **Status:** do uporządkowania (usunąć lub jasno opisać).

---

## 7. Mapa drogowa

### Faza 0 — Infrastruktura ✅
- [x] Git na serwerze (w katalogu child theme)
- [x] Dedykowany deploy key, push przez SSH
- [x] Repo `PRINEX-theme` (publiczne), branch `main`
- [x] Eksport Code Snippets do repo (mirror + README + manifest)
- [x] Review kodu przez Claude.ai bez kopiuj/wklej

### Faza 1 — Audyt i stabilizacja ✅
- [x] Diagnostyka: jak `style.css` dociera na stronę → core GeneratePress, potwierdzone
- [x] Fix snippetu #10 (SVG upload) + re-eksport
- [x] Decyzja: jedno źródło prawdy dla CSS ramy → `style.css` (bez konsolidacji)
- [x] Nagłówek/menu spójne wszędzie → usunięto duplikat makiety ze snippetu #11, globalny `.prinex-header` na każdej stronie
- [ ] Cleanup `functions.php` (usunięcie redundantnego enqueue)
- [ ] Weryfikacja nawigacji mobilnej
- [ ] Sprzątanie martwych snippetów (1–4, 6, 8)
- [ ] Zgodność kodu z makietami Claude Design (rama, kolory, font)

### Faza 2 — Strona główna ⏳
- [ ] Przegląd zgodności z makietą `strona-glowna-unpacked.html`
- [ ] Hero, kafle, FAQ, opinie — review CSS/JS
- [ ] Responsywność (mobile / tablet)

### Faza 3 — Kategoria „Naklejki 3D Premium" ⏳
- [ ] Review szablonu + snippetu CSS/JS
- [ ] Filtry (Wszystkie / Metalizowane) — działanie JS
- [ ] Siatka kafli, badge'e (Nowość / Popularny)
- [ ] Kafel „Indywidualna" (placeholder → docelowa strona wyceny)
- [ ] Sekcja SEO z pola „Opis" kategorii

### Faza 4 — Strona produktu ⏳
- [ ] Swatche wariantów (Nakład, Rozmiar)
- [ ] Cena + rabat na swatchach (snippet #7)
- [ ] Galeria, opis, dane techniczne

### Faza 5 — Koszyk i checkout ⏳
- [ ] Przegląd i stylizacja koszyka
- [ ] Proces checkout
- [ ] Maile transakcyjne

### Faza 6 — Wykończenie ⏳
- [ ] Pozostałe podstrony (Oferta, Kontakt, O nas)
- [ ] Strona „Wycena indywidualna" (wymaga kalkulatora cen)
- [ ] SEO (meta, structured data)
- [ ] Wydajność (Core Web Vitals)
- [ ] Przegląd mobilny całości

---

## 8. Legenda statusów

| Symbol | Znaczenie |
|---|---|
| ✅ | zrobione |
| 🔄 | w toku |
| ⏳ | zaplanowane |
| ⚠️ | problem / wymaga uwagi |
| 🔍 | do weryfikacji |
| 🧹 | do uporządkowania |

---

*Dokument żywy — aktualizowany w miarę postępów. Ostatnia aktualizacja: czerwiec 2026.*
