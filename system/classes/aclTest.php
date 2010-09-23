<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

class system_classes_aclTest extends PHPUnit_Framework_TestCase
{
	private $acl_group;
	private $acl_user_alice;
	private $acl_user_bob;
	
	public function setup()
	{
		/*
		// create test group and user
		$this->acl_group = UserGroup::create( array( 'name' => 'acltest-group' ) );
		$this->acl_user_alice = User::create( array( 'username' => 'acl-alice' ) );
		$this->acl_user_bob = User::create( array( 'username' => 'acl-bob' ) );
		$this->acl_group->add( 'acl-alice' );
		$this->acl_group->add( 'acl-bob' );
		$this->acl_group->update();
		*/
	}
	
	public function test_group_permissions()
	{
		$this->markTestSkipped('Test does not match class code; needs updating');

		ACL::create_permission( 'acltest', 'A test ACL permission' );

		$this->assertTrue(
			ACL::token_exists( 'acltest' ),
			'Could not create acltest permission.'
		);
		
		$token_id = ACL::token_id( 'acltest' );

		ACL::grant_group( $this->acl_group->id, $token_id, 'full' );
		$this->assertTrue(
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
		$this->assertTrue(
			$this->acl_group->can( 'acltest', 'full' ),
			'Could not grant acltest permission to acltest-group through UserGroup call.'
		);
		
		// full > read/write
		$this->assertTrue(
			$this->acl_group->can( 'acltest', 'read' ),
			"Group with 'full' acltest permission cannot 'read'."
		);
		$this->assertTrue(
			$this->acl_group->can( 'acltest', 'write' ),
			"Group with 'full' acltest permission cannot 'write'."
		);
	}
	
	public function test_user_permissions()
	{
		$this->markTestSkipped('Test does not match class code; needs updating');
		$this->acl_user_alice->grant( 'acltest', 'full' );
		$this->assertTrue(
			$this->acl_user_alice->can( 'acltest', 'full' ),
			'Could not grant acltest permission to user.'
		);
		
		$this->acl_user_alice->revoke( 'acltest' );
		
		// check that members of a group inherit that group's permissions
		$this->acl_group->grant( 'acltest', 'full' );
		$this->assertTrue(
			$this->acl_user_alice->can( 'acltest', 'full' ),
			'Users do not inherit group permissions.'
		);
	}
	
	/** TODO write test_post_permissions() to verify that sensible default
	 * permissions are attached to new posts
	 */
	public function test_post_permissions()
	{

	}

	/**
	 * function test_admin_access
	 * Tests permission related aspects of the Posts class
	 *
	 **/
	public function test_admin_access()
	{
		$this->markTestSkipped('Test does not match class code; needs updating');
		// Add acl-alice to the admin group
		//(which has been granted admin priviliges in installhandler).
		$this->acl_user_alice->add_to_group( 'admin' );
		$admin_group = UserGroup::get_by_name('admin');
		if ( $admin_group instanceOf UserGroup ) {
			$admin_group->update();
		}

		$this->assertTrue(
			$this->acl_user_alice->can( 'admin' ),
			'Admin user does not have admin permission.'
		);

		$this->assert_false(
			$this->acl_user_bob->can( 'admin' ),
			'Unpriviliged user has admin permission.'
		);

	}
	
	public function teardown()
	{
		/*
		$this->acl_group->delete();
		$this->acl_user_alice->delete();
		$this->acl_user_bob->delete();
		*/
	}
	
}
?>