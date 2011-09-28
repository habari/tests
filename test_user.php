<?php

include 'bootstrap.php';

class UserTest extends UnitTestCase
{
	private $user;

	public function setup()
	{
		$this->user = User::create( array( 'username' => 'testcaseuser', 'email' => 'test@example.com', 'password' => 'test') );

	}

	public function test_anonymoususer()
	{
		$this->mark_test_incomplete();
	}

	public function test_identifyuser()
	{
		$this->mark_test_incomplete();
	}

	public function test_createuser()
	{
		$this->assert_true(
			$this->user instanceof User,
			'Could not create test user.'
		);

		// @TODO: test action_user_insert_allow
	}

	public function test_updateuser()
	{
		$this->mark_test_incomplete();
	}

	public function test_deleteuser()
	{
		$this->mark_test_incomplete();

		// @TODO: test action_user_delete_allow
	}

	public function test_rememberuser()
	{
		$this->mark_test_incomplete();
	}

	public function test_forgetuser()
	{
		$this->mark_test_incomplete();
	}

	public function test_authenticateuser()
	{
		$this->mark_test_incomplete();
	}

	public function test_getuser()
	{
		$this->mark_test_incomplete();
	}

	public function test_getuserid()
	{
		$this->mark_test_incomplete();
	}

	public function test_countuserposts()
	{
		$this->mark_test_incomplete();
	}

	public function test_commenteruser()
	{
		$this->mark_test_incomplete();
	}

	public function test_usercan()
	{
		$this->mark_test_incomplete();
	}

	public function test_usercanany()
	{
		$this->mark_test_incomplete();
	}

	public function test_usercannot()
	{
		$this->mark_test_incomplete();
	}

	public function test_grantuser()
	{
		$this->mark_test_incomplete();
	}

	public function test_denyuser()
	{
		$this->mark_test_incomplete();
	}

	public function test_revokeuser()
	{
		$this->mark_test_incomplete();
	}

	public function test_listusergroups()
	{
		$this->mark_test_incomplete();
	}

	public function test_useringroup()
	{
		$this->mark_test_incomplete();
	}

	public function test_addusertogroup()
	{
		$this->mark_test_incomplete();
	}

	public function test_removeuserfromgroup()
	{
		$this->mark_test_incomplete();
	}

	public function test_getuserinfo()
	{
		$this->mark_test_incomplete();
	}

	public function test_getuserurlargs()
	{
		$this->mark_test_incomplete();
	}

	public function teardown()
	{
		$this->user->delete();
	}
}

UserTest::run_one('UserTest');

?>
