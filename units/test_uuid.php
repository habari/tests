<?php

class UUIDTest extends UnitTestCase
{
	private $uuid;

	public function setup()
	{
		$this->uuid = new UUID();
	}

	public function test__tostring()
	{
		$this->assert_equal( "{$this->uuid}", $this->uuid->get_hex() );
	}

	public function test_get_array()
	{
		$this->mark_test_incomplete( 'This test has not been implemented yet.' );
	}

	public function test_get_raw()
	{
		$this->mark_test_incomplete( 'This test has not been implemented yet.' );
	}

	public function test_get_hex()
	{
		$this->mark_test_incomplete( 'This test has not been implemented yet.' );
	}

	public function test_get()
	{
		// @TODO: figure out if there's a way to make this less dependent on randomness
		$this->assert_not_equal( $this->uuid->get_hex(), UUID::get() );
	}

	public function teardown()
	{
		// does this need to be unset?
	}
}
?>
