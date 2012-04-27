<?php

	/**
	 * Tests for the Posts class
	 *
	 * @todo Test CUD.
	 * @todo Test the following parameters to Posts::get().
	 * - offset => amount by which to offset returned posts, used in conjunction with limit
	 * - page => the 'page' of posts to return when paging, sets the appropriate offset
	 * - count => return the number of posts that would be returned by this request
	 * - add_select => an array of clauses to be added to the generated SELECT clause.
	 * - fetch_fn => the function used to fetch data, one of 'get_results', 'get_row', 'get_value', 'get_query'
	 */
class PostsTest extends UnitTestCase
{

	/**
	 * Set up for the whole test suite
	 */
	protected function module_setup()
	{
		set_time_limit(0);
		if($user = User::get_by_name( 'posts_test' )) {
			$this->user = $user;
			//$this->skip_all("User {$user->id} is required 'posts_test' user.");
		}
		else {
			$this->user = User::create(array (
				'username'=>'posts_test',
				'email'=>'posts_test@example.com',
				'password'=>md5('q' . rand( 0,65535 ) ),
			) );
		}
	}

	/**
	 * Teardown for the whole test suite
	 */
	protected function module_teardown()
	{
		$user = User::get_by_name( 'posts_test' );
		$user->delete();
	}

	/**
	 * Teardown for each test
	 */
	protected function teardown()
	{
		Posts::get(array('nolimit' => 1))->delete();
	}

	/**
	 * Get a post by a single id
	 */
	public function test_get_post_by_id()
	{
		$expected = Post::create( array(
			'title' => 'This is a Post',
			'content' => 'If this was really a post, would it have such ridiculous content?',
			'user_id' => $this->user->id,
			'status' => Post::status( 'published' ),
			'content_type' => Post::type( 'entry' ),
			'pubdate' => HabariDateTime::date_create( time() ),
		));

		$result = Posts::get( array( 'id' => $expected->id ) );

		$this->assert_true( $result instanceof Posts, 'Result should be of type Posts' );
		$this->assert_true( $result->onepost, 'A single post should be returned if a single id is passed in' );

		$result = $result[0];
		$this->assert_true( $result instanceof Post, 'Items should be of type Post' );
		$this->assert_equal( $result->id, $expected->id, 'id of returned Post should be the one we asked for' );
	}

	/**
	 * Get a posts by multiple ids
	 */
	public function test_get_posts_by_ids()
	{
		$expected = array();
		$expected[] = Post::create( array(
			'title' => 'This is a Post',
			'content' => 'If this was really a post, would it have such ridiculous content?',
			'user_id' => $this->user->id,
			'status' => Post::status( 'published' ),
			'content_type' => Post::type( 'entry' ),
			'pubdate' => HabariDateTime::date_create( time() ),
		));
		$expected[] = Post::create( array(
			'title' => 'I am not a Post',
			'content' => 'But I\'m certainly not a pipe',
			'user_id' => $this->user->id,
			'status' => Post::status( 'published' ),
			'content_type' => Post::type( 'entry' ),
			'pubdate' => HabariDateTime::date_create( time() ),
		));

		$ids = array();
		foreach ( $expected as $post ) $ids[] = $post->id;

		$result = Posts::get( array('id' => $ids ) );

		$this->assert_true( $result instanceof Posts, 'Result should be of type Posts' );
		// @todo This currently isn't true, because the options limit is respected. Should it be?
		//$this->assert_equal( count( $result ), count( $expected ), 'The number of posts we asked for should be returned' );

		foreach ( $result as $r ) {
			$this->assert_true( $r instanceof Post, 'Items should be of type Post' );
			$this->assert_true( in_array( $r->id, $ids ), 'id of returned Post should be in the list of the ones we asked for' );
		}

	}

	/**
	 * Get posts that don't have a particular id
	 */
	public function test_get_post_not_id()
	{
		$expected = Post::create(array(
			'title' => 'This is a Post',
			'content' => 'If this was really a post, would it have such ridiculous content?',
			'user_id' => $this->user->id,
			'status' => Post::status('published' ),
			'content_type' => Post::type('entry'),
			'pubdate' => HabariDateTime::date_create(time()),
		));
		$unexpected = Post::create(array(
			'title' => 'I am not a Post',
			'content' => 'But I\'m certainly not a pipe',
			'user_id' => $this->user->id,
			'status' => Post::status('published'),
			'content_type' => Post::type('entry'),
			'pubdate' => HabariDateTime::date_create(time()),
		));

		$result = Posts::get(array('not:id' => $unexpected->id));

		$this->assert_true($result instanceof Posts, 'Result should be of type Posts');
		$result = $result[0];
		$this->assert_true($result instanceof Post, 'Items should be of type Post');
		$this->assert_equal($expected->id, $result->id, 'id of returned Post should the one we didn\'t ask to exclude');
	}

