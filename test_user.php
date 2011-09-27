<?php

include 'bootstrap.php';

class UserTest extends UnitTestCase
{
	function test_createuser()
	{
		$user = User::create( array( 'username' => 'testcaseuser', 'email' => 'test@example.com', 'password' => 'test') );
		$this->assert_true(
			$user instanceof User,
			'Could not create test user.'
		);
		$user->delete();
	}
}

UserTest::run_one('UserTest');

?>
