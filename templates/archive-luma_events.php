<?php
/**
 * Default events archive template.
 *
 * This template can be overridden by copying it to yourtheme/luma-events/archive-luma_events.php
 *
 * @package Import_Luma_Events
 */

get_header(); ?>

<div class="ile-archive-wrapper">
	<header class="ile-archive-header">
		<h1 class="ile-archive-title"><?php esc_html_e( 'Events', 'import-luma-events' ); ?></h1>
		<?php if ( have_posts() ) : ?>
			<p class="ile-archive-description"><?php esc_html_e( 'Join us for upcoming events and networking opportunities', 'import-luma-events' ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>

		<div class="ile-events-grid">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php
				$event_id = get_the_ID();
				$start_ts = get_post_meta( $event_id, 'start_ts', true );
				$start_date = get_post_meta( $event_id, 'event_start_date', true );
				$venue_name = get_post_meta( $event_id, 'venue_name', true );
				$venue_city = get_post_meta( $event_id, 'venue_city', true );
				$venue_state = get_post_meta( $event_id, 'venue_state', true );
				$meeting_url = get_post_meta( $event_id, 'meeting_url', true );
				$zoom_meeting_url = get_post_meta( $event_id, 'zoom_meeting_url', true );
				$is_virtual = ! empty( $meeting_url ) || ! empty( $zoom_meeting_url );

				// Format date - use the stored date which is already timezone-converted
				if ( $start_date ) {
					$start_datetime_obj = new DateTime( $start_date, wp_timezone() );
					$day = $start_datetime_obj->format( 'd' );
					$month = $start_datetime_obj->format( 'M' );
					$year = $start_datetime_obj->format( 'Y' );
					$time = $start_datetime_obj->format( 'g:i A' );
				} else {
					$day = $start_ts ? date_i18n( 'd', $start_ts ) : '';
					$month = $start_ts ? date_i18n( 'M', $start_ts ) : '';
					$year = $start_ts ? date_i18n( 'Y', $start_ts ) : '';
					$time = $start_ts ? date_i18n( 'g:i A', $start_ts ) : '';
				}

				// Location string
				if ( $is_virtual ) {
					$location = __( 'Virtual Event', 'import-luma-events' );
				} elseif ( $venue_name ) {
					$location = $venue_name;
					if ( $venue_city ) {
						$location .= ', ' . $venue_city;
					}
				} else {
					$location = __( 'TBA', 'import-luma-events' );
				}
				?>

				<article class="ile-event-card">
					<a href="<?php the_permalink(); ?>" class="ile-event-card-link">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="ile-event-card-image">
								<?php the_post_thumbnail( 'medium', array( 'class' => 'ile-card-thumb' ) ); ?>
								<div class="ile-event-date-badge">
									<span class="ile-badge-day"><?php echo esc_html( $day ); ?></span>
									<span class="ile-badge-month"><?php echo esc_html( $month ); ?></span>
								</div>
							</div>
						<?php else : ?>
							<div class="ile-event-card-image ile-no-image">
								<div class="ile-event-date-badge">
									<span class="ile-badge-day"><?php echo esc_html( $day ); ?></span>
									<span class="ile-badge-month"><?php echo esc_html( $month ); ?></span>
								</div>
							</div>
						<?php endif; ?>

						<div class="ile-event-card-content">
							<h3 class="ile-event-card-title"><?php the_title(); ?></h3>

							<div class="ile-event-card-meta">
								<?php if ( $time ) : ?>
									<div class="ile-meta-item">
										<span class="ile-meta-icon">🕐</span>
										<span class="ile-meta-text"><?php echo esc_html( $time ); ?></span>
									</div>
								<?php endif; ?>

								<div class="ile-meta-item">
									<span class="ile-meta-icon"><?php echo $is_virtual ? '💻' : '📍'; ?></span>
									<span class="ile-meta-text"><?php echo esc_html( $location ); ?></span>
								</div>
							</div>

							<?php if ( has_excerpt() ) : ?>
								<div class="ile-event-card-excerpt">
									<?php echo wp_trim_words( get_the_excerpt(), 20, '...' ); ?>
								</div>
							<?php endif; ?>

							<div class="ile-event-card-footer">
								<span class="ile-learn-more">
									<?php esc_html_e( 'Learn More', 'import-luma-events' ); ?> →
								</span>
							</div>
						</div>
					</a>
				</article>

			<?php endwhile; ?>
		</div>

		<!-- Pagination -->
		<?php if ( get_the_posts_pagination() ) : ?>
			<div class="ile-pagination">
				<?php
				the_posts_pagination( array(
					'mid_size'  => 2,
					'prev_text' => __( '← Previous', 'import-luma-events' ),
					'next_text' => __( 'Next →', 'import-luma-events' ),
				) );
				?>
			</div>
		<?php endif; ?>

	<?php else : ?>

		<div class="ile-no-events">
			<div class="ile-no-events-icon">📅</div>
			<h2><?php esc_html_e( 'No events found', 'import-luma-events' ); ?></h2>
			<p><?php esc_html_e( 'Check back soon for upcoming events!', 'import-luma-events' ); ?></p>
		</div>

	<?php endif; ?>
</div>

<?php get_footer(); ?>
