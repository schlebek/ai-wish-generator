<?php
/**
 * Plugin Name:  AI Wish Generator
 * Plugin URI:   https://bebetu.pl
 * Description:  Inteligentny generator życzeń zasilany przez Google Gemini AI — warianty, historia, statystyki, eksport JPG/PDF, karty z szablonami, ulepszanie tekstu AI.
 * Version:      3.4.0
 * Author:       Stanisław Chlebek
 * Text Domain:  ai-wish-generator
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'BWG_VERSION',     '3.4.0' );
define( 'BWG_PLUGIN_FILE',  __FILE__ );
define( 'BWG_PLUGIN_DIR',   plugin_dir_path( __FILE__ ) );
define( 'BWG_PLUGIN_URL',   plugin_dir_url( __FILE__ ) );

require_once BWG_PLUGIN_DIR . 'includes/class-database.php';
require_once BWG_PLUGIN_DIR . 'includes/class-gemini.php';
require_once BWG_PLUGIN_DIR . 'includes/class-rate-limiter.php';
require_once BWG_PLUGIN_DIR . 'includes/class-ajax.php';
require_once BWG_PLUGIN_DIR . 'includes/class-admin.php';

register_activation_hook( __FILE__,   array( 'BWG_Database', 'install' ) );
register_deactivation_hook( __FILE__, array( 'BWG_Database', 'deactivate' ) );

add_action( 'plugins_loaded', 'bwg_init' );
add_action( 'init',           'bwg_register_block' );
add_action( 'init',           'bwg_load_textdomain' );

function bwg_load_textdomain(): void {
	$rel_dir = dirname( plugin_basename( BWG_PLUGIN_FILE ) ) . '/languages';

	// 1) Try exact locale (e.g. de_DE, de_DE_formal) from WP_LANG_DIR first,
	//    then from the plugin's own languages/ directory.
	if ( load_plugin_textdomain( 'ai-wish-generator', false, $rel_dir ) ) {
		return;
	}

	// 2) Fallback: strip regional/formal variant (de_DE_formal → de_DE, pt_PT → pt_PT).
	$locale = determine_locale();
	$parts  = explode( '_', $locale );
	if ( count( $parts ) > 2 ) {
		$base = $parts[0] . '_' . $parts[1];
		$file = BWG_PLUGIN_DIR . 'languages/ai-wish-generator-' . $base . '.mo';
		if ( file_exists( $file ) ) {
			load_textdomain( 'ai-wish-generator', $file );
			return;
		}
	}

	// 3) Fallback: language-only code (e.g. de, fr, uk).
	$lang_only = $parts[0];
	$file      = BWG_PLUGIN_DIR . 'languages/ai-wish-generator-' . $lang_only . '.mo';
	if ( file_exists( $file ) ) {
		load_textdomain( 'ai-wish-generator', $file );
	}
	// If nothing matched, Polish source strings are displayed (intended default).
}

function bwg_init(): void {
	BWG_Admin::init();
	BWG_Ajax::init();
	add_shortcode( 'ai_wish_generator', 'bwg_shortcode' );
	add_action( 'wp_enqueue_scripts', 'bwg_register_assets' );
}

function bwg_register_assets(): void {
	wp_register_style(
		'bwg-frontend',
		BWG_PLUGIN_URL . 'assets/css/frontend.css',
		array(),
		BWG_VERSION
	);
	wp_register_script(
		'bwg-html2canvas',
		BWG_PLUGIN_URL . 'assets/js/vendor/html2canvas.min.js',
		array(),
		'1.4.1',
		true
	);
	wp_register_script(
		'bwg-jspdf',
		BWG_PLUGIN_URL . 'assets/js/vendor/jspdf.umd.min.js',
		array(),
		'2.5.1',
		true
	);
	wp_register_script(
		'bwg-frontend',
		BWG_PLUGIN_URL . 'assets/js/frontend.js',
		array( 'jquery', 'bwg-html2canvas', 'bwg-jspdf' ),
		BWG_VERSION,
		true
	);
}

/* ---------------------------------------------------------------
   Gutenberg block
---------------------------------------------------------------- */

