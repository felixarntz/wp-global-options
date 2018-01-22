<?php
/**
 * Plugin initialization file
 *
 * @package WPGlobalOptions
 * @since 1.0.0
 *
 * @wordpress-plugin
 * Plugin Name: WP Global Options
 * Plugin URI:  https://github.com/felixarntz/wp-global-options
 * Description: Implements a global option storage in WordPress.
 * Version:     1.0.0
 * Author:      Felix Arntz
 * Author URI:  https://leaves-and-love.net
 * License:     GNU General Public License v2 (or later)
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-global-options
 * Network:     true
 * Tags:        network roles, network, multisite, multinetwork
 */

/**
 * Loads the plugin textdomain.
 *
 * @since 1.0.0
 */
function go_load_textdomain() {
	load_plugin_textdomain( 'wp-global-options' );
}

/**
 * Initializes the plugin.
 *
 * Loads the required files.
 *
 * @since 1.0.0
 */
function go_init() {
	define( 'GO_PATH', plugin_dir_path( __FILE__ ) );
	define( 'GO_URL', plugin_dir_url( __FILE__ ) );

	require_once GO_PATH . 'wp-global-options/wp-includes/formatting.php';
	require_once GO_PATH . 'wp-global-options/wp-includes/option.php';
	require_once GO_PATH . 'wp-global-options/wp-includes/setup.php';

	go_register_table();

	if ( function_exists( 'wp_cache_add_global_groups' ) ) {
		wp_cache_add_global_groups( array( 'global-options', 'global-transient' ) );
	}
}

/**
 * Registers the global options database table.
 *
 * @since 1.0.0
 */
function go_register_table() {
	global $wpdb;

	if ( isset( $wpdb->global_options ) ) {
		return;
	}

	$wpdb->ms_global_tables[] = 'global_options';
	$wpdb->global_options     = $wpdb->base_prefix . 'global_options';
}

/**
 * Shows an admin notice if the WordPress version installed is not supported.
 *
 * @since 1.0.0
 */
function go_requirements_notice() {
	$plugin_file = plugin_basename( __FILE__ );

	if ( ! current_user_can( 'deactivate_plugin', $plugin_file ) ) {
		return;
	}

	?>
	<div class="notice notice-warning is-dismissible">
		<p>
			<?php
			printf(
				/* translators: %s: URL to deactivate plugin */
				__( 'Please note: WP Global Options requires WordPress 4.9 or higher. <a href="%s">Deactivate plugin</a>.', 'wp-global-options' ),
				wp_nonce_url(
					add_query_arg(
						array(
							'action'        => 'deactivate',
							'plugin'        => $plugin_file,
							'plugin_status' => 'all',
						),
						network_admin_url( 'plugins.php' )
					),
					'deactivate-plugin_' . $plugin_file
				)
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Ensures that this plugin gets activated in every new network by filtering the `active_sitewide_plugins` option.
 *
 * @since 1.0.0
 *
 * @param array $network_options All network options for the new network.
 * @return array Modified network options including the plugin.
 */
function go_activate_on_new_network( $network_options ) {
	$plugin_file = plugin_basename( __FILE__ );

	if ( ! isset( $network_options['active_sitewide_plugins'][ $plugin_file ] ) ) {
		$network_options['active_sitewide_plugins'][ $plugin_file ] = time();
	}

	return $network_options;
}

/**
 * Ensures that this plugin gets activated on a request to update the network-active plugins.
 *
 * @since 1.0.0
 *
 * @param array $plugins Associative array of `$plugin_basename => $time` pairs.
 * @return array Modified array.
 */
function go_activate_on_update_request( $plugins ) {
	$network_options = go_activate_on_new_network( array(
		'active_sitewide_plugins' => $plugins,
	) );

	remove_filter( 'pre_update_site_option_active_sitewide_plugins', 'go_activate_on_update_request' );

	return $network_options['active_sitewide_plugins'];
}

/**
 * Adds the hook to ensure that this plugin gets activated in every network created by WP Multi Network.
 *
 * @since 1.0.0
 */
function go_activate_on_new_wpmn_network_add_hook() {
	add_filter( 'pre_update_site_option_active_sitewide_plugins', 'go_activate_on_update_request' );
}

add_action( 'plugins_loaded', 'go_load_textdomain', 1 );

if ( version_compare( $GLOBALS['wp_version'], '4.9', '<' ) ) {
	add_action( 'admin_notices', 'go_requirements_notice' );
	add_action( 'network_admin_notices', 'go_requirements_notice' );
} else {
	add_action( 'plugins_loaded', 'go_init' );

	add_filter( 'populate_network_meta', 'go_activate_on_new_network', 10, 1 );
	add_action( 'add_network', 'go_activate_on_new_wpmn_network_add_hook', 10, 0 );
}
