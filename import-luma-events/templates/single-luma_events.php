<?php
/**
 * Default single event template.
 *
 * This template can be overridden by copying it to yourtheme/luma-events/single-luma_events.php
 *
 * @package Import_Luma_Events
 */

get_header();

// Get event meta data
$event_id = get_the_ID();
$start_ts = get_post_meta( $event_id, 'start_ts', true );
$end_ts = get_post_meta( $event_id, 'end_ts', true );
$start_date = get_post_meta( $event_id, 'event_start_date', true );
$end_date = get_post_meta( $event_id, 'event_end_date', true );
$timezone = get_post_meta( $event_id, 'event_timezone', true );
$luma_url = get_post_meta( $event_id, 'luma_event_url', true );

// Venue/Location data
$venue_name = get_post_meta( $event_id, 'venue_name', true );
$venue_address = get_post_meta( $event_id, 'venue_address', true );
$venue_city = get_post_meta( $event_id, 'venue_city', true );
$venue_state = get_post_meta( $event_id, 'venue_state', true );
$venue_country = get_post_meta( $event_id, 'venue_country', true );
$venue_lat = get_post_meta( $event_id, 'venue_lat', true );
$venue_lon = get_post_meta( $event_id, 'venue_lon', true );

// Virtual event data
$meeting_url = get_post_meta( $event_id, 'meeting_url', true );
$zoom_meeting_url = get_post_meta( $event_id, 'zoom_meeting_url', true );

// Organizer data
$org_name = get_post_meta( $event_id, 'organizer_name', true );

// Determine if virtual event
$is_virtual = ! empty( $meeting_url ) || ! empty( $zoom_meeting_url );
$virtual_url = $zoom_meeting_url ? $zoom_meeting_url : $meeting_url;

// Format dates - use the stored dates which are already timezone-converted
if ( $start_date ) {
	$start_datetime_obj = new DateTime( $start_date, wp_timezone() );
	$start_date_formatted = $start_datetime_obj->format( 'l, F j, Y' );
	$start_time = $start_datetime_obj->format( 'g:i A' );
} else {
	$start_date_formatted = $start_ts ? date_i18n( 'l, F j, Y', $start_ts ) : '';
	$start_time = $start_ts ? date_i18n( 'g:i A', $start_ts ) : '';
}

if ( $end_date ) {
	$end_datetime_obj = new DateTime( $end_date, wp_timezone() );
	$end_time = $end_datetime_obj->format( 'g:i A' );
} else {
	$end_time = $end_ts ? date_i18n( 'g:i A', $end_ts ) : '';
}

$timezone_short = $timezone ? explode( '/', $timezone )[1] ?? '' : '';
$timezone_short = str_replace( '_', ' ', $timezone_short );
?>