function bwg_register_block(): void {
	wp_register_script(
		'bwg-block-editor',
		BWG_PLUGIN_URL . 'block/index.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
		BWG_VERSION,
		true
	);

	wp_register_style(
		'bwg-block-editor',
		BWG_PLUGIN_URL . 'block/editor.css',
		array( 'wp-edit-blocks' ),
		BWG_VERSION
	);

	register_block_type( 'ai-wish/generator', array(
		'editor_script'   => 'bwg-block-editor',
		'editor_style'    => 'bwg-block-editor',
		'render_callback' => 'bwg_block_render',
		'attributes'      => array(
			'occasions' => array( 'type' => 'string',  'default' => '' ),
			'tones'     => array( 'type' => 'string',  'default' => '' ),
			'variants'  => array( 'type' => 'integer', 'default' => 3 ),
		),
	) );
}

function bwg_block_render( array $attributes ): string {
	return bwg_shortcode( array(
		'occasions' => sanitize_text_field( $attributes['occasions'] ?? '' ),
		'tones'     => sanitize_text_field( $attributes['tones']     ?? '' ),
		'variants'  => (string) absint( $attributes['variants']      ?? 3 ),
	) );
}

/* ---------------------------------------------------------------
   Shortcode
---------------------------------------------------------------- */

function bwg_shortcode( array $atts ): string {
	$atts = shortcode_atts(
		array(
			'occasions' => '',
			'tones'     => '',
			'variants'  => (string) (int) get_option( 'bwg_variants_default', 3 ),
		),
		$atts,
		'ai_wish_generator'
	);

	wp_enqueue_style( 'bwg-frontend' );
	wp_enqueue_script( 'bwg-frontend' );

	$all_occasions = bwg_get_occasions();
	$all_tones     = bwg_get_tones();

	wp_localize_script( 'bwg-frontend', 'BWG', array(
		'ajax_url'       => admin_url( 'admin-ajax.php' ),
		'nonce'          => wp_create_nonce( 'bwg_generate' ),
		'variants'       => min( 5, max( 1, (int) $atts['variants'] ) ),
		'occasions_keys' => array_keys( $all_occasions ),
		'tones_keys'     => array_keys( $all_tones ),
		'lengths_keys'   => array_keys( bwg_get_lengths() ),
		'i18n'           => array(
			'fill_fields'  => __( 'Wypełnij pola „Od kogo" i „Dla kogo".', 'ai-wish-generator' ),
			'generating'   => __( 'Generowanie…', 'ai-wish-generator' ),
			'improving'    => __( 'AI ulepsza…', 'ai-wish-generator' ),
			'generate_btn' => __( 'Generuj przez AI ✨', 'ai-wish-generator' ),
			'improve_btn'  => __( 'Ulepsz przez AI ✨', 'ai-wish-generator' ),
			'copied'       => __( 'Skopiowano!', 'ai-wish-generator' ),
			'error'        => __( 'Wystąpił błąd. Spróbuj ponownie.', 'ai-wish-generator' ),
			'edit_hint'    => __( 'Kliknij, aby edytować', 'ai-wish-generator' ),
			'words'        => __( 'słów', 'ai-wish-generator' ),
			'chars'        => __( 'znaków', 'ai-wish-generator' ),
		),
	) );

	if ( $atts['occasions'] ) {
		$keys      = array_map( 'trim', explode( ',', $atts['occasions'] ) );
		$occasions = array_intersect_key( $all_occasions, array_flip( $keys ) ) ?: $all_occasions;
	} else {
		$occasions = $all_occasions;
	}

	if ( $atts['tones'] ) {
		$keys  = array_map( 'trim', explode( ',', $atts['tones'] ) );
		$tones = array_intersect_key( $all_tones, array_flip( $keys ) ) ?: $all_tones;
	} else {
		$tones = $all_tones;
	}

	$lengths = bwg_get_lengths();

	ob_start();
	include BWG_PLUGIN_DIR . 'templates/shortcode.php';
	return ob_get_clean();
}

/* ---------------------------------------------------------------
   Data helpers
---------------------------------------------------------------- */

