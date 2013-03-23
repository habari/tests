<?php
namespace Habari;

class UserTest extends UnitTestCase
{
	private $user;

	public function setup()
	{
		if( User::get( 'testcaseuser' ) ) {
			User::get( 'testcaseuser' )->delete();
		}
		$this->user = User::create( array( 'username' => 'testcaseuser', 'email' => 'test@example.com', 'password' => 'test') );

	}

	public function test_anonymous()
	{
		$anonymous = User::anonymous();
		$this->assert_true( $this->user instanceof User, 'Anonymous user should be a User.' );
		$this->assert_equal( 0, $anonymous->id, "Anonymous user ID should be 0." );
		$this->assert_equal( "Anonymous", $anonymous->username, "Anonymous username should be 'Anonymous'." );

		// @TODO: test action_create_anonymous_user

		unset( $anonymous );
	}

	public function test_identify()
	{
		$this->mark_test_incomplete();
	}

	public function test_create() // also tests insert()
	{
		$this->assert_true(
			$this->user instanceof User,
			'Could not create test user.'
		);

		// @TODO: test action_user_insert_allow
	}

	public function test_update()
	{
		$this->mark_test_incomplete();
	}

	public function test_delete()
	{
		$this->mark_test_incomplete();

		// @TODO: test action_user_delete_allow
	}

	public function test_remember()
	{
		$this->mark_test_incomplete();
	}

	public function test_forget()
	{
		$this->mark_test_incomplete();
	}

	public function test_authenticate()
	{
		$this->mark_test_incomplete();
	}

	public function test_get()
	{
		$this->mark_test_incomplete();
	}

	public function test_get_id()
	{
		$this->mark_test_incomplete();
	}

	public function test_count_posts()
	{
		$this->mark_test_incomplete();
	}

	public function test_commenter()
	{
		$this->mark_test_incomplete();
	}

	public function test_can()
	{
		$this->mark_test_incomplete();
	}

	public function test_can_any()
	{
		$this->mark_test_incomplete();
	}

	public function test_cannot()
	{
		$this->mark_test_incomplete();
	}

	public function test_grant()
	{
		$this->mark_test_incomplete();
	}

	public function test_deny()
	{
		$this->mark_test_incomplete();
	}

	public function test_revoke()
	{
		$this->mark_test_incomplete();
	}

	public function test_list_groups()
	{
		$this->mark_test_incomplete();
	}

	public function test_in_group()
	{
		$this->mark_test_incomplete();
	}

	public function test_add_to_group()
	{
		$this->mark_test_incomplete();
	}

	public function test_remove_from_group()
	{
		$this->mark_test_incomplete();
	}

	public function test_get_info()
	{
		$this->mark_test_incomplete();
	}

	public function test_get_url_args()
	{
		$this->mark_test_incomplete();
	}

	public function teardown()
	{
		$this->user->delete();
	}
}
?>
