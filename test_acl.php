<?php

include 'bootstrap.php';

/**
 * class ACLTest
 * This class tests aspects of the ACL system
 *
 **/
class ACLTest extends UnitTestCase
{
	private $acl_group;
	private $acl_user_alice;
	private $acl_user_bob;
	
	function setup()
	{
		// create test group and user
		$this->acl_group = UserGroup::create( array( 'name' => 'acltest-group' ) );
		$this->acl_user_alice = User::create( array( 'username' => 'acl-alice' ) );
		$this->acl_user_bob = User::create( array( 'username' => 'acl-bob' ) );
		$this->acl_group->add( 'acl-alice' );
		$this->acl_group->add( 'acl-bob' );
	}
	
	function test_group_permissions()
	{
		ACL::create_permission( 'acltest', 'A test ACL permission' );

		$this->assert_true(
			ACL::token_exists( 'acltest' ),
			'Could not create acltest permission.'
		);
		
		$token_id = ACL::token_id( 'acltest' );

		ACL::grant_group( $this->acl_group->id, $token_id, 'full' );
		$this->assert_true(
			$this->acl_group->can( 'acltest', 'full' ),
			'Could not grant acltest permission to acltest-group.'
		);
		
		ACL::revoke_group_permission( $this->acl_group->id, $token_id );
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
		
		// full > read/write
		$this->assert_true(
			$this->acl_group->can( 'acltest', 'read' ),
			"Group with 'full' acltest permission cannot not 'read'."
		);
		$this->assert_true(
			$this->acl_group->can( 'acltest', 'write' ),
			"Group with 'full' acltest permission cannot 'write'."
		);
	}
	
	function test_user_permissions()
	{
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
			'Users do not inherit group permissions.'
		);
	}
	
	/** TODO write test_post_permissions() to verify that sensible default
	 * permissions are attached to new posts
	 */
	function test_post_permissions()
	{

	}

	/**
	 * function test_admin_access
	 * Tests permission related aspects of the Posts class
	 *
	 **/
	function test_admin_access()
	{
		// Add acl-alice to the admin group
		//(which has been granted admin priviliges in installhandler).
		$this->acl_user_alice->add_to_group( 'admin' );

		$this->assert_true(
			$this->acl_user_alice->can( 'admin' ),
			'Admin user does not have admin permission.'
		);

		$this->assert_false(
			$this->acl_user_bob->can( 'admin' ),
			'Unpriviliged user has admin permission.'
		);

	}
	
	function teardown()
	{
		$this->acl_group->delete();
		$this->acl_user_alice->delete();
		$this->acl_user_bob->delete();
	}

}

ACLTest::run_one('ACLTest');


?>
