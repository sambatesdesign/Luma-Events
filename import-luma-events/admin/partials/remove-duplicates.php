<?php
/**
 * Remove duplicate EventBrite events that match Luma events.
 *
 * @package Import_Luma_Events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Handle cancel action - MUST come before find_duplicates to clear cache first.
if ( isset( $_GET['clear_cache'] ) && check_admin_referer( 'ile_clear_cache', '_wpnonce' ) ) {
	delete_transient( 'ile_duplicates' );
	wp_safe_redirect( admin_url( 'edit.php?post_type=luma_events&page=ile-remove-duplicates' ) );
	exit;
}

// Handle form submission.
if ( isset( $_POST['find_duplicates'] ) && check_admin_referer( 'ile_find_duplicates' ) ) {
	// Find all Luma events.
	$luma_events = get_posts( array(
		'post_type'      => 'luma_events',
		'posts_per_page' => -1,
		'post_status'    => 'any',
	) );

	// Find all EventBrite events.
	$eventbrite_events = get_posts( array(
		'post_type'      => 'eventbrite_events',
		'posts_per_page' => -1,
		'post_status'    => 'any',
	) );

	// Build array of Luma events with title and date.
	$luma_events_data = array();
	$luma_debug = array(); // Debug info for Luma events.

	foreach ( $luma_events as $event ) {
		$start_date = get_post_meta( $event->ID, 'event_start_date', true );

		// Normalize date to YYYY-MM-DD format (remove time if present).
		$normalized_date = '';
		if ( ! empty( $start_date ) ) {
			// Extract just the date part (YYYY-MM-DD).
			$normalized_date = substr( $start_date, 0, 10 );
		}

		$luma_events_data[] = array(
			'id'         => $event->ID,
			'title'      => $event->post_title,
			'start_date' => $normalized_date,
		);

		// Debug: Store first 5 Luma events' dates.
		if ( count( $luma_debug ) < 5 ) {
			$luma_debug[] = array(
				'title' => $event->post_title,
				'id'    => $event->ID,
				'date'  => $start_date ? $start_date : 'EMPTY',
			);
		}
	}

	// Find duplicates using fuzzy title matching.
	$duplicates = array();
	$debug_info = array(); // For debugging date issues.

	foreach ( $eventbrite_events as $eb_event ) {
		$eb_start_date = get_post_meta( $eb_event->ID, 'event_start_date', true );
		$eb_title = $eb_event->post_title;

		// Debug: Store first 5 EventBrite events' dates.
		if ( count( $debug_info ) < 5 ) {
			$all_meta = get_post_meta( $eb_event->ID );
			$date_keys = array();
			foreach ( $all_meta as $key => $value ) {
				if ( stripos( $key, 'date' ) !== false || stripos( $key, 'time' ) !== false ) {
					$date_keys[ $key ] = is_array( $value ) ? $value[0] : $value;
				}
			}
			$debug_info[] = array(
				'title' => $eb_title,
				'id'    => $eb_event->ID,
				'dates' => $date_keys,
			);
		}

		// Normalize EventBrite title (remove common prefixes/suffixes).
		$eb_normalized = strtolower( trim( $eb_title ) );
		$eb_normalized = preg_replace( '/^\[uk\]\s*/i', '', $eb_normalized ); // Remove [UK] prefix.
		$eb_normalized = preg_replace( '/\s*\(sold out.*\)/i', '', $eb_normalized ); // Remove (Sold Out) suffix.

		// Check against all Luma events with same date.
		foreach ( $luma_events_data as $luma_event ) {
			// Skip if dates don't match.
			if ( $luma_event['start_date'] !== $eb_start_date ) {
				continue;
			}

			// Normalize Luma title.
			$luma_normalized = strtolower( trim( $luma_event['title'] ) );
			$luma_normalized = preg_replace( '/^\[uk\]\s*/i', '', $luma_normalized );
			$luma_normalized = preg_replace( '/\s*\(sold out.*\)/i', '', $luma_normalized );

			// Check for exact match or fuzzy match (allowing for minor variations).
			$is_match = false;
			$match_similarity = 0;

			// Exact match after normalization.
			if ( $eb_normalized === $luma_normalized ) {
				$is_match = true;
				$match_similarity = 100;
			}
			// Fuzzy match: 80%+ similarity for titles longer than 10 characters.
			elseif ( strlen( $eb_normalized ) > 10 && strlen( $luma_normalized ) > 10 ) {
				similar_text( $eb_normalized, $luma_normalized, $match_similarity );
				if ( $match_similarity >= 80 ) {
					$is_match = true;
				}
			}

			if ( $is_match ) {
				$duplicates[] = array(
					'eventbrite_id' => $eb_event->ID,
					'luma_id'       => $luma_event['id'],
					'title'         => $eb_event->post_title,
					'start_date'    => $eb_start_date,
					'similarity'    => round( $match_similarity, 1 ),
				);
				break; // Found a match, move to next EventBrite event.
			}
		}
	}

	// Store duplicates in transient for review.
	set_transient( 'ile_duplicates', $duplicates, HOUR_IN_SECONDS );

	// Show success message with debug info.
	echo '<div class="notice notice-success"><p>';
	printf(
		/* translators: %d: count of duplicates found */
		esc_html__( 'Found %d duplicate events!', 'import-luma-events' ),
		count( $duplicates )
	);
	echo '</p></div>';

	// Show debug info about date fields.
	echo '<div class="notice notice-info">';

	// Luma dates debug.
	if ( ! empty( $luma_debug ) ) {
		echo '<p><strong>Debug: Luma Event Dates (first 5 events)</strong></p>';
		echo '<ul style="font-family: monospace; font-size: 12px; margin-left: 20px;">';
		foreach ( $luma_debug as $info ) {
			echo '<li><strong>' . esc_html( $info['title'] ) . '</strong> (ID: ' . $info['id'] . ')<br>';
			echo '&nbsp;&nbsp;• event_start_date: ' . esc_html( $info['date'] ) . '<br>';
			echo '</li>';
		}
		echo '</ul>';
	}

	// EventBrite dates debug.
	if ( ! empty( $debug_info ) ) {
		echo '<p><strong>Debug: EventBrite Date Fields (first 5 events)</strong></p>';
		echo '<ul style="font-family: monospace; font-size: 12px; margin-left: 20px;">';
		foreach ( $debug_info as $info ) {
			echo '<li><strong>' . esc_html( $info['title'] ) . '</strong> (ID: ' . $info['id'] . ')<br>';
			if ( empty( $info['dates'] ) ) {
				echo '<span style="color: red;">No date/time meta fields found!</span>';
			} else {
				foreach ( $info['dates'] as $key => $value ) {
					echo '&nbsp;&nbsp;• ' . esc_html( $key ) . ': ' . esc_html( $value ) . '<br>';
				}
			}
			echo '</li>';
		}
		echo '</ul>';
	}

	echo '</div>';
}