	/**
	 * Get posts that don't belong to a set of ids
	 */
	public function test_get_post_not_ids()
	{
		$expected = Post::create(array(
			'title' => 'This is a Post',
			'content' => 'If this was really a post, would it have such ridiculous content?',
			'user_id' => $this->user->id,
			'status' => Post::status('published' ),
			'content_type' => Post::type('entry'),
			'pubdate' => HabariDateTime::date_create(time()),
		));
		$unexpected = array();
		$unexpected[] = Post::create(array(
			'title' => 'I am not a Post',
			'content' => 'But I\'m certainly not a pipe',
			'user_id' => $this->user->id,
			'status' => Post::status('published'),
			'content_type' => Post::type('entry'),
			'pubdate' => HabariDateTime::date_create(time()),
		));
		$unexpected[] = Post::create(array(
			'title' => 'Chili',
			'content' => 'The Breakfast of Champions',
			'user_id' => $this->user->id,
			'status' => Post::status('published'),
			'content_type' => Post::type('entry'),
			'pubdate' => HabariDateTime::date_create(time()),
		));

		$ids = array();
		foreach ( $unexpected as $post ) $ids[] = $post->id;

		$result = Posts::get( array('not:id' => $ids ) );

		$this->assert_true($result instanceof Posts, 'Result should be of type Posts');

		foreach ( $result as $r ) {
			$this->assert_true( $r instanceof Post, 'Items should be of type Post' );
			$this->assert_false( in_array( $r->id, $ids ), 'id of returned Post should not be in the list of the ones excluded' );
		}
	}

	/**
	 * Get a post by a single slug
	 */
	public function test_get_post_by_slug()
	{
		$expected = Post::create( array(
			'title' => 'This is a Post',
			'content' => 'If this was really a post, would it have such rslugiculous content?',
			'user_id' => $this->user->id,
			'status' => Post::status( 'published' ),
			'content_type' => Post::type( 'entry' ),
			'pubdate' => HabariDateTime::date_create( time() ),
		));

		$result = Posts::get( array( 'slug' => $expected->slug ) );

		$this->assert_true( $result instanceof Posts, 'Result should be of type Posts' );
		$this->assert_true( $result->onepost, 'A single post should be returned if a single slug is passed in' );

		$result = $result[0];
		$this->assert_true( $result instanceof Post, 'Items should be of type Post' );
		$this->assert_equal( $result->slug, $expected->slug, 'slug of returned Post should be the one we asked for' );
	}

	/**
	 * Get a posts by multiple slugs
	 */
	public function test_get_posts_by_slugs()
	{
		$expected = array();
		$expected[] = Post::create( array(
			'title' => 'This is a Post',
			'content' => 'If this was really a post, would it have such rslugiculous content?',
			'user_id' => $this->user->id,
			'status' => Post::status( 'published' ),
			'content_type' => Post::type( 'entry' ),
			'pubdate' => HabariDateTime::date_create( time() ),
		));
		$expected[] = Post::create( array(
			'title' => 'I am not a Post',
			'content' => 'But I\'m certainly not a pipe',
			'user_id' => $this->user->id,
			'status' => Post::status( 'published' ),
			'content_type' => Post::type( 'entry' ),
			'pubdate' => HabariDateTime::date_create( time() ),
		));

		$slugs = array();
		foreach ( $expected as $post ) $slugs[] = $post->slug;

		$result = Posts::get( array('slug' => $slugs ) );

		$this->assert_true( $result instanceof Posts, 'Result should be of type Posts' );
		// @todo This currently isn't true, because the options limit is respected. Should it be?
		//$this->assert_equal( count( $result ), count( $expected ), 'The number of posts we asked for should be returned' );

		foreach ( $result as $r ) {
			$this->assert_true( $r instanceof Post, 'Items should be of type Post' );
			$this->assert_true( in_array( $r->slug, $slugs ), 'slug of returned Post should be in the list of the ones we asked for' );
		}

	}

	/**
	 * Get posts that don't have a particular slug
	 */
	public function test_get_post_not_slug()
	{
		$expected = Post::create(array(
			'title' => 'This is a Post',
			'content' => 'If this was really a post, would it have such ridiculous content?',
			'user_id' => $this->user->id,
			'status' => Post::status('published' ),
			'content_type' => Post::type('entry'),
			'pubdate' => HabariDateTime::date_create(time()),
		));
		$unexpected = Post::create(array(
			'title' => 'I am not a Post',
			'content' => 'But I\'m certainly not a pipe',
			'user_id' => $this->user->id,
			'status' => Post::status('published'),
			'content_type' => Post::type('entry'),
			'pubdate' => HabariDateTime::date_create(time()),
		));

		$result = Posts::get(array('not:slug' => $unexpected->slug));

		$this->assert_true($result instanceof Posts, 'Result should be of type Posts');
		$result = $result[0];
		$this->assert_true($result instanceof Post, 'Items should be of type Post');
		$this->assert_equal($expected->slug, $result->slug, 'slug of returned Post should the one we didn\'t ask to exclude');
	}

