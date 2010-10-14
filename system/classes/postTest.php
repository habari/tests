<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

class system_classes_PostTest extends PHPUnit_Framework_TestCase
{
	protected $user;

	protected function setUp()
	{
		set_time_limit(0);

		$user = User::get_by_name( 'posts_test' );
		if ( !$user ) {
			$user = User::create(array (
				'username'=>'posts_test',
				'email'=>'posts_test@example.com',
				'password'=>md5('q' . rand(0,65535)),
			));
		}
		$this->user = $user;

	}

	protected function tearDown()
	{
		$posts = Posts::get( array('user_id' => $this->user->id ));
		foreach ( $posts as $post ) {
			$post->delete();
		}
		$this->user->delete();
		unset($this->user);
	}

	public function test_create_post()
	{
		$tags = array('one', 'two', 'THREE');
		$params = array(
			'title' => 'A post title',
			'content' => 'Some great content. Really.',
			'user_id' => $this->user->id,
			'status' => Post::status('published'),
			'content_type' => Post::type('entry'),
			'tags' => 'one, two, THREE',
			'pubdate' => HabariDateTime::date_create( time() ),
		);
		$post = Post::create($params);

		$this->assertType('Post', $post, 'Post should be created.');

		// Check the post's id is set.
		$this->assertGreaterThan(0, (int)$post->id, 'The Post id should be greater than zero');

		// Check the post's tags are usable.
		$this->assertType('Tags', $post->tags, 'The Post\'s tags should be of type Tags');
		$this->assertEquals(count($post->tags), count($tags), 'All tags should have been created.');
		foreach ( $post->tags as $tag ) {
			$this->assertType('Tag', $tag, 'All the tags should be of type Tag');
		}

	}

}

?>
