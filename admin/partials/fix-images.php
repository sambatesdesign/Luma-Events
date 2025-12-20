<?php
/**
 * Utility to fix missing images for Luma events.
 *
 * @package Import_Luma_Events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Handle form submission.
if ( isset( $_POST['fix_images'] ) && check_admin_referer( 'ile_fix_images' ) ) {
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// Increase timeout for image downloads.
	add_filter( 'http_request_timeout', function() { return 30; } );

	// Add user agent to prevent blocking.
	add_filter( 'http_headers_useragent', function() {
		return 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' );
	} );

	// Allow redirects (important for Unsplash URLs).
	add_filter( 'http_request_args', function( $args ) {
		$args['redirection'] = 5;
		$args['sslverify'] = false; // Disable SSL verification if your server has SSL issues.
		return $args;
	} );

	// Increase execution time.
	set_time_limit( 300 );

	$args = array(
		'post_type'      => 'luma_events',
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'     => 'cover_url',
				'compare' => 'EXISTS',
			),
		),
	);

	$events        = get_posts( $args );
	$fixed         = 0;
	$failed        = 0;
	$error_details = array();

	foreach ( $events as $event ) {
		// Skip if already has featured image.
		if ( has_post_thumbnail( $event->ID ) ) {
			continue;
		}

		$cover_url = get_post_meta( $event->ID, 'cover_url', true );

		if ( empty( $cover_url ) ) {
			$error_details[] = sprintf(
				'<strong>%s</strong>: No cover URL found in metadata',
				esc_html( $event->post_title )
			);
			$failed++;
			continue;
		}

		// Try to download image using WordPress HTTP API.
		$response = wp_remote_get( $cover_url, array(
			'timeout'   => 30,
			'sslverify' => false,
		) );

		if ( is_wp_error( $response ) ) {
			$error_details[] = sprintf(
				'<strong>%s</strong>: HTTP Error: %s<br><small>URL: %s</small>',
				esc_html( $event->post_title ),
				esc_html( $response->get_error_message() ),
				esc_html( $cover_url )
			);
			$failed++;
			continue;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code !== 200 ) {
			$error_details[] = sprintf(
				'<strong>%s</strong>: HTTP %d error<br><small>URL: %s</small>',
				esc_html( $event->post_title ),
				$response_code,
				esc_html( $cover_url )
			);
			$failed++;
			continue;
		}

		$image_data = wp_remote_retrieve_body( $response );
		if ( empty( $image_data ) ) {
			$error_details[] = sprintf(
				'<strong>%s</strong>: Empty response body<br><small>URL: %s</small>',
				esc_html( $event->post_title ),
				esc_html( $cover_url )
			);
			$failed++;
			continue;
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

		// Create filename.
		$filename = sanitize_file_name( $event->post_name ) . '-' . time() . '.' . $ext;

		// Upload the file.
		$upload = wp_upload_bits( $filename, null, $image_data );

		if ( $upload['error'] ) {
			$error_details[] = sprintf(
				'<strong>%s</strong>: Upload error: %s<br><small>URL: %s</small>',
				esc_html( $event->post_title ),
				esc_html( $upload['error'] ),
				esc_html( $cover_url )
			);
			$failed++;
			continue;
		}

		// Create attachment.
		$attachment = array(
			'post_mime_type' => $content_type,
			'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attachment_id = wp_insert_attachment( $attachment, $upload['file'], $event->ID );

		if ( is_wp_error( $attachment_id ) ) {
			$error_details[] = sprintf(
				'<strong>%s</strong>: Attachment error: %s<br><small>URL: %s</small>',
				esc_html( $event->post_title ),
				esc_html( $attachment_id->get_error_message() ),
				esc_html( $cover_url )
			);
			$failed++;
			continue;
		}

		// Generate attachment metadata.
		$attach_data = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
		wp_update_attachment_metadata( $attachment_id, $attach_data );

		// Set as featured image.
		set_post_thumbnail( $event->ID, $attachment_id );
		$fixed++;
	}

	// Show results.
	if ( $fixed > 0 ) {
		echo '<div class="notice notice-success"><p>';
		printf(
			/* translators: %d: count of fixed images */
			esc_html__( 'Successfully downloaded %d images!', 'import-luma-events' ),
			$fixed
		);
		echo '</p></div>';
	}

	if ( $failed > 0 ) {
		echo '<div class="notice notice-error"><p>';
		printf(
			/* translators: %d: count of failed images */
			esc_html__( 'Failed to download %d images. See details below:', 'import-luma-events' ),
			$failed
		);
		echo '</p><ul style="list-style: disc; margin-left: 20px;">';
		foreach ( $error_details as $detail ) {
			echo '<li>' . $detail . '</li>';
		}
		echo '</ul></div>';
	}
}

// Count events missing images.
$args = array(
	'post_type'      => 'luma_events',
	'posts_per_page' => -1,
	'meta_query'     => array(
		array(
			'key'     => '_thumbnail_id',
			'compare' => 'NOT EXISTS',
		),
	),
);

$missing = count( get_posts( $args ) );
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Fix Missing Event Images', 'import-luma-events' ); ?></h1>

	<div class="card">
		<h2><?php esc_html_e( 'Re-download Missing Images', 'import-luma-events' ); ?></h2>

		<p>
			<?php
			printf(
				/* translators: %d: count of events */
				esc_html__( 'Found %d events without featured images.', 'import-luma-events' ),
				$missing
			);
			?>
		</p>

		<p><?php esc_html_e( 'This tool will attempt to re-download cover images from Luma for events that are missing featured images.', 'import-luma-events' ); ?></p>

		<?php if ( $missing > 0 ) : ?>
			<form method="post">
				<?php wp_nonce_field( 'ile_fix_images' ); ?>
				<input type="hidden" name="fix_images" value="1">
				<?php submit_button( __( 'Fix Missing Images', 'import-luma-events' ), 'primary', 'submit', false ); ?>
			</form>
		<?php else : ?>
			<p><strong><?php esc_html_e( 'All events have featured images!', 'import-luma-events' ); ?></strong></p>
		<?php endif; ?>
	</div>

	<div class="card">
		<h2><?php esc_html_e( 'Why Are Images Missing?', 'import-luma-events' ); ?></h2>

		<p><?php esc_html_e( 'Images may fail to download for several reasons:', 'import-luma-events' ); ?></p>

		<ul style="list-style: disc; margin-left: 20px;">
			<li><?php esc_html_e( 'Server timeout during import', 'import-luma-events' ); ?></li>
			<li><?php esc_html_e( 'Image URL was temporarily unavailable', 'import-luma-events' ); ?></li>
			<li><?php esc_html_e( 'Server firewall blocking external image downloads', 'import-luma-events' ); ?></li>
			<li><?php esc_html_e( 'PHP memory limit too low', 'import-luma-events' ); ?></li>
		</ul>

	</div>
</div>
