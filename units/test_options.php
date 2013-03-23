<?php
namespace Habari;

class OptionsTest extends UnitTestCase
{
	private $prefix = 'optionstest__';

	public function test_getSingle()
	{
		// The installed option is guaranteed to exist, so we try to get that
		$option = Options::get( 'installed' );

		$this->assert_equal( '1', $option, 'Could not retrieve single option.' );

		$option = Options::get( null ); // null is not a valid name

		$this->assert_equal( null, $option, 'Unset option should return null.' );
	}

	public function test_getSingleDefaultValue()
	{
		// The installed option is guaranteed to exist, so we try to get that
		$option = Options::get( null, "default value" );

		$this->assert_equal( "default value", $option, 'Default value not returned.' );
	}

	public function test_getMultiple()
	{
		// The installed and db_version options are guaranteed to exist, so we try to get them
		$options = Options::get( array( 'installed', 'db_version') );

		$this->assert_true( is_array( $options ), 'Retrieving multiple options should return an array.' );
		$this->assert_true( array_key_exists( 'installed', $options ), 'Returned array should contain named option.' );
		$this->assert_true( array_key_exists( 'db_version', $options ), 'Returned array should contain named option.' );
		$this->assert_equal( 2, count( $options ), 'Returned array should contain the number of options requested.' );
	}

	public function test_getMultipleArray()
	{
		// The installed and db_version options are guaranteed to exist, so we try to get them
		$option_keys = array( 'installed', 'db_version' );
		$options = Options::get( $option_keys );

		$this->assert_true( is_array( $options ), 'Retrieving multiple options should return an array.' );
		foreach ( $option_keys as $option_key ) {
			$this->assert_true( array_key_exists( $option_key, $options ), 'Returned array should contain named option.' );
		}
		$this->assert_equal( count( $option_keys ), count( $options ), 'Returned array should contain the number of options requested.' );
	}

	public function test_getGroup()
	{
		$options_in = array( 'foo', 'bar', 'baz' );

		foreach ( $options_in as $option ) {
			Options::set( $this->prefix.$option, strrev( $option ) );
		}

		$options_out = Options::get_group( $this->prefix );

		$this->assert_true( is_array( $options_out ), 'Retrieving option group should return an array.' );
		foreach ( $options_in as $option_in ) {
			$this->assert_true( array_key_exists( $option_in, $options_out ), 'Returned array should contain named option.' );
		}

		// Clean up
		foreach ( $options_in as $option ) {
			Options::delete( $this->prefix.$option );
		}
	}

	public function test_setSingle()
	{
		Options::set( $this->prefix.'pony', 'rides' );

		$option = Options::get($this->prefix.'pony' );
		$this->assert_equal( 'rides', $option, 'Retrieved option value should be what was set.' );

		// Clean up
		Options::delete( $this->prefix.'pony' );
	}

	public function test_setMultiple()
	{
		$options_in = array( $this->prefix.'one' => 1, $this->prefix.'two' => 2, $this->prefix.'three' => 3 );

		Options::set( $options_in );

		foreach ( $options_in as $option_in => $value ) {
			$option_out = Options::get( $option_in );
			$this->assert_true( $option_out != null, 'All options should be set.' );
			$this->assert_equal( $value, $option_out, 'Retrieved option value should be what was set.' );
		}

		// Clean up
		foreach ( $options_in as $option ) {
			Options::delete( $option );
		}
	}

	public function test_setMultiplePrefix()
	{
		$options_in = array( 'one' => 1, 'two' => 2, 'three' => 3 );

		Options::set( $options_in, $this->prefix );

		foreach ( $options_in as $option_in => $value ) {
			$option_out = Options::get( $this->prefix.$option_in );
			$this->assert_true( $option_out != null, 'All options should be set with prefix.' );
			$this->assert_equal( $value, $option_out, 'Retrieved option value should be what was set.' );
		}

		// Clean up
		foreach ( $options_in as $option ) {
			Options::delete( $this->prefix, $option );
		}
	}

}
?>
