<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

class system_classes_OptionsTest extends PHPUnit_Framework_TestCase
{
	function setup()
	{
		$this->prefix = 'optionstest__';
	}

	public function testGetSingle()
	{
		// The installed option is guaranteed to exist, so we try to get that
		$option = Options::get('installed');

		$this->assertEquals('1', $option, 'Could not retrieve single option.');
	}

	public function testGetMultiple()
	{
		// The installed and db_version options are guaranteed to exist, so we try to get them
		$options = Options::get('installed', 'db_version');

		$this->assertType('array', $options, 'Retrieving multiple options should return an array.');
		$this->assertArrayHasKey('installed', $options, 'Returned array should contain named option.');
		$this->assertArrayHasKey('db_version', $options, 'Returned array should contain named option.');
		$this->assertEquals(2, count($options), 'Returned array should contain the number of options requested.');
	}

	public function testGetMultipleArray()
	{
		// The installed and db_version options are guaranteed to exist, so we try to get them
		$option_keys = array('installed', 'db_version');
		$options = Options::get($option_keys);

		$this->assertType('array', $options, 'Retrieving multiple options should return an array.');
		foreach ( $option_keys as $option_key ) {
			$this->assertArrayHasKey($option_key, $options, 'Returned array should contain named option.');
		}
		$this->assertEquals(count($option_keys), count($options), 'Returned array should contain the number of options requested.');
	}

	public function testGetGroup()
	{
		$options_in = array('foo', 'bar', 'baz');

		foreach ( $options_in as $option ) {
			Options::set($this->prefix.$option, strrev($option));
		}

		$options_out = Options::get_group($this->prefix);

		$this->assertType('array', $options_out, 'Retrieving option group should return an array.');
		foreach ( $options_in as $option_in ) {
			$this->assertArrayHasKey($option_in, $options_out, 'Returned array should contain named option.');
		}

		// Clean up
		foreach ( $options_in as $option ) {
			Options::delete($this->prefix.$option);
		}
	}

	public function testSetSingle()
	{
		Options::set($this->prefix.'pony','rides');

		$option = Options::get($this->prefix.'pony');
		$this->assertEquals('rides', $option, 'Retrieved option value should be what was set.');

		// Clean up
		Options::delete($this->prefix.'pony');
	}

	public function testSetMultiple()
	{
		$options_in = array($this->prefix.'one' => 1, $this->prefix.'two' => 2, $this->prefix.'three' => 3);

		Options::set($options_in);

		foreach ( $options_in as $option_in => $value ) {
			$option_out = Options::get($option_in);
			$this->assertNotNull($option_out, 'All options should be set.');
			$this->assertEquals($value, $option_out, 'Retrieved option value should be what was set.');
		}

		// Clean up
		foreach ( $options_in as $option ) {
			Options::delete($option);
		}
	}

	public function testSetMultiplePrefix()
	{
		$options_in = array('one' => 1, 'two' => 2, 'three' => 3);

		Options::set($options_in, $this->prefix);

		foreach ( $options_in as $option_in => $value ) {
			$option_out = Options::get($this->prefix.$option_in);
			$this->assertNotNull($option_out, 'All options should be set with prefix.');
			$this->assertEquals($value, $option_out, 'Retrieved option value should be what was set.');
		}

		// Clean up
		foreach ( $options_in as $option ) {
			Options::delete($this->prefix, $option);
		}
	}

}
?>
