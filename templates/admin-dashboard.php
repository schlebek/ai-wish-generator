<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap bwg-admin">

	<h1 class="bwg-admin__page-title">
		<span class="dashicons dashicons-format-quote"></span>
		<?php esc_html_e( 'AI Wish — Statystyki', 'ai-wish-generator' ); ?>
	</h1>

	<div class="bwg-stats-grid">

		<div class="bwg-stat-card bwg-stat-card--accent">
			<div class="bwg-stat-card__icon">📊</div>
			<div class="bwg-stat-card__value"><?php echo esc_html( number_format_i18n( $total ) ); ?></div>
			<div class="bwg-stat-card__label"><?php esc_html_e( 'Życzeń łącznie', 'ai-wish-generator' ); ?></div>
		</div>

		<div class="bwg-stat-card">
			<div class="bwg-stat-card__icon">📅</div>
			<div class="bwg-stat-card__value"><?php echo esc_html( number_format_i18n( $today ) ); ?></div>
			<div class="bwg-stat-card__label"><?php esc_html_e( 'Dzisiaj', 'ai-wish-generator' ); ?></div>
		</div>

		<div class="bwg-stat-card">
			<div class="bwg-stat-card__icon">⚙️</div>
			<div class="bwg-stat-card__value"><?php echo esc_html( get_option( 'bwg_model', 'gemini-2.5-flash' ) ); ?></div>
			<div class="bwg-stat-card__label"><?php esc_html_e( 'Model AI', 'ai-wish-generator' ); ?></div>
		</div>

		<div class="bwg-stat-card">
			<div class="bwg-stat-card__icon">🔒</div>
			<div class="bwg-stat-card__value">
				<?php
				$limit = (int) get_option( 'bwg_daily_limit', 10 );
				echo esc_html( $limit > 0 ? $limit . '/dzień' : __( 'Brak', 'ai-wish-generator' ) );
				?>
			</div>
			<div class="bwg-stat-card__label"><?php esc_html_e( 'Limit per IP', 'ai-wish-generator' ); ?></div>
		</div>

	</div>

	<?php if ( ! empty( $by_occasion ) ) : ?>
		<div class="bwg-panel">
			<h2 class="bwg-panel__title"><?php esc_html_e( 'Popularność okazji', 'ai-wish-generator' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Okazja', 'ai-wish-generator' ); ?></th>
						<th style="width:120px;"><?php esc_html_e( 'Liczba życzeń', 'ai-wish-generator' ); ?></th>
						<th><?php esc_html_e( 'Udział', 'ai-wish-generator' ); ?></th>
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
		<h2 class="bwg-panel__title"><?php esc_html_e( 'Shortcode', 'ai-wish-generator' ); ?></h2>
		<p><?php esc_html_e( 'Wklej na dowolnej stronie lub poście:', 'ai-wish-generator' ); ?></p>
		<code>[ai_wish_generator]</code>
		<p style="margin-top:12px;"><?php esc_html_e( 'Opcjonalne atrybuty:', 'ai-wish-generator' ); ?></p>
		<code>[ai_wish_generator occasions="urodziny,slub" tones="wzruszajacy,smieszny" variants="3"]</code>
	</div>

</div>
