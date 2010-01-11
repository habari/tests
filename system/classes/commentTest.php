<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

/**
 * Test class for Comment.
 */
class system_classes_CommentTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    Comment
	 */
	protected $comment;

	protected $post_id;
	protected $paramarray;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 */
	protected function setUp()
	{
		$this->post_id = Post::get()->id;
		$this->paramarray = array(
			'id' => 'foofoo',
			'post_id' => $this->post_id,
			'name' => 'test',
			'email' => 'test@example.org',
			'url' => 'http://example.org',
			'ip' => ip2long('127.0.0.1'),
			'content' => 'test content',
			'status' => Comment::STATUS_UNAPPROVED,
			'date' => HabariDateTime::date_create(),
			'type' => Comment::COMMENT
		);
		$this->comment = new Comment($this->paramarray);
		$this->comment->insert();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 */
	protected function tearDown()
	{
		if ( $this->comment instanceof Comment ) {
			$this->comment->delete();
		}
		unset( $this->comment );
	}

	/**
	 * Test a comment is created and all values are proper, and new id has been set new
	 */
	public function testCreate()
	{
		$comment = Comment::create($this->paramarray);
		$id = DB::last_insert_id();

		$this->assertType('Comment', $comment);
		foreach( $this->paramarray as $key => $val ) {
			switch ($key) {
				case 'id':
					$this->assertEquals($id, $comment->$key);
					break;
				default:
					$this->assertEquals($val, $comment->$key);
					break;
			}
		}
		$comment->delete();
	}

	/**
	 *
	 */
	public function testUpdate()
	{
		$this->comment->content = 'updated test content';
		$this->comment->update();

		$updated_comment = Comment::get($this->comment->id);
		$this->assertEquals($updated_comment->content, $this->comment->content);
		$updated_comment->delete();
	}

	/**
	 * make sure we get the right comment
	 */
	public function testGet()
	{
		$comment = Comment::get($this->comment->id);

		$this->assertType('Comment', $comment);
		$this->assertEquals($comment->id, $this->comment->id);
	}

	/**
	 * 
	 */
	public function testDelete()
	{
		$id = $this->comment->id;
		$this->comment->delete();

		$this->assertFalse(Comment::get($id));
	}

	/**
	 * 
	 */
	public function test__get()
	{
		$this->assertType( 'Post', $this->comment->post );
		$this->assertEquals( $this->comment->post->id, $this->post_id );

		$this->assertEquals( $this->comment->statusname, 'unapproved' );

		$this->assertEquals( $this->comment->typename, 'comment' );

		$this->assertEquals( $this->comment->editlink, URL::get('admin', "page=comment&id={$this->comment->id}") );

		$this->assertType( 'CommentInfo', $this->comment->info );
	}

	/**
	 * @todo Implement test__set().
	 */
	public function test__set() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testCommentInfo().
	 */
	public function testCommentInfo() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testList_comment_types().
	 */
	public function testList_comment_types() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testList_comment_statuses().
	 */
	public function testList_comment_statuses() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 *
	 */
	public function testStatus_action() {
		$comment_status_actions = array(
			Comment::STATUS_UNAPPROVED => _t('Unapprove'),
			Comment::STATUS_APPROVED => _t('Approve'),
			Comment::STATUS_SPAM => _t('Spam'),
		);

		foreach ( $comment_status_actions as $status => $action ) {
			$this->assertEquals( $action, Comment::status_action($status) );
		}
	}

	/**
	 * @todo Implement testStatus().
	 */
	public function testStatus() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testStatus_name().
	 */
	public function testStatus_name() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testType().
	 */
	public function testType() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testType_name().
	 */
	public function testType_name() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testContent_type().
	 */
	public function testContent_type() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testGet_access().
	 */
	public function testGet_access() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}
}
?>
