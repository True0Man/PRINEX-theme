# PRINEX — STAN PRAC: STRONA GŁÓWNA (handoff do nowej rozmowy)

> **Jak użyć:** wgraj ten plik na początku nowej rozmowy w projekcie PRINEX (albo wklej jego treść).
> Dzięki niemu nowa rozmowa wie, gdzie skończyliśmy i może płynnie kontynuować.
> **Data:** 30.05.2026

---

## GDZIE JESTEŚMY — skrót

Pracujemy nad **stroną główną PRINEX**. Reszta projektu (kontekst marki, serwer, produkty) jest w Project Knowledge — briefy + CLAUDE.md.

### Ustalone fundamenty (już zdecydowane, NIE zmieniać bez powodu)
- **Font:** Figtree (variable) — zmiana z Montserrat
- **Kolory:** granat `#0B457D`, zieleń `#78B833`, tło `#E8ECEF`
- **Wymiary:** canvas 1680 / serwis 1440 / treść 1400 px · siatka 12 kolumn
- **Motyw:** GeneratePress + GenerateBlocks (NIE Astra)
- **Ikony:** SVG, zero Font Awesome
- **Responsywność:** ODŁOŻONA (najpierw desktop)
- **Skala typografii:** H1 52 / H2 38 / H3 24 / lead 20 / body 17 / etykiety 14 px

### Przepływ pracy (3 narzędzia)
- **Claude Design** (claude.ai/design) — makiety wizualne, tu projektujemy stronę główną
- **Rozmowa (claude.ai)** — strategia, prompty, treści, tłumaczenie makiety na realia
- **Claude Code** (terminal, `ssh prinex`) — wdrożenie na żywym serwerze
- ZASADA: strony statyczne → najpierw Claude Design (widzisz), potem Claude Code (wdraża)

---

## STRONA GŁÓWNA — postęp

### ✅ Zrobione
- Pierwsza makieta wygenerowana w Claude Design (prompt: `PROMPT_Claude_Design_strona_glowna.md`)
- Wygląda dobrze: kolory trafione, logo wycentrowane, struktura OK
- Struktura sekcji: top bar → menu → hero → 4 filary → kafle produktów → "Dlaczego" → "O materiale" (żywica PU) → trust badges → stopka

### ✅ Logo
- Mamy logo SVG (wordmark "PRINEX": PRIN granat + EX zieleń, proporcje ~4,8:1)
- Plik "brudny" z CorelDRAW — do wyczyszczenia przez SVGO przed wdrożeniem
- BRAK: wersji na ciemne tło + favicon (do dorobienia)

### ⏳ Runda zmian W TRAKCIE (zlecona do Claude Design, czeka na wynik)
1. Białe tło menu głównego na pełną szerokość ekranu (full-width)
2. Hero: tytuł "Zamów indywidualne naklejki 3D Premium" / opis "Wybierz atrakcyjne naklejki 3D Premium dla swojej marki i zacznij sprzedawać więcej" / CTA "Stwórz naklejki" + link "ZOBACZ OFERTĘ →"
3. Menu główne w stylu stickerapp.com (płaskie linki, 3 strefy, BEZ lupki), aktywna pozycja z zieloną kreską
4. Tło serwisu `#E8ECEF`, tylko menu główne białe; top bar granatowy
5. Pod "Nasze naklejki 3D Premium" podtytuł: "Poznaj naszą pełną ofertę i wybierz rozwiązanie najlepsze dla Twojej firmy"
6. Kafle: 4 w rzędzie (6 produktów = 4+2), kwadratowe zdjęcie wypełnia kafel, nazwa POD kaflem + 1 linijka opisu, USUNIĘTE cechy i cena, cały kafel klikalny, dynamiczny hover (scale 1.05 + cień)
7. Nowa sekcja FAQ (akordeon, styl StickerApp) — 6 pytań produktowych
8. Nowa sekcja "Poznaj naszych Klientów" — opis + grid 16 logo (UWAGA: tylko SZARE PLACEHOLDERY, nigdy prawdziwe cudze logo)
9. Trust badges: 4 zamiast 3 (4. = "Produkcja w Polsce")

### ❗ Decyzje otwarte / do potwierdzenia
- Kafle 4+2 czy dopełnić do 8 (dodać Holograficzną + Indywidualną)?
- Usunięcie ceny z kafli — potwierdzone przez klienta, ale to świadoma rezygnacja z haczyka cenowego
- 4. trust badge "Produkcja w Polsce" — czy zostaje, czy podmienić
- Menu prawa strona — zostają konto + koszyk, czy dodać linki użytkowe (Wycena)?
- Kafel "Biała" miał przypadkową zieloną obwódkę — do usunięcia (zielona tylko na hover)

### ⚠️ Pułapki do pilnowania
- Logo cudzych firm w sekcji klientów = naruszenie znaków towarowych. Tylko placeholdery / tylko klienci za zgodą.
- Zdjęcia produktowe to wciąż PLACEHOLDERY — klient musi dostarczyć realne (hero stoi/upada na zdjęciu)

---

## NASTĘPNY KROK
Po otrzymaniu nowej makiety z Claude Design (runda zmian wyżej) → ocena → ewentualne poprawki → gdy strona główna zaakceptowana, przygotować prompt dla Claude Code do wdrożenia w GenerateBlocks.

## PLIKI POMOCNICZE (w C:/Claude/)
- `CLAUDE.md` — kontekst główny (korzeń)
- `04-mockupy/00-global/PRINEX_design_setup.md` — system projektowy
- `04-mockupy/01-strona-glowna/PROMPT_Claude_Design_strona_glowna.md` — pierwszy prompt
- `04-mockupy/01-strona-glowna/` — tu zapisywać screenshoty makiet (mockup-*)
