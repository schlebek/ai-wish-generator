# Changelog

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
