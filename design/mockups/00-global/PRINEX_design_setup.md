# PRINEX — DESIGN SETUP (specyfikacja systemu projektowego)

> **Cel:** wklej tę specyfikację do Claude Design przy pierwszej konfiguracji systemu projektowego.
> Dzięki niej każda kolejna makieta (strona główna, produkt, kategoria, koszyk itd.) będzie automatycznie spójna.
> **Wersja:** 1.0 · **Data:** 30.05.2026
> Plik referencyjny też dla tej rozmowy (claude.ai) i dla Claude Code przy wdrożeniu.

---

## 1. MARKA

- **Nazwa:** PRINEX
- **Branża:** producent naklejek 3D Premium (domingowe, zalewane żywicą poliuretanową PU) dla klientów B2B
- **Tagline:** Twoje naklejki 3D Premium
- **Główny claim (hero):** Wyróżnij swój produkt
- **Subclaim:** Wybierz atrakcyjne i wyjątkowo trwałe naklejki 3D Premium dla swojej marki.
- **Ton:** profesjonalny B2B, konkretny, pragmatyczny. NIE infantylny ("super!", "ojej!"), NIE suchy korporacyjny ("Państwa zamówienie zostanie procedowane"). Nowoczesny, redukujący niepewność.

---

## 2. KOLORY (paleta marki)

| Rola | Nazwa | HEX | Zastosowanie |
|---|---|---|---|
| **Primary** | Granat PRINEX | `#0B457D` | Nagłówki, logo, akcenty korporacyjne, tekst kluczowy |
| **Accent** | Zieleń PRINEX | `#78B833` | CTA, przyciski, kreska sygnaturowa, ikony, podświetlenia |
| **Background** | Tło jasne | `#E8ECEF` | Tła sekcji, bloki wyróżnione |
| Tekst | Ciemny szary | `#333333` | Treść główna (body) |
| Biel | Biały | `#FFFFFF` | Tła kart, tekst na granacie/zieleni |

**Zasady użycia koloru:**
- CTA zawsze Zieleń `#78B833` (biały tekst). Wyjątkowo kluczowe CTA mogą być na Granacie `#0B457D`.
- Nagłówki sekcji: Granat, bold.
- Claimy wspierające: Zieleń, regular, większy rozmiar, lekkie.
- Nie wprowadzać kolorów spoza palety bez powodu. Pastelowe tła kafli = rozcieńczone wersje granatu/zieleni + beż/biel.

---

## 3. TYPOGRAFIA

- **Font:** **Figtree** (variable font, Google Fonts)
- **Wagi w użyciu:**
  - 400 Regular — treść (body)
  - 600 SemiBold — przyciski, etykiety, podkreślenia
  - 700 Bold — nagłówki sekcji
- **Hierarchia (sugerowana skala dla canvasu 1680):**
  - H1 (hero): 56–64 px / Bold / Granat
  - H2 (sekcje): 36–40 px / Bold / Granat
  - H3 (podsekcje): 24–28 px / SemiBold / Granat
  - Body: 16–18 px / Regular / ciemny szary
  - Etykiety / małe: 13–14 px / SemiBold / UPPERCASE dla krótkich
- **Litery dla CTA i etykiet:** często UPPERCASE (np. ZAMAWIAM, KONTAKT).

> UWAGA: Brief źródłowy mówił o Montserrat. DECYZJA z 30.05.2026: zmiana na **Figtree**. Zaktualizować brief i CLAUDE.md.

---

## 4. WYMIARY I SIATKA

| Element | Wartość |
|---|---|
| **Canvas / artboard** | **1680 px** szerokości |
| **Serwis (sekcje full-width, tła)** | **1440 px** |
| **Treść (kontener główny)** | **1400 px** |
| Marginesy boczne treści | ~20 px (różnica 1440 − 1400) |
| Margines canvas → serwis | 120 px z każdej strony (1680 − 1440 = 240) |

**Siatka:** 12 kolumn w kontenerze treści, gutter ~30 px.

**Ograniczenia czytelności:** bloki długiego tekstu (opisy, "O materiale", FAQ) ograniczać do ~720–800 px szerokości mimo szerokiego kontenera — inaczej linijki za długie do czytania.

**Tryb koloru pliku:** RGB. Rozdzielczość: 72 ppi. Eksport podglądów: PNG/JPG.

> UWAGA: treść 1400 to layout bardzo szeroki (poza domyślnym GeneratePress 1200). Świadoma decyzja — wdrożenie wymaga zmiany szerokości kontenera w Customizerze GeneratePress.

