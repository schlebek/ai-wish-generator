<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap bwg-admin">

	<h1 class="bwg-admin__page-title">
		<span class="dashicons dashicons-list-view"></span>
		<?php esc_html_e( 'AI Wish — Historia życzeń', 'ai-wish-generator' ); ?>
	</h1>

	<p class="bwg-history__summary">
		<?php
		printf(
			/* translators: 1: total count, 2: current page, 3: total pages */
			esc_html__( 'Łącznie: %1$s życzeń — strona %2$s z %3$s', 'ai-wish-generator' ),
			'<strong>' . esc_html( number_format_i18n( $total ) ) . '</strong>',
			esc_html( $current_page ),
			esc_html( max( 1, $total_pages ) )
		);
		?>
	</p>

	<?php if ( empty( $wishes ) ) : ?>
		<div class="notice notice-info inline"><p><?php esc_html_e( 'Brak wygenerowanych życzeń.', 'ai-wish-generator' ); ?></p></div>
	<?php else : ?>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th style="width:130px;"><?php esc_html_e( 'Data', 'ai-wish-generator' ); ?></th>
					<th style="width:110px;"><?php esc_html_e( 'Okazja', 'ai-wish-generator' ); ?></th>
					<th style="width:90px;"><?php esc_html_e( 'Ton', 'ai-wish-generator' ); ?></th>
					<th style="width:140px;"><?php esc_html_e( 'Od / Dla', 'ai-wish-generator' ); ?></th>
					<th><?php esc_html_e( 'Treść życzeń', 'ai-wish-generator' ); ?></th>
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
							<br><small><?php echo esc_html( $wish['age'] ); ?> <?php esc_html_e( 'lat', 'ai-wish-generator' ); ?></small>
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
					echo wp_kses_post( paginate_links( array(
						'base'      => add_query_arg( 'paged', '%#%' ),
						'format'    => '',
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
						'total'     => $total_pages,
						'current'   => $current_page,
					) ) );
					?>
				</div>
			</div>
		<?php endif; ?>

	<?php endif; ?>

</div>
