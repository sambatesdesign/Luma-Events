<?php
/**
 * Documentation page template.
 *
 * @package Import_Luma_Events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap ile-documentation">
	<h1><?php esc_html_e( 'Import Luma Events - Documentation', 'import-luma-events' ); ?></h1>

	<div class="ile-docs-container">
		<!-- Getting Started -->
		<div class="card ile-docs-section">
			<h2><?php esc_html_e( '🚀 Getting Started', 'import-luma-events' ); ?></h2>

			<h3><?php esc_html_e( 'Quick Start Guide', 'import-luma-events' ); ?></h3>
			<ol class="ile-docs-list">
				<li>
					<strong><?php esc_html_e( 'Get Your Luma API Credentials', 'import-luma-events' ); ?></strong>
					<p><?php esc_html_e( 'Log in to your Luma account, go to Settings → Developer, and copy your API key and Calendar ID.', 'import-luma-events' ); ?></p>
				</li>
				<li>
					<strong><?php esc_html_e( 'Configure the Plugin', 'import-luma-events' ); ?></strong>
					<p>
						<?php
						printf(
							/* translators: %s: settings page link */
							__( 'Go to <a href="%s">Luma Events → Settings</a> and enter your API credentials.', 'import-luma-events' ),
							esc_url( admin_url( 'edit.php?post_type=luma_events&page=ile-settings' ) )
						);
						?>
					</p>
				</li>
				<li>
					<strong><?php esc_html_e( 'Import Your Events', 'import-luma-events' ); ?></strong>
					<p>
						<?php
						printf(
							/* translators: %s: import page link */
							__( 'Navigate to <a href="%s">Luma Events → Import Events</a> and click "Import Events Now".', 'import-luma-events' ),
							esc_url( admin_url( 'edit.php?post_type=luma_events&page=ile-import' ) )
						);
						?>
					</p>
				</li>
				<li>
					<strong><?php esc_html_e( 'Display Events on Your Site', 'import-luma-events' ); ?></strong>
					<p><?php esc_html_e( 'Use the shortcode [luma_events] or visit your Events page to see imported events.', 'import-luma-events' ); ?></p>
				</li>
			</ol>
		</div>

		<!-- Shortcodes -->
		<div class="card ile-docs-section">
			<h2><?php esc_html_e( '📝 Shortcodes', 'import-luma-events' ); ?></h2>

			<h3><?php esc_html_e( 'Basic Usage', 'import-luma-events' ); ?></h3>
			<pre class="ile-code">[luma_events]</pre>
			<p><?php esc_html_e( 'Display a list of events with default settings.', 'import-luma-events' ); ?></p>

			<h3><?php esc_html_e( 'Available Parameters', 'import-luma-events' ); ?></h3>

			<h4><code>posts_per_page</code></h4>
			<p><?php esc_html_e( 'Number of events to display per page. Default: 10', 'import-luma-events' ); ?></p>
			<pre class="ile-code">[luma_events posts_per_page="6"]</pre>

			<h4><code>columns</code></h4>
			<p><?php esc_html_e( 'Number of columns in the grid layout. Default: 3', 'import-luma-events' ); ?></p>
			<pre class="ile-code">[luma_events columns="4"]</pre>

			<h4><code>show_past</code></h4>
			<p><?php esc_html_e( 'Show past events. Default: false', 'import-luma-events' ); ?></p>
			<pre class="ile-code">[luma_events show_past="true"]</pre>

			<h4><code>order</code></h4>
			<p><?php esc_html_e( 'Sort order: ASC or DESC. Default: ASC', 'import-luma-events' ); ?></p>
			<pre class="ile-code">[luma_events order="DESC"]</pre>

			<h3><?php esc_html_e( 'Example Combinations', 'import-luma-events' ); ?></h3>
			<pre class="ile-code">[luma_events posts_per_page="6" columns="3"]</pre>
			<pre class="ile-code">[luma_events posts_per_page="12" columns="4" order="DESC"]</pre>
		</div>

		<!-- Templates -->
		<div class="card ile-docs-section">
			<h2><?php esc_html_e( '🎨 Template Customization', 'import-luma-events' ); ?></h2>

			<h3><?php esc_html_e( 'Override Plugin Templates', 'import-luma-events' ); ?></h3>
			<p><?php esc_html_e( 'You can customize the event templates by copying them to your theme:', 'import-luma-events' ); ?></p>

			<h4><?php esc_html_e( 'Single Event Template', 'import-luma-events' ); ?></h4>
			<p><strong><?php esc_html_e( 'From:', 'import-luma-events' ); ?></strong> <code>plugins/import-luma-events/templates/single-luma_events.php</code></p>
			<p><strong><?php esc_html_e( 'To:', 'import-luma-events' ); ?></strong> <code>yourtheme/luma-events/single-luma_events.php</code></p>

			<h4><?php esc_html_e( 'Events Archive Template', 'import-luma-events' ); ?></h4>
			<p><strong><?php esc_html_e( 'From:', 'import-luma-events' ); ?></strong> <code>plugins/import-luma-events/templates/archive-luma_events.php</code></p>
			<p><strong><?php esc_html_e( 'To:', 'import-luma-events' ); ?></strong> <code>yourtheme/luma-events/archive-luma_events.php</code></p>

			<h4><?php esc_html_e( 'Styles', 'import-luma-events' ); ?></h4>
			<p><strong><?php esc_html_e( 'From:', 'import-luma-events' ); ?></strong> <code>plugins/import-luma-events/public/css/events.css</code></p>
			<p><strong><?php esc_html_e( 'To:', 'import-luma-events' ); ?></strong> <code>yourtheme/luma-events/events.css</code></p>

			<h3><?php esc_html_e( 'Available Event Data', 'import-luma-events' ); ?></h3>
			<p><?php esc_html_e( 'When customizing templates, you can access the following event meta fields:', 'import-luma-events' ); ?></p>

			<ul class="ile-docs-list">
				<li><code>luma_event_id</code> - <?php esc_html_e( 'Unique Luma event ID', 'import-luma-events' ); ?></li>
				<li><code>luma_event_url</code> - <?php esc_html_e( 'Link to register on Luma', 'import-luma-events' ); ?></li>
				<li><code>event_start_date</code> - <?php esc_html_e( 'Event start date (Y-m-d format)', 'import-luma-events' ); ?></li>
				<li><code>event_end_date</code> - <?php esc_html_e( 'Event end date (Y-m-d format)', 'import-luma-events' ); ?></li>
				<li><code>start_ts</code> - <?php esc_html_e( 'Start timestamp', 'import-luma-events' ); ?></li>
				<li><code>end_ts</code> - <?php esc_html_e( 'End timestamp', 'import-luma-events' ); ?></li>
				<li><code>event_timezone</code> - <?php esc_html_e( 'Event timezone', 'import-luma-events' ); ?></li>
				<li><code>venue_name</code> - <?php esc_html_e( 'Venue name', 'import-luma-events' ); ?></li>
				<li><code>venue_address</code> - <?php esc_html_e( 'Street address', 'import-luma-events' ); ?></li>
				<li><code>venue_city</code>, <code>venue_state</code>, <code>venue_country</code> - <?php esc_html_e( 'Location details', 'import-luma-events' ); ?></li>
				<li><code>venue_lat</code>, <code>venue_lon</code> - <?php esc_html_e( 'GPS coordinates', 'import-luma-events' ); ?></li>
				<li><code>meeting_url</code> - <?php esc_html_e( 'Virtual meeting URL', 'import-luma-events' ); ?></li>
				<li><code>zoom_meeting_url</code> - <?php esc_html_e( 'Zoom meeting URL', 'import-luma-events' ); ?></li>
				<li><code>organizer_name</code> - <?php esc_html_e( 'Event organizer name', 'import-luma-events' ); ?></li>
				<li><code>cover_url</code> - <?php esc_html_e( 'Event cover image URL', 'import-luma-events' ); ?></li>
			</ul>

			<h3><?php esc_html_e( 'Example Usage in Templates', 'import-luma-events' ); ?></h3>
			<pre class="ile-code">$event_id = get_the_ID();
