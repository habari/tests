<?php

include 'bootstrap.php';

class UserGroupTest extends UnitTestCase
{

	function test_creategroup()
	{
		$user = User::create( array( 'username' => 'testcaseuser', 'email' => 'test@example.com', 'password' => 'test') );
		$this->assert_true(
			$user instanceof User,
			'Could not create test user.'
		);

		$group = UserGroup::create( array( 'name' => 'new test group' ) );
		$this->assert_true(
			$group instanceof UserGroup,
			'Could not create a new group named "new test group".'
		);

		ACL::create_token( 'test permission', 'A permission for test cases', 'Administration' );
		ACL::create_token( 'test deny permission', 'A permission for test cases', 'Administration' );

		$this->assert_true(
			ACL::token_exists('test permission'),
			'The test permission was not created.'
		);
		$this->assert_true(
			ACL::token_exists(' test  PeRmission '),
			'Permission names are not normalized.'
		);

		$group->add( 'testcaseuser' );
		$group->grant( 'test permission' );
		$group->deny( 'test  deny permisSion' );
		$group->update();

		$newgroup = UserGroup::get( 'new test group' );

		$this->assert_true(
			in_array( $user->id, $newgroup->members ),
			'The created user is not a member of the new group.'
		);

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
	}

	function test_deletegroup()
	{
		$group = UserGroup::get('new test group');
		$this->assert_true(
			$group instanceof UserGroup,
			'Could not retrieve group named "new test group".'
		);

		$group->delete();
		$this->assert_true(
			DB::get_value('SELECT count(*) FROM {groups} WHERE name = ?', array('new group')) == 0,
			'Was not able to delete a created group.'
		);

		$user = User::get('testcaseuser');
		$user->delete();
	}

	function test_createduplicategroup()
	{
		// Can I create two groups with the same name?
		$group = UserGroup::create( array( 'name' => 'new dupe group' ) );
		$group2 = UserGroup::create( array( 'name' => 'new dupe group' ) );
		assert( $group2 instanceof UserGroup );

		$this->assert_true(
			DB::get_value('SELECT count(*) FROM {groups} WHERE name = ?', array('new dupe group')) == 1,
			'Was able to create two groups with the same name.'
		);

		$group->delete();
		$group2->delete();
	}

}


UserGroupTest::run_one('UserGroupTest');

?>