function bwg_get_occasions(): array {
	return array(
		'urodziny'        => __( 'Urodziny', 'ai-wish-generator' ),
		'chrzest'         => __( 'Chrzest Święty', 'ai-wish-generator' ),
		'roczek'          => __( 'Pierwszy Roczek', 'ai-wish-generator' ),
		'narodziny'       => __( 'Narodziny dziecka', 'ai-wish-generator' ),
		'komunia'         => __( 'Komunia Święta', 'ai-wish-generator' ),
		'imieniny'        => __( 'Imieniny', 'ai-wish-generator' ),
		'slub'            => __( 'Ślub', 'ai-wish-generator' ),
		'rocznica'        => __( 'Rocznica Ślubu', 'ai-wish-generator' ),
		'wielkanoc'       => __( 'Wielkanoc', 'ai-wish-generator' ),
		'boze_narodzenie' => __( 'Boże Narodzenie', 'ai-wish-generator' ),
		'awans'           => __( 'Awans / Sukces zawodowy', 'ai-wish-generator' ),
		'nowy_rok'        => __( 'Nowy Rok', 'ai-wish-generator' ),
	);
}

function bwg_get_tones(): array {
	return array(
		'wzruszajacy' => __( 'Wzruszający i sentymentalny', 'ai-wish-generator' ),
		'madry'       => __( 'Mądry i refleksyjny', 'ai-wish-generator' ),
		'smieszny'    => __( 'Śmieszny i rymowany', 'ai-wish-generator' ),
		'krotki'      => __( 'Krótki i nowoczesny', 'ai-wish-generator' ),
		'oficjalny'   => __( 'Oficjalny i elegancki', 'ai-wish-generator' ),
		'poetycki'    => __( 'Poetycki i metaforyczny', 'ai-wish-generator' ),
	);
}

function bwg_get_models(): array {
	return array(
		'gemini-2.5-flash' => array(
			'name'  => 'Gemini 2.5 Flash',
			'badge' => 'Zalecany',
			'desc'  => 'Najlepszy stosunek jakości do ceny. Szybki, inteligentny, świetny do generowania kreatywnych treści.',
			'price' => '$0.30 / $2.50 za 1M tokenów (wejście / wyjście)',
			'free'  => true,
		),
		'gemini-2.5-flash-lite' => array(
			'name'  => 'Gemini 2.5 Flash-Lite',
			'badge' => 'Najtańszy',
			'desc'  => 'Najniższy koszt w rodzinie 2.5. Idealny przy dużym ruchu i prostych zadaniach.',
			'price' => '$0.10 / $0.40 za 1M tokenów',
			'free'  => true,
		),
		'gemini-2.5-pro' => array(
			'name'  => 'Gemini 2.5 Pro',
			'badge' => 'Najwyższa jakość',
			'desc'  => 'Najinteligentniejszy model 2.5. Najlepsza kreatywność i rozumienie kontekstu. Wolniejszy i droższy.',
			'price' => '$1.25 / $10.00 za 1M tokenów (do 200k ctx)',
			'free'  => true,
		),
		'gemini-3-flash-preview' => array(
			'name'  => 'Gemini 3 Flash Preview',
			'badge' => 'Nowa generacja',
			'desc'  => 'Model 3. generacji — lepsza jakość niż 2.5 Flash przy zbliżonym koszcie. Wciąż w fazie preview.',
			'price' => '$0.50 / $3.00 za 1M tokenów',
			'free'  => true,
		),
		'gemini-3.1-flash-lite-preview' => array(
			'name'  => 'Gemini 3.1 Flash-Lite Preview',
			'badge' => 'Nowa generacja · Tani',
			'desc'  => 'Najtańszy model 3.x. Dobry kompromis kosztowy przy nowych możliwościach. W fazie preview.',
			'price' => '$0.25 / $1.50 za 1M tokenów',
			'free'  => true,
		),
		'gemini-3.1-pro-preview' => array(
			'name'  => 'Gemini 3.1 Pro Preview',
			'badge' => 'Najpotężniejszy',
			'desc'  => 'Najbardziej zaawansowany dostępny model. Najwyższa kreatywność i jakość tekstu. Wyższy koszt, wolniejszy.',
			'price' => '$2.00 / $12.00 za 1M tokenów (do 200k ctx)',
			'free'  => false,
		),
	);
}

function bwg_get_lengths(): array {
	return array(
		'krotkie'     => __( 'Krótkie (2 zdania)', 'ai-wish-generator' ),
		'standardowe' => __( 'Standardowe (3–5 zdań)', 'ai-wish-generator' ),
		'rozbudowane' => __( 'Rozbudowane (6–8 zdań)', 'ai-wish-generator' ),
		'wiersz'      => __( 'Wiersz / Poemat', 'ai-wish-generator' ),
	);
}
