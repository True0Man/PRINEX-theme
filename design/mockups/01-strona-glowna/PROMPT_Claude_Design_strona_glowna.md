# PROMPT DO CLAUDE DESIGN — STRONA GŁÓWNA PRINEX

> **Jak użyć:** wklej całość poniżej (od linii „=== PROMPT ===") do Claude Design.
> Wgraj też logo SVG PRINEX jako załącznik.
> Zapisz ten plik w `C:/Claude/04-mockupy/01-strona-glowna/`.

---

=== PROMPT ===

Zaprojektuj **stronę główną sklepu internetowego PRINEX** — producenta naklejek 3D Premium (wypukłych, zalewanych żywicą poliuretanową) dla klientów biznesowych B2B. Makieta desktop, statyczna strona główna. Język polski.

## MARKA I TON
- Nazwa: **PRINEX**
- Tagline: „Twoje naklejki 3D Premium"
- Główny claim (hero): **„Wyróżnij swój produkt"**
- Subclaim: „Wybierz atrakcyjne i wyjątkowo trwałe naklejki 3D Premium dla swojej marki."
- Ton: profesjonalny B2B, konkretny, nowoczesny. NIE infantylny, NIE suchy korporacyjny. Pewny siebie, czysty, premium.

## KOLORY (używaj dokładnie tych wartości, RGB)
- Granat PRINEX `#0B457D` — nagłówki, logo, tekst kluczowy
- Zieleń PRINEX `#78B833` — przyciski CTA, akcenty, kreska sygnaturowa, ikony
- Tło jasne `#E8ECEF` — tła sekcji, bloki wyróżnione
- Tekst body: ciemny szary `#333333`
- Biel `#FFFFFF` — karty, tekst na granacie/zieleni

## TYPOGRAFIA — font Figtree (Google Fonts), skala:
- Hero H1: 52 px / 700 Bold / line-height 1.1 / Granat
- Nagłówki sekcji H2: 38 px / 700 Bold / Granat
- Podnagłówki H3 (tytuły kart): 24 px / 600 SemiBold / Granat
- Lead / subclaim: 20 px / 400 / line-height 1.5
- Treść body: 17 px / 400 / line-height 1.6 / ciemny szary
- Etykiety / małe: 14 px / 600 SemiBold / UPPERCASE
- Przyciski: 16 px / 600 / UPPERCASE / letter-spacing 0.03em

## WYMIARY
- Canvas / artboard: **1680 px** szerokości
- Sekcje full-width (tła, paski): rozciągają się na **1440 px**
- Kontener treści (gdzie siedzi tekst i karty): **1400 px**, wycentrowany
- Bloki długiego tekstu (sekcja edukacyjna): max ~800 px szerokości dla czytelności
- Siatka 12 kolumn, gutter ~30 px
- TYLKO desktop (responsywność pomijamy na tym etapie)

## LOGO
Wgrane jako SVG — poziomy wordmark „PRINEX", gdzie „PRIN" jest w granacie `#0B457D`, a „EX" w zieleni `#78B833`. Proporcje ok. 4,8:1 (szeroki, niski). W nagłówku **wycentrowany**, zapewnij mu dużo miejsca w środku.

## ELEMENT SYGNATUROWY
Cienka pozioma kreska w Zieleni `#78B833` (~2 px grubości, 40–60 px długości) umieszczana **nad** każdym nagłówkiem sekcji jako akcent.

---

## STRUKTURA STRONY (od góry do dołu)

### 1. TOP BAR (wąski pasek, tło granatowe `#0B457D`, tekst biały, 14 px UPPERCASE)
- Lewa: „PRODUKUJEMY WYPUKŁE NAKLEJKI 3D PREMIUM"
- Środek: ikona dostawy + „DARMOWA DOSTAWA JUŻ OD 200 ZŁ" | ikona zegara + „PRACUJEMY 24/7"
- Prawa: „KONTAKT" | „JAK ZAMAWIAĆ?"
- (BEZ numeru telefonu)

### 2. NAGŁÓWEK / MENU (tło białe)
- Lewa: pozycje menu „Naklejki 3D Premium" (z zieloną kreską pod aktywną) · „Oferta ▾" · „Pomoc ▾"
- Środek: **LOGO PRINEX** wycentrowane
- Prawa: ikona „Moje konto" · ikona „Koszyk" z licznikiem

### 3. HERO (duża sekcja, tło jasne `#E8ECEF` lub subtelny gradient granat→jasny)
- Po lewej: zielona kreska sygnaturowa → H1 **„Wyróżnij swój produkt"** (granat) → subclaim 20 px → przycisk CTA „ZOBACZ OFERTĘ" (zielony `#78B833`, biały tekst, ze strzałką) + przycisk drugorzędny „JAK ZAMAWIAĆ?" (outline granatowy)
- Po prawej: duże, efektowne zdjęcie naklejki 3D Premium (wypukła, błyszcząca, granatowa z logo) — użyj placeholdera produktowego
- Pod hero pasek 4 filarów (ikony SVG + hasło): **EFEKTOWNE · ODPORNE · ELASTYCZNE · TRWAŁE**

### 4. KAFLE PRODUKTÓW (nagłówek sekcji „Nasze naklejki 3D Premium")
Rząd kolorowych kafli (inspiracja: huawei.pl — duże kolorowe powierzchnie, BEZ ciężkich ramek i cieni). Każdy kafel ma **inne pastelowe tło** (rozcieńczony granat, rozcieńczona zieleń, beż, jasna szarość, biel) — bo produkty wizualnie podobne, kolor je różnicuje. Pokaż 6 kafli:

1. **Srebrna Szlifowana** — Metalik | Szczotkowana | Premium
2. **Srebrna Błysk** — Metalik | Lustrzana | Premium
3. **Złota Szlifowana** — Metalik | Szczotkowana | Premium
4. **Złota Błysk** — Metalik | Lustrzana | Premium
5. **Biała** — Klasyczna | Uniwersalna | Premium
6. **Econo** — Ekonomiczna | Lekka | Standard

Każdy kafel: nazwa (góra, granat bold) → 3 cechy oddzielone „|" (mniejsze, szare) → zdjęcie naklejki (placeholder) → „od XXX zł" → przycisk „ZOBACZ" (zielony). 

### 5. SEKCJA „DLACZEGO PRINEX" (tło białe)
Zielona kreska + H2 „Dlaczego nasze naklejki?". Trzy kolumny z ikoną SVG + krótkim opisem:
- **Trwałość** — Dwuskładnikowa żywica PU, odporność na UV i warunki atmosferyczne
- **Efekt 3D** — Wypukła, błyszcząca powierzchnia, która przyciąga wzrok
- **Pod Twoją markę** — Realizujemy według Twojego wzoru, dowolny kształt i format

### 6. SEKCJA EDUKACYJNA „O MATERIALE" (tło jasne `#E8ECEF`, tekst max 800 px)
Zielona kreska + H2 „Czym jest żywica poliuretanowa PU?". Krótki akapit edukacyjny (3-4 zdania) o tym, dlaczego żywica dwuskładnikowa daje trwałość, odporność UV i efekt wypukłości. Po prawej zdjęcie zbliżenia tekstury żywicy (placeholder).

### 7. TRUST BADGES (pasek, tło białe)
Trzy elementy w rzędzie (ikona SVG + hasło):
- **Gwarancja jakości druku** — Reklamujemy każdy egzemplarz poniżej standardu
- **Wysyłka w 4–5 dni roboczych** — Liczone od akceptacji projektu
- **Bezpieczna żywica PU** — Materiały sprawdzone i trwałe

### 8. STOPKA (tło granatowe `#0B457D`, tekst biały)
- Logo PRINEX (wersja na ciemne tło / biała)
- Kolumny linków: Oferta · Pomoc (FAQ, Jak zamawiać) · Informacje (Regulamin, Polityka prywatności, Polityka zwrotów) · Kontakt
- Zielona kreska sygnaturowa jako akcent
- Na dole: „© 2026 PRINEX. Twoje naklejki 3D Premium."

---

## STYL KOMPONENTÓW
- **Przyciski CTA:** tło zieleń `#78B833`, tekst biały, SemiBold, UPPERCASE, zaokrąglenie ~6 px, hover ciemniejsza zieleń. Strzałka „→" po prawej.
- **Karty:** czyste kolorowe powierzchnie, minimalne lub zerowe cienie, delikatne zaokrąglenie rogów.
- **Karta zaznaczona / hover:** subtelna obwódka w zieleni `#78B833`.
- **Ikony:** styl liniowy/minimalistyczny, kolorowane granatem lub zielenią. (W finalnym sklepie będą to SVG — tu placeholdery OK.)
- Dużo „oddechu" (whitespace), czysto, premium, bez przeładowania.

## WAŻNE
- To strona **statyczna** — w finale Claude Code odbuduje ją w GenerateBlocks (GeneratePress) blisko 1:1.
- Trzymaj się dokładnie palety i fontu — spójność z resztą sklepu.
- Zdjęcia produktowe to placeholdery (klient dostarczy własne).

=== KONIEC PROMPTU ===
