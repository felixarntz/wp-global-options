<?php
/**
 * Sets up the global options database table.
 *
 * @package WPNetworkRoles
 * @since 1.0.0
 */

/**
 * Gets the global options database table schema.
 *
 * @since 1.0.0
 * @access private
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return string Table schema SQL.
 */
function _go_get_db_table_schema() {
	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();

	return "CREATE TABLE $wpdb->global_options (
  option_id bigint(20) unsigned NOT NULL auto_increment,
  option_name varchar(191) NOT NULL default '',
  option_value longtext NOT NULL,
  autoload varchar(20) NOT NULL default 'yes',
  PRIMARY KEY  (option_id),
  UNIQUE KEY option_name (option_name)
) $charset_collate;\n";
}

/**
 * Maybe installs the global options database table and sets the installed flag.
 *
 * @since 1.0.0
 * @access private
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 */
function _go_maybe_install_db_table() {
	global $wpdb;

	if ( ! is_user_logged_in() ) {
		return;
	}

	$installed = get_global_option( 'installed' );

	if ( ! $installed ) {
		$installed = (bool) $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->esc_like( $wpdb->global_options ) ) );

		if ( ! $installed ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$schema = _go_get_db_table_schema();
			dbDelta( $schema );
		}

		add_global_option( 'installed', '1', 'yes' );
	}
}
add_action( 'init', '_go_maybe_install_db_table', 1, 0 );
