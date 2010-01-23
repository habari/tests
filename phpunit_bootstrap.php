<?php
// vim: syntax=php ts=4 noet sw=4
// This is used to find the starting point of Habari
if ( !defined( 'HABARI_PATH' ) ) {
	if ( isset( $_ENV['HABARI_PATH'] ) ) {
		$tmpPath = $_ENV['HABARI_PATH'];
	} else {
		$tmpPath = dirname( dirname( __FILE__ ) );
	}
	// guess if we have the correct path by checking for index.php
	if ( !is_readable( $tmpPath . DIRECTORY_SEPARATOR . 'index.php' )) {
		$tmpPath = $tmpPath . DIRECTORY_SEPARATOR . 'htdocs';
	}
	if ( !is_readable( $tmpPath . DIRECTORY_SEPARATOR . 'index.php' )) {
		die( "Could not find Habari Path; specify it in \$_ENV['HABARI_PATH']\n" );
	}
	define( 'HABARI_PATH', $tmpPath );
}
// Specify a directory for test data
if ( ! defined( 'TEST_DATA_DIR' ) ) {
	define( 'TEST_DATA_DIR', dirname( __FILE__) );
}
// This constant prevents the regular handling of URL-based requests
// during testing
if ( !defined( 'UNIT_TEST' ) ) {
	define( 'UNIT_TEST', true );
}
// Debugging is usually not required when simply running tests
if ( !defined( 'DEBUG' ) ) {
	// define('DEBUG', true);
}
if ( !defined( 'SUPPRESS_ERROR_HANDLER' ) ) {
	define( 'SUPPRESS_ERROR_HANDLER', true );
}
require_once HABARI_PATH . DIRECTORY_SEPARATOR . 'index.php';