	/**
	 * Get posts that don't belong to a set of slugs
	 */
	public function test_get_post_not_slugs()
	{
		$expected = Post::create(array(
			'title' => 'This is a Post',
			'content' => 'If this was really a post, would it have such ridiculous content?',
			'user_id' => $this->user->id,
			'status' => Post::status('published' ),
			'content_type' => Post::type('entry'),
			'pubdate' => HabariDateTime::date_create(time()),
		));
		$unexpected = array();
		$unexpected[] = Post::create(array(
			'title' => 'I am not a Post',
			'content' => 'But I\'m certainly not a pipe',
			'user_id' => $this->user->id,
			'status' => Post::status('published'),
			'content_type' => Post::type('entry'),
			'pubdate' => HabariDateTime::date_create(time()),
		));
		$unexpected[] = Post::create(array(
			'title' => 'Chili',
			'content' => 'The Breakfast of Champions',
			'user_id' => $this->user->id,
			'status' => Post::status('published'),
			'content_type' => Post::type('entry'),
			'pubdate' => HabariDateTime::date_create(time()),
		));

		$slugs = array();
		foreach ( $unexpected as $post ) $slugs[] = $post->slug;

		$result = Posts::get( array('not:slug' => $slugs ) );

		$this->assert_true($result instanceof Posts, 'Result should be of type Posts');
		foreach ( $result as $r ) {
			$this->assert_true( $r instanceof Post, 'Items should be of type Post' );
			$this->assert_false( in_array( $r->slug, $slugs ), 'slug of returned Post should not be in the list of the ones excluded' );
		}
	}

	/**
	 * Get by single content type
	 */
	public function test_get_posts_by_content_type()
	{
		$expected = Post::create( array(
			'title' => 'This is a Post',
			'content' => 'If this was really a post, would it have such rslugiculous content?',
			'user_id' => $this->user->id,
			'status' => Post::status( 'published' ),
			'content_type' => Post::type( 'entry' ),
			'pubdate' => HabariDateTime::date_create( time() ),
		));
		$unexpected = Post::create( array(
			'title' => 'I am not a Post',
			'content' => 'But I\'m certainly not a pipe',
			'user_id' => $this->user->id,
			'status' => Post::status( 'published' ),
			'content_type' => Post::type( 'page' ),
			'pubdate' => HabariDateTime::date_create( time() ),
		));

		$result = Posts::get( array( 'content_type' => Post::type( 'entry' ) ) );

		$this->assert_true( $result instanceof Posts, 'Result should be of type Posts' );
		$this->assert_equal( 1, count($result), 'The expected number of posts should be returned' );

		foreach ( $result as $r ) {
			$this->assert_true( $r instanceof Post, 'Items should be of type Post' );
			$this->assert_equal( $r->content_type, Post::type( 'entry' ), 'Returned posts should be of the requested content type' );
		}
	}

	/**
	 * Get by an array of content types
	 */
	public function test_get_posts_by_content_types()
	{
		$expected = array();
		$expected[] = Post::create( array(
			'title' => 'This is a Post',
			'content' => 'If this was really a post, would it have such ridiculous content?',
			'user_id' => $this->user->id,
			'status' => Post::status( 'published' ),
			'content_type' => Post::type( 'entry' ),
			'pubdate' => HabariDateTime::date_create( time() ),
		));
		$expected[] = Post::create( array(
			'title' => 'I am not a Post',
			'content' => 'But I\'m certainly not a pipe',
			'user_id' => $this->user->id,
			'status' => Post::status( 'published' ),
			'content_type' => Post::type( 'page' ),
			'pubdate' => HabariDateTime::date_create( time() ),
		));

		$types = array();
		foreach ( $expected as $post ) $types[] = $post->content_type;

		$result = Posts::get(array('content_type' => $types));

		$this->assert_true($result instanceof Posts, 'Result should be of type Posts');
		$this->assert_equal(count($expected), count($result), 'The number of posts we asked for should be returned');

		foreach ( $result as $r ) {
			$this->assert_true($r instanceof Post, 'Items should be of type Post');
			$this->assert_true(in_array($r->content_type, $types), 'Returned posts should be of the requested content type');
		}
	}

