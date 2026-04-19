<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap bwg-admin">

	<h1 class="bwg-admin__page-title">
		<span class="dashicons dashicons-format-quote"></span>
		Bebetu AI — Statystyki
	</h1>

	<div class="bwg-stats-grid">

		<div class="bwg-stat-card bwg-stat-card--accent">
			<div class="bwg-stat-card__icon">📊</div>
			<div class="bwg-stat-card__value"><?php echo esc_html( number_format_i18n( $total ) ); ?></div>
			<div class="bwg-stat-card__label">Życzeń łącznie</div>
		</div>

		<div class="bwg-stat-card">
			<div class="bwg-stat-card__icon">📅</div>
			<div class="bwg-stat-card__value"><?php echo esc_html( number_format_i18n( $today ) ); ?></div>
			<div class="bwg-stat-card__label">Dzisiaj</div>
		</div>

		<div class="bwg-stat-card">
			<div class="bwg-stat-card__icon">⚙️</div>
			<div class="bwg-stat-card__value"><?php echo esc_html( get_option( 'bwg_model', 'gemini-2.5-flash' ) ); ?></div>
			<div class="bwg-stat-card__label">Model AI</div>
		</div>

		<div class="bwg-stat-card">
			<div class="bwg-stat-card__icon">🔒</div>
			<div class="bwg-stat-card__value">
				<?php
				$limit = (int) get_option( 'bwg_daily_limit', 10 );
				echo esc_html( $limit > 0 ? $limit . '/dzień' : 'Brak' );
				?>
			</div>
			<div class="bwg-stat-card__label">Limit per IP</div>
		</div>

	</div>

	<?php if ( ! empty( $by_occasion ) ) : ?>
		<div class="bwg-panel">
			<h2 class="bwg-panel__title">Popularność okazji</h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>Okazja</th>
						<th style="width:120px;">Liczba życzeń</th>
						<th>Udział</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $by_occasion as $row ) :
						$pct   = $total > 0 ? round( $row['cnt'] / $total * 100 ) : 0;
						$label = $all_occasions[ $row['occasion'] ] ?? $row['occasion'];
					?>
					<tr>
						<td><?php echo esc_html( $label ); ?></td>
						<td><?php echo esc_html( number_format_i18n( $row['cnt'] ) ); ?></td>
						<td>
							<div class="bwg-bar">
								<div class="bwg-bar__fill" style="width:<?php echo esc_attr( $pct ); ?>%"></div>
								<span class="bwg-bar__label"><?php echo esc_html( $pct ); ?>%</span>
							</div>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>

	<div class="bwg-shortcode-hint bwg-panel">
		<h2 class="bwg-panel__title">Shortcode</h2>
		<p>Wklej na dowolnej stronie lub poście:</p>
		<code>[bebetu_ai_generator]</code>
		<p style="margin-top:12px;">Opcjonalne atrybuty:</p>
		<code>[bebetu_ai_generator occasions="urodziny,slub" tones="wzruszajacy,smieszny" variants="3"]</code>
	</div>

</div>
