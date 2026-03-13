<?php
/**
 * The core plugin class.
 *
 * @package Import_Luma_Events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Import_Luma_Events Class.
 */
class Import_Luma_Events {

	/**
	 * The single instance of the class.
	 *
	 * @var Import_Luma_Events
	 */
	protected static $instance = null;

	/**
	 * Custom Post Type instance.
	 *
	 * @var Import_Luma_Events_CPT
	 */
	public $cpt;

	/**
	 * Luma API instance.
	 *
	 * @var Import_Luma_Events_Luma_API
	 */
	public $luma_api;

	/**
	 * Import Manager instance.
	 *
	 * @var Import_Luma_Events_Import_Manager
	 */
	public $import_manager;

	/**
	 * Admin instance.
	 *
	 * @var Import_Luma_Events_Admin
	 */
	public $admin;

	/**
	 * Cron instance.
	 *
	 * @var Import_Luma_Events_Cron
	 */
	public $cron;

	/**
	 * Shortcode instance.
	 *
	 * @var Import_Luma_Events_Shortcode
	 */
	public $shortcode;

	/**
	 * Meta Box instance.
	 *
	 * @var Import_Luma_Events_Meta_Box
	 */
	public $meta_box;

	/**
	 * Template Loader instance.
	 *
	 * @var Import_Luma_Events_Template_Loader
	 */
	public $template_loader;

	/**
	 * Main Import_Luma_Events Instance.
	 *
	 * Ensures only one instance of Import_Luma_Events is loaded or can be loaded.
	 *
	 * @return Import_Luma_Events - Main instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_components();
	}

	/**
	 * Load required dependencies.
	 */
	private function load_dependencies() {
		require_once IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'includes/class-import-luma-events-cpt.php';
		require_once IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'includes/class-import-luma-events-luma-api.php';
		require_once IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'includes/class-import-luma-events-import-manager.php';
		require_once IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'includes/class-import-luma-events-admin.php';
		require_once IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'includes/class-import-luma-events-cron.php';
		require_once IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'includes/class-import-luma-events-shortcode.php';
		require_once IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'includes/class-import-luma-events-meta-box.php';
		require_once IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'includes/class-import-luma-events-template-loader.php';
	}

	/**
	 * Initialize plugin components.
	 */
	private function init_components() {
		$this->cpt             = new Import_Luma_Events_CPT();
		$this->luma_api        = new Import_Luma_Events_Luma_API();
		$this->import_manager  = new Import_Luma_Events_Import_Manager();
		$this->admin           = new Import_Luma_Events_Admin();
		$this->cron            = new Import_Luma_Events_Cron();
		$this->shortcode       = new Import_Luma_Events_Shortcode();
		$this->meta_box        = new Import_Luma_Events_Meta_Box();
		$this->template_loader = new Import_Luma_Events_Template_Loader();
	}

	/**
	 * Run the plugin.
	 */
	public function run() {
		// Register activation and deactivation hooks.
		register_activation_hook( IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'import-luma-events.php', array( $this, 'activate' ) );
		register_deactivation_hook( IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'import-luma-events.php', array( $this, 'deactivate' ) );

		// Init components.
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Initialize plugin.
	 */
	public function init() {
		// Load text domain.
		load_plugin_textdomain( 'import-luma-events', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Plugin activation.
	 */
	public function activate() {
		// Register post type and taxonomies.
		$this->cpt->register_post_type();
		$this->cpt->register_taxonomies();

		// Create default pages if they don't exist.
		$this->create_default_pages();

		// Set flag for first-time activation redirect.
		if ( ! get_option( 'ile_activation_redirect', false ) ) {
			add_option( 'ile_activation_redirect', true );
		}

		// Flush rewrite rules.
		flush_rewrite_rules();

		// Schedule cron events.
		$this->cron->schedule_sync();
	}

	/**
	 * Plugin deactivation.
	 */
	public function deactivate() {
		// Clear scheduled cron events.
		$this->cron->clear_scheduled_sync();

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Create default pages on activation.
	 */
	private function create_default_pages() {
		// Check if Events page already exists.
		$events_page = get_page_by_path( 'events' );

		if ( ! $events_page ) {
			// Create Events page.
			$events_page_id = wp_insert_post(
				array(
					'post_title'   => __( 'Events', 'import-luma-events' ),
					'post_content' => '[luma_events posts_per_page="12" columns="3"]',
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_name'    => 'events',
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
				)
			);

			// Store page ID in options.
			update_option( 'ile_events_page_id', $events_page_id );
		}
	}
}
