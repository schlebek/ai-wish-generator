=== AI Wish Generator ===
Contributors: schlebek
Tags: wishes, ai, generator, birthday, gemini
Requires at least: 6.0
Tested up to: 6.7
Stable tag: 3.4.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI-powered personalized wish generator for any occasion — birthdays, weddings, baptisms and more. Shortcode + Gutenberg block.

== Description ==

AI Wish Generator creates personalized, unique wishes using Google Gemini AI. Paste the shortcode or insert the Gutenberg block on any page and let your visitors generate heartfelt wishes in seconds.

**Key features:**

* Generate 1–5 wish variants in a single request
* 12 occasions: birthday, baptism, first birthday, communion, wedding, anniversary, Christmas, Easter, New Year, name day, promotion, and more
* 6 tones: sentimental, wise, funny, short & modern, official, poetic
* 4 length modes: short (2 sentences), standard (3–5), extended (6–8), poem
* Rhyme mode — toggle for rhymed wishes or poems (AABB / ABAB scheme)
* "Surprise me" button — randomizes occasion, tone and length, then generates instantly
* AI improve — paste any wish and improve it: shorten, add rhyme, add emotion, make it formal
* Card export — download wishes as JPG or PDF with 5 visual card templates
* Share — one-click Facebook and WhatsApp sharing
* Editable cards — click any generated wish to edit inline, with word/character counter
* Rate limiting — configurable daily limit per IP address
* Cache — 24h transient cache for identical requests
* Admin panel — history log, per-occasion statistics, model and API settings
* Supports all current Gemini models with pricing info displayed in settings

== External Services ==

This plugin connects to the **Google Gemini API** to generate wish text. The following data is sent to Google's servers when a user requests wishes:

* The "From" and "To" names entered in the form
* Occasion, tone, length and rhyme preferences
* Optionally: the recipient's age

No data is stored by Google beyond the scope of a single API request. You must provide your own Google Gemini API key.

* Google Gemini API: https://ai.google.dev/
* Google Privacy Policy: https://policies.google.com/privacy
* Google Terms of Service: https://policies.google.com/terms

By activating and configuring this plugin you agree to Google's terms of service.

== Installation ==

1. Upload the `ai-wish-generator` folder to `/wp-content/plugins/`
2. Activate the plugin via **Plugins → Installed Plugins**
3. Go to **AI Wish → Settings** and enter your Google Gemini API key
4. Get a free API key at https://aistudio.google.com/app/apikey
5. Place `[ai_wish_generator]` on any page or use the Gutenberg block

== Frequently Asked Questions ==

= Is the Google Gemini API free? =

Yes, Google offers a free tier for Gemini API that is sufficient for most personal and small business sites. Paid tiers are available for higher traffic. See https://ai.google.dev/gemini-api/docs/pricing for details.

= Which Gemini model should I use? =

The default **Gemini 2.5 Flash** is recommended — it offers the best balance of quality, speed and cost. The Settings page shows a description and pricing for every available model.

= Can I limit how many wishes users can generate? =

Yes. Go to **AI Wish → Settings** and set a daily limit per IP address. Set to 0 for unlimited.

= How do I show only specific occasions or tones? =

Use shortcode attributes: `[ai_wish_generator occasions="urodziny,slub" tones="wzruszajacy,smieszny" variants="3"]`

= Can I add the generator to multiple pages? =

Yes. You can use the shortcode or Gutenberg block on as many pages as you like. Each instance is independent.

= Where is wish history stored? =

All generated wishes are stored in your WordPress database (custom table `{prefix}bwg_wishes`). You can view them in **AI Wish → History**. The table is removed when you uninstall the plugin.

== Screenshots ==

1. Frontend wish generator form with all options and "Surprise me" button
2. Generated wish card with word counter, copy, export and share buttons
3. Admin Settings — model selection with description and pricing info card
4. Admin Dashboard — total stats, occasion popularity chart and shortcode reference

== Changelog ==

= 3.3.0 =
* Added: i18n support — all strings wrapped in translation functions
* Added: vendor libraries (html2canvas, jsPDF) bundled locally — removed CDN dependency
* Added: load_plugin_textdomain for translation support
* Changed: scripts registered and enqueued via wp_enqueue_script (WP.org compliance)
* Added: readme.txt with External Services disclosure

= 3.2.0 =
* Added: full Gemini model selection with description and pricing info card in settings
* Added: bwg_get_models() helper with metadata for all current models
* Removed: deprecated Gemini 2.0 models

= 3.1.0 =
* Added: Gutenberg block, wish variants (1–5), history, admin panel, rate limiting, 24h cache
* Added: card export JPG/PDF, 5 card templates, AI improve, rhyme mode, length control
* Added: "Surprise me" button, contenteditable cards, Facebook/WhatsApp share

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 3.3.0 =
Vendor libraries are now bundled locally. External CDN dependencies removed.
