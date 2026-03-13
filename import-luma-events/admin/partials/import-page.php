<?php
/**
 * Import page template.
 *
 * @package Import_Luma_Events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options     = get_option( 'ile_luma_options' );
$calendar_id = isset( $options['luma_calendar_id'] ) ? $options['luma_calendar_id'] : '';
$last_sync   = get_option( 'ile_last_sync', array() );

// Display success/error messages.
if ( isset( $_GET['imported'] ) && 'success' === $_GET['imported'] ) {
	$created = isset( $_GET['created'] ) ? intval( $_GET['created'] ) : 0;
	$updated = isset( $_GET['updated'] ) ? intval( $_GET['updated'] ) : 0;
	$skipped = isset( $_GET['skipped'] ) ? intval( $_GET['skipped'] ) : 0;

	echo '<div class="notice notice-success is-dismissible"><p>';
	printf(
		/* translators: %1$d: created count, %2$d: updated count, %3$d: skipped count */
		esc_html__( 'Import completed! Created: %1$d, Updated: %2$d, Skipped: %3$d', 'import-luma-events' ),
		$created,
		$updated,
		$skipped
	);
	echo '</p></div>';
}

if ( isset( $_GET['ile_msg'] ) && 'cron_rescheduled' === $_GET['ile_msg'] ) {
	echo '<div class="notice notice-success is-dismissible"><p>';
	esc_html_e( 'Cron schedule has been reset successfully!', 'import-luma-events' );
	echo '</p></div>';
}

if ( isset( $_GET['error'] ) ) {
	$error = sanitize_text_field( $_GET['error'] );
	$message = '';

	switch ( $error ) {
		case 'no_calendar':
			$message = __( 'Please configure your calendar ID in settings first.', 'import-luma-events' );
			break;
		case 'import_failed':
			$message = __( 'Import failed. Please check your API settings and try again.', 'import-luma-events' );
			break;
		default:
			$message = __( 'An error occurred.', 'import-luma-events' );
	}

	echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
}
?>

