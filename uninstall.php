<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

require_once plugin_dir_path( __FILE__ ) . 'includes/class-database.php';

BWG_Database::drop_tables();

delete_option( 'bwg_gemini_api_key' );
delete_option( 'bwg_model' );
delete_option( 'bwg_daily_limit' );
delete_option( 'bwg_variants_default' );
delete_option( 'bwg_db_version' );
