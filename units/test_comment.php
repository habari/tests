<?php
namespace Habari;

class CommentTest extends UnitTestCase
{
	protected $comment;
	protected $post_id;
	protected $paramarray;
	protected $user;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 */
	protected function module_setup()
	{
		$user = User::get_by_name( 'posts_test' );
		if ( !$user ) {
			$user = User::create( array (
				'username'=>'posts_test',
				'email'=>'posts_test@example.com',
				'password'=>md5('q' . rand( 0,65535 ) ),
			) );
		}
		$this->user = $user;
		$post = Post::create( array(
			'title' => 'Test Post',
			'content' => 'These tests expect there to be at least one post.',
			'user_id' => $user->id,
			'status' => Post::status( 'published' ),
			'content_type' => Post::type( 'entry' ),
		) );

		$this->post_id = $post->id;
		$this->paramarray = array(
			'id' => 'foofoo',
			'post_id' => $this->post_id,
			'name' => 'test',
			'email' => 'test@example.org',
			'url' => 'http://example.org',
			'ip' => ip2long('127.0.0.1'),
			'content' => 'test content',
			'status' => Comment::status( 'unapproved' ),
			'date' => DateTime::date_create(),
			'type' => Comment::type( 'comment' )
		);
		$this->comment = Comment::create( $this->paramarray );
	}

	protected function module_teardown()
	{
		if ( $this->comment instanceof Comment ) {
			$this->comment->delete();
		}
		unset( $this->comment );

		$posts = Posts::get( array( 'user_id' => $this->user->id ) );
		foreach ( $posts as $post ) {
			$post->delete();
		}
		$this->user->delete();
		unset( $this->user );

	}

	public function test_create_comment()
	{
		$comment = Comment::create( $this->paramarray );
		$id = DB::last_insert_id();

		$this->assert_type( 'Habari\Comment', $comment );
		foreach( $this->paramarray as $key => $val ) {
			switch ( $key ) {
				case 'id':
					$this->assert_equal( $id, $comment->$key );
					break;
				default:
					$this->assert_equal( $val, $comment->$key );
					break;
			}
		}
		$comment->delete();
	}

	public function test_update_comment()
	{
		$old_content = $this->comment->content;
		$this->comment->content = 'updated test content';
		$this->comment->update();

		$updated_comment = Comment::get( $this->comment->id );
		$this->assert_equal( $updated_comment->content, $this->comment->content );
		$this->comment->content = $old_content;
		$this->comment-> update();
//		$updated_comment->delete();
	}

	public function test_get_comment()
	{
		$comment = Comment::get( $this->comment->id );

		$this->assert_type( 'Habari\Comment', $comment );
		$this->assert_equal( $comment->id, $this->comment->id );
	}

	public function test_delete_comment()
	{
		$comment = Comment::create( $this->paramarray );
		$id = $comment->id;
		$comment->delete();

		$this->assert_false( Comment::get( $id ) );
	}

	public function test__get()
	{
		$this->assert_type( 'Habari\Post', $this->comment->post, 'Expected Post. Received ' . get_class( $this->comment->post ) );
		$this->assert_equal( $this->comment->post->id, $this->post_id );

		$this->assert_equal( $this->comment->statusname, 'unapproved' );

		$this->assert_equal( $this->comment->typename, 'comment' );

//		$this->assert_equal( $this->comment->editlink, URL::get( 'admin', "page=comment&id={$this->comment->id}" ), $this->comment->editlink . ' : ' . URL::get( 'admin', "page=comment&id={$this->comment->id}" ) );
		$this->assert_equal( $this->comment->editlink, URL::get( 'edit_comment', $this->comment, false ) );

		$this->assert_type( 'Habari\CommentInfo', $this->comment->info );
	}

	/**
	 * @todo Implement test__set().
	 */
	public function test__set()
	{
	}

	public function test_commentinfo()
	{
		// make sure adding info to comment works
		$this->comment->info->test = 'test';
		$this->assert_equal( 'test', $this->comment->info->test );
		$this->comment->update();
		$test_comment = Comment::get( $this->comment->id );
		$this->assert_equal( $this->comment->info->test, $test_comment->info->test );
		unset( $test_comment );

		// make sure construction works with info
		$new_comment = new Comment();
		$this->assert_type( 'Habari\CommentInfo', $new_comment->info );
		$this->assert_false( $new_comment->info->is_key_set() );
		$new_comment->info->test = 'test';
		$new_comment->insert();
		$this->assert_true( $new_comment->info->is_key_set() );
		$test_comment = Comment::get( $new_comment->id );
		$this->assert_equal( $new_comment->info->test, $test_comment->info->test );
		$new_comment->delete();
		unset( $test_comment );
	}

	/**
	 * @todo Implement test_list_comment_types().
	 */
	public function test_list_comment_types()
	{
	}

	/**
	 * @todo Implement test_list_comment_statuses().
	 */
	public function test_list_comment_statuses()
	{
	}

	public function test_status_action()
	{
		$comment_status_actions = array(
			Comment::status( 'unapproved' ) => _t( 'Unapprove' ),
			Comment::status( 'approved' ) => _t( 'Approve' ),
			Comment::status( 'spam' ) => _t( 'Spam' ),
		);

		foreach ( $comment_status_actions as $status => $action ) {
			$this->assert_equal( $action, Comment::status_action( $status ) );
		}
	}

	/**
	 * @todo Implement test_status().
	 */
	public function test_status()
	{
	}

	/**
	 * @todo Implement test_status_name().
	 */
	public function test_status_name()
	{
	}

	/**
	 * @todo Implement test_type().
	 */
	public function test_type()
	{
	}

	/**
	 * @todo Implement test_type_name().
	 */
	public function test_type_name()
	{
	}

	/**
	 * @todo Implement test_content_type().
	 */
	public function test_content_type()
	{
	}

	/**
	 * @todo Implement test_get_access().
	 */
	public function test_get_access()
	{
	}
}
?>
