<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap bwg-admin">

	<h1 class="bwg-admin__page-title">
		<span class="dashicons dashicons-admin-settings"></span>
		<?php esc_html_e( 'AI Wish — Ustawienia', 'ai-wish-generator' ); ?>
	</h1>

	<?php settings_errors( 'bwg_settings_group' ); ?>

	<form method="post" action="options.php">
		<?php settings_fields( 'bwg_settings_group' ); ?>

		<div class="bwg-panel">
			<h2 class="bwg-panel__title"><?php esc_html_e( 'API Google Gemini', 'ai-wish-generator' ); ?></h2>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="bwg_gemini_api_key"><?php esc_html_e( 'Klucz API', 'ai-wish-generator' ); ?></label></th>
					<td>
						<input
							type="password"
							id="bwg_gemini_api_key"
							name="bwg_gemini_api_key"
							value="<?php echo esc_attr( get_option( 'bwg_gemini_api_key' ) ); ?>"
							class="regular-text"
							autocomplete="new-password"
						>
						<p class="description">
							<?php
							printf(
								/* translators: %s: link to Google AI Studio */
								esc_html__( 'Klucz API z %s.', 'ai-wish-generator' ),
								'<a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener">Google AI Studio</a>'
							);
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="bwg_model"><?php esc_html_e( 'Model Gemini', 'ai-wish-generator' ); ?></label></th>
					<td>
						<?php
						$current_model = get_option( 'bwg_model', 'gemini-2.5-flash' );
						$models        = bwg_get_models();
						?>
						<select id="bwg_model" name="bwg_model" class="bwg-model-select">
							<?php foreach ( $models as $id => $m ) : ?>
							<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $current_model, $id ); ?>>
								<?php echo esc_html( $m['name'] . ' — ' . $m['badge'] ); ?>
							</option>
							<?php endforeach; ?>
						</select>
						<div id="bwg-model-info" class="bwg-model-info"></div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Test połączenia', 'ai-wish-generator' ); ?></th>
					<td>
						<button type="button" id="bwg-test-api" class="button button-secondary">
							<?php esc_html_e( 'Testuj klucz API', 'ai-wish-generator' ); ?>
						</button>
						<span id="bwg-test-result" class="bwg-test-result" style="display:none;margin-left:12px;"></span>
					</td>
				</tr>
			</table>
		</div>

		<div class="bwg-panel">
			<h2 class="bwg-panel__title"><?php esc_html_e( 'Generowanie', 'ai-wish-generator' ); ?></h2>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="bwg_daily_limit"><?php esc_html_e( 'Dzienny limit na IP', 'ai-wish-generator' ); ?></label></th>
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
						<p class="description"><?php esc_html_e( 'Maks. generowań (każde kliknięcie = 1). Wpisz 0 = brak limitu.', 'ai-wish-generator' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="bwg_variants_default"><?php esc_html_e( 'Domyślna liczba wariantów', 'ai-wish-generator' ); ?></label></th>
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
						<p class="description">
							<?php
							printf(
								/* translators: %s: shortcode attribute example */
								esc_html__( 'Ile propozycji jednocześnie (1–5). Można nadpisać atrybutem shortcode %s.', 'ai-wish-generator' ),
								'<code>variants=""</code>'
							);
							?>
						</p>
					</td>
				</tr>
			</table>
		</div>

		<?php submit_button( __( 'Zapisz ustawienia', 'ai-wish-generator' ) ); ?>
	</form>

</div>
