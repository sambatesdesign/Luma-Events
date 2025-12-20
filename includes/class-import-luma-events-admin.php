<?php
/**
 * Admin functionality - settings page and import interface.
 *
 * @package Import_Luma_Events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import_Luma_Events_Admin Class.
 */
class Import_Luma_Events_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'activation_redirect' ) );
		add_action( 'admin_post_ile_import_events', array( $this, 'handle_import' ) );
		add_action( 'admin_post_ile_complete_setup', array( $this, 'handle_setup_completion' ) );
		add_action( 'admin_post_ile_reschedule_cron', array( $this, 'handle_reschedule_cron' ) );
		add_action( 'admin_post_ile_run_sync_now', array( $this, 'handle_run_sync_now' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_ile_test_connection', array( $this, 'ajax_test_connection' ) );
		add_filter( 'plugin_row_meta', array( $this, 'add_plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Add links to plugin row meta (under plugin name in Plugins list).
	 *
	 * @param array  $links Existing links.
	 * @param string $file  Plugin file.
	 * @return array Modified links.
	 */
	public function add_plugin_row_meta( $links, $file ) {
		if ( plugin_basename( IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'import-luma-events.php' ) === $file ) {
			$new_links = array(
				'docs'    => '<a href="' . admin_url( 'edit.php?post_type=luma_events&page=ile-documentation' ) . '">' . __( 'Documentation', 'import-luma-events' ) . '</a>',
				'support' => '<a href="https://github.com/sambatesdesign/Luma-Events/issues" target="_blank">' . __( 'Support', 'import-luma-events' ) . '</a>',
			);
			$links = array_merge( $links, $new_links );
		}
		return $links;
	}

	/**
	 * Add admin menu pages.
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=luma_events',
			__( 'Import Events', 'import-luma-events' ),
			__( 'Import Events', 'import-luma-events' ),
			'manage_options',
			'ile-import',
			array( $this, 'render_import_page' )
		);

		add_submenu_page(
			'edit.php?post_type=luma_events',
			__( 'Fix Missing Images', 'import-luma-events' ),
			__( 'Fix Missing Images', 'import-luma-events' ),
			'manage_options',
			'ile-fix-images',
			array( $this, 'render_fix_images_page' )
		);

		add_submenu_page(
			'edit.php?post_type=luma_events',
			__( 'Settings', 'import-luma-events' ),
			__( 'Settings', 'import-luma-events' ),
			'manage_options',
			'ile-settings',
			array( $this, 'render_settings_page' )
		);

		add_submenu_page(
			'edit.php?post_type=luma_events',
			__( 'Documentation', 'import-luma-events' ),
			__( 'Documentation', 'import-luma-events' ),
			'manage_options',
			'ile-documentation',
			array( $this, 'render_documentation_page' )
		);

		// Add setup wizard page (hidden from menu).
		add_submenu_page(
			null,
			__( 'Setup Wizard', 'import-luma-events' ),
			__( 'Setup Wizard', 'import-luma-events' ),
			'manage_options',
			'ile-setup-wizard',
			array( $this, 'render_setup_wizard_page' )
		);
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		register_setting(
			'ile_luma_options',
			'ile_luma_options',
			array( $this, 'sanitize_settings' )
		);

		// API Settings Section.
		add_settings_section(
			'ile_api_section',
			__( 'Luma API Settings', 'import-luma-events' ),
			array( $this, 'render_api_section' ),
			'ile-settings'
		);

		add_settings_field(
			'luma_api_key',
			__( 'Luma API Key', 'import-luma-events' ),
			array( $this, 'render_api_key_field' ),
			'ile-settings',
			'ile_api_section'
		);

		add_settings_field(
			'luma_calendar_id',
			__( 'Calendar ID', 'import-luma-events' ),
			array( $this, 'render_calendar_id_field' ),
			'ile-settings',
			'ile_api_section'
		);

		// Sync Settings Section.
		add_settings_section(
			'ile_sync_section',
			__( 'Automatic Sync Settings', 'import-luma-events' ),
			array( $this, 'render_sync_section' ),
			'ile-settings'
		);

		add_settings_field(
			'enable_auto_sync',
			__( 'Enable Automatic Sync', 'import-luma-events' ),
			array( $this, 'render_auto_sync_field' ),
			'ile-settings',
			'ile_sync_section'
		);

		add_settings_field(
			'sync_frequency',
			__( 'Sync Frequency', 'import-luma-events' ),
			array( $this, 'render_sync_frequency_field' ),
			'ile-settings',
			'ile_sync_section'
		);
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $input Raw input.
	 * @return array Sanitized input.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		if ( isset( $input['luma_api_key'] ) ) {
			$sanitized['luma_api_key'] = sanitize_text_field( $input['luma_api_key'] );
		}

		if ( isset( $input['luma_calendar_id'] ) ) {
			$sanitized['luma_calendar_id'] = sanitize_text_field( $input['luma_calendar_id'] );
		}

		if ( isset( $input['enable_auto_sync'] ) ) {
			$sanitized['enable_auto_sync'] = (bool) $input['enable_auto_sync'];
		}

		if ( isset( $input['sync_frequency'] ) ) {
			$sanitized['sync_frequency'] = sanitize_text_field( $input['sync_frequency'] );
		}

		return $sanitized;
	}

	/**
	 * Render API settings section.
	 */
	public function render_api_section() {
		echo '<p>' . esc_html__( 'Enter your Luma API credentials. You can generate an API key from your Luma account settings.', 'import-luma-events' ) . '</p>';
	}

	/**
	 * Render API key field.
	 */
	public function render_api_key_field() {
		$options = get_option( 'ile_luma_options' );
		$value   = isset( $options['luma_api_key'] ) ? $options['luma_api_key'] : '';
		?>
		<input type="text"
			   name="ile_luma_options[luma_api_key]"
			   value="<?php echo esc_attr( $value ); ?>"
			   class="regular-text"
			   placeholder="secret-xxxxxxxxxxxxx">
		<p class="description">
			<?php esc_html_e( 'Your Luma API key (starts with "secret-"). Requires a Luma Plus subscription.', 'import-luma-events' ); ?>
		</p>
		<?php
	}

	/**
	 * Render calendar ID field.
	 */
	public function render_calendar_id_field() {
		$options = get_option( 'ile_luma_options' );
		$value   = isset( $options['luma_calendar_id'] ) ? $options['luma_calendar_id'] : '';
		?>
		<input type="text"
			   name="ile_luma_options[luma_calendar_id]"
			   value="<?php echo esc_attr( $value ); ?>"
			   class="regular-text"
			   placeholder="cal-xxxxxxxxxxxxx">
		<p class="description">
			<?php esc_html_e( 'Your Luma calendar ID (starts with "cal-"). This calendar will be synced automatically.', 'import-luma-events' ); ?>
		</p>
		<?php
	}

	/**
	 * Render sync settings section.
	 */
	public function render_sync_section() {
		echo '<p>' . esc_html__( 'Configure automatic syncing of events from Luma.', 'import-luma-events' ) . '</p>';
	}

	/**
	 * Render auto sync field.
	 */
	public function render_auto_sync_field() {
		$options = get_option( 'ile_luma_options' );
		$checked = isset( $options['enable_auto_sync'] ) && $options['enable_auto_sync'];
		?>
		<label>
			<input type="checkbox"
				   name="ile_luma_options[enable_auto_sync]"
				   value="1"
				   <?php checked( $checked ); ?>>
			<?php esc_html_e( 'Automatically sync events from Luma on a schedule', 'import-luma-events' ); ?>
		</label>
		<?php
	}

	/**
	 * Render sync frequency field.
	 */
	public function render_sync_frequency_field() {
		$options   = get_option( 'ile_luma_options' );
		$frequency = isset( $options['sync_frequency'] ) ? $options['sync_frequency'] : 'hourly';
		?>
		<select name="ile_luma_options[sync_frequency]">
			<option value="hourly" <?php selected( $frequency, 'hourly' ); ?>><?php esc_html_e( 'Every Hour', 'import-luma-events' ); ?></option>
			<option value="twicedaily" <?php selected( $frequency, 'twicedaily' ); ?>><?php esc_html_e( 'Twice Daily', 'import-luma-events' ); ?></option>
			<option value="daily" <?php selected( $frequency, 'daily' ); ?>><?php esc_html_e( 'Daily', 'import-luma-events' ); ?></option>
		</select>
		<p class="description">
			<?php esc_html_e( 'How often to check for new or updated events.', 'import-luma-events' ); ?>
		</p>
		<?php
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Import Luma Events - Settings', 'import-luma-events' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'ile_luma_options' );
				do_settings_sections( 'ile-settings' );
				submit_button();
				?>
			</form>

			<hr>

			<h2><?php esc_html_e( 'Test API Connection', 'import-luma-events' ); ?></h2>
			<p><?php esc_html_e( 'Click the button below to test your API connection.', 'import-luma-events' ); ?></p>
			<button type="button" id="ile-test-connection" class="button">
				<?php esc_html_e( 'Test Connection', 'import-luma-events' ); ?>
			</button>
			<div id="ile-test-result" style="margin-top: 10px;"></div>
		</div>
		<?php
	}

	/**
	 * Render import page.
	 */
	public function render_import_page() {
		require_once IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'admin/partials/import-page.php';
	}

	/**
	 * Render fix images page.
	 */
	public function render_fix_images_page() {
		require_once IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'admin/partials/fix-images.php';
	}

	/**
	 * Handle import form submission.
	 */
	public function handle_import() {
		// Increase time limit for import.
		set_time_limit( 300 );

		check_admin_referer( 'ile_import_events', 'ile_import_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'import-luma-events' ) );
		}

		$options    = get_option( 'ile_luma_options' );
		$calendar_id = isset( $options['luma_calendar_id'] ) ? $options['luma_calendar_id'] : '';

		if ( empty( $calendar_id ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'ile-import', 'error' => 'no_calendar' ), admin_url( 'edit.php?post_type=luma_events' ) ) );
			exit;
		}

		$import_manager = new Import_Luma_Events_Import_Manager();
		$import_options = array(
			'post_status'     => 'publish',
			'update_existing' => true,
		);

		$result = $import_manager->import_calendar_events( $calendar_id, $import_options );

		if ( $result['success'] ) {
			$redirect_args = array(
				'page'    => 'ile-import',
				'imported' => 'success',
				'created' => $result['created'],
				'updated' => $result['updated'],
				'skipped' => $result['skipped'],
			);
		} else {
			$redirect_args = array(
				'page'  => 'ile-import',
				'error' => 'import_failed',
			);
		}

		// Flush output buffer to prevent redirect issues.
		if ( ob_get_length() ) {
			ob_end_clean();
		}

		wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'edit.php?post_type=luma_events' ) ) );
		exit;
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		// Enqueue CSS for all luma_events admin pages.
		$screen = get_current_screen();
		if ( $screen && 'luma_events' === $screen->post_type ) {
			wp_enqueue_style(
				'ile-admin',
				IMPORT_LUMA_EVENTS_PLUGIN_URL . 'admin/css/admin.css',
				array(),
				IMPORT_LUMA_EVENTS_VERSION
			);
		}

		// Enqueue JS only for settings and import pages.
		if ( ! in_array( $hook, array( 'luma_events_page_ile-settings', 'luma_events_page_ile-import' ), true ) ) {
			return;
		}

		wp_enqueue_script(
			'ile-admin',
			IMPORT_LUMA_EVENTS_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			IMPORT_LUMA_EVENTS_VERSION,
			true
		);

		wp_localize_script(
			'ile-admin',
			'ileAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ile_admin' ),
			)
		);
	}

	/**
	 * AJAX handler for testing API connection.
	 */
	public function ajax_test_connection() {
		check_ajax_referer( 'ile_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'import-luma-events' ) ) );
		}

		$luma_api = new Import_Luma_Events_Luma_API();
		$result   = $luma_api->test_connection();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'API connection successful!', 'import-luma-events' ) ) );
	}

	/**
	 * Redirect to setup wizard on first activation.
	 */
	public function activation_redirect() {
		// Only redirect on activation, and only once.
		if ( get_option( 'ile_activation_redirect', false ) ) {
			delete_option( 'ile_activation_redirect' );

			// Don't redirect if activating multiple plugins at once.
			if ( ! isset( $_GET['activate-multi'] ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=ile-setup-wizard' ) );
				exit;
			}
		}
	}

	/**
	 * Render setup wizard page.
	 */
	public function render_setup_wizard_page() {
		$options = get_option( 'ile_luma_options', array() );
		$step    = isset( $_GET['step'] ) ? intval( $_GET['step'] ) : 1;
		?>
		<div class="wrap ile-setup-wizard">
			<h1><?php esc_html_e( 'Welcome to Import Luma Events', 'import-luma-events' ); ?></h1>

			<div class="ile-wizard-content">
				<?php if ( 1 === $step ) : ?>
					<!-- Step 1: Welcome -->
					<div class="ile-wizard-step">
						<h2><?php esc_html_e( 'Let\'s Get Started!', 'import-luma-events' ); ?></h2>
						<p class="description">
							<?php esc_html_e( 'This wizard will help you set up your Luma calendar integration and import your first events.', 'import-luma-events' ); ?>
						</p>

						<div class="ile-wizard-features">
							<h3><?php esc_html_e( 'What you\'ll get:', 'import-luma-events' ); ?></h3>
							<ul>
								<li>✅ <?php esc_html_e( 'Automatic calendar sync from Luma', 'import-luma-events' ); ?></li>
								<li>✅ <?php esc_html_e( 'Beautiful event pages on your website', 'import-luma-events' ); ?></li>
								<li>✅ <?php esc_html_e( 'Shortcodes to display events anywhere', 'import-luma-events' ); ?></li>
								<li>✅ <?php esc_html_e( 'Automatic updates when events change', 'import-luma-events' ); ?></li>
							</ul>
						</div>

						<div class="ile-wizard-pages">
							<h3><?php esc_html_e( 'Pages Created:', 'import-luma-events' ); ?></h3>
							<p>
								<?php
								$events_page_id = get_option( 'ile_events_page_id' );
								if ( $events_page_id ) {
									$events_page = get_post( $events_page_id );
									if ( $events_page ) {
										echo '✅ ';
										printf(
											/* translators: %s: URL to events page */
											esc_html__( 'Events page created at %s', 'import-luma-events' ),
											'<a href="' . esc_url( get_permalink( $events_page_id ) ) . '" target="_blank">' . esc_html( get_permalink( $events_page_id ) ) . '</a>'
										);
									}
								}
								?>
							</p>
						</div>

						<p class="ile-wizard-actions">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=ile-setup-wizard&step=2' ) ); ?>" class="button button-primary button-hero">
								<?php esc_html_e( 'Get Started →', 'import-luma-events' ); ?>
							</a>
						</p>
					</div>

				<?php elseif ( 2 === $step ) : ?>
					<!-- Step 2: API Configuration -->
					<div class="ile-wizard-step">
						<h2><?php esc_html_e( 'Connect Your Luma Calendar', 'import-luma-events' ); ?></h2>
						<p class="description">
							<?php esc_html_e( 'Enter your Luma API credentials to sync your events.', 'import-luma-events' ); ?>
						</p>

						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="ile-setup-form">
							<input type="hidden" name="action" value="ile_complete_setup">
							<?php wp_nonce_field( 'ile_complete_setup', 'ile_setup_nonce' ); ?>

							<table class="form-table">
								<tr>
									<th scope="row">
										<label for="ile_api_key"><?php esc_html_e( 'Luma API Key', 'import-luma-events' ); ?></label>
									</th>
									<td>
										<input type="password" id="ile_api_key" name="ile_luma_options[api_key]" value="<?php echo esc_attr( isset( $options['api_key'] ) ? $options['api_key'] : '' ); ?>" class="regular-text" required>
										<p class="description">
											<?php
											printf(
												/* translators: %s: URL to Luma settings */
												esc_html__( 'Get your API key from %s (requires Luma Plus)', 'import-luma-events' ),
												'<a href="https://lu.ma/settings" target="_blank">Luma Settings</a>'
											);
											?>
										</p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="ile_calendar_id"><?php esc_html_e( 'Calendar ID', 'import-luma-events' ); ?></label>
									</th>
									<td>
										<input type="text" id="ile_calendar_id" name="ile_luma_options[calendar_id]" value="<?php echo esc_attr( isset( $options['calendar_id'] ) ? $options['calendar_id'] : '' ); ?>" class="regular-text" required>
										<p class="description">
											<?php esc_html_e( 'Find this in your Luma calendar URL (e.g., cal-XXXXXXXXXXXXX)', 'import-luma-events' ); ?>
										</p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="ile_enable_sync"><?php esc_html_e( 'Enable Automatic Sync', 'import-luma-events' ); ?></label>
									</th>
									<td>
										<label>
											<input type="checkbox" id="ile_enable_sync" name="ile_luma_options[enable_sync]" value="1" <?php checked( isset( $options['enable_sync'] ) ? $options['enable_sync'] : 1, 1 ); ?>>
											<?php esc_html_e( 'Automatically sync events daily', 'import-luma-events' ); ?>
										</label>
									</td>
								</tr>
							</table>

							<p class="ile-wizard-actions">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=ile-setup-wizard&step=1' ) ); ?>" class="button button-secondary">
									<?php esc_html_e( '← Back', 'import-luma-events' ); ?>
								</a>
								<button type="submit" class="button button-primary button-hero" id="ile-setup-submit">
									<?php esc_html_e( 'Save & Import Events →', 'import-luma-events' ); ?>
								</button>
							</p>

							<div id="ile-setup-loading" style="display: none; margin-top: 20px;">
								<div class="ile-loader-container">
									<div class="ile-loader"></div>
									<p class="ile-loading-text">
										<strong><?php esc_html_e( 'Setting up and importing your events...', 'import-luma-events' ); ?></strong><br>
										<span class="description"><?php esc_html_e( 'This may take a few moments. Please don\'t close this window.', 'import-luma-events' ); ?></span>
									</p>
								</div>
							</div>
						</form>
					</div>
				<?php endif; ?>
			</div>

			<style>
				.ile-setup-wizard {
					max-width: 800px;
				}
				.ile-wizard-content {
					background: #fff;
					border: 1px solid #ccd0d4;
					padding: 40px;
					margin-top: 20px;
				}
				.ile-wizard-step h2 {
					margin-top: 0;
					font-size: 24px;
				}
				.ile-wizard-features,
				.ile-wizard-pages {
					background: #f0f6fc;
					border-left: 4px solid #2271b1;
					padding: 20px;
					margin: 30px 0;
				}
				.ile-wizard-features h3,
				.ile-wizard-pages h3 {
					margin-top: 0;
				}
				.ile-wizard-features ul {
					list-style: none;
					padding-left: 0;
					font-size: 16px;
					line-height: 2;
				}
				.ile-wizard-actions {
					margin-top: 30px;
					padding-top: 30px;
					border-top: 1px solid #ccd0d4;
				}
				.ile-wizard-actions .button-hero {
					padding: 12px 24px;
					height: auto;
					font-size: 16px;
				}
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
			</style>

			<script>
				jQuery(document).ready(function($) {
					$('#ile-setup-form').on('submit', function() {
						// Show loading indicator
						$('#ile-setup-loading').slideDown();

						// Disable submit button
						$('#ile-setup-submit').prop('disabled', true).text('<?php echo esc_js( __( 'Importing...', 'import-luma-events' ) ); ?>');

						// Scroll to loading indicator
						$('html, body').animate({
							scrollTop: $('#ile-setup-loading').offset().top - 100
						}, 500);
					});
				});
			</script>
		</div>
		<?php
	}

	/**
	 * Handle setup wizard completion.
	 */
	public function handle_setup_completion() {
		check_admin_referer( 'ile_complete_setup', 'ile_setup_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'import-luma-events' ) );
		}

		// Increase time limit for import.
		set_time_limit( 300 );

		// Save settings.
		if ( isset( $_POST['ile_luma_options'] ) ) {
			$options = array(
				'luma_api_key'    => sanitize_text_field( $_POST['ile_luma_options']['api_key'] ),
				'luma_calendar_id' => sanitize_text_field( $_POST['ile_luma_options']['calendar_id'] ),
				'enable_auto_sync' => isset( $_POST['ile_luma_options']['enable_sync'] ) ? 1 : 0,
				'sync_frequency'  => 'daily',
			);
			update_option( 'ile_luma_options', $options );
		}

		// Load required classes if not already loaded.
		if ( ! class_exists( 'Import_Luma_Events_Luma_API' ) ) {
			require_once IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'includes/class-import-luma-events-luma-api.php';
		}
		if ( ! class_exists( 'Import_Luma_Events_Import_Manager' ) ) {
			require_once IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'includes/class-import-luma-events-import-manager.php';
		}

		// Get the saved calendar ID.
		$saved_options = get_option( 'ile_luma_options' );
		$calendar_id   = isset( $saved_options['luma_calendar_id'] ) ? $saved_options['luma_calendar_id'] : '';

		if ( empty( $calendar_id ) ) {
			// Redirect with error.
			wp_safe_redirect( add_query_arg( array(
				'post_type' => 'luma_events',
				'ile_error' => urlencode( __( 'Calendar ID not found. Please check your settings.', 'import-luma-events' ) ),
			), admin_url( 'edit.php' ) ) );
			exit;
		}

		// Trigger import and catch any errors.
		try {
			$import_manager = new Import_Luma_Events_Import_Manager();
			$import_options = array(
				'post_status'     => 'publish',
				'update_existing' => true,
			);
			$result = $import_manager->import_calendar_events( $calendar_id, $import_options );
		} catch ( Exception $e ) {
			error_log( 'Luma Events Setup Import Exception: ' . $e->getMessage() );
			$result = new WP_Error( 'import_exception', $e->getMessage() );
		}

		// Flush all output buffers to prevent redirect issues.
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		// Redirect to events page with success message.
		$redirect_args = array(
			'post_type'       => 'luma_events',
			'ile_setup_complete' => '1',
		);

		if ( is_wp_error( $result ) ) {
			$redirect_args['ile_error'] = urlencode( $result->get_error_message() );
		} else {
			$redirect_args['ile_imported'] = $result['imported'];
			$redirect_args['ile_updated']  = $result['updated'];
			$redirect_args['ile_skipped']  = $result['skipped'];
		}

		wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'edit.php' ) ) );
		exit;
	}

	/**
	 * Render documentation page.
	 */
	public function render_documentation_page() {
		require_once IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'admin/partials/documentation-page.php';
	}

	/**
	 * Handle cron reschedule request.
	 */
	public function handle_reschedule_cron() {
		check_admin_referer( 'ile_reschedule_cron' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'import-luma-events' ) );
		}

		// Load cron class.
		if ( ! class_exists( 'Import_Luma_Events_Cron' ) ) {
			require_once IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'includes/class-import-luma-events-cron.php';
		}

		$cron = new Import_Luma_Events_Cron();
		$cron->schedule_sync();

		// Redirect back to import page.
		wp_safe_redirect(
			add_query_arg(
				array(
					'page' => 'ile-import',
					'ile_msg' => 'cron_rescheduled',
				),
				admin_url( 'edit.php?post_type=luma_events' )
			)
		);
		exit;
	}

	/**
	 * Handle manual sync now request.
	 */
	public function handle_run_sync_now() {
		check_admin_referer( 'ile_run_sync_now' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'import-luma-events' ) );
		}

		// Load cron class.
		if ( ! class_exists( 'Import_Luma_Events_Cron' ) ) {
			require_once IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'includes/class-import-luma-events-cron.php';
		}

		$cron = new Import_Luma_Events_Cron();

		// Run the sync immediately.
		$cron->run_sync();

		// Clear and reschedule the next sync (1 hour from now).
		$cron->clear_scheduled_sync();

		$options = get_option( 'ile_luma_options' );
		$frequency = isset( $options['sync_frequency'] ) ? $options['sync_frequency'] : 'hourly';

		// Get the interval in seconds
		$schedules = wp_get_schedules();
		$interval = isset( $schedules[ $frequency ] ) ? $schedules[ $frequency ]['interval'] : 3600;

		// Schedule next run based on the interval
		$next_run = time() + $interval;
		wp_schedule_event( $next_run, $frequency, 'ile_sync_events' );

		// Redirect back to import page.
		wp_safe_redirect(
			add_query_arg(
				array(
					'page' => 'ile-import',
					'ile_msg' => 'sync_completed',
				),
				admin_url( 'edit.php?post_type=luma_events' )
			)
		);
		exit;
	}
}
