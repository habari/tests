<?php

include 'bootstrap.php';

define('NUM_POSTS', 1);

class PostsTest extends UnitTestCase
{
	protected $posts;

	protected $types = array('entry', 'page');
	protected $statuses = array('published', 'draft');
	protected $info_titles = array();

	protected function module_setup()
	{
		$this->info_titles[] = 'This is a Post';
		$this->info_titles[] = '';
		$this->info_titles[] = 'I am not a Post';
		$this->info_titles[] = 'Chili, The Breakfast of Champions';
		$this->info_titles[] = 'More Cowbell!';
		$this->info_titles[] = 'A Simple Test Post';

		set_time_limit(0);

		$this->posts = array();

		$user = User::get_by_name( 'posts_test' );
		if ( !$user ) {
			$user = User::create(array (
				'username'=>'posts_test',
				'email'=>'posts_test@example.com',
				'password'=>md5('q' . rand( 0,65535 ) ),
			) );
		}

		$time = time() - 160;

		// Create all the posts we're going to be retrieving in our tests
		foreach( $this->types as $type ) {
			foreach( $this->statuses as $status ) {
				for ( $i = 0; $i < NUM_POSTS; $i++ ) {
					$this->posts[] = $this->make_post( $user, $time = $time - rand( 3600, 3600*36 ), $type, $status );
				}
			}
		}

	}

	protected function module_teardown()
	{
		foreach ( $this->posts as $post ) {
			$post->delete();
		}
		unset( $this->posts );
		$user = User::get_by_name( 'posts_test' );
		$user->delete();
	}

	public function test_get_posts_by_id()
	{
		// Get a single post by id
		$expected = $this->posts[array_rand( $this->posts )];

		$result = Posts::get( array( 'id' => $expected->id ) );

		$this->assert_true( $result instanceof Posts, 'Result should be of type Posts' );
		$this->assert_true( $result->onepost, 'A single post should be returned if a single id is passed in' );

		$result = $result[0];
		$this->assert_true( $result instanceof Post, 'Items should be of type Post' );
		$this->assert_equal( $result->id, $expected->id, 'id of returned Post should be the one we asked for' );

		// Get multiple posts by id
		$expected = array_rand( $this->posts, count( $this->posts ) );

		$ids = array();
		foreach ( $expected as $e ) $ids[] = $this->posts[$e]->id;

		$result = Posts::get( array('id' => $ids ) );

		$this->assert_true( $result instanceof Posts, 'Result should be of type Posts' );
		// @todo This currently isn't true, because the options limit is respected. Should it be?
		//$this->assert_equal( count( $result ), count( $expected ), 'The number of posts we asked for should be returned' );

		foreach ( $result as $r ) {
			$this->assert_true( $r instanceof Post, 'Items should be of type Post' );
			$this->assert_true( in_array( $r->id, $ids ), 'id of returned Post should be in the list of the ones we asked for' );
		}

	}

	public function test_get_posts_by_slug()
	{
		// Get a single post by slug
		$expected = $this->posts[array_rand( $this->posts )];

		$result = Posts::get( array( 'slug' => $expected->slug, 'status' => 'any' ) );

		$this->assert_true( $result instanceof Posts, 'Result should be of type Posts' );
		$this->assert_true( $result->onepost, 'A single post should be returned if a single slug is passed in' );

		$result = $result[0];
		$this->assert_true( $result instanceof Post, 'Items should be of type Post' );
		$this->assert_equal( $result->id, $expected->id, 'id of returned Post should be the one we asked for' );
		$this->assert_equal( $result->slug, $expected->slug, 'slug of returned Post should be the one we asked for' );

		// Get multiple posts by id
		$expected = array_rand( $this->posts, count( $this->posts ) );

		$slugs = array();
		foreach ( $expected as $e ) $slugs[] = $this->posts[$e]->slug;

		$result = Posts::get( array( 'slug' => $slugs ) );

		$this->assert_true( $result instanceof Posts, 'Result should be of type Posts' );
		// @todo This currently isn't true, because the options limit is respected. Should it be?
		//$this->assert_equal( count( $result ), count( $expected ), 'The number of posts we asked for should be returned' );

		foreach ( $result as $r ) {
			$this->assert_true( $r instanceof Post, 'Items should be of type Post' );
			$this->assert_true( in_array( $r->slug, $slugs ), 'slug of returned Post should be in the list of the ones we asked for' );
		}
	}

