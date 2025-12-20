<?php
/**
 * Meta Box for Luma Events - Admin UI
 *
 * @package Import_Luma_Events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import_Luma_Events_Meta_Box Class.
 */
class Import_Luma_Events_Meta_Box {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );
		add_filter( 'manage_luma_events_posts_custom_column', array( $this, 'hide_custom_fields' ), 10, 2 );
	}

	/**
	 * Add meta boxes.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'luma_event_details',
			__( 'Event Details', 'import-luma-events' ),
			array( $this, 'render_event_details' ),
			'luma_events',
			'normal',
			'high'
		);

		add_meta_box(
			'luma_event_location',
			__( 'Location Details', 'import-luma-events' ),
			array( $this, 'render_location_details' ),
			'luma_events',
			'normal',
			'high'
		);

		add_meta_box(
			'luma_event_organizer',
			__( 'Organizer Details', 'import-luma-events' ),
			array( $this, 'render_organizer_details' ),
			'luma_events',
			'side',
			'default'
		);

		add_meta_box(
			'luma_event_info',
			__( 'Luma Event Info', 'import-luma-events' ),
			array( $this, 'render_luma_info' ),
			'luma_events',
			'side',
			'default'
		);
	}

	/**
	 * Render Event Details meta box.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_event_details( $post ) {
		wp_nonce_field( 'luma_event_details_nonce', 'luma_event_details_nonce' );

		$start_date = get_post_meta( $post->ID, 'event_start_date', true );
		$end_date   = get_post_meta( $post->ID, 'event_end_date', true );
		$timezone   = get_post_meta( $post->ID, 'event_timezone', true );
		$duration   = get_post_meta( $post->ID, 'duration', true );

		?>
		<style>
			.luma-meta-field {
				margin-bottom: 15px;
			}
			.luma-meta-field label {
				display: block;
				font-weight: 600;
				margin-bottom: 5px;
			}
			.luma-meta-field input[type="text"],
			.luma-meta-field input[type="datetime-local"] {
				width: 100%;
				max-width: 400px;
			}
			.luma-meta-field-group {
				display: grid;
				grid-template-columns: 1fr 1fr;
				gap: 15px;
			}
			.luma-readonly {
				background-color: #f0f0f0;
				cursor: not-allowed;
			}
			.luma-help-text {
				color: #666;
				font-style: italic;
				font-size: 12px;
				margin-top: 3px;
			}
		</style>

		<div class="luma-meta-field-group">
			<div class="luma-meta-field">
				<label for="event_start_date"><?php esc_html_e( 'Start Date & Time:', 'import-luma-events' ); ?></label>
				<input type="datetime-local"
					   id="event_start_date"
					   name="event_start_date"
					   value="<?php echo esc_attr( str_replace( ' ', 'T', $start_date ) ); ?>"
					   class="regular-text">
			</div>

			<div class="luma-meta-field">
				<label for="event_end_date"><?php esc_html_e( 'End Date & Time:', 'import-luma-events' ); ?></label>
				<input type="datetime-local"
					   id="event_end_date"
					   name="event_end_date"
					   value="<?php echo esc_attr( str_replace( ' ', 'T', $end_date ) ); ?>"
					   class="regular-text">
			</div>
		</div>

		<div class="luma-meta-field">
			<label for="event_timezone"><?php esc_html_e( 'Timezone:', 'import-luma-events' ); ?></label>
			<input type="text"
				   id="event_timezone"
				   name="event_timezone"
				   value="<?php echo esc_attr( $timezone ); ?>"
				   class="regular-text"
				   readonly>
			<p class="luma-help-text"><?php esc_html_e( 'Timezone is synced from Luma (read-only)', 'import-luma-events' ); ?></p>
		</div>

		<div class="luma-meta-field">
			<label for="duration"><?php esc_html_e( 'Duration:', 'import-luma-events' ); ?></label>
			<input type="text"
				   id="duration"
				   name="duration"
				   value="<?php echo esc_attr( $duration ); ?>"
				   class="regular-text luma-readonly"
				   readonly>
			<p class="luma-help-text"><?php esc_html_e( 'ISO 8601 duration format (read-only)', 'import-luma-events' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Render Location Details meta box.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_location_details( $post ) {
		$venue_name    = get_post_meta( $post->ID, 'venue_name', true );
		$venue_address = get_post_meta( $post->ID, 'venue_address', true );
		$venue_city    = get_post_meta( $post->ID, 'venue_city', true );
		$venue_state   = get_post_meta( $post->ID, 'venue_state', true );
		$venue_country = get_post_meta( $post->ID, 'venue_country', true );
		$venue_lat     = get_post_meta( $post->ID, 'venue_lat', true );
		$venue_lon     = get_post_meta( $post->ID, 'venue_lon', true );
		$meeting_url   = get_post_meta( $post->ID, 'meeting_url', true );

		?>
		<div class="luma-meta-field">
			<label for="venue_name"><?php esc_html_e( 'Venue:', 'import-luma-events' ); ?></label>
			<input type="text"
				   id="venue_name"
				   name="venue_name"
				   value="<?php echo esc_attr( $venue_name ); ?>"
				   class="regular-text">
		</div>

		<div class="luma-meta-field">
			<label for="venue_address"><?php esc_html_e( 'Address:', 'import-luma-events' ); ?></label>
			<input type="text"
				   id="venue_address"
				   name="venue_address"
				   value="<?php echo esc_attr( $venue_address ); ?>"
				   class="regular-text">
		</div>

		<div class="luma-meta-field-group">
			<div class="luma-meta-field">
				<label for="venue_city"><?php esc_html_e( 'City:', 'import-luma-events' ); ?></label>
				<input type="text"
					   id="venue_city"
					   name="venue_city"
					   value="<?php echo esc_attr( $venue_city ); ?>"
					   class="regular-text">
			</div>

			<div class="luma-meta-field">
				<label for="venue_state"><?php esc_html_e( 'State:', 'import-luma-events' ); ?></label>
				<input type="text"
					   id="venue_state"
					   name="venue_state"
					   value="<?php echo esc_attr( $venue_state ); ?>"
					   class="regular-text">
			</div>
		</div>

		<div class="luma-meta-field">
			<label for="venue_country"><?php esc_html_e( 'Country:', 'import-luma-events' ); ?></label>
			<input type="text"
				   id="venue_country"
				   name="venue_country"
				   value="<?php echo esc_attr( $venue_country ); ?>"
				   class="regular-text">
		</div>

		<div class="luma-meta-field-group">
			<div class="luma-meta-field">
				<label for="venue_lat"><?php esc_html_e( 'Latitude:', 'import-luma-events' ); ?></label>
				<input type="text"
					   id="venue_lat"
					   name="venue_lat"
					   value="<?php echo esc_attr( $venue_lat ); ?>"
					   class="regular-text luma-readonly"
					   readonly>
			</div>

			<div class="luma-meta-field">
				<label for="venue_lon"><?php esc_html_e( 'Longitude:', 'import-luma-events' ); ?></label>
				<input type="text"
					   id="venue_lon"
					   name="venue_lon"
					   value="<?php echo esc_attr( $venue_lon ); ?>"
					   class="regular-text luma-readonly"
					   readonly>
			</div>
		</div>

		<div class="luma-meta-field">
			<label for="meeting_url"><?php esc_html_e( 'Virtual Meeting URL:', 'import-luma-events' ); ?></label>
			<input type="url"
				   id="meeting_url"
				   name="meeting_url"
				   value="<?php echo esc_attr( $meeting_url ); ?>"
				   class="regular-text">
			<p class="luma-help-text"><?php esc_html_e( 'For virtual events (Zoom, Google Meet, etc.)', 'import-luma-events' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Render Organizer Details meta box.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_organizer_details( $post ) {
		$organizer_name  = get_post_meta( $post->ID, 'organizer_name', true );
		$organizer_email = get_post_meta( $post->ID, 'organizer_email', true );

		?>
		<div class="luma-meta-field">
			<label for="organizer_name"><?php esc_html_e( 'Organizer Name:', 'import-luma-events' ); ?></label>
			<input type="text"
				   id="organizer_name"
				   name="organizer_name"
				   value="<?php echo esc_attr( $organizer_name ); ?>"
				   class="widefat">
		</div>

		<div class="luma-meta-field">
			<label for="organizer_email"><?php esc_html_e( 'Organizer Email:', 'import-luma-events' ); ?></label>
			<input type="email"
				   id="organizer_email"
				   name="organizer_email"
				   value="<?php echo esc_attr( $organizer_email ); ?>"
				   class="widefat">
		</div>
		<?php
	}

	/**
	 * Render Luma Info meta box.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_luma_info( $post ) {
		$luma_event_id  = get_post_meta( $post->ID, 'luma_event_id', true );
		$luma_event_url = get_post_meta( $post->ID, 'luma_event_url', true );
		$last_synced    = get_post_meta( $post->ID, 'luma_last_synced', true );

		?>
		<div class="luma-meta-field">
			<label for="luma_event_id"><?php esc_html_e( 'Luma Event ID:', 'import-luma-events' ); ?></label>
			<input type="text"
				   id="luma_event_id"
				   name="luma_event_id"
				   value="<?php echo esc_attr( $luma_event_id ); ?>"
				   class="widefat luma-readonly"
				   readonly>
			<p class="luma-help-text"><?php esc_html_e( 'Unique Luma identifier', 'import-luma-events' ); ?></p>
		</div>

		<div class="luma-meta-field">
			<label for="luma_event_url"><?php esc_html_e( 'Luma Event URL:', 'import-luma-events' ); ?></label>
			<input type="url"
				   id="luma_event_url"
				   name="luma_event_url"
				   value="<?php echo esc_attr( $luma_event_url ); ?>"
				   class="widefat luma-readonly"
				   readonly>
			<?php if ( $luma_event_url ) : ?>
				<p><a href="<?php echo esc_url( $luma_event_url ); ?>" target="_blank"><?php esc_html_e( 'View on Luma', 'import-luma-events' ); ?> →</a></p>
			<?php endif; ?>
		</div>

		<?php if ( $last_synced ) : ?>
			<div class="luma-meta-field">
				<label><?php esc_html_e( 'Last Synced:', 'import-luma-events' ); ?></label>
				<p><?php echo esc_html( $last_synced ); ?></p>
			</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_meta_box( $post_id, $post ) {
		// Check if this is an autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check post type.
		if ( 'luma_events' !== $post->post_type ) {
			return;
		}

		// Check nonce.
		if ( ! isset( $_POST['luma_event_details_nonce'] ) || ! wp_verify_nonce( $_POST['luma_event_details_nonce'], 'luma_event_details_nonce' ) ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save editable fields.
		$fields = array(
			'event_start_date',
			'event_end_date',
			'venue_name',
			'venue_address',
			'venue_city',
			'venue_state',
			'venue_country',
			'meeting_url',
			'organizer_name',
			'organizer_email',
		);

		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = sanitize_text_field( $_POST[ $field ] );

				// Convert datetime-local format back to MySQL format.
				if ( in_array( $field, array( 'event_start_date', 'event_end_date' ), true ) ) {
					$value = str_replace( 'T', ' ', $value ) . ':00';
				}

				update_post_meta( $post_id, $field, $value );

				// Update timestamps.
				if ( 'event_start_date' === $field ) {
					update_post_meta( $post_id, 'start_ts', strtotime( $value ) );
				} elseif ( 'event_end_date' === $field ) {
					update_post_meta( $post_id, 'end_ts', strtotime( $value ) );
				}
			}
		}
	}

	/**
	 * Hide custom fields meta box (we have our own).
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public function hide_custom_fields( $column, $post_id ) {
		// This filter doesn't actually hide it, but we can use CSS in the admin.
		// Add this to admin CSS if needed.
		return $column;
	}
}
