<?php

include 'bootstrap.php';

class UserGroupTest extends UnitTestCase
{
	private $group;
	private $user_alice;
	private $user_bob;
	private $user_carl;
	private $allow_filter;

	public function setup()
	{
		// create test group and user
		$this->allow_filter = true;
		$this->group = UserGroup::create( array( 'name' => 'new test group' ) );
		$this->user_alice = User::create( array( 'username' => 'alice' ) );
		$this->user_bob = User::create( array( 'username' => 'bob' ) );
		$this->user_carl = User::create( array( 'username' => 'carl' ) );
	}

	public function test_creategroup()
	{
		// group should have been created in the setup
		$this->assert_true(
			$this->group instanceof UserGroup,
			'Could not create a new group named "new test group".'
		);

		Plugins::register( array( $this, 'filter_usergroup_insert_allow' ), 'filter','usergroup_insert_allow' );

		// test the plugin hook
		$this->allow_filter = false;
		$second_group = UserGroup::create( array( 'name' => 'another test group' ) );
		$this->assert_false(
			$second_group instanceof UserGroup,
			'Denial by plugin hook did not prevent creation of a new group.'
		);

		$this->allow_filter = true;
		$second_group = UserGroup::create( array( 'name' => 'another test group' ) );
		$this->assert_true(
			$second_group instanceof UserGroup,
			'Plugin hook illogically prevented creation of a new group.'
		);
		$second_group->delete();
	}

	public function test_createduplicategroup() // probably should be part of the previous one
	{
		// Can I create two groups with the same name?
		$group = UserGroup::create( array( 'name' => 'new dupe group' ) );
		$group2 = UserGroup::create( array( 'name' => 'new dupe group' ) );
		$this->assert_true(
			$group2 instanceof UserGroup,
			'Could not create a group with the same name' );

		$this->assert_true(
			DB::get_value('SELECT count(*) FROM {groups} WHERE name = ?', array('new dupe group')) == 1,
			'Was able to create two groups with the same name.'
		);

		$group->delete();
		$group2->delete();
	}

	public function filter_usergroup_insert_allow( $allow )
	{
		return $this->allow_filter;
	}

	public function test_updategroup()
	{
		$this->mark_test_incomplete();
	}

	public function test_deletegroup()
	{
		$group = UserGroup::get( "new test group" );

		Plugins::register( array( $this, 'filter_usergroup_delete_allow' ), 'filter','usergroup_delete_allow' );
		$this->assert_true(
			$group instanceof UserGroup,
			'Could not retrieve group named "new test group".'
		);

		$this->allow_filter = false;
		$group->delete();
		$this->assert_false(
			DB::get_value('SELECT count(*) FROM {groups} WHERE name = ?', array('new test group')) == 0,
			'Was able to delete a group despite not being allowed to do so.'
		);

		$this->allow_filter = true;
		$group->delete();
		$this->assert_true(
			DB::get_value('SELECT count(*) FROM {groups} WHERE name = ?', array('new test group')) == 0,
			'Was not able to delete a created group.'
		);

		$group = UserGroup::get( "new test group" );
		$this->assert_false(
			$group instanceof UserGroup,
			'Was able to retrieve (deleted) group named "new test group".'
		);
	}

	public function filter_usergroup_delete_allow( $allow )
	{
		return $this->allow_filter;
	}

	public function test_addtogroup()
	{
		$group = UserGroup::get( "new test group" );
		$this->group->add( 'alice' ); // need to also test by ID

		$this->assert_true(
			in_array( $this->user_alice->id, $group->members ),
			'Single user not added to group.'
		);

		$this->group->add( array( 'bob', 'carl' ) );

		$this->assert_true(
			in_array( $this->user_bob->id, $group->members ) and
			in_array( $this->user_carl->id, $group->members ),
			'Array of users not added to group.'
		);

		// should check in ->member_ids also
	}