// Handle delete action.
if ( isset( $_POST['delete_duplicates'] ) && check_admin_referer( 'ile_delete_duplicates' ) ) {
	$duplicates = get_transient( 'ile_duplicates' );
	$deleted = 0;

	if ( $duplicates && is_array( $duplicates ) ) {
		foreach ( $duplicates as $duplicate ) {
			if ( isset( $_POST['delete_' . $duplicate['eventbrite_id']] ) ) {
				// Delete the EventBrite event.
				wp_delete_post( $duplicate['eventbrite_id'], true );
				$deleted++;
			}
		}
	}

	// Clear transient.
	delete_transient( 'ile_duplicates' );

	echo '<div class="notice notice-success"><p>';
	printf(
		/* translators: %d: count of deleted events */
		esc_html__( 'Successfully deleted %d duplicate EventBrite events!', 'import-luma-events' ),
		$deleted
	);
	echo '</p></div>';

	// Clear duplicates array so we don't show the form again.
	$duplicates = array();
}

// Get duplicates from transient if available.
$duplicates = get_transient( 'ile_duplicates' );
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Remove Duplicate Events', 'import-luma-events' ); ?></h1>

	<div class="card">
		<h2><?php esc_html_e( 'Find Duplicate EventBrite Events', 'import-luma-events' ); ?></h2>

		<p><?php esc_html_e( 'This tool will find EventBrite events that have the same title as your Luma events, so you can remove the duplicates.', 'import-luma-events' ); ?></p>

		<p><strong><?php esc_html_e( 'How it works:', 'import-luma-events' ); ?></strong></p>
		<ol style="margin-left: 20px;">
			<li><?php esc_html_e( 'Click "Find Duplicates" to scan for matching event titles', 'import-luma-events' ); ?></li>
			<li><?php esc_html_e( 'Review the list of duplicates found', 'import-luma-events' ); ?></li>
			<li><?php esc_html_e( 'Select which EventBrite events to delete', 'import-luma-events' ); ?></li>
			<li><?php esc_html_e( 'Click "Delete Selected" to permanently remove them', 'import-luma-events' ); ?></li>
		</ol>

		<p class="description">
			<strong><?php esc_html_e( 'Note:', 'import-luma-events' ); ?></strong>
			<?php esc_html_e( 'This matches events by both title (case-insensitive) AND start date. The Luma events will be kept, and only the EventBrite duplicates will be deleted.', 'import-luma-events' ); ?>
		</p>

		<?php if ( ! $duplicates ) : ?>
			<form method="post" style="margin-top: 20px;">
				<?php wp_nonce_field( 'ile_find_duplicates' ); ?>
				<input type="hidden" name="find_duplicates" value="1">
				<?php submit_button( __( 'Find Duplicates', 'import-luma-events' ), 'primary', 'submit', false ); ?>
			</form>
		<?php endif; ?>
	</div>

	<?php if ( $duplicates && is_array( $duplicates ) && count( $duplicates ) > 0 ) : ?>
		<div class="card">
			<h2>
				<?php
				printf(
					/* translators: %d: count of duplicates */
					esc_html__( 'Found %d Duplicate Events', 'import-luma-events' ),
					count( $duplicates )
				);
				?>
			</h2>

			<p><?php esc_html_e( 'Select the EventBrite events you want to delete:', 'import-luma-events' ); ?></p>

			<form method="post">
				<?php wp_nonce_field( 'ile_delete_duplicates' ); ?>
				<input type="hidden" name="delete_duplicates" value="1">

				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 50px;">
								<input type="checkbox" id="select-all">
							</th>
							<th><?php esc_html_e( 'Event Title', 'import-luma-events' ); ?></th>
							<th><?php esc_html_e( 'Start Date', 'import-luma-events' ); ?></th>
							<th><?php esc_html_e( 'EventBrite Post', 'import-luma-events' ); ?></th>
							<th><?php esc_html_e( 'Luma Post', 'import-luma-events' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $duplicates as $duplicate ) : ?>
							<tr>
								<td>
									<input type="checkbox" name="delete_<?php echo esc_attr( $duplicate['eventbrite_id'] ); ?>" class="duplicate-checkbox" checked>
								</td>
								<td>
									<strong><?php echo esc_html( $duplicate['title'] ); ?></strong>
								</td>
								<td>
									<?php
									if ( ! empty( $duplicate['start_date'] ) ) {
										$formatted_date = date( 'M j, Y', strtotime( $duplicate['start_date'] ) );
										echo esc_html( $formatted_date );
									} else {
										echo '<span class="description">No date</span>';
									}
									?>
								</td>
								<td>
									<a href="<?php echo esc_url( get_edit_post_link( $duplicate['eventbrite_id'] ) ); ?>" target="_blank">
										<?php esc_html_e( 'View EventBrite Post', 'import-luma-events' ); ?>
										<span class="dashicons dashicons-external" style="font-size: 14px;"></span>
									</a>
									<br>
									<span class="description">ID: <?php echo esc_html( $duplicate['eventbrite_id'] ); ?></span>
								</td>
								<td>
									<a href="<?php echo esc_url( get_edit_post_link( $duplicate['luma_id'] ) ); ?>" target="_blank">
										<?php esc_html_e( 'View Luma Post', 'import-luma-events' ); ?>
										<span class="dashicons dashicons-external" style="font-size: 14px;"></span>
									</a>
									<br>
									<span class="description">ID: <?php echo esc_html( $duplicate['luma_id'] ); ?></span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<p style="margin-top: 20px;">
					<?php submit_button( __( 'Delete Selected EventBrite Events', 'import-luma-events' ), 'delete', 'submit', false ); ?>
					<?php
					$clear_url = wp_nonce_url(
						admin_url( 'edit.php?post_type=luma_events&page=ile-remove-duplicates&clear_cache=1' ),
						'ile_clear_cache'
					);
					?>
					<a href="<?php echo esc_url( $clear_url ); ?>" class="button" style="margin-left: 10px;">
						<?php esc_html_e( 'Start Over', 'import-luma-events' ); ?>
					</a>
				</p>
			</form>
		</div>

		<script>
			jQuery(document).ready(function($) {
				$('#select-all').on('change', function() {
					$('.duplicate-checkbox').prop('checked', this.checked);
				});
			});
		</script>
	<?php elseif ( $duplicates && is_array( $duplicates ) && count( $duplicates ) === 0 ) : ?>
		<div class="card">
			<h2><?php esc_html_e( 'No Duplicates Found', 'import-luma-events' ); ?></h2>
			<p><?php esc_html_e( 'No EventBrite events were found with matching titles to your Luma events.', 'import-luma-events' ); ?></p>
		</div>
	<?php endif; ?>

	<div class="card">
		<h2><?php esc_html_e( 'Important Notes', 'import-luma-events' ); ?></h2>
		<ul style="list-style: disc; margin-left: 20px;">
			<li><?php esc_html_e( 'This action permanently deletes EventBrite events - they cannot be recovered', 'import-luma-events' ); ?></li>
			<li><?php esc_html_e( 'Only EventBrite events will be deleted - your Luma events will be preserved', 'import-luma-events' ); ?></li>
			<li><?php esc_html_e( 'Matching is based on exact title match (case-insensitive) AND start date', 'import-luma-events' ); ?></li>
			<li><?php esc_html_e( 'Events with similar titles but different dates will NOT be flagged as duplicates', 'import-luma-events' ); ?></li>
			<li><?php esc_html_e( 'Review the duplicates carefully before deleting', 'import-luma-events' ); ?></li>
		</ul>
	</div>
</div>
