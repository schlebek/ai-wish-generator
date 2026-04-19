<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BWG_Database {

	const TABLE_SUFFIX = 'bwg_wishes';
	const DB_VERSION   = '1.0';

	public static function install(): void {
		global $wpdb;
		$table   = $wpdb->prefix . self::TABLE_SUFFIX;
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			session_id  VARCHAR(36) NOT NULL DEFAULT '',
			ip_hash     VARCHAR(64) NOT NULL DEFAULT '',
			sender      VARCHAR(255) NOT NULL DEFAULT '',
			recipient   VARCHAR(255) NOT NULL DEFAULT '',
			age         TINYINT UNSIGNED DEFAULT NULL,
			occasion    VARCHAR(50) NOT NULL DEFAULT '',
			tone        VARCHAR(50) NOT NULL DEFAULT '',
			wish_text   TEXT NOT NULL,
			created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_created (created_at),
			KEY idx_ip_date (ip_hash, created_at)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'bwg_db_version', self::DB_VERSION );
	}

	public static function deactivate(): void {
		// Tables are preserved on deactivation — removed only on uninstall.
	}

	public static function drop_tables(): void {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . self::TABLE_SUFFIX );
		delete_option( 'bwg_db_version' );
	}

	/** @return int|false */
	public static function save_wish( array $data ) {
		global $wpdb;
		$inserted = $wpdb->insert(
			$wpdb->prefix . self::TABLE_SUFFIX,
			[
				'session_id' => $data['session_id'],
				'ip_hash'    => $data['ip_hash'],
				'sender'     => $data['sender'],
				'recipient'  => $data['recipient'],
				'age'        => $data['age'] ?: null,
				'occasion'   => $data['occasion'],
				'tone'       => $data['tone'],
				'wish_text'  => $data['wish_text'],
			],
			[ '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s' ]
		);
		return $inserted ? $wpdb->insert_id : false;
	}

	public static function get_total_count(): int {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . self::TABLE_SUFFIX );
	}

	public static function get_today_count(): int {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM ' . $wpdb->prefix . self::TABLE_SUFFIX . ' WHERE DATE(created_at) = %s',
				current_time( 'Y-m-d' )
			)
		);
	}

	public static function get_recent( int $limit = 20, int $offset = 0 ): array {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
		return (array) $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . $wpdb->prefix . self::TABLE_SUFFIX . ' ORDER BY created_at DESC LIMIT %d OFFSET %d',
				$limit,
				$offset
			),
			ARRAY_A
		);
	}

	public static function get_stats_by_occasion(): array {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return (array) $wpdb->get_results(
			'SELECT occasion, COUNT(*) as cnt FROM ' . $wpdb->prefix . self::TABLE_SUFFIX . ' GROUP BY occasion ORDER BY cnt DESC',
			ARRAY_A
		);
	}

	public static function count_by_ip_today( string $ip_hash ): int {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM ' . $wpdb->prefix . self::TABLE_SUFFIX . ' WHERE ip_hash = %s AND DATE(created_at) = %s',
				$ip_hash,
				current_time( 'Y-m-d' )
			)
		);
	}
}
