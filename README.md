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

> **Ważne — GeneratePress Theme Builder (elementy PHP) — procedura edycji:**
> Elementy 161 (nagłówek) i 162 (stopka) są typu `hook` + `execute_php`.
> GeneratePress renderuje PHP z **`wp_postmeta._generate_element_content`**,
> NIE z `wp_posts.post_content` — edycja `post_content` jest przy renderowaniu ignorowana.
>
> **Procedura zmiany treści (4 kroki, zawsze w tej kolejności):**
>
> **① Zmodyfikuj treść** — zapisz nową wersję do pliku tymczasowego (np. `/tmp/el161.txt`).
> Plik zaczyna od `<?php`. Wgraj przez SCP: `scp plik.php prinex:/tmp/el161.txt`.
>
> **② Sprawdź składnię PHP (`php -l`) — jeśli błąd, NIE wykonuj kroku ③:**
> ```bash
> php -l /tmp/el161.txt
> # Oczekiwany wynik: "No syntax errors detected in /tmp/el161.txt"
> # Jeśli błąd składni → popraw plik, powtórz lint, NIE zapisuj do bazy.
> ```
>
> **③ Zapisz przez `FROM_BASE64()` w SQL (jedyna bezpieczna metoda):**
> ```bash
> python3 - <<'EOF'
> import base64, subprocess
> b = base64.b64encode(open('/tmp/el161.txt', 'rb').read()).decode()
> sql = f"UPDATE wp_postmeta SET meta_value=FROM_BASE64('{b}') WHERE post_id=161 AND meta_key='_generate_element_content' LIMIT 1;"
> r = subprocess.run(['/usr/bin/wp', 'db', 'query', sql], capture_output=True, text=True)
> print('SQL rc:', r.returncode, r.stderr.strip() or 'OK')
> EOF
> ```
>
> **④ Potwierdź HTTP 200 na żywej stronie:**
> ```bash
> curl -s -o /dev/null -w "%{http_code}" https://prinex.com.pl/
> # Oczekiwany wynik: 200  (jeśli inny — sprawdź logi PHP, cofnij przez backup SQL)
> ```
>
> **Dlaczego NIE `update_post_meta()` / `wp eval update_post_meta(...)`:**
> WordPress `$wpdb` niszczy backslashe podczas zapisu — `\n` (newline w treści) staje
> się literą `n`. Powoduje `Parse error: unexpected variable $logo` na żywej stronie.
> *(Błąd udokumentowany 2026-06-26: dodanie ikony aktówki przez `update_post_meta` → strona down)*
>
> **Dlaczego NIE edycja `post_content` / WP-Admin / `wp_update_post()`:**
> GeneratePress ignoruje `post_content` — zmiana nie wejdzie na stronę.
> *(Błąd udokumentowany 2026-06-26: edycja nagłówka przez WP-Admin → brak efektu na żywej stronie)*

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
| 7 | cena + rabat na swatchach Nakładu (Etap 2) | front-end | ✅ | PHP: ceny z prinex_sale_net() (#23); badge OPTYMALNY z _prinex_optymalny; fallback na get_price() |
| 8 | „POPULARNY" na swatchu Rozmiaru | front-end | 💤 | Wyłączony — do weryfikacji |
| 9 | Figtree Google Font | front-end | ✅ | Ładuje font dla całego serwisu |
| 10 | SVG upload support | global | ✅ | Zezwala na upload SVG/SVGZ do mediów. *Naprawiony — patrz §6* |
| 11 | Strona główna — CSS/JS | front-end | ✅ | Hero, kafle, FAQ, opinie; tylko `is_front_page()`. *Oczyszczony z martwego nagłówka makiety — patrz §6* |
| 12 | Kategoria 3D Premium — CSS/JS | front-end | ✅ | Breadcrumb, filtry, siatka, SEO; tylko ta kategoria |
| 13 | Strona produktu: layout 58/42 + hooki (Etap 1) | front-end | ✅ | Split 58/42, swathe, sum-card, CSS konfiguratora — NETLOCKED |
| 14 | Strona produktu: sekcje dolne (FAQ/O materiale/Trust) | front-end | ✅ | Sekcje dolne strony produktu — zero logiki cen |
| 15 | Krok 2: Wgraj pliki (CSS/JS) | front-end | ✅ | Upload dropzone /wgraj-pliki/ |
| 16 | Neutralizacja GP graphite (button focus/hover) | front-end | ✅ | Globalne — naprawia niebieski focus z GeneratePress |
| 17 | ZAMAWIAM redirect do upload | global | ✅ | Po dodaniu do koszyka → /wgraj-pliki/ |
| 18 | prinex_debug_cart_2b | global | 💤 | Debug — wyłączony |
| 19 | prinex_finalize_debug | global | 💤 | Debug — wyłączony |
| 20 | prinex_debug_write | global | 💤 | Debug — wyłączony |
| 21 | Koszyk: layout CSS/JS (Etap 1) | front-end | ✅ | Stylizacja koszyka WooCommerce |
| 22 | Notice dodano do koszyka — brandowy | front-end | ✅ | Zielony notice z auto-dismiss |
| 23 | Silnik cennika (#23) | global | ✅ | prinex_sale_net(), prinex_sale_rate(), prinex_rozmiar_to_dims() — dane w prinex_cennik (JSON) |
| 24 | Panel admina Cennik (#24) | admin | ✅ | Podstrona wp-admin Cennik PRINEX; meta box: folia/rodzaj/OPTYMALNY |
| 25 | WC filtry cennika (#25) | global | ✅ | woocommerce_available_variation + woocommerce_before_calculate_totals z prinex_sale_net() |
| 26 | Żywa aktualizacja cen na stronie produktu (Etap B) | front-end | ✅ | JS found_variation: sum-card, pasek dostawy, wiersze nakładu; zero nowych węzłów/CSS |
| 27 | Custom format/nakład: live AJAX preview (Etap C1) | front-end | ✅ | Custom format/nakład + podgląd ceny (AJAX prinex_custom_price); osadzanie v6.1 |
| 28 | Dostawa i płatność + Zamówienie otrzymane | front-end | ✅ | Override checkoutu (5 szablonów: form-checkout/form-billing/review-order/payment/thankyou); stepper, Dane odbiorcy (Osoba/Firma+NIP), status plików jak koszyk, bramki w lewej kol., terms→#place_order, meta HPOS-safe; ekran order-received brandowany; is_checkout() |
| 29 | Panel klienta /moje-konto/ | front-end | ✅ | Override myaccount/* (6 ekranow: login/pulpit/zamowienia/widok/adresy/dane konta); menu relabel+ukryj Pobrania; status chip; order-again; reuse status plikow + dane do przelewu. Warstwa 1 |
| 30 | Książka adresowa (Warstwa 2c) | global | ✅ | Wiele adresów (user_meta _prinex_addresses); AJAX CRUD z nonce + autoryzacją per-ID + NIP checksum; UI Dane do wysyłki. Scope global (wp_ajax) |
| 31 | Checkout: picker zapisanego adresu (2c-int) | front-end | ✅ | Dodatek przez hook: wybor zapisanego adresu → prefill pol billing; rdzen #28 bez zmian; gosc nie widzi |

---

## 6. Problemy — rozwiązane i otwarte

### ✅ Panel klienta /moje-konto/ — Warstwa 1 (2026-06-30)
- 10 override'ow `woocommerce/myaccount/` (my-account wrapper + navigation + form-login + form-lost-password + dashboard + orders + view-order + form-edit-account + form-edit-address + my-address) + snippet #29 (`is_account_page()`).
- Menu konta: relabel (Pulpit/Zamowienia/Dane do wysylki/Dane konta/Wyloguj) + ukryte Pobrania (filtr woocommerce_account_menu_items).
- Reuse z order-received: status plikow per pozycja (`_prinex_upload_files`), dane do przelewu (`woocommerce_bacs_accounts`).
- Fix: neutralizacja domyslnego floatu WC 25%/75% na nav/content (psul grid). view-order: $tax_name (nie $tax) — kolizja z VAT.
- Model C: logowanie glowne, rejestracja subtelna (taby). Social FB/Google = wizualne (OAuth = Warstwa 2a).
- WARSTWA 2 (osobno, decyzje Macieja): social login (klucze), GUS po NIP (klucz), wiele adresow (custom CRUD na meta — wstepna rekomendacja), faktura na inne dane, Osoba/Firma w adresie (reuse #28).
- NIETKNIETE: variable.php, #13/#14, #21, #28.

### ✅ Checkout „Dostawa i płatność" — przeprojektowany (2026-06-30)
- **Konwersja:** strona `/zamowienie/` (ID 11) z blokowego checkoutu (`wp:woocommerce/checkout`) na **klasyczny** `[woocommerce_checkout]` — spójność z klasycznym koszykiem, kontrola PHP/override. Backup zawartości strony w `~/backups/prinex-page11-*`.
- **Szablony (child theme `woocommerce/checkout/`):** `form-checkout.php` (layout 2-kol + stepper), `form-billing.php` (Dane odbiorcy: zwijane + Osoba/Firma + NIP), `review-order.php` (podsumowanie; realny `shipping_method[]` ukryty w CSS → zawsze POST), `payment.php` (same bramki).
- **Snippet #28** (`is_checkout()`): CSS/JS + `remove_action('woocommerce_checkout_order_review','woocommerce_checkout_payment',20)` (bramki w lewej kolumnie, AJAX odświeża `.woocommerce-checkout-payment` w miejscu); `#place_order`+terms w prawym podsumowaniu POZA fragmentem AJAX; pola `billing_nip`/`billing_customer_type` + walidacja firmy; meta **HPOS-safe** przez `woocommerce_checkout_create_order` (`update_post_meta` nie działa przy HPOS).
- **Status plików w „Twoje naklejki"** = ten sam mechanizm co koszyk (`$cart_item['prinex_upload_files']` / `prinex_upload_projekt`).
- **Weryfikacja:** złożone realne zamówienia testowe (bacs, osoba + firma) przez `process_checkout()` — status on-hold, free shipping, VAT 23%, meta zapisane; parytet wizualny vs `06-zamowienie/mockup-2.png` (Edge/Playwright). Jedyna realna bramka: **bacs** (PayU/Przelewy24/BLIK = placeholdery, nieinstalowane).
- **NIETKNIĘTE:** variable.php, snippety #13/#14 (strona produktu), #21 (koszyk).

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
- [x] Cena + rabat na swatchach (snippet #7) + silnik cennika (snippety #23/#24/#25, Etap 2 — 2026-06-27)
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

## 8. Upload plików zamówień — `inc/prinex-upload.php`

Plik `inc/prinex-upload.php` to backend uploadu klienta i hooki WooCommerce obsługujące
pliki do zamówień. Jest to **plik PHP w child theme** (nie snippet), załadowany przez
`functions.php` przez `require_once`.

### Architektura modułu

| Obszar | Opis |
|---|---|
| **Front-end** | Strona `/wgraj-pliki/` (WP ID 170): dropzone, 3 ścieżki (wgraj/pomiń/projekt) |
| **AJAX upload** | Nonce, UUID4 katalog, MIME+ext walidacja, SVG sanitize, 50 MB/plik · 100 MB/token |
| **Hook 2A** | `woocommerce_add_to_cart_redirect` → redirect do `/wgraj-pliki/` |
| **Hook 2B** | `template_redirect`: generuje token per wizyta, pin do cart item meta |
| **Hook 2C** | Przenosi pliki `{token}/` do `order_{id}/` przy checkout/zmianie statusu |
| **Download** | `prinex_ajax_download_file`: endpoint z nonce + realpath guard |

### Hook 2C — podfolde dla zamówień multi-item (wdrożony 2026-06-26)

Funkcja `prinex_transfer_files_to_order($order)` wywoływana przez:
- `woocommerce_checkout_order_created`
- `woocommerce_order_status_changed -> processing/completed`

**Logika przenoszenia:**

| Pozycje z plikami | Zachowanie |
|---|---|
| 0 | return (brak plików) |
| 1 | pliki płasko do `order_{id}/` (bez podfoldera) |
| >1 | każda pozycja dostaje podfolder `{format} - {nakład}` |

**Schemat nazwy podfoldera:**

```
pa_rozmiar term name + pa_naklad term name
Np. "30 × 60" + "100 szt." → "30×60 - 100szt"
```

Spacje usunięte (`str_replace`), trailing `. ` ucięte (`rtrim`),
znaki niebezpieczne (`/ \ : * ? " < > |`) usunięte, polskie znaki i `×` zachowane.
Kolizja (dwie pozycje z tym samym formatem/nakładem): sufiks `(1)`, `(2)` itd.

**Metadane `_prinex_upload_files[n][disk_name]`:**
- 1 pozycja z plikami: `plik.pdf`
- >1 pozycji: `30x60 - 100szt/plik.pdf` (relative: subfolder/plik)

**Endpoint AJAX pobierania** (`?file=`): zaktualizowany w tej samej sesji.
Akceptuje dokładnie 1 poziom `/` — blokuje `\`, `..`, wiodące `/`, >1 slash.
Realpath check pozostaje ostateczną linią obrony.

**Idempotencja:** jeśli katalog `{token}/` już przeniesiony (nie istnieje) — skip.

**Backup przed zmianą:** `prinex-upload.php.bak-20260626` w tym samym katalogu.

### Synchronizacja plików zamówień → OneDrive

Osobna infrastruktura poza tym repo — kontener prinex-sync (LXC na Proxmox).
Pełna dokumentacja: `docs/sync-onedrive.md` w repo PRINEX (C:/Claude).

Skrót przepływu:
```
order_{id}/ na LH.pl --SFTP--> kontener prinex-sync --rclone--> OneDrive klienta
```
Trigger: operator zmienia status BaseLinker na "Nowe zamówienia" (ID 118439).
Cron: co 15 min na kontenerze.

---

## 9. Legenda statusów

| Symbol | Znaczenie |
|---|---|
| ✅ | zrobione |
| 🔄 | w toku |
| ⏳ | zaplanowane |
| ⚠️ | problem / wymaga uwagi |
| 🔍 | do weryfikacji |
| 🧹 | do uporządkowania |

---

*Dokument żywy — aktualizowany w miarę postępów. Ostatnia aktualizacja: 2026-06-26.*