$start_date = get_post_meta( $event_id, 'event_start_date', true );
$venue_name = get_post_meta( $event_id, 'venue_name', true );
$luma_url = get_post_meta( $event_id, 'luma_event_url', true );</pre>
		</div>

		<!-- Automatic Sync -->
		<div class="card ile-docs-section">
			<h2><?php esc_html_e( '🔄 Automatic Sync', 'import-luma-events' ); ?></h2>

			<p><?php esc_html_e( 'Keep your events automatically updated by enabling automatic sync in the plugin settings.', 'import-luma-events' ); ?></p>

			<h3><?php esc_html_e( 'How It Works', 'import-luma-events' ); ?></h3>
			<ul class="ile-docs-list">
				<li><?php esc_html_e( 'Events are automatically imported from Luma on a schedule you choose (hourly, twice daily, or daily)', 'import-luma-events' ); ?></li>
				<li><?php esc_html_e( 'New events are created automatically', 'import-luma-events' ); ?></li>
				<li><?php esc_html_e( 'Existing events are updated with the latest information', 'import-luma-events' ); ?></li>
				<li><?php esc_html_e( 'Event images are synced from Luma', 'import-luma-events' ); ?></li>
			</ul>

			<h3><?php esc_html_e( 'Enable Automatic Sync', 'import-luma-events' ); ?></h3>
			<ol class="ile-docs-list">
				<li>
					<?php
					printf(
						/* translators: %s: settings page link */
						__( 'Go to <a href="%s">Luma Events → Settings</a>', 'import-luma-events' ),
						esc_url( admin_url( 'edit.php?post_type=luma_events&page=ile-settings' ) )
					);
					?>
				</li>
				<li><?php esc_html_e( 'Check "Enable Automatic Sync"', 'import-luma-events' ); ?></li>
				<li><?php esc_html_e( 'Choose your preferred sync frequency', 'import-luma-events' ); ?></li>
				<li><?php esc_html_e( 'Click "Save Settings"', 'import-luma-events' ); ?></li>
			</ol>
		</div>

		<!-- Troubleshooting -->
		<div class="card ile-docs-section">
			<h2><?php esc_html_e( '🔧 Troubleshooting', 'import-luma-events' ); ?></h2>

			<h3><?php esc_html_e( 'Events Not Importing', 'import-luma-events' ); ?></h3>
			<ul class="ile-docs-list">
				<li><?php esc_html_e( 'Verify your API key and Calendar ID are correct in Settings', 'import-luma-events' ); ?></li>
				<li><?php esc_html_e( 'Check that your Luma calendar actually has events', 'import-luma-events' ); ?></li>
				<li><?php esc_html_e( 'Look for error messages on the Import page', 'import-luma-events' ); ?></li>
			</ul>

			<h3><?php esc_html_e( 'Missing Event Images', 'import-luma-events' ); ?></h3>
			<p>
				<?php
				printf(
					/* translators: %s: fix images page link */
					__( 'If some events are missing images, use the <a href="%s">Fix Missing Images</a> tool to re-download them.', 'import-luma-events' ),
					esc_url( admin_url( 'edit.php?post_type=luma_events&page=ile-fix-images' ) )
				);
				?>
			</p>

			<h3><?php esc_html_e( 'Import Times Out', 'import-luma-events' ); ?></h3>
			<ul class="ile-docs-list">
				<li><?php esc_html_e( 'The plugin automatically increases PHP timeout limits, but your server may have restrictions', 'import-luma-events' ); ?></li>
				<li><?php esc_html_e( 'Contact your hosting provider to increase max_execution_time if imports consistently fail', 'import-luma-events' ); ?></li>
			</ul>

			<h3><?php esc_html_e( 'Templates Not Showing Correctly', 'import-luma-events' ); ?></h3>
			<ul class="ile-docs-list">
				<li><?php esc_html_e( 'Make sure your theme is not overriding the templates in an unexpected way', 'import-luma-events' ); ?></li>
				<li><?php esc_html_e( 'Check your browser console for CSS/JavaScript errors', 'import-luma-events' ); ?></li>
				<li><?php esc_html_e( 'Try deactivating other plugins to check for conflicts', 'import-luma-events' ); ?></li>
			</ul>
		</div>

		<!-- Support -->
		<div class="card ile-docs-section">
			<h2><?php esc_html_e( '💬 Support & Contributing', 'import-luma-events' ); ?></h2>

			<h3><?php esc_html_e( 'Get Help', 'import-luma-events' ); ?></h3>
			<p>
				<?php
				printf(
					/* translators: %s: GitHub issues URL */
					__( 'Need help? Found a bug? Have a feature request? Please <a href="%s" target="_blank">open an issue on GitHub</a>.', 'import-luma-events' ),
					esc_url( 'https://github.com/sambatesdesign/Luma-Events/issues' )
				);
				?>
			</p>

			<h3><?php esc_html_e( 'Contributing', 'import-luma-events' ); ?></h3>
			<p><?php esc_html_e( 'This is an open-source plugin! Contributions are welcome:', 'import-luma-events' ); ?></p>
			<ul class="ile-docs-list">
				<li>
					<?php
					printf(
						/* translators: %s: GitHub repo URL */
						__( 'Fork the repository on <a href="%s" target="_blank">GitHub</a>', 'import-luma-events' ),
						esc_url( 'https://github.com/sambatesdesign/Luma-Events' )
					);
					?>
				</li>
				<li><?php esc_html_e( 'Make your changes', 'import-luma-events' ); ?></li>
				<li><?php esc_html_e( 'Submit a pull request', 'import-luma-events' ); ?></li>
			</ul>

			<h3><?php esc_html_e( 'Credits', 'import-luma-events' ); ?></h3>
			<p>
				<?php
				printf(
					/* translators: %s: MazeSpace Studios URL */
					__( 'Developed by <a href="%s" target="_blank">MazeSpace Studios LTD</a>', 'import-luma-events' ),
					esc_url( 'https://mazespacestudios.com' )
				);
				?>
			</p>
		</div>
	</div>
