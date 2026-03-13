<?php
/**
 * Template Loader for Luma Events.
 *
 * Provides fallback templates if theme doesn't have them.
 *
 * @package Import_Luma_Events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import_Luma_Events_Template_Loader Class.
 */
class Import_Luma_Events_Template_Loader {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'template_include', array( $this, 'template_loader' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Load templates from plugin if not found in theme.
	 *
	 * @param string $template Template path.
	 * @return string Modified template path.
	 */
	public function template_loader( $template ) {
		if ( is_singular( 'luma_events' ) || is_post_type_archive( 'luma_events' ) ) {

			// Check if theme has override in luma-events folder.
			if ( is_singular( 'luma_events' ) ) {
				$theme_template = locate_template( array( 'luma-events/single-luma_events.php', 'single-luma_events.php' ) );
			} else {
				$theme_template = locate_template( array( 'luma-events/archive-luma_events.php', 'archive-luma_events.php' ) );
			}

			// Use theme template if it exists.
			if ( $theme_template ) {
				return $theme_template;
			}

			// Otherwise use plugin template.
			if ( is_singular( 'luma_events' ) ) {
				return IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'templates/single-luma_events.php';
			} else {
				return IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'templates/archive-luma_events.php';
			}
		}

		return $template;
	}

	/**
	 * Enqueue plugin styles for event templates.
	 */
	public function enqueue_styles() {
		if ( is_singular( 'luma_events' ) || is_post_type_archive( 'luma_events' ) ) {
			// Check if theme has custom styles - if so, don't enqueue plugin styles.
			$theme_has_styles = file_exists( get_stylesheet_directory() . '/luma-events/events.css' );

			if ( ! $theme_has_styles ) {
				wp_enqueue_style(
					'ile-events',
					IMPORT_LUMA_EVENTS_PLUGIN_URL . 'public/css/events.css',
					array(),
					IMPORT_LUMA_EVENTS_VERSION
				);
			}
		}
	}
}
