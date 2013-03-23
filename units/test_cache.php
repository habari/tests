<?php
namespace Habari;

class CacheTest extends UnitTestCase

{

	public function module_setup()
	{
		$this->access_names = array( 'read', 'edit', 'delete', 'create' );
		$this->bitmask = new Bitmask($this->access_names);
	}

	public function test_cache_get_set()
	{
		$value = rand(0,999999);
		Cache::set('habari:test', $value);
		$this->assert_equal($value, Cache::get('habari:test'), 'Cache stored value not equal to retreived value.');
		$value2 = $value + 1;
		Cache::set('habari:test', $value2);
		$this->assert_equal($value2, Cache::get('habari:test'), 'Cache stored value could not be changed.');
	}

	public function test_cache_has()
	{
		$value = rand(0,999999);
		Cache::set('habari:test', $value);
		$this->assert_true(Cache::has('habari:test'), 'Cannot find known stored value.');
	}

	public function test_expiry()
	{
		$value = rand(0,999999);
		Cache::set('habari:test', $value, 2);
		sleep(3);
		$this->assert_false(Cache::has('habari:test'), 'Cache value did not expire after the short expiry time.');
	}

	public function test_expire()
	{
		$value = rand(0,999999);
		Cache::set('habari:test', $value);
		$this->assert_true(Cache::has('habari:test'), 'Cache value not stored as expected.');
		Cache::expire('habari:test');
		$this->assert_false(Cache::has('habari:test'), 'Cache value did not expire as expected.');
	}
}
?>