	/**
	 * Get posts that don't have a particular content type
	 */
	public function test_get_post_not_content_type()
	{
		$expected = Post::create(array(
			'title' => 'This is a Post',
			'content' => 'If this was really a post, would it have such ridiculous content?',
			'user_id' => $this->user->id,
			'status' => Post::status('published' ),
			'content_type' => Post::type('entry'),
			'pubdate' => HabariDateTime::date_create(time()),
		));
		$unexpected = Post::create(array(
			'title' => 'I am not a Post',
			'content' => 'But I\'m certainly not a pipe',
			'user_id' => $this->user->id,
			'status' => Post::status('published'),
			'content_type' => Post::type('page'),
			'pubdate' => HabariDateTime::date_create(time()),
		));

		$result = Posts::get(array('not:content_type' => $unexpected->content_type));

		$this->assert_true($result instanceof Posts, 'Result should be of type Posts', $result);
		$result = $result[0];
		$this->assert_true($result instanceof Post, 'Items should be of type Post');
		$this->assert_equal($expected->content_type, $result->content_type, 'Returned posts should be of the requested content type');
	}

	/**
	 * Get posts that don't belong to a set of content types
	 */
	public function test_get_post_not_content_types()
	{
		$unexpected = array();
		$unexpected[] = Post::create(array(
			'title' => 'This is a Post',
			'content' => 'If this was really a post, would it have such ridiculous content?',
			'user_id' => $this->user->id,
			'status' => Post::status('published' ),
			'content_type' => Post::type('entry'),
			'pubdate' => HabariDateTime::date_create(time()),
		));
		$unexpected[] = Post::create(array(
			'title' => 'I am not a Post',
			'content' => 'But I\'m certainly not a pipe',
			'user_id' => $this->user->id,
			'status' => Post::status('published'),
			'content_type' => Post::type('page'),
			'pubdate' => HabariDateTime::date_create(time()),
		));

		$types = array();
		foreach ( $unexpected as $post ) $types[] = $post->content_type;

		$result = Posts::get(array('not:content_type' => $types));

		$this->assert_equal(0, count($result), 'No posts should be returned');
	}

		// Get any content type
		// @todo How do we test this?

	/**
	 * Get by single status
	 */
	public function test_get_posts_by_status()
	{
		$expected = Post::create( array(
			'title' => 'This is a Post',
			'content' => 'If this was really a post, would it have such rslugiculous content?',
			'user_id' => $this->user->id,
			'status' => Post::status( 'published' ),
			'content_type' => Post::type( 'entry' ),
			'pubdate' => HabariDateTime::date_create( time() ),
		));
		$unexpected = Post::create( array(
			'title' => 'I am not a Post',
			'content' => 'But I\'m certainly not a pipe',
			'user_id' => $this->user->id,
			'status' => Post::status( 'draft' ),
			'content_type' => Post::type( 'entry' ),
			'pubdate' => HabariDateTime::date_create( time() ),
		));

		$result = Posts::get( array( 'status' => Post::status( 'draft' ) ) );

		$this->assert_true( $result instanceof Posts, 'Result should be of type Posts' );
		$this->assert_equal( 1, count($result), 'The expected number of posts should be returned' );

		foreach ( $result as $r ) {
			$this->assert_true( $r instanceof Post, 'Items should be of type Post' );
			$this->assert_equal( $r->status, Post::status( 'draft' ), 'Returned posts should be of the requested status' );
		}
	}

	/**
	 * Get by an array of statuses
	 */
	public function test_get_posts_by_statuses()
	{
		$expected = array();
		$expected[] = Post::create( array(
			'title' => 'This is a Post',
			'content' => 'If this was really a post, would it have such rslugiculous content?',
			'user_id' => $this->user->id,
			'status' => Post::status( 'published' ),
			'content_type' => Post::type( 'entry' ),
			'pubdate' => HabariDateTime::date_create( time() ),
		));
		$expected[] = Post::create( array(
			'title' => 'I am not a Post',
			'content' => 'But I\'m certainly not a pipe',
			'user_id' => $this->user->id,
			'status' => Post::status( 'draft' ),
			'content_type' => Post::type( 'entry' ),
			'pubdate' => HabariDateTime::date_create( time() ),
		));

		$statuses = array();
		foreach ( $expected as $post ) $statuses[] = $post->status;

		$result = Posts::get( array( 'status' => $statuses ) );

		$this->assert_true( $result instanceof Posts, 'Result should be of type Posts' );
		$this->assert_equal( count($expected), count($result), 'The expected number of posts should be returned' );

		foreach ( $result as $r ) {
			$this->assert_true( $r instanceof Post, 'Items should be of type Post' );
			$this->assert_true( in_array( $r->content_type, $statuses ), 'Returned posts should be of the requested status' );
		}
	}

//		// Get any status
//		// @todo How do we test this?