<div class="ile-single-event-wrapper">
	<?php while ( have_posts() ) : the_post(); ?>

		<div class="ile-event-header">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="ile-event-hero">
					<?php the_post_thumbnail( 'large', array( 'class' => 'ile-event-hero-image' ) ); ?>
					<div class="ile-event-hero-overlay"></div>
				</div>
			<?php endif; ?>

			<div class="ile-event-title-section">
				<h1 class="ile-event-title"><?php the_title(); ?></h1>
				<div class="ile-event-date-main"><?php echo esc_html( $start_date_formatted ); ?></div>
			</div>
		</div>

		<div class="ile-event-content-wrapper">
			<div class="ile-event-sidebar">
				<!-- Date and Time -->
				<div class="ile-event-meta-box">
					<h3 class="ile-meta-box-title">
						<span class="ile-icon">📅</span>
						<?php esc_html_e( 'Date and Time', 'import-luma-events' ); ?>
					</h3>
					<div class="ile-meta-box-content">
						<p class="ile-meta-date"><?php echo esc_html( $start_date_formatted ); ?></p>
						<?php if ( $start_time ) : ?>
							<p class="ile-meta-time">
								<?php echo esc_html( $start_time ); ?>
								<?php if ( $end_time ) : ?>
									- <?php echo esc_html( $end_time ); ?>
								<?php endif; ?>
								<?php if ( $timezone_short ) : ?>
									<span class="ile-timezone">(<?php echo esc_html( $timezone_short ); ?>)</span>
								<?php endif; ?>
							</p>
						<?php endif; ?>
					</div>
				</div>

				<!-- Location -->
				<div class="ile-event-meta-box">
					<h3 class="ile-meta-box-title">
						<span class="ile-icon"><?php echo $is_virtual ? '💻' : '📍'; ?></span>
						<?php esc_html_e( 'Location', 'import-luma-events' ); ?>
					</h3>
					<div class="ile-meta-box-content">
						<?php if ( $is_virtual ) : ?>
							<p class="ile-virtual-label"><?php esc_html_e( 'Virtual Event', 'import-luma-events' ); ?></p>
							<?php if ( $virtual_url ) : ?>
								<p class="ile-meeting-info"><?php esc_html_e( 'Meeting link will be provided after registration', 'import-luma-events' ); ?></p>
							<?php endif; ?>
						<?php else : ?>
							<?php if ( $venue_name ) : ?>
								<p class="ile-venue-name"><?php echo esc_html( $venue_name ); ?></p>
							<?php endif; ?>
							<?php if ( $venue_address ) : ?>
								<p class="ile-venue-address"><?php echo esc_html( $venue_address ); ?></p>
							<?php endif; ?>
							<?php if ( $venue_city || $venue_state ) : ?>
								<p class="ile-venue-city">
									<?php
									$location_parts = array_filter( array( $venue_city, $venue_state, $venue_country ) );
									echo esc_html( implode( ', ', $location_parts ) );
									?>
								</p>
							<?php endif; ?>
							<?php if ( $venue_lat && $venue_lon ) : ?>
								<a href="#ile-map" class="ile-view-map-link"><?php esc_html_e( '→ View Map', 'import-luma-events' ); ?></a>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>

				<!-- Organizer -->
				<?php if ( $org_name ) : ?>
					<div class="ile-event-meta-box">
						<h3 class="ile-meta-box-title">
							<span class="ile-icon">👤</span>
							<?php esc_html_e( 'Organizer', 'import-luma-events' ); ?>
						</h3>
						<div class="ile-meta-box-content">
							<p><?php echo esc_html( $org_name ); ?></p>
						</div>
					</div>
				<?php endif; ?>

				<!-- Register Button -->
				<?php if ( $luma_url ) : ?>
					<div class="ile-event-register-box">
						<a href="<?php echo esc_url( $luma_url ); ?>" target="_blank" rel="noopener" class="ile-register-button">
							<?php esc_html_e( 'Register on Luma', 'import-luma-events' ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<div class="ile-event-main-content">
				<div class="ile-event-description">
					<h2 class="ile-section-title"><?php esc_html_e( 'Event Details', 'import-luma-events' ); ?></h2>
					<div class="ile-description-content">
						<?php the_content(); ?>
					</div>
				</div>

				<!-- Google Map -->
				<?php if ( ! $is_virtual && $venue_lat && $venue_lon ) : ?>
					<div id="ile-map" class="ile-map-section">
						<h2 class="ile-section-title"><?php esc_html_e( 'Event Location', 'import-luma-events' ); ?></h2>
						<div class="ile-map-container">
							<iframe
								src="https://maps.google.com/maps?q=<?php echo esc_attr( $venue_lat ); ?>,<?php echo esc_attr( $venue_lon ); ?>&hl=en&z=14&output=embed"
								width="100%"
								height="450"
								frameborder="0"
								style="border:0;"
								allowfullscreen
								loading="lazy"
								referrerpolicy="no-referrer-when-downgrade">
							</iframe>
						</div>
						<div class="ile-map-address">
							<?php if ( $venue_name ) : ?>
								<p class="ile-map-venue-name"><?php echo esc_html( $venue_name ); ?></p>
							<?php endif; ?>
							<p class="ile-map-address-text">
								<?php
								$full_address = array_filter( array( $venue_address, $venue_city, $venue_state, $venue_country ) );
								echo esc_html( implode( ', ', $full_address ) );
								?>
							</p>
							<a href="https://www.google.com/maps/search/?api=1&query=<?php echo esc_attr( $venue_lat ); ?>,<?php echo esc_attr( $venue_lon ); ?>"
							   target="_blank"
							   rel="noopener"
							   class="ile-get-directions">
								<?php esc_html_e( 'Get Directions →', 'import-luma-events' ); ?>
							</a>
						</div>
					</div>
				<?php elseif ( $is_virtual ) : ?>
					<div class="ile-virtual-section">
						<div class="ile-virtual-image">
							<img src="<?php echo esc_url( IMPORT_LUMA_EVENTS_PLUGIN_URL . 've.png' ); ?>" alt="<?php esc_attr_e( 'Virtual Event', 'import-luma-events' ); ?>" />
						</div>
						<h3><?php esc_html_e( 'Virtual Event', 'import-luma-events' ); ?></h3>
						<p><?php esc_html_e( 'Join from anywhere in the world', 'import-luma-events' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>

	<?php endwhile; ?>
</div>

<?php get_footer(); ?>
