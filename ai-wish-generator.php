<?php
/**
 * Plugin Name:  AI Wish Generator
 * Plugin URI:   https://bebetu.pl
 * Description:  Inteligentny generator życzeń zasilany przez Google Gemini AI — warianty, historia, statystyki, eksport JPG/PDF, karty z szablonami, ulepszanie tekstu AI.
 * Version:      3.1.0
 * Author:       Bebetu.pl
 * Text Domain:  ai-wish-generator
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'BWG_VERSION',     '3.1.0' );
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

function bwg_init(): void {
	BWG_Admin::init();
	BWG_Ajax::init();
	add_shortcode( 'bebetu_ai_generator', 'bwg_shortcode' );
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
		'bwg-frontend',
		BWG_PLUGIN_URL . 'assets/js/frontend.js',
		array( 'jquery' ),
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
		'bebetu_ai_generator'
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
		'cdn'            => array(
			'html2canvas' => 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js',
			'jspdf'       => 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js',
		),
		'i18n'           => array(
			'fill_fields'  => 'Wypełnij pola „Od kogo" i „Dla kogo".',
			'generating'   => 'Generowanie…',
			'improving'    => 'AI ulepsza…',
			'generate_btn' => 'Generuj przez AI ✨',
			'improve_btn'  => 'Ulepsz przez AI ✨',
			'copied'       => 'Skopiowano!',
			'error'        => 'Wystąpił błąd. Spróbuj ponownie.',
			'edit_hint'    => 'Kliknij, aby edytować',
			'words'        => 'słów',
			'chars'        => 'znaków',
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
		'urodziny'        => 'Urodziny',
		'chrzest'         => 'Chrzest Święty',
		'roczek'          => 'Pierwszy Roczek',
		'narodziny'       => 'Narodziny dziecka',
		'komunia'         => 'Komunia Święta',
		'imieniny'        => 'Imieniny',
		'slub'            => 'Ślub',
		'rocznica'        => 'Rocznica Ślubu',
		'wielkanoc'       => 'Wielkanoc',
		'boze_narodzenie' => 'Boże Narodzenie',
		'awans'           => 'Awans / Sukces zawodowy',
		'nowy_rok'        => 'Nowy Rok',
	);
}

function bwg_get_tones(): array {
	return array(
		'wzruszajacy' => 'Wzruszający i sentymentalny',
		'madry'       => 'Mądry i refleksyjny',
		'smieszny'    => 'Śmieszny i rymowany',
		'krotki'      => 'Krótki i nowoczesny',
		'oficjalny'   => 'Oficjalny i elegancki',
		'poetycki'    => 'Poetycki i metaforyczny',
	);
}

function bwg_get_lengths(): array {
	return array(
		'krotkie'     => 'Krótkie (2 zdania)',
		'standardowe' => 'Standardowe (3–5 zdań)',
		'rozbudowane' => 'Rozbudowane (6–8 zdań)',
		'wiersz'      => 'Wiersz / Poemat',
	);
}