	/**
	 * Get by user id
	 */
	public function test_get_posts_by_user_id()
	{
		// Create another user and a post
		$decoy = User::create(array(
			'username'=>'decoy',
			'email'=>'decoy@example.com',
			'password'=>md5('q' . rand( 0,65535 )),
		));
		$expected = Post::create(array(
			'title' => 'This is a Post',
			'content' => 'If this was really a post, would it have such rslugiculous content?',
			'user_id' => $this->user->id,
			'status' => Post::status('published'),
			'content_type' => Post::type('entry'),
			'pubdate' => HabariDateTime::date_create(time()),
		));
		$unexpected = Post::create(array(
			'title' => 'I am not a Post',
			'content' => 'But I\'m certainly not a pipe',
			'user_id' => $decoy->id,
			'status' => Post::status('published'),
			'content_type' => Post::type('entry'),
			'pubdate' => HabariDateTime::date_create(time()),
		));

		$result = Posts::get( array( 'user_id' => $this->user->id ) );

		$this->assert_true( $result instanceof Posts, 'Result should be of type Posts' );
		$this->assert_equal( 1, count($result), 'The expected number of posts should be returned' );

		foreach ( $result as $r ) {
			$this->assert_true( $r instanceof Post, 'Items should be of type Post' );
			$this->assert_equal( $r->author->id, $this->user->id, 'Returned posts should belong to the expected user' );
		}

		$decoy->delete();
	}

	/**
	 * Get by an array of user ids
	 */
	public function test_get_posts_by_user_ids()
	{
		// Create another user and a post
		$decoy = User::create(array(
			'username'=>'decoy',
			'email'=>'decoy@example.com',
			'password'=>md5('q' . rand( 0,65535 )),
		));
		$expected = array();
		$expected[] = Post::create(array(
			'title' => 'This is a Post',
			'content' => 'If this was really a post, would it have such rslugiculous content?',
			'user_id' => $this->user->id,
			'status' => Post::status('published'),
			'content_type' => Post::type('entry'),
			'pubdate' => HabariDateTime::date_create(time()),
		));
		$expected[] = Post::create(array(
			'title' => 'I am not a Post',
			'content' => 'But I\'m certainly not a pipe',
			'user_id' => $decoy->id,
			'status' => Post::status('published'),
			'content_type' => Post::type('entry'),
			'pubdate' => HabariDateTime::date_create(time()),
		));

		$result = Posts::get(array('user_id' => array($this->user->id, $decoy->id)));

		$this->assert_true($result instanceof Posts, 'Result should be of type Posts');
		$this->assert_equal(count($expected), count($result), 'The expected number of posts should be returned');

		foreach ( $result as $r ) {
			$this->assert_true($r instanceof Post, 'Items should be of type Post');
			$this->assert_true(in_array($r->author->id, array($this->user->id, $decoy->id)), 'Returned posts should belong to the expected user');
		}

		$decoy->delete();
	}

	/**
	 * Get posts by date
	 * - year => a year of post publication
	 * - month => a month of post publication, ignored if year is not specified
	 * - day => a day of post publication, ignored if month and year are not specified
	 * - before => a timestamp to compare post publication dates
	 * - after => a timestamp to compare post publication dates
	 * - month_cts => return the number of posts published in each month
	 */
	public function test_get_posts_by_date()
	{
		$this->mark_test_incomplete();
	}

	/**
	 * Get posts by tag
	 */
	public function test_get_posts_by_tag()
	{
		$this->mark_test_incomplete();
//		tags:term_display
//		tags:term
//		tags:all:term_display
//		tags:all:term
//		tags:not:term_display
//		tags:not:term
	}