	public function test_removefromgroup()
	{
		$group = UserGroup::get( "new test group" );
		$this->group->add( array( 'alice', 'bob', 'carl' ) );
		$this->assert_true(
			in_array( $this->user_alice->id, $group->members ) and
			in_array( $this->user_bob->id, $group->members ) and
			in_array( $this->user_carl->id, $group->members ),
			'Array of users not added to group.'
		);

		$group->remove( $this->user_alice->id );
		$this->assert_false(
			in_array( $this->user_alice->id, $group->members ),
			'Single user not removed from group.'
		);

		$group->remove( array( 'bob', 'carl' ) );
		$this->assert_false(
			in_array( $this->user_bob->id, $group->members ) and in_array( $this->user_carl->id, $group->members ),
			'Array of users not removed from group.'
		);
		// should check in ->member_ids also
	}

	public function test_grantgroup()
	{
		$this->mark_test_incomplete();
	}

	public function test_denygroup()
	{
		$this->mark_test_incomplete();
	}

	public function test_revokegroup()
	{
		$this->mark_test_incomplete();
	}

	public function test_groupcan()
	{
		$this->mark_test_incomplete();
	}

	public function test_groupgetaccess()
	{
		$this->mark_test_incomplete();
	}

	public function test_cleargrouppermissionscache()
	{
		$this->mark_test_incomplete();
	}

	public function test_loadgrouppermissionscache()
	{
		$this->mark_test_incomplete();
	}

	public function test_getgroup()
	{
		$group = UserGroup::get( "new test group" ); // ::get_by_name()
		$this->assert_true(
			$group instanceof UserGroup,
			'Could not retrieve group named "new test group".'
		);

		$second_group = UserGroup::get( $group->id ); // ::get_by_id()
		$this->assert_true(
			$second_group instanceof UserGroup,
			"Could not retrieve group with id {$group->id}."
		);

		$invalid_group = UserGroup::get( "nonexistent test group" );
		$this->assert_false(
			$invalid_group,
			'Was able to retrieve a nonexistent group.'
		);
	}

	public function test_groupexists()
	{
		$this->mark_test_incomplete();
	}

	public function test_groupname()
	{
		$this->mark_test_incomplete();
	}

	public function test_groupid()
	{
		$this->mark_test_incomplete();
	}

	public function test_memberofgroup()
	{
		$this->mark_test_incomplete();
	}

/*	public function test_creategroup() // grant/deny parts of this need to be broken out of this
	{
		$user = User::create( array( 'username' => 'testcaseuser', 'email' => 'test@example.com', 'password' => 'test') );

		$group = UserGroup::create( array( 'name' => 'new test group' ) );
		$user = $this->user_alice;
		$group = $this->group;
		$this->assert_true(
			$group instanceof UserGroup,
			'Could not create a new group named "new test group".'
		);

		ACL::create_token( 'test permission', 'A permission for test cases', 'Administration' );
		ACL::create_token( 'test deny permission', 'A permission for test cases', 'Administration' );

		$group->add( 'group_alice' );
		$group->grant( 'test permission' );
		$group->deny( 'test deny permission' );
		$group->update();

		$newgroup = UserGroup::get( 'new test group' );

		$this->assert_true(
			in_array( ACL::token_id( 'test permission' ), array_keys( $newgroup->permissions ) ),
			'The group does not have the new permission.'
		);

		$this->assert_true(
			ACL::group_can( 'new test group', 'test permission' ),
			'The group does not have the new permission.'
		);

		$this->assert_false(
			ACL::group_can( 'new test group', 'test deny permission' ),
			'The group has a denied permission.'
		);

		$this->assert_true(
			$user->can( 'test permission' ),
			'The user does not have a permission his group has been granted.'
		);

		ACL::destroy_token( 'test permission' );
		ACL::destroy_token( 'test deny permission' );
	} */

	public function teardown()
	{
		$this->group->delete();
		$this->user_alice->delete();
		$this->user_bob->delete();
		$this->user_carl->delete();
	}
}

UserGroupTest::run_one('UserGroupTest');

?>
