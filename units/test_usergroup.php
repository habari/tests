<?php

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

	public function test_create()
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

	public function test_update()
	{
		$this->mark_test_incomplete();
	}

	public function test_delete()
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

	public function test_add()
	{
		$group = UserGroup::get( "new test group" );
		$this->group->add( 'alice' ); // @TODO: test by ID

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
		// @TODO: Look in ->member_ids also?
	}

	public function test_remove()
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
		// @TODO: Look in ->member_ids also?
	}

	public function setup_acl()
	{
		ACL::create_token( 'test permission', 'A permission for test cases', 'Administration' );
		ACL::create_token( 'test deny permission', 'A permission for test cases', 'Administration' );

		$this->group->add( 'alice' );
		$this->group->grant( 'test permission' );
		$this->group->deny( 'test deny permission' );
	}

	public function teardown_acl()
	{
		ACL::destroy_token( 'test permission' );
		ACL::destroy_token( 'test deny permission' );
	}

	public function test_grant()
	{
		self::setup_acl();

		$this->assert_true(
			in_array( ACL::token_id( 'test permission' ), array_keys( $this->group->permissions ) ),
			'The group does not have the new permission.'
		);

		$this->assert_true(
			$this->user_alice->can( 'test permission' ),
			'A group user does not have a permission granted to the group.'
		);
		self::teardown_acl();
	}

	public function test_deny()
	{
		self::setup_acl();

		$group = UserGroup::get( "new test group" );
		$this->assert_false(
			in_array( ACL::token_id( 'test deny permission' ), array_keys( $group->permissions ) ),
			'The group has a denied permission.'
		);
		self::teardown_acl();
	}

	public function test_revoke()
	{
		self::setup_acl();

		$group = UserGroup::get( "new test group" );
		$this->assert_true(
			in_array( ACL::token_id( 'test permission' ), array_keys( $group->permissions ) ),
			'The group does not have the new permission.'
		);
		$group->revoke( ACL::token_id( 'test permission' ) ); // @TODO: test by name

		$group = UserGroup::get( "new test group" );
		$this->assert_false(
			in_array( ACL::token_id( 'test permission' ), array_keys( $group->permissions ) ),
			'The group still has the revoked permission.'
		);

		self::teardown_acl();
	}

	public function test_can()
	{
		self::setup_acl();

		$this->assert_true(
			$this->group->can( "test permission" ),
			'The group does not have the new permission.'
		);

		$this->assert_false(
			$this->group->can( "test deny permission" ),
			'The group is not denied the new permission.'
		);
		self::teardown_acl();
	}

	public function test_get_access()
	{
		$this->mark_test_incomplete();
	}

	public function test_clear_permissions_cache()
	{
		$this->mark_test_incomplete();
	}

	public function test_load_permissions_cache()
	{
		$this->mark_test_incomplete();
	}

	public function test_get()
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

		$this->assert_false(
			UserGroup::get( "nonexistent test group" ),
			'Was able to retrieve a nonexistent group by name.'
		);

		$this->assert_false(
			UserGroup::get( -1 ),
			'Was able to retrieve a nonexistent group by ID.'
		);
	}

	public function test_exists()
	{
		$this->assert_true(
			UserGroup::exists( "new test group" ),
			'Cannot confirm existence of "new test group".'
		);

		$group = UserGroup::get( "new test group" );
		$this->assert_true(
			UserGroup::exists( $group->id ),
			'Cannot confirm existence of "new test group".'
		);

		$this->assert_false(
			UserGroup::exists( "nonexistent test group" ),
			'A nonexistent group should not exist.'
		);

		$this->assert_false(
			UserGroup::exists( -1 ),
			'A nonexistent group should not exist.'
		);
	}

	public function test_name()
	{
		$this->assert_equal( // silly, but the code would allow it
			UserGroup::name( "new test group" ), "new test group",
			'Cannot retrieve "new test group" by ID.'
		);

		$group = UserGroup::get( "new test group" );
		$this->assert_equal(
			UserGroup::name( $group->id ), "new test group",
			'Cannot retrieve "new test group" by ID.'
		);

		$this->assert_false(
			UserGroup::name( "nonexistent test group" ),
			'Retrieved a name for an invalid group name.'
		);

		$this->assert_false(
			UserGroup::name( -1 ),
			'Retrieved a name for an invalid group ID.'
		);
	}

	public function test_id()
	{
		$group = UserGroup::get( "new test group" );
		$this->assert_equal(
			UserGroup::id( "new test group" ), $group->id,
			'Cannot retrieve "new test group" by name.'
		);

		$this->assert_equal( // silly, but the code would allow it
			UserGroup::id( $group->id ), $group->id,
			'Cannot retrieve "new test group" by ID.'
		);

		$this->assert_false(
			UserGroup::id( "nonexistent test group" ),
			'Retrieved an ID for an invalid group name.'
		);

		$this->assert_false(
			UserGroup::id( -1 ),
			'Retrieved a ID for an invalid group ID.'
		);
	}

	public function test_member()
	{
		$group = UserGroup::get( "new test group" );

		// Add Alice to the group.
		$group->add( 'alice' );
		$this->assert_true(
			$group->member( $this->user_alice->id ),
			'Unable to find user added to test group.'
		);
		$this->assert_true(
			$group->member( 'alice' ),
			'Unable to find user added to test group.'
		);

		// Bob should not have been added to the group.
		$this->assert_false(
			$group->member( $this->user_bob->id ),
			'User not in test group should not be a member.'
		);
		$this->assert_false(
			$group->member( 'bob' ),
			'User not in test group should not be a member.'
		);

	}

	public function teardown()
	{
		$this->group->delete();
		$this->user_alice->delete();
		$this->user_bob->delete();
		$this->user_carl->delete();
	}
}

?>
