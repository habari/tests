<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

class system_classes_FileCacheTest extends PHPUnit_Framework_TestCase
{
	
	protected function setUp()
	{
		if(!defined('CACHE_CLASS')) {
			define('CACHE_CLASS', 'FileCache');
		}
		elseif(CACHE_CLASS != 'FileCache') {
			$this->markTestSkipped('The cache constant must be set to "FileCache" to test the file cache.');
		}
	}

	public function test_has()
	{
		Cache::expire('foo');
		$this->assertFalse(Cache::has('foo'), 'The cache has a value that has been explicitly expired.');
		Cache::set('foo', 'a value');
		$this->assertTrue(Cache::has('foo'), 'The cache does not have a value that has been explicitly set.');
	}

	public function test_has_group()
	{
		Cache::expire(array('*', 'bar'), 'glob');
		$this->assertFalse(Cache::has_group('foo'), 'The cache has a group that was explicitly expired.');
		Cache::set(array('foo', 'bar'), 'a value');
		$this->assertTrue(Cache::has_group('foo'), 'The cache does not have a group that was explicitly set.');
	}

	public function test_get_group()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function test_get()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function test_set()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function test_expire()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function test_extend()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testGet_name_hash()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testGet_group_hash()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testRecord_fresh()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testClear_expired()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
?>
