<?php

include 'bootstrap.php';

define('NUM_POSTS', 1);

class PostsTest extends UnitTestCase
{
	protected $posts;

	protected $types = array('entry', 'page');
	protected $statuses = array('published', 'draft');

	protected function module_setup()
	{
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
		$want = $this->posts[array_rand( $this->posts )];

		$got = Posts::get( array( 'id' => $want->id ) );

		$this->assert_true( $got instanceof Posts, 'Result should be of type Posts' );
		$this->assert_true( $got->onepost, 'A single post should be returned if a single id is passed in' );

		$g = $got[0];
		$this->assert_true( $g instanceof Post, 'Items should be of type Post' );
		$this->assert_equal( $g->id, $want->id, 'id of returned Post should be the one we asked for' );

		// Get multiple posts by id
		$want = array_rand( $this->posts, count( $this->posts ) );

		$ids = array();
		foreach ( $want as $w ) $ids[] = $this->posts[$w]->id;

		$got = Posts::get( array('id' => $ids ) );

		$this->assert_true( $got instanceof Posts, 'Result should be of type Posts' );
		// @todo This currently isn't true, because the options limit is respected. Should it be?
		//$this->assert_equal( count( $got ), count( $want ), 'The number of posts we asked for should be returned' );

		foreach ( $got as $g ) {
			$this->assert_true( $g instanceof Post, 'Items should be of type Post' );
			$this->assert_true( in_array( $g->id, $ids ), 'id of returned Post should be in the list of the ones we asked for' );
		}

	}

	public function test_get_posts_by_slug()
	{
		// Get a single post by slug
		$want = $this->posts[array_rand( $this->posts )];

		$got = Posts::get( array( 'slug' => $want->slug, 'status' => 'any' ) );

		$this->assert_true( $got instanceof Posts, 'Result should be of type Posts' );
		$this->assert_true( $got->onepost, 'A single post should be returned if a single slug is passed in' );

		$g = $got[0];
		$this->assert_true( $g instanceof Post, 'Items should be of type Post' );
		$this->assert_equal( $g->id, $want->id, 'id of returned Post should be the one we asked for' );
		$this->assert_equal( $g->slug, $want->slug, 'slug of returned Post should be the one we asked for' );

		// Get multiple posts by id
		$want = array_rand( $this->posts, count( $this->posts ) );

		$slugs = array();
		foreach ( $want as $w ) $slugs[] = $this->posts[$w]->slug;

		$got = Posts::get( array( 'slug' => $slugs ) );

		$this->assert_true( $got instanceof Posts, 'Result should be of type Posts' );
		// @todo This currently isn't true, because the options limit is respected. Should it be?
		//$this->assert_equal( count( $got ), count( $want ), 'The number of posts we asked for should be returned' );

		foreach ( $got as $g ) {
			$this->assert_true( $g instanceof Post, 'Items should be of type Post' );
			$this->assert_true( in_array( $g->slug, $slugs ), 'slug of returned Post should be in the list of the ones we asked for' );
		}
	}

	public function test_get_posts_by_content_type()
	{
		// Get by single content type
		$want = array();
		foreach ( $this->posts as $post ) {
			if ( $post->content_type == Post::type( 'page' ) ) {
				$want[] = $post;
			}
		}

		$got = Posts::get( array( 'content_type' => Post::type( 'page' ) ) );

		$this->assert_true( $got instanceof Posts, 'Result should be of type Posts' );

		foreach ( $got as $g ) {
			$this->assert_true( $g instanceof Post, 'Items should be of type Post' );
			$this->assert_equal( $g->content_type, Post::type( 'page' ), 'Returned posts should be of the requested content type' );
		}

		// Get by an array of content types
		// @todo How do we test this?
		// Get any content type
		// @todo How do we test this?
	}

//	public function test_get_posts_by_status()
//	{
//	}

//	public function test_get_posts_by_user_id()
//	{
//	}

//	public function test_get_posts_by_date()
//	{
//	}

//	public function test_get_posts_by_tag()
//	{
		// tag
		// tag_slug
		// all:tag
		// not:tag
//	}

//	public function test_get_posts_by_info()
//	{
		// has:info
		// all:info
		// any:info
		// not:all:info
		// not:any:info
//	}

	/*
	 * @todo Make this test do something useful. It currently illustrates #1220 by failing on postgres, but it should actually assert things.
	 */
	public function test_get_posts_by_tag_and_info()
	{
		$got = Posts::get( array( 'tag' => 'one', 'has:info' => 'test', 'orderby' => 'ABS(info_test_value) DESC' ) );
	}

//	public function test_get_posts_by_where()
//	{
//	}

//	public function test_get_posts_by_criteria()
//	{
//	}

//	public function test_get_posts_with_limit()
//	{
		// limit
		// nolimit
//	}

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
