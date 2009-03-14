<?php
// vim: le=unix syntax=php ts=4 noet sw=4

require_once dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';
require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/classes/phpunit_suite.php';

class Habari_system_TestSuite extends PHPUnit_Framework_TestSuite
{
	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('Habari_system');
		$suite->addTestSuite('Habari_system_classes_TestSuite');
		return $suite;
	}
}
?>
