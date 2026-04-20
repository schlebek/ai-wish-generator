# Changelog

## [3.4.0] - 2026-04-20

### Added
- Tłumaczenia na 9 języków: angielski, niemiecki, francuski, hiszpański, włoski, portugalski (BR), rosyjski, ukraiński, niderlandzki, czeski
- Generator plików `.po` / `.mo` (`languages/generate.js`) — uruchom `node generate.js` po zmianach w stringach
- Plik szablonu `.pot` dla nowych tłumaczów
- Inteligentny fallback języka w `bwg_load_textdomain()`: `de_DE_formal` → `de_DE` → `de`

## [3.3.0] - 2026-04-19

### Added
- Pełna internacjonalizacja (i18n) — wszystkie ciągi tekstowe opakowane w `__()`/`esc_html_e()`
- `bwg_load_textdomain()` — ładowanie domeny tłumaczeń z katalogu `languages/`
- Katalog `languages/` gotowy na pliki `.po`/`.mo`
- Biblioteki html2canvas (1.4.1) i jsPDF (2.5.1) dołączone lokalnie w `assets/js/vendor/`
- Rejestracja bibliotek przez `wp_register_script()` jako zależności `bwg-frontend`
- `readme.txt` w formacie WordPress.org z sekcją External Services

### Changed
- Usunięto dynamiczne ładowanie bibliotek eksportu przez CDN (`loadScript`/`ensureLibs`)
- `wp_localize_script` nie zawiera już adresów CDN
- Shortcode zmieniony z `bebetu_ai_generator` na `ai_wish_generator`
- Autor wtyczki zmieniony na Stanisław Chlebek

### Fixed
- Pełna zgodność z wymaganiami repozytorium WordPress.org

## [3.2.0] - 2026-04-19

### Added
- Obsługa wszystkich modeli Gemini dostępnych w API (2.5 Flash, 2.5 Flash-Lite, 2.5 Pro, 3 Flash Preview, 3.1 Flash-Lite Preview, 3.1 Pro Preview)
- Dynamiczna karta informacyjna przy wyborze modelu — opis, zastosowanie, cena za 1M tokenów, info o darmowym tierze
- Helper `bwg_get_models()` z pełnymi metadanymi modeli

### Changed
- Panel ustawień: pole modelu zmienione z listy 3 opcji na pełną listę aktualnych modeli
- Usunięto przestarzałe modele Gemini 2.0 (wycofywane 1 czerwca 2026)

## [3.1.0] - 2026-04-19

### Added
- Gutenberg block (`ai-wish/generator`) z dynamicznym renderowaniem
- Warianty życzeń (1–5) generowane w jednym zapytaniu do API
- Historia życzeń w bazie danych (custom table `bwg_wishes`)
- Panel admina: statystyki, historia, ustawienia (klucz API, model, limit dzienny)
- Rate limiting per IP (SHA-256 hash) z konfigurowalnymi limitami
- Cache transient 24h dla identycznych parametrów
- Eksport kart do JPG i PDF (lazy-load html2canvas + jsPDF)
- 5 szablonów kart (Klasyczny, Kwiatowy, Błękit, Złoty, Minimalistyczny)
- Ulepszanie życzeń przez AI (5 kierunków: ogólne, skróć, rym, emocje, oficjalny)
- Tryb rymowany (toggle + automatyczny przy wyborze "Wiersz")
- Kontrola długości: krótkie / standardowe / rozbudowane / wiersz
- Przycisk "Zaskocz mnie" — losuje okazję, ton i długość
- Contenteditable karty z licznikiem słów i znaków
- Udostępnianie na Facebook i WhatsApp
- Toast notifications
- Cycling loader messages podczas generowania

### Changed
- Pełna szerokość layoutu (usunięto max-width na wrapperze)
- Tytuł zmieniony na "Magiczny generator życzeń"
- Komunikaty loadera bez słowa "Gemini" — neutralne ("Generujemy…")
- "Zaskocz mnie" pokazuje toast z wylosowanymi wartościami

### Fixed
- PHP Parse error w `class-gemini.php` — niezeskejpowane cudzysłowy ASCII w stringu
- Kompatybilność z PHP 7.4 (usunięto union types, typed properties)

## [1.0.0] - 2026-04-10

### Added
- Pierwsza wersja pluginu z podstawowym shortcode `[ai_wish_generator]`
- Integracja z Google Gemini API (`gemini-2.5-flash`)
- Formularz: od kogo, dla kogo, okazja, ton
- Generowanie życzeń przez AJAX
