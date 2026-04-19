<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap bwg-admin">

	<h1 class="bwg-admin__page-title">
		<span class="dashicons dashicons-admin-settings"></span>
		Bebetu AI — Ustawienia
	</h1>

	<?php settings_errors( 'bwg_settings_group' ); ?>

	<form method="post" action="options.php">
		<?php settings_fields( 'bwg_settings_group' ); ?>

		<div class="bwg-panel">
			<h2 class="bwg-panel__title">API Google Gemini</h2>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="bwg_gemini_api_key">Klucz API</label></th>
					<td>
						<input
							type="password"
							id="bwg_gemini_api_key"
							name="bwg_gemini_api_key"
							value="<?php echo esc_attr( get_option( 'bwg_gemini_api_key' ) ); ?>"
							class="regular-text"
							autocomplete="new-password"
						>
						<p class="description">Klucz API z <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener">Google AI Studio</a>.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="bwg_model">Model Gemini</label></th>
					<td>
						<select id="bwg_model" name="bwg_model">
							<?php
							$current_model = get_option( 'bwg_model', 'gemini-2.5-flash' );
							$models = [
								'gemini-2.5-flash' => 'Gemini 2.5 Flash (zalecany)',
								'gemini-2.5-pro'   => 'Gemini 2.5 Pro (lepsza jakość, wolniejszy)',
								'gemini-2.0-flash' => 'Gemini 2.0 Flash (szybki)',
							];
							foreach ( $models as $val => $label ) :
							?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current_model, $val ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">Test połączenia</th>
					<td>
						<button type="button" id="bwg-test-api" class="button button-secondary">
							Testuj klucz API
						</button>
						<span id="bwg-test-result" class="bwg-test-result" style="display:none;margin-left:12px;"></span>
					</td>
				</tr>
			</table>
		</div>

		<div class="bwg-panel">
			<h2 class="bwg-panel__title">Generowanie</h2>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="bwg_daily_limit">Dzienny limit na IP</label></th>
					<td>
						<input
							type="number"
							id="bwg_daily_limit"
							name="bwg_daily_limit"
							value="<?php echo esc_attr( get_option( 'bwg_daily_limit', 10 ) ); ?>"
							min="0"
							max="1000"
							class="small-text"
						>
						<p class="description">Maks. generowań (każde kliknięcie = 1). Wpisz 0 = brak limitu.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="bwg_variants_default">Domyślna liczba wariantów</label></th>
					<td>
						<input
							type="number"
							id="bwg_variants_default"
							name="bwg_variants_default"
							value="<?php echo esc_attr( get_option( 'bwg_variants_default', 3 ) ); ?>"
							min="1"
							max="5"
							class="small-text"
						>
						<p class="description">Ile propozycji jednocześnie (1–5). Można nadpisać atrybutem shortcode <code>variants=""</code>.</p>
					</td>
				</tr>
			</table>
		</div>

		<?php submit_button( 'Zapisz ustawienia' ); ?>
	</form>

</div>
