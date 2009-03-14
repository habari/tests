<?php
// vim: le=unix syntax=php ts=4 noet sw=4

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';
require_once 'PHPUnit/Framework.php';

$testclasses = array();
foreach (new DirectoryIterator( dirname( __FILE__ ) ) as $d) {
	if ($d->isDot() || substr($d, -8) != 'Test.php') {
		continue;
	}
	require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . $d;
	Habari_system_classes_TestSuite::$testclasses[] = substr((string)$d, 0, strlen((string)$d) - 4); // trim .php
}

class Habari_system_classes_TestSuite extends PHPUnit_Framework_TestSuite
{
	public static $testclasses = array();
	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('Habari_system_classes');
		$prefix = preg_replace('/Habari_(.*)TestSuite/', '$1', __CLASS__);
		foreach (self::$testclasses as $class) {
			$suite->addTestSuite($prefix . $class);
		}
		return $suite;
	}
}
