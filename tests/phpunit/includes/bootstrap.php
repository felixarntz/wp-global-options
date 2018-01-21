<?php
/**
 * @package WPNetworkRoles
 * @subpackage Tests
 */

// disable xdebug backtrace
if ( function_exists( 'xdebug_disable' ) ) {
	xdebug_disable();
}

if ( false !== getenv( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', getenv( 'WP_PLUGIN_DIR' ) );
}

$GLOBALS['wp_tests_options'] = array(
	'active_plugins' => array(
		'wp-global-options/wp-global-options.php',
	),
);

if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	$test_root = getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit';
} elseif ( file_exists( '/tmp/wordpress-tests-lib/includes/bootstrap.php' ) ) {
	$test_root = '/tmp/wordpress-tests-lib';
} else {
	$test_root = '../../../../../../tests/phpunit';
}

require $test_root . '/includes/bootstrap.php';

require_once dirname( __FILE__ ) . '/factory.php';
require_once dirname( __FILE__ ) . '/testcase.php';

echo "Installing WP Global Options...\n";

activate_plugin( 'wp-global-options/wp-global-options.php' );
