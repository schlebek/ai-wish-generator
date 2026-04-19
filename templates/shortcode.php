<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div id="bwg-container" class="bwg-wrap">

	<div class="bwg-header">
		<h3 class="bwg-title">✨ Magiczny generator życzeń</h3>
		<button type="button" id="bwg-surprise-btn" class="bwg-btn bwg-btn--surprise" title="Losuj okazję i ton">
			🎲 Zaskocz mnie
		</button>
	</div>

	<div class="bwg-field">
		<label class="bwg-label" for="bwg_sender">Od kogo?</label>
		<input type="text" id="bwg_sender" class="bwg-input" placeholder="np. Mama i Tata" autocomplete="off">
	</div>

	<div class="bwg-field">
		<label class="bwg-label" for="bwg_recipient">Dla kogo?</label>
		<input type="text" id="bwg_recipient" class="bwg-input" placeholder="np. dla naszej córeczki Lenki" autocomplete="off">
	</div>

	<div class="bwg-row">
		<div class="bwg-field bwg-field--small">
			<label class="bwg-label" for="bwg_age">Wiek (opcjonalnie):</label>
			<input type="number" id="bwg_age" class="bwg-input" placeholder="lat" min="0" max="120">
		</div>
		<div class="bwg-field bwg-field--large">
			<label class="bwg-label" for="bwg_occasion">Okazja:</label>
			<select id="bwg_occasion" class="bwg-select">
				<?php foreach ( $occasions as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>

	<div class="bwg-row">
		<div class="bwg-field bwg-field--large">
			<label class="bwg-label" for="bwg_tone">Ton i styl:</label>
			<select id="bwg_tone" class="bwg-select">
				<?php foreach ( $tones as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="bwg-field bwg-field--large">
			<label class="bwg-label" for="bwg_length">Długość:</label>
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
			<span class="bwg-toggle__label">🎵 Tryb rymowany</span>
		</label>
	</div>

	<button type="button" id="bwg-submit-btn" class="bwg-btn bwg-btn--primary">
		Generuj przez AI ✨
	</button>

	<div id="bwg-loader" class="bwg-loader" style="display:none;" aria-live="polite">
		<span class="bwg-loader__dot"></span>
		<span class="bwg-loader__dot"></span>
		<span class="bwg-loader__dot"></span>
		<span id="bwg-loader-text">Generujemy dla Ciebie piękne słowa…</span>
	</div>

	<div id="bwg-results" aria-live="polite"></div>

	<div id="bwg-remaining" class="bwg-remaining" style="display:none;" aria-live="polite"></div>

</div>

<!-- Sekcja: Ulepsz życzenia -->
<div class="bwg-wrap bwg-improve-wrap">
	<button type="button" class="bwg-accordion-toggle" id="bwg-improve-toggle" aria-expanded="false">
		<span>✨ Ulepsz swoje życzenia przez AI</span>
		<span class="bwg-accordion-arrow">▾</span>
	</button>

	<div class="bwg-accordion-body" id="bwg-improve-body" style="display:none;">
		<p class="bwg-improve-desc">
			Wklej gotowe życzenia (swoje lub z generatora) — AI je poprawi lub przekształci.
		</p>

		<div class="bwg-field">
			<label class="bwg-label" for="bwg_improve_text">Treść życzeń do ulepszenia:</label>
			<textarea id="bwg_improve_text" class="bwg-input bwg-textarea" rows="4" placeholder="Wklej tutaj życzenia…"></textarea>
		</div>

		<div class="bwg-field">
			<label class="bwg-label" for="bwg_improve_direction">Kierunek ulepszenia:</label>
			<select id="bwg_improve_direction" class="bwg-select">
				<option value="ogolne">Ulepsz ogólnie (usuń banalne frazy)</option>
				<option value="skroc">Skróć do 2 zdań</option>
				<option value="dodaj_rym">Przepisz jako rymowany wiersz</option>
				<option value="wiecej_emocji">Dodaj więcej emocji i głębi</option>
				<option value="oficjalny">Nadaj oficjalny, elegancki charakter</option>
			</select>
		</div>

		<button type="button" id="bwg-improve-btn" class="bwg-btn bwg-btn--primary">
			Ulepsz przez AI ✨
		</button>

		<div id="bwg-improve-result" class="bwg-improve-result" style="display:none;">
			<div class="bwg-card">
				<span class="bwg-card__badge">Ulepszona wersja</span>
				<p class="bwg-card__text" id="bwg-improve-text-out" contenteditable="true"></p>
				<div class="bwg-card__counter" id="bwg-improve-counter"></div>
				<div class="bwg-card__actions">
					<button class="bwg-btn bwg-btn--ghost" id="bwg-improve-copy">📋 Kopiuj</button>
					<button class="bwg-btn bwg-btn--ghost" id="bwg-improve-preview">🎨 Pobierz kartę</button>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal: Podgląd karty z szablonami -->
<div id="bwg-modal" class="bwg-modal" style="display:none;" role="dialog" aria-modal="true" aria-label="Wybierz szablon karty">
	<div class="bwg-modal__overlay" id="bwg-modal-overlay"></div>
	<div class="bwg-modal__box">
		<button class="bwg-modal__close" id="bwg-modal-close" aria-label="Zamknij">✕</button>
		<h3 class="bwg-modal__title">Wybierz szablon karty</h3>
		<div class="bwg-template-picker" id="bwg-template-picker"></div>
		<div class="bwg-modal__preview-wrap">
			<div id="bwg-preview-card" class="bwg-preview-card">
				<p id="bwg-preview-text" class="bwg-preview-text"></p>
			</div>
		</div>
		<div class="bwg-modal__actions">
			<button class="bwg-btn bwg-btn--primary" id="bwg-modal-jpg">⬇️ Pobierz JPG</button>
			<button class="bwg-btn bwg-btn--secondary" id="bwg-modal-pdf">⬇️ Pobierz PDF</button>
		</div>
	</div>
</div>
