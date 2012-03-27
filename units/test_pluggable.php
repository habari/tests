<?php

class PluggableTest extends UnitTestCase {

	private $simple_assets = array('simple_assets' => array(
		'files' => array('list', 'of', 'files'),
		'tables' => array('table')
	));

	public function module_setup()
	{
		if(!method_exists('Pluggable', 'register_assets')) {
			$this->skip_test('cache_assets', 'This test requires Pluggable::register_assets');
			$this->skip_test('test_store_released_asset_type', 'This test requires Pluggable::register_assets');
			$this->skip_test('test_store_released_asset', 'This test requires Pluggable::register_assets');
		}
	}

	public function teardown()
	{
		Cache::expire('pluggable_assets');
		Options::delete(array('released_pluggable_assets', 'inactive_pluggable_assets'));
	}

	/**
	 * Test that plugins can register assets
	 */
	public function test_register_assets()
	{
		Plugins::register(array($this, 'filter_register_simple_assets'), 'filter', 'pluggable_assets' );
		$result = Plugins::filter('pluggable_assets', array());
		$expected = $this->simple_assets;
		$key = 'simple_assets';

		$this->assert_true(array_key_exists($key, $result), "Expected <em>$key</em> key to exist in <em>" . var_export($result, true) . "</em>");
		$this->assert_equal($expected[$key], $result[$key], "Expected <em>" . var_export($result[$key], true) . "</em> to equal <em>" . var_export($expected[$key], true) . "</em>");
	}

	/**
	 * Test registered assets are stored in the cache
	 */
	public function test_cache_assets()
	{
		Plugins::register(array($this, 'filter_register_simple_assets'), 'filter', 'pluggable_assets' );
		Pluggable::register_assets();
		$expected = $this->simple_assets;
		$key = 'simple_assets';
		$result = Cache::get('pluggable_assets');

		$this->assert_true(array_key_exists($key, $result), "Expected <em>$key</em> key to exist in <em>" . var_export($result, true) . "</em>");
		$this->assert_equal($expected[$key], $result[$key], "Expected <em>" . var_export($result[$key], true) . "</em> to equal <em>" . var_export($expected[$key], true) . "</em>");
	}

	/**
	 * Test released asset types are stored in the options
	 */
	public function test_store_released_asset_type()
	{
		Cache::set('pluggable_assets', $this->simple_assets);

		// Releases ['simple_assets']['files'];
		Plugins::register(array($this, 'filter_register_release_type'), 'filter', 'pluggable_assets' );
		Pluggable::register_assets();

		$key = 'simple_assets';
		$expected = array($key => array('files' => $this->simple_assets[$key]['files']));
		$result = Options::get('released_pluggable_assets');

		$this->assert_equal($expected, $result, "Expected <em>" . var_export($result, true) . "</em> to equal <em>" . var_export($expected, true) . "</em>");
	}

	/**
	 * Test released assets are stored in the options
	 */
	public function test_store_released_asset()
	{
		Cache::set('pluggable_assets', $this->simple_assets);

		// Releases ['simple_assets']['files']['of']);
		Plugins::register(array($this, 'filter_register_release_asset'), 'filter', 'pluggable_assets' );
		Pluggable::register_assets();

		$key = 'simple_assets';
		//$expected = array($key => array('files' => array('of')));
		$result = Options::get('released_pluggable_assets');

		// Like to do this, but PHP's mushing together of arrays and dictionaries makes the keys not match
		//$this->assert_equal($expected, $result, "Expected <em>" . var_export($result, true) . "</em> to equal <em>" . var_export($expected, true) . "</em>");
		$this->assert_true(array_key_exists($key, $result) && array_key_exists('files', $result[$key]) && in_array('of', $result[$key]['files']));
	}

	/* Dummy plugin hooks */

	/**
	 * Plugin hook to register assets.
	 */
	public function filter_register_simple_assets($assets)
	{
		return array_merge($this->simple_assets, $assets);
	}

	/**
	 * Plugin hook to register assets with a whole type removed.
	 */
	public function filter_register_release_type($assets)
	{
		$released_assets = $this->simple_assets;
		unset($released_assets['simple_assets']['files']);
		// Overwrite the assets
		return $released_assets;
	}

	/**
	 * Plugin hook to register assets with a single asset removed from one type.
	 */
	public function filter_register_release_asset($assets)
	{
		$released_assets = $this->simple_assets;
		// Have to unset 'of' value using key
		unset($released_assets['simple_assets']['files'][1]);
		// Overwrite the assets
		return $released_assets;
	}

}
?>
