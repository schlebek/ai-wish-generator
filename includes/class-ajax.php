<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BWG_Ajax {

	public static function init(): void {
		add_action( 'wp_ajax_bwg_generate',        [ self::class, 'handle_generate' ] );
		add_action( 'wp_ajax_nopriv_bwg_generate',  [ self::class, 'handle_generate' ] );
		add_action( 'wp_ajax_bwg_improve',          [ self::class, 'handle_improve' ] );
		add_action( 'wp_ajax_nopriv_bwg_improve',   [ self::class, 'handle_improve' ] );
		add_action( 'wp_ajax_bwg_test_api',         [ self::class, 'handle_test_api' ] );
	}

	public static function handle_generate(): void {
		check_ajax_referer( 'bwg_generate', 'nonce' );

		$api_key = get_option( 'bwg_gemini_api_key' );
		if ( ! $api_key ) {
			wp_send_json_error( [ 'message' => __( 'Generator nie jest skonfigurowany. Skontaktuj się z administratorem.', 'ai-wish-generator' ) ] );
		}

		$ip_hash = BWG_Rate_Limiter::get_ip_hash();
		if ( ! BWG_Rate_Limiter::is_allowed( $ip_hash ) ) {
			wp_send_json_error( [ 'message' => __( 'Osiągnąłeś dzienny limit generowania życzeń. Wróć jutro!', 'ai-wish-generator' ) ] );
		}

		$sender    = sanitize_text_field( wp_unslash( $_POST['sender']    ?? '' ) );
		$recipient = sanitize_text_field( wp_unslash( $_POST['recipient'] ?? '' ) );
		$age       = absint( $_POST['age']      ?? 0 );
		$occasion  = sanitize_key( $_POST['occasion'] ?? '' );
		$tone      = sanitize_key( $_POST['tone']     ?? '' );
		$length    = sanitize_key( $_POST['length']   ?? 'standardowe' );
		$rhyme     = ! empty( $_POST['rhyme'] ) && '1' === $_POST['rhyme'];
		$variants  = min( 5, max( 1, absint( $_POST['variants'] ?? 3 ) ) );
		$force     = ! empty( $_POST['force'] ) && '1' === $_POST['force'];

		if ( ! $sender || ! $recipient ) {
			wp_send_json_error( [ 'message' => __( 'Proszę wypełnić pola „Od kogo" i „Dla kogo".', 'ai-wish-generator' ) ] );
		}

		$all_occasions = bwg_get_occasions();
		$all_tones     = bwg_get_tones();
		$all_lengths   = bwg_get_lengths();

		if ( ! isset( $all_occasions[ $occasion ] ) ) {
			wp_send_json_error( [ 'message' => __( 'Nieprawidłowa okazja.', 'ai-wish-generator' ) ] );
		}
		if ( ! isset( $all_tones[ $tone ] ) ) {
			wp_send_json_error( [ 'message' => __( 'Nieprawidłowy ton.', 'ai-wish-generator' ) ] );
		}
		if ( ! isset( $all_lengths[ $length ] ) ) {
			$length = 'standardowe';
		}

		// Transient cache (skip on force-regen)
		$cache_key = 'bwg_' . md5( $sender . $recipient . $age . $occasion . $tone . $length . (int) $rhyme . $variants );
		if ( ! $force ) {
			$cached = get_transient( $cache_key );
			if ( false !== $cached && is_array( $cached ) ) {
				wp_send_json_success( [
					'wishes'    => $cached,
					'remaining' => BWG_Rate_Limiter::get_remaining( $ip_hash ),
					'cached'    => true,
				] );
			}
		}

		$prompt = BWG_Gemini::build_prompt(
			$sender,
			$recipient,
			$all_occasions[ $occasion ],
			$all_tones[ $tone ],
			$age ?: null,
			$length,
			$rhyme
		);

		$model  = get_option( 'bwg_model', 'gemini-2.5-flash' );
		$gemini = new BWG_Gemini( $api_key, $model );
		$result = $gemini->generate( $prompt, $variants );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		// Store in cache for 24 h
		set_transient( $cache_key, $result, DAY_IN_SECONDS );

		$session_id = wp_generate_uuid4();
		foreach ( $result as $wish_text ) {
			BWG_Database::save_wish( [
				'session_id' => $session_id,
				'ip_hash'    => $ip_hash,
				'sender'     => $sender,
				'recipient'  => $recipient,
				'age'        => $age ?: null,
				'occasion'   => $occasion,
				'tone'       => $tone,
				'wish_text'  => $wish_text,
			] );
		}

		wp_send_json_success( [
			'wishes'    => $result,
			'remaining' => BWG_Rate_Limiter::get_remaining( $ip_hash ),
			'cached'    => false,
		] );
	}

	public static function handle_improve(): void {
		check_ajax_referer( 'bwg_generate', 'nonce' );

		$api_key = get_option( 'bwg_gemini_api_key' );
		if ( ! $api_key ) {
			wp_send_json_error( [ 'message' => __( 'Generator nie jest skonfigurowany.', 'ai-wish-generator' ) ] );
		}

		$ip_hash = BWG_Rate_Limiter::get_ip_hash();
		if ( ! BWG_Rate_Limiter::is_allowed( $ip_hash ) ) {
			wp_send_json_error( [ 'message' => __( 'Osiągnąłeś dzienny limit. Wróć jutro!', 'ai-wish-generator' ) ] );
		}

		$text      = sanitize_textarea_field( wp_unslash( $_POST['text']      ?? '' ) );
		$direction = sanitize_key( $_POST['direction'] ?? 'ogolne' );

		if ( mb_strlen( $text ) < 10 ) {
			wp_send_json_error( [ 'message' => __( 'Tekst do ulepszenia jest za krótki.', 'ai-wish-generator' ) ] );
		}

		$allowed_directions = [ 'ogolne', 'skroc', 'dodaj_rym', 'wiecej_emocji', 'oficjalny' ];
		if ( ! in_array( $direction, $allowed_directions, true ) ) {
			$direction = 'ogolne';
		}

		$prompt = BWG_Gemini::build_improve_prompt( $text, $direction );
		$model  = get_option( 'bwg_model', 'gemini-2.5-flash' );
		$gemini = new BWG_Gemini( $api_key, $model );
		$result = $gemini->generate( $prompt, 1 );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		// Save improved wish to history
		BWG_Database::save_wish( [
			'session_id' => wp_generate_uuid4(),
			'ip_hash'    => $ip_hash,
			'sender'     => '',
			'recipient'  => '',
			'age'        => null,
			'occasion'   => 'ulepszone',
			'tone'       => $direction,
			'wish_text'  => $result[0],
		] );

		wp_send_json_success( [ 'improved' => $result[0] ] );
	}

	public static function handle_test_api(): void {
		check_ajax_referer( 'bwg_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Brak uprawnień.', 'ai-wish-generator' ) ] );
		}

		$api_key = sanitize_text_field( wp_unslash( $_POST['api_key'] ?? '' ) );
		if ( ! $api_key ) {
			$api_key = get_option( 'bwg_gemini_api_key', '' );
		}
		if ( ! $api_key ) {
			wp_send_json_error( [ 'message' => 'Brak klucza API.' ] );
		}

		$model  = get_option( 'bwg_model', 'gemini-2.5-flash' );
		$gemini = new BWG_Gemini( $api_key, $model );
		$result = $gemini->generate( 'Odpowiedz jednym słowem: tak.', 1 );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( [ 'message' => 'Połączenie z Gemini działa poprawnie ✓' ] );
	}
}