	public function test_get_posts_by_content_type()
	{
		// Get by single content type
		$expected = array();
		foreach ( $this->posts as $post ) {
			if ( $post->content_type == Post::type( 'page' ) ) {
				$expected[] = $post;
			}
		}

		$result = Posts::get( array( 'content_type' => Post::type( 'page' ) ) );

		$this->assert_true( $result instanceof Posts, 'Result should be of type Posts' );

		foreach ( $result as $r ) {
			$this->assert_true( $r instanceof Post, 'Items should be of type Post' );
			$this->assert_equal( $r->content_type, Post::type( 'page' ), 'Returned posts should be of the requested content type' );
		}

		// Get by an array of content types
		// @todo How do we test this?
		// Get any content type
		// @todo How do we test this?
	}

	public function test_get_posts_by_status()
	{
		// Get by single status
		$expected = array();
		foreach ( $this->posts as $post ) {
			if ( $post->status == Post::status( 'draft' ) ) {
				$expected[] = $post;
			}
		}

		$result = Posts::get( array( 'status' => Post::status( 'draft' ) ) );

		$this->assert_true( $result instanceof Posts, 'Result should be of type Posts' );

		foreach ( $result as $r ) {
			$this->assert_true( $r instanceof Post, 'Items should be of type Post' );
			$this->assert_equal( $r->status, Post::status( 'draft' ), 'Returned posts should be of the requested status' );
		}
		// Get by an array of statuses
		// @todo How do we test this?
		// Get any status
		// @todo How do we test this?
	}

	public function test_get_posts_by_user_id()
	{
		// Create another user and a post
		$decoy = User::create(array (
			'username'=>'decoy',
			'email'=>'decoy@example.com',
			'password'=>md5('q' . rand( 0,65535 ) ),
		) );
		$this->posts[] = $this->make_post( $user, time() - rand( 3600, 3600*36 ), 'entry', 'published' );

		$expected = User::get_by_name( 'posts_test' );

		$result = Posts::get( array( 'user_id' => $expected->id ) );

		$this->assert_true( $result instanceof Posts, 'Result should be of type Posts' );

		foreach ( $result as $r ) {
			$this->assert_true( $r instanceof Post, 'Items should be of type Post' );
			$this->assert_equal( $r->author->id, $expected->id, 'Returned posts should belong to the expected user' );
		}

		// Get by an array of ids
	}

	public function test_get_posts_by_date()
	{
		$this->mark_test_incomplete();
	}

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

