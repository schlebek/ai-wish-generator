<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div id="bwg-container" class="bwg-wrap">

	<div class="bwg-header">
		<h3 class="bwg-title">✨ <?php esc_html_e( 'Magiczny generator życzeń', 'ai-wish-generator' ); ?></h3>
		<button type="button" id="bwg-surprise-btn" class="bwg-btn bwg-btn--surprise" title="<?php esc_attr_e( 'Losuj okazję i ton', 'ai-wish-generator' ); ?>">
			🎲 <?php esc_html_e( 'Zaskocz mnie', 'ai-wish-generator' ); ?>
		</button>
	</div>

	<div class="bwg-field">
		<label class="bwg-label" for="bwg_sender"><?php esc_html_e( 'Od kogo?', 'ai-wish-generator' ); ?></label>
		<input type="text" id="bwg_sender" class="bwg-input" placeholder="<?php esc_attr_e( 'np. Mama i Tata', 'ai-wish-generator' ); ?>" autocomplete="off">
	</div>

	<div class="bwg-field">
		<label class="bwg-label" for="bwg_recipient"><?php esc_html_e( 'Dla kogo?', 'ai-wish-generator' ); ?></label>
		<input type="text" id="bwg_recipient" class="bwg-input" placeholder="<?php esc_attr_e( 'np. dla naszej córeczki Lenki', 'ai-wish-generator' ); ?>" autocomplete="off">
	</div>

	<div class="bwg-row">
		<div class="bwg-field bwg-field--small">
			<label class="bwg-label" for="bwg_age"><?php esc_html_e( 'Wiek (opcjonalnie):', 'ai-wish-generator' ); ?></label>
			<input type="number" id="bwg_age" class="bwg-input" placeholder="<?php esc_attr_e( 'lat', 'ai-wish-generator' ); ?>" min="0" max="120">
		</div>
		<div class="bwg-field bwg-field--large">
			<label class="bwg-label" for="bwg_occasion"><?php esc_html_e( 'Okazja:', 'ai-wish-generator' ); ?></label>
			<select id="bwg_occasion" class="bwg-select">
				<?php foreach ( $occasions as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>

	<div class="bwg-row">
		<div class="bwg-field bwg-field--large">
			<label class="bwg-label" for="bwg_tone"><?php esc_html_e( 'Ton i styl:', 'ai-wish-generator' ); ?></label>
			<select id="bwg_tone" class="bwg-select">
				<?php foreach ( $tones as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="bwg-field bwg-field--large">
			<label class="bwg-label" for="bwg_length"><?php esc_html_e( 'Długość:', 'ai-wish-generator' ); ?></label>
			<select id="bwg_length" class="bwg-select">
				<?php foreach ( $lengths as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, 'standardowe' ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>

	<div class="bwg-rhyme-row">
		<label class="bwg-toggle" for="bwg_rhyme">
			<input type="checkbox" id="bwg_rhyme" class="bwg-toggle__input">
			<span class="bwg-toggle__track"></span>
			<span class="bwg-toggle__label">🎵 <?php esc_html_e( 'Tryb rymowany', 'ai-wish-generator' ); ?></span>
		</label>
	</div>

	<button type="button" id="bwg-submit-btn" class="bwg-btn bwg-btn--primary">
		<?php esc_html_e( 'Generuj przez AI ✨', 'ai-wish-generator' ); ?>
	</button>

	<div id="bwg-loader" class="bwg-loader" style="display:none;" aria-live="polite">
		<span class="bwg-loader__dot"></span>
		<span class="bwg-loader__dot"></span>
		<span class="bwg-loader__dot"></span>
		<span id="bwg-loader-text"><?php esc_html_e( 'Generujemy dla Ciebie piękne słowa…', 'ai-wish-generator' ); ?></span>
	</div>

	<div id="bwg-results" aria-live="polite"></div>

	<div id="bwg-remaining" class="bwg-remaining" style="display:none;" aria-live="polite"></div>

</div>

<!-- Sekcja: Ulepsz życzenia -->
<div class="bwg-wrap bwg-improve-wrap">
	<button type="button" class="bwg-accordion-toggle" id="bwg-improve-toggle" aria-expanded="false">
		<span>✨ <?php esc_html_e( 'Ulepsz swoje życzenia przez AI', 'ai-wish-generator' ); ?></span>
		<span class="bwg-accordion-arrow">▾</span>
	</button>

	<div class="bwg-accordion-body" id="bwg-improve-body" style="display:none;">
		<p class="bwg-improve-desc">
			<?php esc_html_e( 'Wklej gotowe życzenia (swoje lub z generatora) — AI je poprawi lub przekształci.', 'ai-wish-generator' ); ?>
		</p>

		<div class="bwg-field">
			<label class="bwg-label" for="bwg_improve_text"><?php esc_html_e( 'Treść życzeń do ulepszenia:', 'ai-wish-generator' ); ?></label>
			<textarea id="bwg_improve_text" class="bwg-input bwg-textarea" rows="4" placeholder="<?php esc_attr_e( 'Wklej tutaj życzenia…', 'ai-wish-generator' ); ?>"></textarea>
		</div>

		<div class="bwg-field">
			<label class="bwg-label" for="bwg_improve_direction"><?php esc_html_e( 'Kierunek ulepszenia:', 'ai-wish-generator' ); ?></label>
			<select id="bwg_improve_direction" class="bwg-select">
				<option value="ogolne"><?php esc_html_e( 'Ulepsz ogólnie (usuń banalne frazy)', 'ai-wish-generator' ); ?></option>
				<option value="skroc"><?php esc_html_e( 'Skróć do 2 zdań', 'ai-wish-generator' ); ?></option>
				<option value="dodaj_rym"><?php esc_html_e( 'Przepisz jako rymowany wiersz', 'ai-wish-generator' ); ?></option>
				<option value="wiecej_emocji"><?php esc_html_e( 'Dodaj więcej emocji i głębi', 'ai-wish-generator' ); ?></option>
				<option value="oficjalny"><?php esc_html_e( 'Nadaj oficjalny, elegancki charakter', 'ai-wish-generator' ); ?></option>
			</select>
		</div>

		<button type="button" id="bwg-improve-btn" class="bwg-btn bwg-btn--primary">
			<?php esc_html_e( 'Ulepsz przez AI ✨', 'ai-wish-generator' ); ?>
		</button>

		<div id="bwg-improve-result" class="bwg-improve-result" style="display:none;">
			<div class="bwg-card">
				<span class="bwg-card__badge"><?php esc_html_e( 'Ulepszona wersja', 'ai-wish-generator' ); ?></span>
				<p class="bwg-card__text" id="bwg-improve-text-out" contenteditable="true"></p>
				<div class="bwg-card__counter" id="bwg-improve-counter"></div>
				<div class="bwg-card__actions">
					<button class="bwg-btn bwg-btn--ghost" id="bwg-improve-copy">📋 <?php esc_html_e( 'Kopiuj', 'ai-wish-generator' ); ?></button>
					<button class="bwg-btn bwg-btn--ghost" id="bwg-improve-preview">🎨 <?php esc_html_e( 'Pobierz kartę', 'ai-wish-generator' ); ?></button>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal: Podgląd karty z szablonami -->
<div id="bwg-modal" class="bwg-modal" style="display:none;" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Wybierz szablon karty', 'ai-wish-generator' ); ?>">
	<div class="bwg-modal__overlay" id="bwg-modal-overlay"></div>
	<div class="bwg-modal__box">
		<button class="bwg-modal__close" id="bwg-modal-close" aria-label="<?php esc_attr_e( 'Zamknij', 'ai-wish-generator' ); ?>">✕</button>
		<h3 class="bwg-modal__title"><?php esc_html_e( 'Wybierz szablon karty', 'ai-wish-generator' ); ?></h3>
		<div class="bwg-template-picker" id="bwg-template-picker"></div>
		<div class="bwg-modal__preview-wrap">
			<div id="bwg-preview-card" class="bwg-preview-card">
				<p id="bwg-preview-text" class="bwg-preview-text"></p>
			</div>
		</div>
		<div class="bwg-modal__actions">
			<button class="bwg-btn bwg-btn--primary" id="bwg-modal-jpg">⬇️ <?php esc_html_e( 'Pobierz JPG', 'ai-wish-generator' ); ?></button>
			<button class="bwg-btn bwg-btn--secondary" id="bwg-modal-pdf">⬇️ <?php esc_html_e( 'Pobierz PDF', 'ai-wish-generator' ); ?></button>
		</div>
	</div>
</div>
