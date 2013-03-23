<?php
namespace Habari;

class UserGroupsTest extends UnitTestCase
{
	public function setup()
	{
		if( UserGroup::get( 'testcasegroup' ) ) {
			UserGroup::get( 'testcasegroup' )->delete();
		}
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
		$groups_before = UserGroups::get_all();
		UserGroup::create( array( 'name' => 'testcasegroup' ) );
		$groups_after = UserGroups::get_all();

		$this->assert_not_equal( count( $groups_before ), count( $groups_after ) );
		$this->assert_not_identical( $groups_before, $groups_after );
		UserGroup::get( 'testcasegroup' )->delete();
	}

	public function teardown()
	{
		// testcasegroup is deleted in test_get_all()
	}
}
?>
