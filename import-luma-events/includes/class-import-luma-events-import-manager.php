<?php
/**
 * Import Manager - Handles importing and syncing Luma events.
 *
 * @package Import_Luma_Events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import_Luma_Events_Import_Manager Class.
 */
class Import_Luma_Events_Import_Manager {

	/**
	 * The Luma API instance.
	 *
	 * @var Import_Luma_Events_Luma_API
	 */
	private $luma_api;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->luma_api = new Import_Luma_Events_Luma_API();
	}

	/**
	 * Import all events from a calendar.
	 *
	 * @param string $calendar_id   The calendar API ID.
	 * @param array  $import_options Import options.
	 * @return array Import results.
	 */
	public function import_calendar_events( $calendar_id, $import_options = array() ) {
		$defaults = array(
			'post_status'     => 'publish',
			'update_existing' => true,
			'category_ids'    => array(),
			'tag_ids'         => array(),
		);

		$options = wp_parse_args( $import_options, $defaults );

		// Fetch events from Luma API.
		$events = $this->luma_api->get_calendar_events( $calendar_id );

		if ( is_wp_error( $events ) ) {
			return array(
				'success' => false,
				'error'   => $events->get_error_message(),
			);
		}

		$results = array(
			'created' => 0,
			'updated' => 0,
			'skipped' => 0,
			'errors'  => array(),
		);

		foreach ( $events as $luma_event ) {
			$result = $this->import_event( $luma_event, $options );

			if ( is_wp_error( $result ) ) {
				$results['errors'][] = $result->get_error_message();
				$results['skipped']++;
			} else {
				if ( 'created' === $result['status'] ) {
					$results['created']++;
				} elseif ( 'updated' === $result['status'] ) {
					$results['updated']++;
				} else {
					$results['skipped']++;
				}
			}
		}

		// Save import history.
		$this->save_import_history( $calendar_id, $results );

		return array_merge( array( 'success' => true ), $results );
	}

	/**
	 * Import a single event.
	 *
	 * @param array $luma_event Raw Luma event data.
	 * @param array $options    Import options.
	 * @return array|WP_Error Import result or error.
	 */
	public function import_event( $luma_event, $options = array() ) {
		// Normalize the event data.
		$event_data = $this->luma_api->normalize_event_data( $luma_event );

		if ( empty( $event_data['luma_event_id'] ) ) {
			return new WP_Error( 'missing_event_id', __( 'Event is missing Luma event ID.', 'import-luma-events' ) );
		}

		// Check if event already exists.
		$existing_post = $this->find_event_by_luma_id( $event_data['luma_event_id'] );

		if ( $existing_post ) {
			// Event exists.
			if ( empty( $options['update_existing'] ) ) {
				return array(
					'status'  => 'skipped',
					'post_id' => $existing_post->ID,
					'message' => __( 'Event already exists and update is disabled.', 'import-luma-events' ),
				);
			}

			// Update existing event.
			$post_id = $this->update_event_post( $existing_post->ID, $event_data, $options );
			return array(
				'status'  => 'updated',
				'post_id' => $post_id,
				'message' => __( 'Event updated successfully.', 'import-luma-events' ),
			);
		}

		// Create new event.
		$post_id = $this->create_event_post( $event_data, $options );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		return array(
			'status'  => 'created',
			'post_id' => $post_id,
			'message' => __( 'Event created successfully.', 'import-luma-events' ),
		);
	}

	/**
	 * Create a new event post.
	 *
	 * @param array $event_data Event data.
	 * @param array $options    Import options.
	 * @return int|WP_Error Post ID or error.
	 */
	private function create_event_post( $event_data, $options ) {
		// Use description_md (markdown) if available, otherwise plain description.
		$content = ! empty( $event_data['description_md'] ) ? $event_data['description_md'] : $event_data['description'];

		// Convert markdown to HTML.
		$content = $this->convert_markdown_to_html( $content );

		// Sanitize content to ensure proper HTML structure.
		$content = wp_kses_post( $content );

		$post_data = array(
			'post_title'   => $event_data['name'],
			'post_content' => $content,
			'post_status'  => isset( $options['post_status'] ) ? $options['post_status'] : 'publish',
			'post_type'    => 'luma_events',
		);

		$post_id = wp_insert_post( $post_data );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Clear all caches for this post.
		clean_post_cache( $post_id );
		wp_cache_delete( $post_id, 'posts' );
		wp_cache_delete( $post_id, 'post_meta' );

		// Save all event meta.
		$this->save_event_meta( $post_id, $event_data );

		// Set featured image.
		if ( ! empty( $event_data['cover_url'] ) ) {
			$this->set_featured_image( $post_id, $event_data['cover_url'] );
		}

		// Assign categories and tags.
		if ( ! empty( $options['category_ids'] ) ) {
			wp_set_object_terms( $post_id, $options['category_ids'], 'luma_category' );
		}

		if ( ! empty( $options['tag_ids'] ) ) {
			wp_set_object_terms( $post_id, $options['tag_ids'], 'luma_tag' );
		}

		return $post_id;
	}

	/**
	 * Update an existing event post.
	 *
	 * @param int   $post_id    Post ID.
	 * @param array $event_data Event data.
	 * @param array $options    Import options.
	 * @return int Post ID.
	 */
	private function update_event_post( $post_id, $event_data, $options ) {
		// Use description_md (markdown) if available, otherwise plain description.
		$content = ! empty( $event_data['description_md'] ) ? $event_data['description_md'] : $event_data['description'];

		// Convert markdown to HTML.
		$content = $this->convert_markdown_to_html( $content );

		// Sanitize content to ensure proper HTML structure.
		$content = wp_kses_post( $content );

		$post_data = array(
			'ID'           => $post_id,
			'post_title'   => $event_data['name'],
			'post_content' => $content,
		);

		wp_update_post( $post_data );

		// Clear all caches for this post.
		clean_post_cache( $post_id );
		wp_cache_delete( $post_id, 'posts' );
		wp_cache_delete( $post_id, 'post_meta' );

		// Update all event meta.
		$this->save_event_meta( $post_id, $event_data );

		// Update featured image if changed.
		if ( ! empty( $event_data['cover_url'] ) ) {
			$current_image_url = get_post_meta( $post_id, 'cover_url', true );
			if ( $current_image_url !== $event_data['cover_url'] ) {
				$this->set_featured_image( $post_id, $event_data['cover_url'] );
			}
		}

		return $post_id;
	}

	/**
	 * Save event meta fields.
	 *
	 * @param int   $post_id    Post ID.
	 * @param array $event_data Event data.
	 */
	private function save_event_meta( $post_id, $event_data ) {
		$meta_fields = array(
			'luma_event_id',
			'luma_event_url',
			'event_start_date',
			'event_end_date',
			'start_ts',
			'end_ts',
			'event_timezone',
			'duration',
			'venue_name',
			'venue_address',
			'venue_city',
			'venue_state',
			'venue_country',
			'venue_lat',
			'venue_lon',
			'google_place_id',
			'meeting_url',
			'zoom_meeting_url',
			'organizer_name',
			'organizer_email',
			'organizer_avatar',
			'visibility',
			'created_at',
			'cover_url',
		);

		foreach ( $meta_fields as $field ) {
			$value = isset( $event_data[ $field ] ) ? $event_data[ $field ] : '';
			update_post_meta( $post_id, $field, $value );
		}

		// Store original Luma data for reference.
		update_post_meta( $post_id, 'luma_origin', 'luma' );
		update_post_meta( $post_id, 'luma_last_synced', current_time( 'mysql' ) );
	}

	/**
	 * Set the featured image from a URL.
	 *
	 * @param int    $post_id   Post ID.
	 * @param string $image_url Image URL.
	 * @return int|false Attachment ID or false on failure.
	 */
	private function set_featured_image( $post_id, $image_url ) {
		// Check if post already has a featured image.
		if ( has_post_thumbnail( $post_id ) ) {
			return get_post_thumbnail_id( $post_id );
		}

		// Validate image URL.
		if ( empty( $image_url ) ) {
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// Download image using WordPress HTTP API.
		$response = wp_remote_get( $image_url, array(
			'timeout'   => 30,
			'sslverify' => false,
		) );

		if ( is_wp_error( $response ) ) {
			error_log( 'Luma Events: HTTP error downloading image for post ' . $post_id . ': ' . $response->get_error_message() );
			error_log( 'Image URL: ' . $image_url );
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code !== 200 ) {
			error_log( 'Luma Events: HTTP ' . $response_code . ' error for image: ' . $image_url );
			return false;
		}

		$image_data = wp_remote_retrieve_body( $response );
		if ( empty( $image_data ) ) {
			error_log( 'Luma Events: Empty image data for: ' . $image_url );
			return false;
		}

		// Get file extension from Content-Type header.
		$content_type = wp_remote_retrieve_header( $response, 'content-type' );
		$ext = 'jpg'; // Default.
		if ( strpos( $content_type, 'image/png' ) !== false ) {
			$ext = 'png';
		} elseif ( strpos( $content_type, 'image/gif' ) !== false ) {
			$ext = 'gif';
		} elseif ( strpos( $content_type, 'image/webp' ) !== false ) {
			$ext = 'webp';
		}

		// Get post slug for filename.
		$post = get_post( $post_id );
		$filename = sanitize_file_name( $post->post_name ) . '-' . time() . '.' . $ext;

		// Upload the file.
		$upload = wp_upload_bits( $filename, null, $image_data );

		if ( $upload['error'] ) {
			error_log( 'Luma Events: Upload error for post ' . $post_id . ': ' . $upload['error'] );
			return false;
		}

		// Create attachment.
		$attachment = array(
			'post_mime_type' => $content_type,
			'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attachment_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );

		if ( is_wp_error( $attachment_id ) ) {
			error_log( 'Luma Events: Attachment creation error: ' . $attachment_id->get_error_message() );
			return false;
		}

		// Generate attachment metadata.
		$attach_data = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
		wp_update_attachment_metadata( $attachment_id, $attach_data );

		// Set as featured image.
		set_post_thumbnail( $post_id, $attachment_id );

		return $attachment_id;
	}

	/**
	 * Find an event post by Luma event ID.
	 *
	 * @param string $luma_event_id The Luma event ID.
	 * @return WP_Post|null Post object or null if not found.
	 */
	private function find_event_by_luma_id( $luma_event_id ) {
		$args = array(
			'post_type'      => 'luma_events',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'   => 'luma_event_id',
					'value' => $luma_event_id,
				),
			),
		);

		$query = new WP_Query( $args );

		return $query->have_posts() ? $query->posts[0] : null;
	}

	/**
	 * Save import history.
	 *
	 * @param string $calendar_id The calendar ID.
	 * @param array  $results     Import results.
	 */
	private function save_import_history( $calendar_id, $results ) {
		$history = get_option( 'ile_import_history', array() );

		$history[] = array(
			'calendar_id' => $calendar_id,
			'date'        => current_time( 'mysql' ),
			'created'     => $results['created'],
			'updated'     => $results['updated'],
			'skipped'     => $results['skipped'],
			'errors'      => count( $results['errors'] ),
		);

		// Keep only last 50 imports.
		if ( count( $history ) > 50 ) {
			$history = array_slice( $history, -50 );
		}

		update_option( 'ile_import_history', $history );
	}

	/**
	 * Convert markdown to HTML.
	 *
	 * Handles common markdown patterns used in Luma event descriptions.
	 *
	 * @param string $markdown The markdown text.
	 * @return string HTML output.
	 */
	private function convert_markdown_to_html( $markdown ) {
		if ( empty( $markdown ) ) {
			return '';
		}

		$html = $markdown;

		// Normalize line endings.
		$html = str_replace( "\r\n", "\n", $html );
		$html = str_replace( "\r", "\n", $html );

		// Convert links: [text](url) to <a href="url">text</a>
		// Use a callback to properly handle URLs with special characters.
		$html = preg_replace_callback(
			'/\[([^\]]+)\]\(([^)]+)\)/',
			function( $matches ) {
				$text = $matches[1];
				$url = trim( $matches[2] );
				// Only convert if it looks like a valid URL.
				if ( preg_match( '/^https?:\/\//', $url ) ) {
					return '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $text ) . '</a>';
				}
				// Return original if not a valid URL.
				return $matches[0];
			},
			$html
		);

		// Convert bold: **text** or __text__ to <strong>text</strong>
		// Use non-greedy matching and ensure we match pairs correctly.
		$html = preg_replace( '/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $html );
		$html = preg_replace( '/__([^_]+)__/', '<strong>$1</strong>', $html );

		// Convert italic with asterisks: *text* to <em>text</em>
		// Must have space or start of string before, and space or end of string/punctuation after.
		// Only match if not adjacent to other asterisks.
		$html = preg_replace( '/(?<!\*)\*([^*\n]+)\*(?!\*)/', '<em>$1</em>', $html );

		// Convert headers: # Header to <h1>Header</h1>, ## to <h2>, etc.
		$html = preg_replace( '/^#{6}\s*(.+)$/m', '<h6>$1</h6>', $html );
		$html = preg_replace( '/^#{5}\s*(.+)$/m', '<h5>$1</h5>', $html );
		$html = preg_replace( '/^#{4}\s*(.+)$/m', '<h4>$1</h4>', $html );
		$html = preg_replace( '/^#{3}\s*(.+)$/m', '<h3>$1</h3>', $html );
		$html = preg_replace( '/^#{2}\s*(.+)$/m', '<h2>$1</h2>', $html );
		$html = preg_replace( '/^#{1}\s*(.+)$/m', '<h1>$1</h1>', $html );

		// Convert unordered lists: - item or * item (at start of line only).
		// Be careful not to match emphasis asterisks.
		$html = preg_replace( '/^-\s+(.+)$/m', '<li>$1</li>', $html );

		// Wrap consecutive <li> tags in <ul>.
		$html = preg_replace( '/((?:<li>.*?<\/li>\s*)+)/s', '<ul>$1</ul>', $html );

		// Split into paragraphs by double newlines.
		$paragraphs = preg_split( '/\n\s*\n/', $html );
		$processed = array();

		foreach ( $paragraphs as $para ) {
			$para = trim( $para );
			if ( empty( $para ) ) {
				continue;
			}

			// Don't wrap if already a block element.
			if ( preg_match( '/^<(h[1-6]|ul|ol|li|div|blockquote|p)/', $para ) ) {
				$processed[] = $para;
			} else {
				// Convert single newlines to <br> within paragraphs.
				$para = str_replace( "\n", '<br>', $para );
				$processed[] = '<p>' . $para . '</p>';
			}
		}

		$html = implode( "\n", $processed );

		return $html;
	}
}
