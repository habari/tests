<?php

include 'bootstrap.php';

class UserGroupsTest extends UnitTestCase
{
	public function setup()
	{

	}

	public function test_get()
	{
		// test 'fetch_fn'
		$this->mark_test_incomplete();

		// test 'count'
		$this->assert_equal( count( UserGroups::get_all() ), UserGroups::get( array( 'count' => true ) ) );

		// test 'limit'
		$this->assert_equal( count( UserGroups::get( array( 'limit' => 1 ) ) ), 1 );

		// test 'offset'
		$this->assert_equal( count( UserGroups::get( array( 'limit' => 1, 'offset' => 1 ) ) ), 1 );
		$this->assert_not_equal( UserGroups::get( array( 'limit' => 1, 'offset' => 1 ) ), UserGroups::get( array( 'limit' => 1 ) ), 1 );

		// test 'where', including an 'id'...
	}

	public function test_get_all()
	{
		$this->mark_test_incomplete();
	}

	public function teardown()
	{

	}
}

UserGroupsTest::run_one('UserGroupsTest');

?>
