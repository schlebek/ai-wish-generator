<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BWG_Gemini {

	/** @var string */
	private $api_key;

	/** @var string */
	private $model;

	public function __construct( string $api_key, string $model = 'gemini-2.5-flash' ) {
		$this->api_key = $api_key;
		$this->model   = $model;
	}

	/** @return array|WP_Error */
	public function generate( string $prompt, int $variants = 1 ) {
		$url = sprintf(
			'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
			rawurlencode( $this->model ),
			$this->api_key
		);

		if ( $variants > 1 ) {
			$prompt .= "\n\nWygeneruj dokładnie {$variants} różne warianty. "
				. 'Każdy wariant oddziel linią zawierającą wyłącznie trzy myślniki: ---';
		}

		$body = wp_json_encode( [
			'contents'         => [
				[
					'parts' => [ [ 'text' => $prompt ] ],
				],
			],
			'generationConfig' => [
				'temperature'     => 0.9,
				'maxOutputTokens' => 8192,
			],
		] );

		$response = wp_remote_post( $url, [
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body'    => $body,
			'timeout' => 30,
		] );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'api_connect', 'Błąd połączenia z Gemini: ' . $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $code ) {
			$msg = $data['error']['message'] ?? "Błąd API (HTTP {$code})";
			return new WP_Error( 'api_error', $msg );
		}

		$raw = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
		if ( ! $raw ) {
			return new WP_Error( 'empty_response', 'Gemini nie zwrócił treści. Spróbuj ponownie.' );
		}

		$raw = trim( $raw );

		if ( $variants > 1 ) {
			$parts = array_values(
				array_filter(
					array_map( 'trim', explode( '---', $raw ) )
				)
			);
			return $parts ?: [ $raw ];
		}

		return [ $raw ];
	}

	public static function build_prompt(
		string $sender,
		string $recipient,
		string $occasion,
		string $tone,
		?int   $age,
		string $length = 'standardowe',
		bool   $rhyme  = false
	): string {
		$length_instructions = [
			'krotkie'     => 'Napisz bardzo krótko — dokładnie 2 zdania.',
			'standardowe' => 'Długość: 3–5 zdań.',
			'rozbudowane' => 'Napisz obszernie — 6–8 zdań, oddając pełną głębię uczuć.',
			'wiersz'      => 'Napisz w formie krótkiego wiersza (4–8 linijek) ze ścisłym rymem (schemat AABB lub ABAB). Zadbaj, by rymy były naturalne i estetyczne.',
		];

		$length_instruction = $length_instructions[ $length ] ?? $length_instructions['standardowe'];

		$age_info = $age ? "Osoba/dziecko kończy {$age} lat. " : '';

		$rhyme_extra = ( $rhyme && 'wiersz' !== $length )
			? "\nŻyczenia muszą się rymować — użyj rymu AABB lub ABAB. Rymy mają być naturalne, nie wymuszone."
			: '';

		return "Jesteś ekspertem od relacji rodzinnych i doświadczonym copywriterem.
Napisz życzenia z okazji: {$occasion}.
Dla: {$recipient}. Od: {$sender}. {$age_info}
Ton i styl: {$tone}.
{$length_instruction}{$rhyme_extra}
Wymagania: życzenia ciepłe, autentyczne, unikalne — bez banalnych frazesów jak \"niech spełniają się marzenia\" czy \"dużo zdrowia i szczęścia\". Pisz po polsku.
Odpowiadaj WYŁĄCZNIE treścią życzeń, bez żadnych komentarzy, tytułów ani wprowadzeń.";
	}

	public static function build_improve_prompt( string $original_text, string $direction ): string {
		$directions = [
			'ogolne'       => 'Ulepsz je ogólnie — spraw, by były cieplejsze, bardziej autentyczne i unikalne. Usuń banalne frazy.',
			'skroc'        => 'Skróć je do maksymalnie 2 zdań, zachowując najważniejszy sens i emocje.',
			'dodaj_rym'    => 'Przepisz je jako krótki wiersz z rymem AABB. Rymy mają być naturalne i estetyczne.',
			'wiecej_emocji' => 'Dodaj więcej emocji, głębi i ciepła. Spraw, by czytelnik poczuł szczere uczucia.',
			'oficjalny'    => 'Nadaj im bardziej oficjalny i elegancki charakter, odpowiedni na formalne okazje.',
		];

		$instruction = $directions[ $direction ] ?? $directions['ogolne'];

		return "Masz następujące życzenia napisane po polsku:

\"{$original_text}\"

{$instruction}

Odpowiedz WYŁĄCZNIE treścią ulepszonych życzeń, bez żadnych komentarzy ani wyjaśnień.";
	}
}
