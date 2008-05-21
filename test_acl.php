<?php

include 'bootstrap.php';

/**
 * class ACLTest
 * This class tests aspects of the ACL system
 *
 **/
class ACLTest extends UnitTestCase
{

	/**
	 * function test_admin_access
	 * Tests permission related aspects of the Posts class
	 *
	 **/
	function test_admin_access()
	{
		// Create an a user and add them to the admin group
    //(which has been granted admin priviliges in installhandler).
		$admin= User::create('username=adminuser&email=test@example.com&password=test');
		$admin->add_to_group( 'admin' );

		$this->assert_true(
			$admin->can( 'admin' ),
			'Admin user does not have admin permission.'
		);

		$user= User::create('username=normaluser&email=test@example.com&password=test');
		$this->assert_false(
			$user->can( 'admin' ),
			'Unpriviliged user has admin permission.'
		);

	}

}

ACLTest::run_one('ACLTest');


?>
