<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';
require_once 'filecacheTest.php';

class system_classes_APCCacheTest extends system_classes_FileCacheTest
{
	protected function setUp()
	{
		if(!defined('CACHE_CLASS')) {
			define('CACHE_CLASS', 'APCCache');
		}
		elseif(CACHE_CLASS != 'APCCache') {
			$this->markTestSkipped('The cache constant must be set to "APCCache" to test the APC cache.');
		}
		if (!extension_loaded('apc')) {
			$this->markTestSkipped('APC extension is not installed. APC must be installed');
		}
	}
}
?>