	/**
	 * Get posts by vocabulary
	 * - vocabulary => an array describing parameters related to vocabularies attached to posts. This can be one of two forms:
	 *   - object-based, in which an array of Term objects are passed
	 *     - any => posts associated with any of the terms are returned
	 *     - all => posts associated with all of the terms are returned
	 *     - not => posts associated with none of the terms are returned
	 *   - property-based, in which an array of vocabulary names and associated fields are passed
	 *     - vocabulary_name:term => a vocabulary name and term slug pair or array of vocabulary name and term slug pairs, any of which can be associated with the posts
	 *     - vocabulary_name:term_display => a vocabulary name and term display pair or array of vocabulary name and term display pairs, any of which can be associated with the posts
	 *     - vocabulary_name:not:term => a vocabulary name and term slug pair or array of vocabulary name and term slug pairs, none of which can be associated with the posts
	 *     - vocabulary_name:not:term_display => a vocabulary name and term display pair or array of vocabulary name and term display pairs, none of which can be associated with the posts
	 *     - vocabulary_name:all:term => a vocabulary name and term slug pair or array of vocabulary name and term slug pairs, all of which must be associated with the posts
	 *     - vocabulary_name:all:term_display => a vocabulary name and term display pair or array of vocabulary name and term display pairs, all of which must be associated with the posts
	 */
	public function test_get_posts_by_vocabulary()
	{
		// setup
		// create a couple Vocabularies and Terms
		if( Vocabulary::get( "fizz" ) ) {
			Vocabulary::get( "fizz" )->delete();
		}
		$fizz = Vocabulary::create( array(
			'name' => 'fizz',
			'description' => 'Vocabulary for Posts testing.',
			'features' => array( 'free' )
		));

		$fizz_term = new Term( array( 'term' => 'fizz', 'term_display' => 'Fizz' ) );
		$fizz->add_term( $fizz_term );

		if( Vocabulary::get( "buzz" ) ) {
			Vocabulary::get( "buzz" )->delete();
		}
		$buzz = Vocabulary::create( array(
			'name' => 'buzz',
			'description' => 'Another Vocabulary for Posts testing.',
			'features' => array( 'free' )
		));

		$buzz_term = new Term( array( 'term' => 'buzz', 'term_display' => 'Buzz' ) );
		$buzz->add_term( $buzz_term );

		// create some Posts and associate them with the two Vocabularies
		for( $i = 1; $i <= 20; $i++ ) {
			$post = Post::create( array(
				'title' => "Test Post $i",
				'content' => 'If this were really a post...',
				'user_id' => $this->user->id,
				'status' => Post::status( 'published' ),
				'content_type' => Post::type( 'entry' ),
				'pubdate' => HabariDateTime::date_create( time() ),
			));
			$post->info->testing_vocab = 1;
			$post->info->i = $i;
			$post->info->commit();

			if( $i % 3 === 0 ) {
				$fizz->set_object_terms( 'post', $post->id, array( $fizz_term->term_display ) );
			}
			if( $i % 5 === 0 ) {
				$buzz->set_object_terms( 'post', $post->id, array( $buzz_term->term_display ) );
			}
		}

		// Object-based syntax

		$total_posts = Posts::count_total();
		$any_vocab_posts = Posts::get( array( 'ignore_permissions' => true, 'vocabulary' => array( "any" => array( $fizz_term, $buzz_term ) ), 'nolimit' => 1, 'count' => 1 ) );
		$all_vocab_posts = Posts::get( array( 'ignore_permissions' => true, 'vocabulary' => array( "all" => array( $fizz_term, $buzz_term ) ), 'nolimit' => 1, 'count' => 1 ) );
		$not_vocab_posts = Posts::get( array( 'ignore_permissions' => true, 'vocabulary' => array( "not" => array( $fizz_term, $buzz_term ) ), 'nolimit' => 1, 'count' => 1 ) );

		$this->assert_true( $any_vocab_posts > $all_vocab_posts, "Any: $any_vocab_posts should be greater than All: $all_vocab_posts" );
		$this->assert_true( $not_vocab_posts > $all_vocab_posts, "Not: $not_vocab_posts should be greater than All: $all_vocab_posts" );
		$this->assert_true( $not_vocab_posts < $total_posts, "Not: $not_vocab_posts should be less than Total: $total_posts" );
		$this->assert_equal( $any_vocab_posts + $not_vocab_posts, $total_posts, "Any: $any_vocab_posts plus Not: $not_vocab_posts should equal Total: $total_posts" );

		// Property-based syntax

		$any_vocab_posts = Posts::get( array( 'ignore_permissions' => true, 'vocabulary' => array( "fizz:term" => "fizz", "buzz:term" => "buzz" ), 'nolimit' => 1, 'count' => 1 ) );
		$all_vocab_posts = Posts::get( array( 'ignore_permissions' => true, 'vocabulary' => array( "fizz:all:term" => "fizz", "buzz:all:term" => "buzz" ), 'nolimit' => 1, 'count' => 1 ) );
		$not_vocab_posts = Posts::get( array( 'ignore_permissions' => true, 'vocabulary' => array( "fizz:not:term" => "fizz", "buzz:not:term" => "buzz" ), 'nolimit' => 1, 'count' => 1 ) );

		$this->assert_true( $any_vocab_posts > $all_vocab_posts, "Any: $any_vocab_posts should be greater than All: $all_vocab_posts" );
		$this->assert_true( $not_vocab_posts > $all_vocab_posts, "Not: $not_vocab_posts should be greater than All: $all_vocab_posts" );
		$this->assert_true( $not_vocab_posts < $total_posts, "Not: $not_vocab_posts should be less than Total: $total_posts" );
		$this->assert_equal( $any_vocab_posts + $not_vocab_posts, $total_posts, "Any: $any_vocab_posts plus Not: $not_vocab_posts should equal Total: $total_posts" );



		// teardown
		Posts::get( array( 'ignore_permissions' => true, 'has:info' => 'testing_vocab', 'nolimit' => 1 ) )->delete();
		$fizz->delete();
		$buzz->delete();
	}

