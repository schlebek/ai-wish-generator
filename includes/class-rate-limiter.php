<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BWG_Rate_Limiter {

	public static function get_ip_hash(): string {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
		// Use only first IP in case of proxy chain
		$ip = trim( explode( ',', $ip )[0] );
		// Hash with secret salt so raw IPs are never stored
		return hash( 'sha256', $ip . AUTH_SALT );
	}

	/** Returns true if the request is allowed. */
	public static function is_allowed( string $ip_hash ): bool {
		$limit = (int) get_option( 'bwg_daily_limit', 10 );
		if ( $limit <= 0 ) {
			return true; // 0 = unlimited
		}
		return BWG_Database::count_by_ip_today( $ip_hash ) < $limit;
	}

	/** Returns how many generations remain today for this IP (999 = unlimited). */
	public static function get_remaining( string $ip_hash ): int {
		$limit = (int) get_option( 'bwg_daily_limit', 10 );
		if ( $limit <= 0 ) {
			return 999;
		}
		return max( 0, $limit - BWG_Database::count_by_ip_today( $ip_hash ) );
	}
}
