<?php

class ACLTest extends UnitTestCase {
	private $acl_group;
	private $acl_user_alice;
	private $acl_user_bob;

	public function setup()
	{
		// create test group and user
		$this->acl_group = UserGroup::create( array( 'name' => 'acltest-group' ) );
		$this->acl_user_alice = User::create( array( 'username' => 'acl-alice' ) );
		$this->acl_user_bob = User::create( array( 'username' => 'acl-bob' ) );
		$this->acl_group->add( 'acl-alice' );
		$this->acl_group->add( 'acl-bob' );
	}

	public function teardown()
	{
		$this->acl_group->delete();
		$this->acl_user_alice->delete();
		$this->acl_user_bob->delete();
	}

	public function test_group_permissions()
	{
		ACL::create_token( 'acltest', 'A test ACL permission', 'Administration' );

		$this->assert_true(
			ACL::token_exists( 'acltest' ),
			'Could not create acltest permission.'
		);

		$this->assert_true(
			ACL::token_exists( 'acLtEst ' ),
			'Permission names are not normalized.'
		);

		$token_id = ACL::token_id( 'acltest' );

		ACL::grant_group( $this->acl_group->id, $token_id, 'full' );
		$this->assert_true(
			$this->acl_group->can( 'acltest', 'full' ),
			'Could not grant acltest permission to acltest-group.'
		);

		ACL::revoke_group_token( $this->acl_group->id, $token_id );
		$this->assert_false(
			ACL::group_can( $this->acl_group->id, $token_id, 'full' ),
			'Could not revoke acltest permission from acltest-group.'
		);

		// check alternate means of granting a permission
		$this->acl_group->grant( 'acltest', 'full' );
		$this->assert_true(
			$this->acl_group->can( 'acltest', 'full' ),
			'Could not grant acltest permission to acltest-group through UserGroup call.'
		);

		// full > read/edit
		$this->assert_true(
			$this->acl_group->can( 'acltest', 'read' ),
			"Group with 'full' acltest permission cannot 'read'."
		);
		$this->assert_true(
			$this->acl_group->can( 'acltest', 'edit' ),
			"Group with 'full' acltest permission cannot 'edit'."
		);
		$this->assert_true(
			$this->acl_group->can( 'acltest', 'full' ),
			"Group with 'full' acltest permission cannot 'full'."
		);
		$this->assert_exception( 'InvalidArgumentException', "'write' is an invalid token flag." );
		$this->acl_group->can( 'acltest', 'write' );

		ACL::destroy_token( 'acltest' );
	}

	public function test_user_permissions()
	{
		ACL::create_token( 'acltest', 'A test ACL permission', 'Administration' );
		$this->acl_user_alice->grant( 'acltest', 'full' );
		$this->assert_true(
			$this->acl_user_alice->can( 'acltest', 'full' ),
			'Could not grant acltest permission to user.'
		);

		$this->acl_user_alice->revoke( 'acltest' );

		// check that members of a group inherit that group's permissions
		$this->acl_group->grant( 'acltest', 'full' );
		$this->assert_true(
			$this->acl_user_alice->can( 'acltest', 'full' ),
			'User did not inherit group permissions.'
		);
		ACL::destroy_token( 'acltest' );
	}

	/** TODO write test_post_permissions() to verify that sensible default
	 * Tests permission related aspects of the Posts class
	 */
	public function test_post_permissions()
	{
		$this->mark_test_incomplete();
	}

	/**
	 * function test_admin_access
	 *
	 **/
	public function test_admin_access()
	{
		// Add acl-alice to the admin group
		//(which has been granted admin priviliges in installhandler).
		$this->acl_user_alice->add_to_group( 'admin' );
		$admin_group = UserGroup::get_by_name('admin');
		if ( $admin_group instanceOf UserGroup ) {
			$admin_group->update();
		}

		$this->assert_true(
			$this->acl_user_alice->can( 'admin' ),
			'Admin user does not have admin permission.'
		);

		$this->assert_false(
			$this->acl_user_bob->can( 'admin' ),
			'Unpriviliged user has admin permission.'
		);

	}
}

include_once dirname(__FILE__) . '/../bootstrap.php';
//ACLTest::run_one( 'ACLTest' );

?>