	/**
	 * Get posts by info
	 * - has:info => a post info key or array of post info keys, which should be present
	 * - all:info => a post info key and value pair or array of post info key and value pairs, which should all be present and match
	 * - not:all:info => a post info key and value pair or array of post info key and value pairs, to exclude if all are present and match
	 * - any:info => a post info key and value pair or array of post info key and value pairs, any of which can match
	 * - not:any:info => a post info key and value pair or array of post info key and value pairs, to exclude if any are present and match
	 */
	public function test_get_posts_by_info()
	{
		// setup
		$informationless_post = Post::create( array(
			'title' => 'This is a Post without information',
			'content' => 'The real point of this post is to make sure that there is at least one countable post without info for the sake of testing.',
			'user_id' => $this->user->id,
			'status' => Post::status( 'published' ),
			'content_type' => Post::type( 'entry' ),
			'pubdate' => HabariDateTime::date_create( time() ),
		));

		$seven_things = array( "one", "two", "red", "blue", "black", "old", "new" );

		// create some posts with info
		for( $i = 1; $i < 42; $i++ ) {
			$post = Post::create( array(
				'title' => 'This Post has Info',
				'content' => 'If this were really a post, would it have such useless information?',
				'user_id' => $this->user->id,
				'status' => Post::status( 'published' ),
				'content_type' => Post::type( 'entry' ),
				'pubdate' => HabariDateTime::date_create( time() ),
			));
			$post->info->testing_info = 1;
			$post->info->$seven_things[ ($i % 7) ] = 1;
			$post->info->i = $i;
			$post->info->commit();
		}

		// has:info

		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts}
				LEFT JOIN {postinfo} pi1 ON
					{posts}.id = pi1.post_id AND
					pi1.name = 'red'
					WHERE
						pi1.name <> ''
		" );
		$count_info_posts = Posts::get( array( 'ignore_permissions' => true, 'has:info' => 'testing_info', 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_not_equal( Posts::count_total(), $count_info_posts );

		$count_posts = Posts::get( array( 'ignore_permissions' => true, 'has:info' => array( 'red' ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );

		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts}
				LEFT JOIN {postinfo} pi1 ON
					{posts}.id = pi1.post_id AND
					pi1.name = 'testing_info'
				LEFT JOIN {postinfo} pi2 ON
					{posts}.id = pi2.post_id AND
					pi2.name = 'red'
					WHERE
						pi1.name <> '' OR
						pi2.name <> ''
		" );
		$count_posts = Posts::get( array( 'ignore_permissions' => true, 'has:info' => array( 'testing_info', 'red' ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );
//		$query = Posts::get( array( 'has:info' => array( 'testing_info', 'red' ), 'nolimit' => 1, 'fetch_fn' => 'get_query' ) );
//		Utils::debug( $query );die();

		// all:info
		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts}
				LEFT JOIN {postinfo} pi1 ON
					{posts}.id = pi1.post_id AND
					pi1.name = 'blue' AND pi1.value = 1
					WHERE
						pi1.name <> ''
		" );
		$count_posts = Posts::get( array( 'ignore_permissions' => true, 'all:info' => array( 'blue' => 1 ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );

		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts}
				LEFT JOIN {postinfo} pi1 ON
					{posts}.id = pi1.post_id AND
					pi1.name = 'blue' AND pi1.value = 1
				LEFT JOIN {postinfo} pi2 ON
					{posts}.id = pi2.post_id AND
					pi2.name = 'two' AND pi2.value = 1
					WHERE
						pi1.name <> '' AND
						pi2.name <> ''
		" );
		$count_posts = Posts::get( array( 'ignore_permissions' => true, 'all:info' => array( 'blue' => true, 'two' => true ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );

		// any:info
		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts}
				LEFT JOIN {postinfo} pi1 ON
					{posts}.id = pi1.post_id AND
					pi1.name = 'black' AND
					pi1.value = 1
				LEFT JOIN {postinfo} pi2 ON
					{posts}.id = pi2.post_id AND
					pi2.name = 'blue' AND
					pi2.value = 1
					WHERE
						pi1.name <> '' OR
						pi2.name <> ''
		" );
		$count_posts = Posts::get( array( 'ignore_permissions' => true, 'any:info' => array( 'black' => 1, 'blue' => 1 ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );

		$count = Posts::get( array( 'ignore_permissions' => true, 'all:info' => array( 'black' => 1 ), 'count' => 1, 'nolimit' => 1 ) ) +
				Posts::get( array( 'all:info' => array( 'blue' => true ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );

		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts}
				LEFT JOIN {postinfo} pi1 ON
					{posts}.id = pi1.post_id AND
					pi1.name = 'i' AND
					pi1.value IN ( 0,1,2,3,4,5 )
					WHERE
						pi1.name <> ''
		" );
		$params = array( 'ignore_permissions' => true, 'any:info' => array( 'i' => array( 1, 2, 3, 4, 5 ) ), 'count' => 1, 'nolimit' => 1 );
		//$this->output(Posts::get(array_merge($params, array('fetch_fn' => 'get_query'))));
		$count_posts = Posts::get( $params );
		$this->assert_equal( $count_posts, $count );

		// not:all:info
		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts} WHERE
				{posts}.id NOT IN (
					SELECT post_id FROM {postinfo}
						WHERE ( name = 'testing_info' AND value = 1 )
						GROUP BY post_id
						HAVING COUNT(*) = 1
				)
		" );
		$count_posts = Posts::get( array( 'ignore_permissions' => true, 'not:all:info' => array( 'testing_info' => 1 ), 'nolimit' => 1, 'count' => 1 ) );
		$this->assert_equal( $count_posts, $count, _t('not:all:info expected %d, got %d', array($count, $count_posts) ));

		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts} WHERE
				{posts}.id NOT IN (
					SELECT post_id FROM {postinfo}
						WHERE ( name = 'one' AND value = 1 )
						GROUP BY post_id
						HAVING COUNT(*) = 1
				)
		" );
		$count_posts = Posts::get( array( 'ignore_permissions' => true, 'not:all:info' => array( 'one' => 1 ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );

		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts} WHERE
				{posts}.id NOT IN (
					SELECT post_id FROM {postinfo}
						WHERE ( name = 'old' AND value = 1 OR
						 name = 'new' AND value = 1 )
						GROUP BY post_id
						HAVING COUNT(*) = 2
				)
		" );
		$count_posts = Posts::get( array( 'ignore_permissions' => true, 'not:all:info' => array( 'old' => 1, 'new' => 1 ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );

		// not:any:info
		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts} WHERE
				{posts}.id NOT IN (
					SELECT post_id FROM {postinfo}
						WHERE ( {postinfo}.name = 'two' AND {postinfo}.value = 1 )
				)
		" );
		$count_posts = Posts::get( array( 'ignore_permissions' => true, 'not:any:info' => array( 'two' => 1 ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );

		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts} WHERE
				{posts}.id NOT IN (
					SELECT post_id FROM {postinfo}
						WHERE ( {postinfo}.name = 'black' AND {postinfo}.value = 1 OR
						 {postinfo}.name = 'blue' AND {postinfo}.value = 1 )
				)
		" );
		$count_posts = Posts::get( array( 'ignore_permissions' => true, 'not:any:info' => array( 'black' => 1, 'blue' => 1 ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );
//		$query = Posts::get( array( 'not:any:info' => array( 'comments_disabled' => 1, 'html_title' => 'Chili, The Breakfast of Champions' ), 'nolimit' => 1, 'fetch_fn' => 'get_query' ) );
//		Utils::debug( $query );die();

		// teardown
		Posts::get( array( 'ignore_permissions' => true, 'has:info' => 'testing_info', 'nolimit' => 1 ) )->delete();
		$informationless_post->delete();
	}
//
//	/*
//	 * @todo Make this test do something useful. It currently illustrates #1220 by failing on postgres, but it should actually assert things.
//	 */
//	public function test_get_posts_by_tag_and_info()
//	{
////		$result = Posts::get( array( 'tags:term' => 'one', 'has:info' => array( 'posts_test' => 'test' ), 'orderby' => 'ABS(info_test_value) DESC' ) );
////		$result = Posts::get( array( 'tags:term' => 'one', 'all:info' => array( 'posts_test' => 'test' ) ) );
//		$this->mark_test_incomplete();
//	}

	/**
	 * Get posts by manipulating the WHERE clause
	 * - where => manipulate the generated WHERE clause. Currently broken, see https://trac.habariproject.org/habari/ticket/1383
	 */
	public function test_get_posts_by_where()
	{
		$this->mark_test_incomplete();
	}

	/**
	 * Get posts and specify the ordering
	 * - orderby => how to order the returned posts
	 */
	public function test_get_posts_orderby()
	{
		$this->mark_test_incomplete();
	}

	/**
	 * Get posts and group the results
	 * - groupby => columns by which to group the returned posts, for aggregate functions
	 */
	public function test_get_posts_by_groupby()
	{
		$this->mark_test_incomplete();
	}

	/**
	 * Get posts and specify an aggregate function
	 * - having => for selecting posts based on an aggregate function
	 */
	public function test_get_posts_by_having()
	{
		$this->mark_test_incomplete();
	}

	/**
	 * Get a post by a search criteria
	 * - criteria => a literal search string to match post title or content
	 */
	public function test_get_posts_by_criteria()
	{
		$this->mark_test_incomplete();
	}

	/**
	 * Get a post by title
	 * - title => an exact case-insensitive match to a post title
	 */
	public function test_get_posts_by_title()
	{
		$this->mark_test_incomplete();
	}

	/**
	 * Get a post by searching title
	 * - title_search => a search string that acts only on the post title
	 */
	public function test_get_posts_by_title_search()
	{
		$this->mark_test_incomplete();
	}

	/**
	 * Limit the number of posts returned
	 * - limit => the maximum number of posts to return, implicitly set for many queries
	 * - nolimit => do not implicitly set limit
	 */
	public function test_get_posts_with_limit()
	{
		$this->mark_test_incomplete();
	}
}

?>
