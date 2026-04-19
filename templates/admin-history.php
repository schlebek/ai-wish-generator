<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap bwg-admin">

	<h1 class="bwg-admin__page-title">
		<span class="dashicons dashicons-list-view"></span>
		Bebetu AI — Historia życzeń
	</h1>

	<p class="bwg-history__summary">
		Łącznie: <strong><?php echo esc_html( number_format_i18n( $total ) ); ?></strong> życzeń
		&mdash; strona <?php echo esc_html( $current_page ); ?> z <?php echo esc_html( max( 1, $total_pages ) ); ?>
	</p>

	<?php if ( empty( $wishes ) ) : ?>
		<div class="notice notice-info inline"><p>Brak wygenerowanych życzeń.</p></div>
	<?php else : ?>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th style="width:130px;">Data</th>
					<th style="width:110px;">Okazja</th>
					<th style="width:90px;">Ton</th>
					<th style="width:140px;">Od / Dla</th>
					<th>Treść życzeń</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $wishes as $wish ) :
					$occasion_label = $all_occasions[ $wish['occasion'] ] ?? $wish['occasion'];
					$tone_label     = $all_tones[ $wish['tone'] ]         ?? $wish['tone'];
				?>
				<tr>
					<td><?php echo esc_html( wp_date( 'd.m.Y H:i', strtotime( $wish['created_at'] ) ) ); ?></td>
					<td><?php echo esc_html( $occasion_label ); ?></td>
					<td><?php echo esc_html( $tone_label ); ?></td>
					<td>
						<strong><?php echo esc_html( $wish['sender'] ); ?></strong>
						<br>→ <?php echo esc_html( $wish['recipient'] ); ?>
						<?php if ( $wish['age'] ) : ?>
							<br><small><?php echo esc_html( $wish['age'] ); ?> lat</small>
						<?php endif; ?>
					</td>
					<td class="bwg-history__text"><?php echo esc_html( $wish['wish_text'] ); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $total_pages > 1 ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
					<?php
					echo wp_kses_post( paginate_links( [
						'base'      => add_query_arg( 'paged', '%#%' ),
						'format'    => '',
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
						'total'     => $total_pages,
						'current'   => $current_page,
					] ) );
					?>
				</div>
			</div>
		<?php endif; ?>

	<?php endif; ?>

</div>