</div>

<style>
	.ile-documentation .ile-docs-container {
		margin-top: 20px;
	}

	.ile-docs-section {
		margin-bottom: 20px;
		max-width: none !important;
	}

	.ile-documentation .card {
		max-width: none !important;
	}

	.ile-docs-section h2 {
		margin-top: 0;
		border-bottom: 2px solid #2271b1;
		padding-bottom: 10px;
	}

	.ile-docs-section h3 {
		margin-top: 25px;
		color: #1d2327;
	}

	.ile-docs-section h4 {
		margin-top: 20px;
		margin-bottom: 5px;
	}

	.ile-docs-list {
		margin-left: 20px;
		line-height: 1.8;
	}

	.ile-docs-list li {
		margin-bottom: 10px;
	}

	.ile-code {
		background: #f6f7f7;
		border: 1px solid #dcdcde;
		border-left: 4px solid #2271b1;
		padding: 15px;
		border-radius: 3px;
		overflow-x: auto;
		font-family: Consolas, Monaco, monospace;
		font-size: 13px;
		margin: 10px 0 20px;
		display: block;
	}

	.ile-docs-section code {
		background: #f6f7f7;
		padding: 2px 6px;
		border-radius: 3px;
		font-family: Consolas, Monaco, monospace;
		font-size: 13px;
	}

	.ile-docs-section pre code {
		background: none;
		padding: 0;
	}
</style>
