<?php
/**
 * Plugin Name:       GatherPress Groups
 * Plugin URI:        https://github.com/carstingaxion/gatherpress-groups
 * Description:       Adds a Group post type to GatherPress, a venue-style collective that receives person–role assignments via GatherPress Relations and links to events and productions through a shadow taxonomy.
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires plugins:  gatherpress
 * Author:            carstenbach
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gatherpress-groups
 * Domain Path:       /languages
 *
 * @package GatherPress_Groups
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

// Constants.
define( 'GATHERPRESS_GROUPS_VERSION', current( get_file_data( __FILE__, array( 'Version' ), 'plugin' ) ) );
define( 'GATHERPRESS_GROUPS_CORE_PATH', __DIR__ );


/**
 * Adds the GatherPress_Groups namespace to the autoloader.
 *
 * This function hooks into the 'gatherpress_autoloader' filter and adds the
 * GatherPress_Groups namespace to the list of namespaces with its core path.
 *
 * @param array<string, string> $namespaces An associative array of namespaces and their paths.
 * @return array<string, string> Modified array of namespaces and their paths.
 */
function gatherpress_groups_autoloader( array $namespaces ): array {
	$namespaces['GatherPress_Groups'] = GATHERPRESS_GROUPS_CORE_PATH;

	return $namespaces;
}
add_filter( 'gatherpress_autoloader', 'gatherpress_groups_autoloader' );

/**
 * Initialize the plugin.
 *
 * Bootstrap function that starts the plugin by initializing the main class.
 *
 * This function hooks into the 'plugins_loaded' action to ensure that
 * the instances are created once all plugins are loaded,
 * only if the GatherPress plugin is active.
 *
 * @since 0.1.0
 * @return void
 */
function gatherpress_groups_setup(): void {
	if ( defined( 'GATHERPRESS_VERSION' ) ) {
		\GatherPress_Groups\Setup::get_instance();
		\GatherPress_Groups\Block::get_instance();
	}
}
add_action( 'plugins_loaded', 'gatherpress_groups_setup' );
