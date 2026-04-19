<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BWG_Admin {

	public static function init(): void {
		add_action( 'admin_menu',            [ self::class, 'register_menu' ] );
		add_action( 'admin_init',            [ self::class, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_assets' ] );
	}

	public static function register_menu(): void {
		add_menu_page(
			'Bebetu AI Generator',
			'Bebetu AI',
			'manage_options',
			'bebetu-ai',
			[ self::class, 'page_dashboard' ],
			'dashicons-format-quote',
			30
		);
		add_submenu_page(
			'bebetu-ai', 'Statystyki', 'Statystyki',
			'manage_options', 'bebetu-ai', [ self::class, 'page_dashboard' ]
		);
		add_submenu_page(
			'bebetu-ai', 'Ustawienia', 'Ustawienia',
			'manage_options', 'bebetu-ai-settings', [ self::class, 'page_settings' ]
		);
		add_submenu_page(
			'bebetu-ai', 'Historia życzeń', 'Historia',
			'manage_options', 'bebetu-ai-history', [ self::class, 'page_history' ]
		);
	}

	public static function register_settings(): void {
		register_setting( 'bwg_settings_group', 'bwg_gemini_api_key', [
			'sanitize_callback' => 'sanitize_text_field',
		] );
		register_setting( 'bwg_settings_group', 'bwg_model', [
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'gemini-2.5-flash',
		] );
		register_setting( 'bwg_settings_group', 'bwg_daily_limit', [
			'sanitize_callback' => 'absint',
			'default'           => 10,
		] );
		register_setting( 'bwg_settings_group', 'bwg_variants_default', [
			'sanitize_callback' => 'absint',
			'default'           => 3,
		] );
	}

	public static function enqueue_assets( string $hook ): void {
		if ( false === strpos( $hook, 'bebetu-ai' ) ) return;
		wp_enqueue_style( 'bwg-admin', BWG_PLUGIN_URL . 'assets/css/admin.css', [], BWG_VERSION );
		wp_enqueue_script(
			'bwg-admin',
			BWG_PLUGIN_URL . 'assets/js/admin.js',
			[ 'jquery' ],
			BWG_VERSION,
			true
		);
		wp_localize_script( 'bwg-admin', 'BWGAdmin', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'bwg_admin' ),
		] );
	}

	public static function page_dashboard(): void {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$total         = BWG_Database::get_total_count();
		$today         = BWG_Database::get_today_count();
		$by_occasion   = BWG_Database::get_stats_by_occasion();
		$all_occasions = bwg_get_occasions();
		include BWG_PLUGIN_DIR . 'templates/admin-dashboard.php';
	}

	public static function page_settings(): void {
		if ( ! current_user_can( 'manage_options' ) ) return;
		include BWG_PLUGIN_DIR . 'templates/admin-settings.php';
	}

	public static function page_history(): void {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$current_page = max( 1, absint( $_GET['paged'] ?? 1 ) );
		$per_page     = 20;
		$offset       = ( $current_page - 1 ) * $per_page;
		$wishes       = BWG_Database::get_recent( $per_page, $offset );
		$total        = BWG_Database::get_total_count();
		$total_pages  = (int) ceil( $total / $per_page );
		$all_occasions = bwg_get_occasions();
		$all_tones     = bwg_get_tones();
		include BWG_PLUGIN_DIR . 'templates/admin-history.php';
	}
}
