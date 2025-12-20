<?php
/**
 * Plugin Name: Import Luma Events
 * Plugin URI: https://github.com/sambatesdesign/Luma-Events
 * Description: Import events from Luma (lu.ma) into WordPress as a custom post type. Supports automatic calendar sync, manual imports, and shortcodes for displaying events.
 * Version: 1.0.0
 * Author: MazeSpace Studios LTD
 * Author URI: https://mazespacestudios.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: import-luma-events
 * Domain Path: /languages
 *
 * @package Import_Luma_Events
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'IMPORT_LUMA_EVENTS_VERSION', '1.0.0' );
define( 'IMPORT_LUMA_EVENTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'IMPORT_LUMA_EVENTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The core plugin class.
 */
require_once IMPORT_LUMA_EVENTS_PLUGIN_DIR . 'includes/class-import-luma-events.php';

/**
 * Begins execution of the plugin.
 */
function run_import_luma_events() {
	$plugin = Import_Luma_Events::get_instance();
	$plugin->run();
}

run_import_luma_events();
