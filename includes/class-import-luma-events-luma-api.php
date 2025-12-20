<?php
/**
 * Luma API Integration.
 *
 * @package Import_Luma_Events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import_Luma_Events_Luma_API Class.
 */
class Import_Luma_Events_Luma_API {

	/**
	 * The Luma API base URL.
	 *
	 * @var string
	 */
	private $base_url = 'https://public-api.luma.com';

	/**
	 * The API key.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$options       = get_option( 'ile_luma_options' );
		$this->api_key = isset( $options['luma_api_key'] ) ? $options['luma_api_key'] : '';
	}

	/**
	 * Get all events from a calendar.
	 *
	 * @param string $calendar_id The calendar API ID.
	 * @return array|WP_Error Array of events or WP_Error on failure.
	 */
	public function get_calendar_events( $calendar_id ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'no_api_key', __( 'Luma API key is required. Please configure it in settings.', 'import-luma-events' ) );
		}

		$url = $this->base_url . '/v1/calendar/list-events?calendar_api_id=' . $calendar_id;

		$response = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'x-luma-api-key' => $this->api_key,
					'Content-Type'   => 'application/json',
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$body          = wp_remote_retrieve_body( $response );

		if ( 200 !== $response_code ) {
			$error_message = sprintf(
				/* translators: %1$s: HTTP response code, %2$s: Response body */
				__( 'Luma API request failed with status %1$s: %2$s', 'import-luma-events' ),
				$response_code,
				$body
			);
			return new WP_Error( 'api_error', $error_message );
		}

		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'json_error', __( 'Failed to parse Luma API response.', 'import-luma-events' ) );
		}

		// Return the entries array.
		return isset( $data['entries'] ) ? $data['entries'] : array();
	}

	/**
	 * Get a single event by its API ID.
	 *
	 * @param string $event_id The event API ID.
	 * @return array|WP_Error Event data or WP_Error on failure.
	 */
	public function get_event( $event_id ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'no_api_key', __( 'Luma API key is required. Please configure it in settings.', 'import-luma-events' ) );
		}

		$url = $this->base_url . '/v1/event/get?api_id=' . $event_id;

		$response = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'x-luma-api-key' => $this->api_key,
					'Content-Type'   => 'application/json',
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$body          = wp_remote_retrieve_body( $response );

		if ( 200 !== $response_code ) {
			$error_message = sprintf(
				/* translators: %1$s: HTTP response code, %2$s: Response body */
				__( 'Luma API request failed with status %1$s: %2$s', 'import-luma-events' ),
				$response_code,
				$body
			);
			return new WP_Error( 'api_error', $error_message );
		}

		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'json_error', __( 'Failed to parse Luma API response.', 'import-luma-events' ) );
		}

		return $data;
	}

	/**
	 * Test the API connection.
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function test_connection() {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'no_api_key', __( 'Luma API key is required.', 'import-luma-events' ) );
		}

		$url = $this->base_url . '/v1/user/get-self';

		$response = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'x-luma-api-key' => $this->api_key,
					'Content-Type'   => 'application/json',
				),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			$body          = wp_remote_retrieve_body( $response );
			$error_message = sprintf(
				/* translators: %1$s: HTTP response code */
				__( 'API connection test failed with status %1$s. Please check your API key.', 'import-luma-events' ),
				$response_code
			);
			return new WP_Error( 'connection_failed', $error_message );
		}

		return true;
	}

	/**
	 * Normalize Luma event data to internal format.
	 *
	 * @param array $luma_event The raw Luma event data.
	 * @return array Normalized event data.
	 */
	public function normalize_event_data( $luma_event ) {
		// Extract the event object (it may be nested in 'event' key).
		$event = isset( $luma_event['event'] ) ? $luma_event['event'] : $luma_event;

		// Extract hosts if available.
		$hosts = isset( $luma_event['hosts'] ) && is_array( $luma_event['hosts'] ) ? $luma_event['hosts'] : array();
		$host  = ! empty( $hosts ) ? $hosts[0] : null;

		// Parse location data.
		$geo_address = isset( $event['geo_address_json'] ) ? $event['geo_address_json'] : null;

		$normalized = array(
			// Event core.
			'luma_event_id'   => isset( $event['api_id'] ) ? $event['api_id'] : '',
			'luma_event_url'  => isset( $event['url'] ) ? $event['url'] : '',
			'name'            => isset( $event['name'] ) ? $event['name'] : '',
			'description'     => isset( $event['description'] ) ? $event['description'] : '',
			'description_md'  => isset( $event['description_md'] ) ? $event['description_md'] : '',
			'cover_url'       => isset( $event['cover_url'] ) ? $event['cover_url'] : '',

			// Dates/times.
			'start_at'        => isset( $event['start_at'] ) ? $event['start_at'] : '',
			'end_at'          => isset( $event['end_at'] ) ? $event['end_at'] : '',
			'event_timezone'  => isset( $event['timezone'] ) ? $event['timezone'] : '',
			'duration'        => isset( $event['duration_interval'] ) ? $event['duration_interval'] : '',

			// Location (in-person).
			'venue_name'      => $geo_address ? ( isset( $geo_address['address'] ) ? $geo_address['address'] : '' ) : '',
			'venue_address'   => $geo_address ? ( isset( $geo_address['full_address'] ) ? $geo_address['full_address'] : '' ) : '',
			'venue_city'      => $geo_address ? ( isset( $geo_address['city'] ) ? $geo_address['city'] : '' ) : '',
			'venue_state'     => $geo_address ? ( isset( $geo_address['region'] ) ? $geo_address['region'] : '' ) : '',
			'venue_country'   => $geo_address ? ( isset( $geo_address['country'] ) ? $geo_address['country'] : '' ) : '',
			'venue_lat'       => isset( $event['geo_latitude'] ) ? $event['geo_latitude'] : '',
			'venue_lon'       => isset( $event['geo_longitude'] ) ? $event['geo_longitude'] : '',
			'google_place_id' => $geo_address ? ( isset( $geo_address['google_maps_place_id'] ) ? $geo_address['google_maps_place_id'] : '' ) : '',

			// Virtual event.
			'meeting_url'     => isset( $event['meeting_url'] ) ? $event['meeting_url'] : '',
			'zoom_meeting_url' => isset( $event['zoom_meeting_url'] ) ? $event['zoom_meeting_url'] : '',

			// Organizer/Host.
			'organizer_name'  => $host ? ( isset( $host['name'] ) ? $host['name'] : '' ) : '',
			'organizer_email' => $host ? ( isset( $host['email'] ) ? $host['email'] : '' ) : '',
			'organizer_avatar' => $host ? ( isset( $host['avatar_url'] ) ? $host['avatar_url'] : '' ) : '',

			// Other.
			'visibility'      => isset( $event['visibility'] ) ? $event['visibility'] : 'public',
			'created_at'      => isset( $event['created_at'] ) ? $event['created_at'] : '',
		);

		// Calculate timestamps for sorting.
		if ( ! empty( $normalized['start_at'] ) ) {
			$normalized['start_ts'] = strtotime( $normalized['start_at'] );
			// Convert to local datetime for easier querying.
			$normalized['event_start_date'] = date( 'Y-m-d H:i:s', $normalized['start_ts'] );
		}

		if ( ! empty( $normalized['end_at'] ) ) {
			$normalized['end_ts'] = strtotime( $normalized['end_at'] );
			$normalized['event_end_date'] = date( 'Y-m-d H:i:s', $normalized['end_ts'] );
		}

		return $normalized;
	}
}