---

## 5. ELEMENT SYGNATUROWY

**Cienka pozioma kreska w Zieleni PRINEX** (`#78B833`):
- grubość ~2 px, długość 40–60 px
- umieszczana **nad nagłówkami sekcji** jako separator/akcent
- spójny element między drukiem a stroną WWW
- pojawia się też w stopce, pod kartami, jako subtelny akcent identyfikacyjny

---

## 6. KOMPONENTY (wzorce powtarzalne)

### Przycisk CTA (główny)
- Tło: Zieleń `#78B833`, tekst biały, SemiBold/Bold, często UPPERCASE
- Pełna szerokość w konfiguratorze; w innych miejscach auto z paddingiem
- Opcjonalnie ikona po lewej + strzałka po prawej
- Stan hover: ciemniejsza zieleń

### Karta produktu (strona główna / kategoria)
- Tło: jeden z pastelowych kolorów (różnicowanie wizualne — produkty są podobne)
- Zawartość: nazwa (góra) → 3 cechy oddzielone `|` → duże zdjęcie → cena → CTA
- Bez ciężkich ramek i cieni — czyste kolorowe powierzchnie (inspiracja: huawei.pl)

### Karta wyboru (rozmiar / nakład / podłoże)
- Klikalna karta, NIE dropdown
- Stan zaznaczony: obwódka w Zieleni `#78B833`
- Dla nakładu: nazwa + cena + marker "Taniej o X%" (zieleń)

### Trust badge
- Mini-ikona + krótkie hasło (1 linijka)
- Trójka pod sekcjami/kartami: Gwarancja jakości druku | Wysyłka 4–5 dni | Bezpieczna żywica PU

### Mini-badge na karcie formularza
- Np. "BEZPŁATNA WYCENA" — buduje wartość zanim klient zacznie wypełniać

---

## 7. MIKROKOPIA (gotowa, spójna)

- Pod formularzami: "Średni czas odpowiedzi: do 24h w dni robocze"
- Pod konfiguratorem: "Cena obejmuje druk + żywicę + konfekcję. Wysyłka liczona osobno."
- Pasek pilności: "Zamów do [godzina] — wyślemy [dzień + data]"
- Cechy produktu (4 filary): EFEKTOWNE · ODPORNE · ELASTYCZNE · TRWAŁE

---

## 8. INSPIRACJE / WZORCE (do web capture w Claude Design)

- **Stickermule** (stickermule.com/pl) — wzorzec ergonomii konfiguratora i kart materiałów. Punkt odniesienia w razie wątpliwości.
- **huawei.pl** — kolorowe kafle produktów na stronie głównej.
- **IKEA** (strona produktu) — bogata galeria, sekcja "Dobrze wiedzieć", sekcja edukacyjna o materiale.

---

## 9. LISTA STRON DO ZAPROJEKTOWANIA (priorytet)

🔴 wysoki · 🟡 średni · 🟢 niski

| # | Strona | Priorytet |
|---|---|---|
| 00 | Elementy globalne (top bar, menu z wycentrowanym logo, stopka) | 🔴 |
| 01 | Strona główna (hero + kolorowe kafle + banery) | 🔴 |
| 02 | Strona produktu (layout 50/50, galeria, konfigurator) | 🔴 |
| 03 | Kategoria / sklep (lista produktów) | 🟡 |
| 04 | Koszyk + kasa (faktura VAT/NIP) | 🟡 |
| 05 | Krok 2 — "Mam plik / Zamów projekt" + upload | 🟡 |
| 06 | Wycena indywidualna (formularz) | 🟢 |
| 07 | Kontakt | 🟢 |
| 08 | O nas | 🟢 |
| 09 | Jak zamawiać | 🟢 |
| 10 | Pomoc / FAQ | 🟢 |
| 11 | Coming Soon | 🟢 |

---

## 10. UWAGA O WDROŻENIU (ważne)

Makiety statyczne (strona główna, O nas, landingi, Coming Soon) → Claude Design może wygenerować kod blisko 1:1 do wdrożenia w GenerateBlocks.

Strony z mechaniką sklepu (produkt, konfigurator, koszyk, kasa) → makieta = WZORZEC WYGLĄDU. Działającą wersję odbudowuje Claude Code w WooCommerce + Variation Swatches (CSS + snippety PHP + ustawienia). "Klikalność" makiety jest pozorowana, nie podłączona pod realny koszyk.