<div class="wrap ile-import-page">
	<h1><?php esc_html_e( 'Import Luma Events', 'import-luma-events' ); ?></h1>

	<!-- Main Grid Layout -->
	<div class="ile-grid-container">
		<!-- Row 1 -->
		<div class="ile-row-1">
			<!-- Import Events Card -->
			<div class="card">
				<h2><?php esc_html_e( 'Import Events from Luma', 'import-luma-events' ); ?></h2>

		<?php if ( empty( $calendar_id ) ) : ?>
			<div class="notice notice-warning inline">
				<p>
					<?php
					printf(
						/* translators: %s: settings page URL */
						__( 'Please <a href="%s">configure your Luma API settings</a> first.', 'import-luma-events' ),
						esc_url( admin_url( 'edit.php?post_type=luma_events&page=ile-settings' ) )
					);
					?>
				</p>
			</div>
		<?php else : ?>
			<p><?php esc_html_e( 'Click the button below to import all events from your configured Luma calendar.', 'import-luma-events' ); ?></p>

			<p>
				<strong><?php esc_html_e( 'Calendar ID:', 'import-luma-events' ); ?></strong>
				<code><?php echo esc_html( $calendar_id ); ?></code>
			</p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="ile-import-form">
				<?php wp_nonce_field( 'ile_import_events', 'ile_import_nonce' ); ?>
				<input type="hidden" name="action" value="ile_import_events">
				<?php submit_button( __( 'Import Events Now', 'import-luma-events' ), 'primary large', 'submit', false ); ?>
			</form>

			<div id="ile-import-status" style="display: none; margin-top: 20px;">
				<div class="ile-loader-container">
					<div class="ile-loader"></div>
					<p class="ile-loading-text">
						<strong><?php esc_html_e( 'Importing events from Luma...', 'import-luma-events' ); ?></strong><br>
						<span class="description"><?php esc_html_e( 'This may take a few moments depending on the number of events.', 'import-luma-events' ); ?></span>
					</p>
				</div>
			</div>
			<?php endif; ?>
			</div>

			<!-- Last Sync Status Card -->
			<div class="card" id="ile-last-sync-card">
				<h2><?php esc_html_e( 'Last Sync Status', 'import-luma-events' ); ?></h2>

		<?php if ( ! empty( $last_sync ) && isset( $last_sync['time'] ) ) : ?>
			<p>
				<strong><?php esc_html_e( 'Last sync:', 'import-luma-events' ); ?></strong>
				<?php
				$last_sync_timestamp = strtotime( $last_sync['time'] );
				$time_ago = human_time_diff( $last_sync_timestamp, current_time( 'timestamp' ) );
				printf(
					/* translators: 1: time ago, 2: absolute time */
					esc_html__( '%1$s ago (%2$s)', 'import-luma-events' ),
					'<strong>' . esc_html( $time_ago ) . '</strong>',
					esc_html( date_i18n( 'M j, Y @ g:i a', $last_sync_timestamp ) )
				);
				?>
			</p>

			<?php if ( 'success' === $last_sync['status'] ) : ?>
				<p>
					<strong><?php esc_html_e( 'Status:', 'import-luma-events' ); ?></strong>
					<span style="color: green;">✓ <?php esc_html_e( 'Success', 'import-luma-events' ); ?></span>
				</p>
				<p>
					<?php
					printf(
						/* translators: %1$d: created, %2$d: updated, %3$d: skipped */
						esc_html__( 'Created: %1$d, Updated: %2$d, Skipped: %3$d', 'import-luma-events' ),
						isset( $last_sync['created'] ) ? intval( $last_sync['created'] ) : 0,
						isset( $last_sync['updated'] ) ? intval( $last_sync['updated'] ) : 0,
						isset( $last_sync['skipped'] ) ? intval( $last_sync['skipped'] ) : 0
					);
					?>
				</p>
			<?php else : ?>
				<p>
					<strong><?php esc_html_e( 'Status:', 'import-luma-events' ); ?></strong>
					<span style="color: red;">✗ <?php esc_html_e( 'Error', 'import-luma-events' ); ?></span>
				</p>
				<?php if ( isset( $last_sync['error'] ) ) : ?>
					<p><code><?php echo esc_html( $last_sync['error'] ); ?></code></p>
				<?php endif; ?>
			<?php endif; ?>
		<?php else : ?>
			<p class="description"><?php esc_html_e( 'No sync has been performed yet.', 'import-luma-events' ); ?></p>
		<?php endif; ?>
			</div>
		</div>

		<!-- Row 2 -->
		<div class="ile-row-2">
			<!-- How It Works Card -->
			<div class="card">
				<h2><?php esc_html_e( 'How It Works', 'import-luma-events' ); ?></h2>
				<ul style="list-style: disc; margin-left: 20px;">
					<li><?php esc_html_e( 'This plugin imports events from your Luma calendar into WordPress as custom posts.', 'import-luma-events' ); ?></li>
					<li><?php esc_html_e( 'Existing events are updated if they already exist (matched by Luma event ID).', 'import-luma-events' ); ?></li>
					<li><?php esc_html_e( 'New events are created automatically.', 'import-luma-events' ); ?></li>
					<li><?php esc_html_e( 'Event images, dates, locations, and all details are synced from Luma.', 'import-luma-events' ); ?></li>
					<li><?php esc_html_e( 'Enable automatic sync to keep your events up-to-date without manual imports.', 'import-luma-events' ); ?></li>
				</ul>
			</div>

			<!-- Automatic Sync Card -->
			<div class="card">
				<h2><?php esc_html_e( 'Automatic Sync', 'import-luma-events' ); ?></h2>

		<?php
		$auto_sync_enabled = isset( $options['enable_auto_sync'] ) && $options['enable_auto_sync'];
		$sync_frequency    = isset( $options['sync_frequency'] ) ? $options['sync_frequency'] : 'hourly';
		?>

		<?php if ( $auto_sync_enabled ) : ?>
			<p>
				<span style="color: green;">✓</span>
				<?php
				// Get the actual schedule from WordPress cron
				$cron_schedules = wp_get_schedules();
				$schedule_label = $sync_frequency;
				if ( isset( $cron_schedules[ $sync_frequency ] ) ) {
					$schedule_label = $sync_frequency . ' (' . $cron_schedules[ $sync_frequency ]['display'] . ')';
				}

				printf(
					/* translators: %s: sync frequency */
					esc_html__( 'Automatic sync is enabled and runs %s.', 'import-luma-events' ),
					'<strong>' . esc_html( $schedule_label ) . '</strong>'
				);
				?>
			</p>

			<?php
			$next_scheduled = wp_next_scheduled( 'ile_sync_events' );
			if ( $next_scheduled ) :
				// wp_next_scheduled() returns UTC timestamp, so we must compare with UTC time()
				$current_time_utc = time();
				$time_diff_seconds = $next_scheduled - $current_time_utc;
				$time_diff_hours = $time_diff_seconds / 3600;

				// Check if the scheduled time is in the past
				if ( $time_diff_seconds < 0 ) {
					$time_until = human_time_diff( $next_scheduled, $current_time_utc ) . ' ago (overdue)';
				} else {
					$time_until = human_time_diff( $current_time_utc, $next_scheduled );
				}
				$timezone_string = get_option( 'timezone_string' );
				if ( empty( $timezone_string ) ) {
					$timezone_string = 'UTC' . ( get_option( 'gmt_offset' ) >= 0 ? '+' : '' ) . get_option( 'gmt_offset' );
				}

				// Check if schedule seems wrong (e.g., hourly but next run is > 2 hours away)
				$schedule_seems_wrong = false;
				if ( $sync_frequency === 'hourly' && $time_diff_hours > 2 ) {
					$schedule_seems_wrong = true;
				} elseif ( $sync_frequency === 'twicedaily' && $time_diff_hours > 13 ) {
					$schedule_seems_wrong = true;
				} elseif ( $sync_frequency === 'daily' && $time_diff_hours > 25 ) {
					$schedule_seems_wrong = true;
				}
				?>

				<p>
					<strong><?php esc_html_e( 'Next scheduled sync:', 'import-luma-events' ); ?></strong>
					<?php
					printf(
						/* translators: 1: relative time, 2: absolute time, 3: timezone */
						esc_html__( 'in %1$s (%2$s %3$s)', 'import-luma-events' ),
						'<strong>' . esc_html( $time_until ) . '</strong>',
						esc_html( date_i18n( 'M j, Y @ g:i a', $next_scheduled ) ),
						'<span class="description">' . esc_html( $timezone_string ) . '</span>'
					);
					?>
					<?php if ( $time_diff_seconds < 0 ) : ?>
						<br>
						<span style="color: red;">⚠ <?php esc_html_e( 'Sync is overdue! WordPress cron requires site visits to run.', 'import-luma-events' ); ?></span>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ile_run_sync_now' ), 'ile_run_sync_now' ) ); ?>" class="button button-primary button-small" style="margin-left: 10px;">
							<?php esc_html_e( 'Run Sync Now', 'import-luma-events' ); ?>
						</a>
					<?php elseif ( $schedule_seems_wrong ) : ?>
						<br>
						<span style="color: orange;">⚠ <?php esc_html_e( 'Schedule may be out of sync.', 'import-luma-events' ); ?></span>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ile_reschedule_cron' ), 'ile_reschedule_cron' ) ); ?>" class="button button-small" style="margin-left: 10px;">
							<?php esc_html_e( 'Fix Schedule', 'import-luma-events' ); ?>
						</a>
					<?php endif; ?>
				</p>

				<!-- Collapsible Debug & Instructions -->
				<details class="ile-details" style="margin-top: 20px;">
					<summary style="cursor: pointer; font-weight: 600; padding: 10px; background: #f6f7f7; border-radius: 4px; user-select: none;">
						<?php esc_html_e( 'Show Debug Info & Advanced Settings', 'import-luma-events' ); ?>
					</summary>
					<div style="margin-top: 15px; padding: 15px; background: #fafafa; border: 1px solid #ddd; border-radius: 4px;">
						<!-- Debug Info -->
						<div style="background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; padding: 12px; font-family: monospace; font-size: 11px; margin-bottom: 15px;">
							<div style="font-weight: bold; margin-bottom: 8px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;">
								<?php esc_html_e( 'Debug Info', 'import-luma-events' ); ?>
							</div>
							<?php
							echo '<strong>Current (UTC):</strong> ' . date( 'H:i:s', time() ) . '<br>';
							echo '<strong>Scheduled (UTC):</strong> ' . date( 'H:i:s', $next_scheduled ) . '<br>';
							echo '<strong>Diff:</strong> ' . number_format( $time_diff_hours, 2 ) . ' hours<br>';
							echo '<strong>Interval:</strong> ';
							if ( isset( $cron_schedules[ $sync_frequency ] ) ) {
								echo ( $cron_schedules[ $sync_frequency ]['interval'] / 3600 ) . ' hours<br>';
							} else {
								echo 'Unknown<br>';
							}

							// Show WordPress cron array
							$cron_array = _get_cron_array();
							if ( $cron_array ) {
								foreach ( $cron_array as $timestamp => $cron_data ) {
									if ( isset( $cron_data['ile_sync_events'] ) ) {
										$schedule_data = reset( $cron_data['ile_sync_events'] );
										echo '<strong>Schedule:</strong> ' . esc_html( $schedule_data['schedule'] ?? 'none' ) . '<br>';
										break;
									}
								}
							}
							?>
						</div>

						<!-- WordPress Cron Instructions -->
						<?php
						$wp_cron_disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
						$site_url = site_url( 'wp-cron.php' );
						?>

						<?php if ( $wp_cron_disabled ) : ?>
							<div class="notice notice-success inline" style="margin: 0;">
								<p>
									<strong>✓ <?php esc_html_e( 'Server Cron Enabled:', 'import-luma-events' ); ?></strong>
									<?php esc_html_e( 'WordPress cron is disabled and you\'re using server cron. Great for reliability!', 'import-luma-events' ); ?>
								</p>
							</div>
						<?php else : ?>
							<div class="notice notice-info inline" style="margin: 0;">
								<p>
									<strong><?php esc_html_e( 'Using WordPress Cron (Recommended: Switch to Server Cron)', 'import-luma-events' ); ?></strong><br>
									<?php esc_html_e( 'WordPress cron relies on site visits. For more reliable scheduling, set up a server cron job:', 'import-luma-events' ); ?>
								</p>

								<ol style="margin-left: 20px; margin-top: 10px;">
									<li>
										<?php esc_html_e( 'Add this to your wp-config.php:', 'import-luma-events' ); ?>
										<pre style="background: #f6f7f7; padding: 10px; margin: 5px 0; overflow-x: auto; font-size: 12px; border-left: 3px solid #2271b1;">define('DISABLE_WP_CRON', true);</pre>
									</li>
									<li>
										<?php esc_html_e( 'Set up a server cron job (via cPanel, Plesk, or SSH):', 'import-luma-events' ); ?>
										<pre style="background: #f6f7f7; padding: 10px; margin: 5px 0; overflow-x: auto; font-size: 12px; border-left: 3px solid #2271b1;">*/15 * * * * curl -s <?php echo esc_html( $site_url ); ?> > /dev/null 2>&1</pre>
										<p class="description" style="margin-top: 5px;">
											<?php esc_html_e( 'This runs every 15 minutes. Adjust the schedule as needed.', 'import-luma-events' ); ?>
										</p>
									</li>
								</ol>

								<p style="margin-top: 10px;">
									<a href="https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/" target="_blank" class="button button-small">
										<?php esc_html_e( 'View WordPress.org Guide', 'import-luma-events' ); ?>
									</a>
								</p>
							</div>
						<?php endif; ?>
					</div>
				</details>
			<?php else : ?>
				<p>
					<span style="color: orange;">⚠</span>
					<?php esc_html_e( 'Automatic sync is enabled but no cron job is scheduled.', 'import-luma-events' ); ?>
					<br>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=luma_events&page=ile-settings' ) ); ?>" class="button button-small" style="margin-top: 10px;">
						<?php esc_html_e( 'Re-configure Settings', 'import-luma-events' ); ?>
					</a>
				</p>
			<?php endif; ?>
		<?php else : ?>
			<p>
				<span style="color: gray;">○</span>
				<?php esc_html_e( 'Automatic sync is currently disabled.', 'import-luma-events' ); ?>
			</p>
		<?php endif; ?>

		<p>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=luma_events&page=ile-settings' ) ); ?>" class="button">
				<?php esc_html_e( 'Configure Sync Settings', 'import-luma-events' ); ?>
			</a>
		</p>
			</div>
		</div>
		<!-- End Row 2 -->
	</div>
	<!-- End Grid Container -->
