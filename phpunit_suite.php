<?php
/**
 * PHPUnit Main Test Suite;
 *
 * to run:
 *   phpunit phpunit_Suite.php
 */

// vim: le=unix syntax=php ts=4 noet sw=4

require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';
require_once 'PHPUnit/Framework.php';

// as directories are added, add their suite files to this list
require dirname(__FILE__) . '/system/phpunit_suite.php';

/**
 * All Tests
 */
class Habari_full_TestSuite extends PHPUnit_Framework_TestSuite {
	
	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('Habari_full');
		// add new suite class names here:
		$suite->addTestSuite('Habari_system_TestSuite');
		return $suite;
	}
	
}