	public function test_get_posts_by_info()
	{
		// has:info
		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts}
				LEFT JOIN {postinfo} pi1 ON
					{posts}.id = pi1.post_id AND
					pi1.name = 'comments_disabled'
					WHERE
						pi1.name <> ''
		" );
		$count_posts = Posts::get( array( 'has:info' => array( 'comments_disabled' ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );

		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts}
				LEFT JOIN {postinfo} pi1 ON
					{posts}.id = pi1.post_id AND
					pi1.name = 'comments_disabled'
				LEFT JOIN {postinfo} pi2 ON
					{posts}.id = pi2.post_id AND
					pi2.name = 'html_title'
					WHERE
						pi1.name <> '' OR
						pi2.name <> ''
		" );
		$count_posts = Posts::get( array( 'has:info' => array( 'comments_disabled', 'html_title' ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );
//		$query = Posts::get( array( 'has:info' => array( 'comments_disabled', 'html_title' ), 'nolimit' => 1, 'fetch_fn' => 'get_query' ) );
//		Utils::debug( $query );die();

		// all:info
		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts}
				LEFT JOIN {postinfo} pi1 ON
					{posts}.id = pi1.post_id AND
					pi1.name = 'comments_disabled' AND pi1.value = 1
					WHERE
						pi1.name <> ''
		" );
		$count_posts = Posts::get( array( 'all:info' => array( 'comments_disabled' => 1 ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );

		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts}
				LEFT JOIN {postinfo} pi1 ON
					{posts}.id = pi1.post_id AND
					pi1.name = 'comments_disabled' AND pi1.value = 1
				LEFT JOIN {postinfo} pi2 ON
					{posts}.id = pi2.post_id AND
					pi2.name = 'html_title' AND pi2.value = 'Chili, The Breakfast of Champions'
					WHERE
						pi1.name <> '' AND
						pi2.name <> ''
		" );
		$count_posts = Posts::get( array( 'all:info' => array( 'comments_disabled' => 1, 'html_title' => 'Chili, The Breakfast of Champions' ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );

		// any:info
		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts}
				LEFT JOIN {postinfo} pi1 ON
					{posts}.id = pi1.post_id AND
					pi1.name = 'comments_disabled' AND
					pi1.value = 1
				LEFT JOIN {postinfo} pi2 ON
					{posts}.id = pi2.post_id AND
					pi2.name = 'html_title' AND
					pi2.value = 'Chili, The Breakfast of Champions'
					WHERE
						pi1.name <> '' OR
						pi2.name <> ''
		" );
		$count_posts = Posts::get( array( 'any:info' => array( 'comments_disabled' => 1, 'html_title' => 'Chili, The Breakfast of Champions' ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );

		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts}
				LEFT JOIN {postinfo} pi1 ON
					{posts}.id = pi1.post_id AND
					pi1.name = 'html_title' AND
					pi1.value IN ( 'Chili, The Breakfast of Champions', 'This is a Post' )
					WHERE
						pi1.name <> ''
		" );
		$count_posts = Posts::get( array( 'any:info' => array( 'html_title' => array( 'Chili, The Breakfast of Champions', 'This is a Post' ) ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );

		// not:all:info
		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts} WHERE
				{posts}.id NOT IN (
					SELECT post_id FROM {postinfo}
						WHERE ( name = 'comments_disabled' AND value = 1 )
						GROUP BY post_id
						HAVING COUNT(*) = 1
				)
		" );
		$count_posts = Posts::get( array( 'not:all:info' => array( 'comments_disabled' => 1 ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );

		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts} WHERE
				{posts}.id NOT IN (
					SELECT post_id FROM {postinfo}
						WHERE ( name = 'comments_disabled' AND value = 1 OR
						 name = 'html_title' AND value = 'Chili, The Breakfast of Champions' )
						GROUP BY post_id
						HAVING COUNT(*) = 2
				)
		" );
		$count_posts = Posts::get( array( 'not:all:info' => array( 'comments_disabled' => 1, 'html_title' => 'Chili, The Breakfast of Champions' ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );

		// not:any:info
		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts} WHERE
				{posts}.id NOT IN (
					SELECT post_id FROM {postinfo}
						WHERE ( {postinfo}.name = 'comments_disabled' AND {postinfo}.value = 1 )
				)
		" );
		$count_posts = Posts::get( array( 'not:any:info' => array( 'comments_disabled' => 1 ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );

		$count = DB::get_value(
			"SELECT COUNT(*) FROM {posts} WHERE
				{posts}.id NOT IN (
					SELECT post_id FROM {postinfo}
						WHERE ( {postinfo}.name = 'comments_disabled' AND {postinfo}.value = 1 OR
						 {postinfo}.name = 'html_title' AND {postinfo}.value = 'Chili, The Breakfast of Champions' )
				)
		" );
		$count_posts = Posts::get( array( 'not:any:info' => array( 'comments_disabled' => 1, 'html_title' => 'Chili, The Breakfast of Champions' ), 'count' => 1, 'nolimit' => 1 ) );
		$this->assert_equal( $count_posts, $count );
//		$query = Posts::get( array( 'not:any:info' => array( 'comments_disabled' => 1, 'html_title' => 'Chili, The Breakfast of Champions' ), 'nolimit' => 1, 'fetch_fn' => 'get_query' ) );
//		Utils::debug( $query );die();
	}

	/*
	 * @todo Make this test do something useful. It currently illustrates #1220 by failing on postgres, but it should actually assert things.
	 */
	public function test_get_posts_by_tag_and_info()
	{
//		$result = Posts::get( array( 'tags:term' => 'one', 'has:info' => array( 'posts_test' => 'test' ), 'orderby' => 'ABS(info_test_value) DESC' ) );
//		$result = Posts::get( array( 'tags:term' => 'one', 'all:info' => array( 'posts_test' => 'test' ) ) );
		$this->mark_test_incomplete();
	}

	public function test_get_posts_by_where()
	{
		$this->mark_test_incomplete();
	}

	public function test_get_posts_by_criteria()
	{
		$this->mark_test_incomplete();
	}

	public function test_get_posts_with_limit()
	{
		$this->mark_test_incomplete();
//		 limit
//		 nolimit
	}

	/**
	 * Methods to create posts
	 */

	/**
	 * make_post
	 * Makes a single post
	 * @param object $user The posts_test user
	 * @param timestamp $time The published timestamp of the new posts
	 */
	private function make_post( $user, $time, $content_type, $status )
	{
		$post = Post::create( array(
			'title' => $this->get_title(),
			'content' => $this->get_content( 1, 3, 'some', array( 'ol'=>1, 'ul'=>1 ), 'cat' ),
			'user_id' => $user->id,
			'status' => Post::status( $status ),
			'content_type' => Post::type( $content_type ),
			//'tags' => 'posts_test',
			'pubdate' => HabariDateTime::date_create( $time ),
		));
		$post->info->posts_test = true;
		$post->info->comments_disabled = rand( 0, 1 );

		$max = count( $this->info_titles ) - 1;
		if( rand( 0, 1) == 1 ) {

			$post->info->html_title = $this->info_titles[rand(0, $max )];
		}
		$post->info->commit();

		return $post;
	}

	private function get_pgraph()
	{
		$start = array( "Nam quis nulla", "Integer malesuada", "In an enim", "Sed vel lectus", "Donec odio urna,", "Phasellus rhoncus", "Aenean id ", "Vestibulum fermentum", "Pellentesque ipsum",  "Nulla non", "Proin in tellus", "Vivamus luctus", "Maecenas sollicitudin", "Etiam egestas", "Lorem ipsum dolor sit amet,", "Nullam feugiat,", "Aliquam erat volutpat", "Mauris pretium", );
		$mid = array( " a arcu imperdiet", " tempus molestie,", " porttitor ut,", " iaculis quis,", " metus id velit", " lacinia neque", " sed nisl molestie", " sit amet nibh", " consectetuer adipiscing", " turpis at pulvinar vulputate,", " erat libero tristique tellus,", " nec bibendum odio risus"," pretium quam", " ullamcorper nec,", " rutrum non,", " nonummy ac,", " augue id magna", );
		$end = array( " nulla.  "," malesuada.  "," lectus.  "," sem.  "," pulvinar.  "," faucibus fringilla.  "," dignissim sagittis.  "," egestas leo.  "," metus.  "," erat.  "," elit.  "," sit amet ante.  "," volutpat.  "," urna.  "," rutrum.  ", );

		$ipsum_text = '';
		$lines = rand( 1,6 );
		for ( $l = 0; $l < $lines; $l++ ) {
			$line = $start[rand( 0,count( $start )-1 )];
			$mids = rand( 1,3 );
			for ( $z = 0; $z < $mids; $z++ ) $line .= $mid[rand( 0,count( $mid )-1 )];
			$line .= $end[rand( 0,count( $end )-1 )];
			$ipsum_text .= $line;
		}
		$ipsum_text .= "\n\n";
		return $ipsum_text;
	}

	private function get_title()
	{
		$text = $this->get_pgraph( 1 );
		$text = strtolower( $text );
		$text = preg_replace( '/[^a-z\s]/', '', $text );
		$text = explode( ' ', $text );
		$words = rand( 2, 8 );
		$title = '';
		for ( $i = 0; $i < $words; $i++ ) {
			$title .= $text[rand( 0, count( $text )-1 )] . ' ';
		}
		$title = ucwords( trim( $title ) );
		return $title;
	}

	private function get_content( $min, $max, $more, $features, $imgtags )
	{
		$lipsum_text = '';
		$howmany = rand( $min, $max );
		for ( $i = 0; $i < $howmany; $i++ ) {
			$lipsum_text .= $this->get_pgraph();
			if ( isset( $features['ol'] ) ) {
				if ( rand( 1, $max - $i + 1 ) == 1 ) {
					$listitems = rand( 3,10 );
					$lipsum_text .= "<ol>\n";
					for ( $z = 0; $z < $listitems; $z++ ) {
						$lipsum_text .= "\t<li>" . $this->get_title() . "</li>\n";
					}
					$lipsum_text .= "</ol>\n";
					unset( $features['ol'] );
				}
			}
			if ( isset( $features['ul'] ) ) {
				if ( rand( 1, $max - $i + 1 ) == 1 ) {
					$listitems = rand( 3,10 );
					$lipsum_text .= "<ul>\n";
					for ( $z = 0; $z < $listitems; $z++ ) {
						$lipsum_text .= "\t<li>" . $this->get_title() . "</li>\n";
					}
					$lipsum_text .= "</ul>\n";
					unset( $features['ul'] );
				}
			}

			switch ( $more ) {
				case 'none':
					break;
				case 'some':
					if ( rand( 1, 2 ) == 1 ) break;
				case 'all':
					if ( $i==0 && $howmany > 1 ) {
						$lipsum_text .= '<!--more-->';
					}
			}
		}
		return $lipsum_text;
	}

}

PostsTest::run_one( 'PostsTest' );

?>