</div>
<!-- End Wrap -->

<style>
	.ile-loader-container {
		display: flex;
		align-items: center;
		gap: 20px;
		padding: 20px;
		background: #f0f6fc;
		border-left: 4px solid #2271b1;
	}

	.ile-loader {
		border: 4px solid #f3f3f3;
		border-top: 4px solid #2271b1;
		border-radius: 50%;
		width: 40px;
		height: 40px;
		animation: ile-spin 1s linear infinite;
		flex-shrink: 0;
	}

	@keyframes ile-spin {
		0% { transform: rotate(0deg); }
		100% { transform: rotate(360deg); }
	}

	.ile-loading-text {
		margin: 0;
	}

	.ile-loading-text strong {
		font-size: 16px;
		color: #1d2327;
	}

	#ile-import-form .button-large {
		padding: 8px 24px;
		height: auto;
		font-size: 16px;
	}

	/* Grid Layout */
	.ile-grid-container {
		display: flex;
		flex-direction: column;
		gap: 20px;
		margin-top: 20px;
	}

	.ile-row-1,
	.ile-row-2 {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 20px;
	}

	.ile-import-page .card {
		margin: 0 !important;
	}

	/* Details/Summary styling */
	.ile-details summary:hover {
		background: #e9eaeb;
	}

	.ile-details[open] summary {
		background: #e9eaeb;
		border-bottom-left-radius: 0;
		border-bottom-right-radius: 0;
	}

	/* Responsive */
	@media (max-width: 1280px) {
		.ile-row-1,
		.ile-row-2 {
			grid-template-columns: 1fr;
		}
	}
</style>

<script>
	jQuery(document).ready(function($) {
		$('#ile-import-form').on('submit', function() {
			// Show loading indicator
			$('#ile-import-status').slideDown();

			// Disable submit button to prevent double-clicks
			$(this).find('input[type="submit"]').prop('disabled', true).val('<?php echo esc_js( __( 'Importing...', 'import-luma-events' ) ); ?>');

			// Scroll to loading indicator
			$('html, body').animate({
				scrollTop: $('#ile-import-status').offset().top - 100
			}, 500);
		});
	});
</script>
