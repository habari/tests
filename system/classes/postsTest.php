<?php

define('NUM_POSTS', 20);

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

class system_classes_PostsTest extends PHPUnit_Framework_TestCase
{
	private $posts;

	public static function setUpBeforeClass()
	{
		set_time_limit(0);

		$this->posts = array();

		$user = User::get_by_name( 'lipsum' );
		if ( !$user ) {
			$user = User::create(array (
				'username'=>'lipsum',
				'email'=>'lipsum@example.com',
				'password'=>md5('q' . rand(0,65535)),
			));
		}

		$time = time() - 160;

		echo "about to creating post:";
		// Create all the posts we're going to be retrieving in our tests
		for ( $i = 0; $i < NUM_POSTS; $i++ ) {
			echo "creating post:";
			$this->posts[] = $this->make_post( $user, $time = $time - rand(3600, 3600*36) );
		}
	}

	public static function tearDownAfterClass()
	{
		foreach ( $this->posts as $post ) {
			$post->delete();
		}
		unset($this->posts);
		$user = User::get_by_name( 'lipsum' );
		$user->delete;
	}

	public function test_get_posts_by_id()
	{
		// Get a single post by id
		$want = array_rand($this->posts);

		$got = Posts::get(array('id' => $want->id));

		$this->assertType('Post', $got);
		$this->assertEquals($got->id, $want->id );

		// Get multiple posts by id
		$want = array_rand($this->posts, rand(2,NUM_POSTS);
		$ids = array();
		foreach ( $want as $w ) $ids[] = $w->id;

		$got = Posts::get(array('id' => $ids));

		foreach ( $got as $g ) {
			$this->assertType('Post', $g);
			//$this->assertEquals($g->id, $want->id );
		}

	}

	public function test_get_posts_by_slug()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function test_get_posts_by_content_type()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function test_get_posts_by_status()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function test_get_posts_by_user_id()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function test_get_posts_by_date()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function test_get_posts_by_tag()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
		// tag
		// tag_slug
		// all:tag
		// not:tag
	}

	public function test_get_posts_by_info()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
		// has:info
		// all:info
		// any:info
		// not:all:info
		// not:any:info
	}

	public function test_get_posts_by_where()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function test_get_posts_by_criteria()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function test_get_posts_with_limit()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
		// limit
		// nolimit
	}

	/**
	 * Methods to create posts
	 */

	/**
	 * make_post
	 * Makes a single post
	 * @param object $user The Lipsum user
	 * @param timestamp $time The published timestamp of the new posts
	 */
	private function make_post( $user, $time )
	{
		$post = Post::create(array(
			'title' => $this->get_title(),
			'content' => $this->get_content(1, 3, 'some', array('ol'=>1, 'ul'=>1), 'cat'),
			'user_id' => $user->id,
			'status' => Post::status('published'),
			'content_type' => Post::type('entry'),
			'tags' => 'lipsum',
			'pubdate' => HabariDateTime::date_create( $time ),
		));
		$post->info->lipsum = true;
		$post->info->commit();

		return $post;
	}

	private function get_pgraph()
	{
		$start = array("Nam quis nulla", "Integer malesuada", "In an enim", "Sed vel lectus", "Donec odio urna,", "Phasellus rhoncus", "Aenean id ", "Vestibulum fermentum", "Pellentesque ipsum",  "Nulla non", "Proin in tellus", "Vivamus luctus", "Maecenas sollicitudin", "Etiam egestas", "Lorem ipsum dolor sit amet,", "Nullam feugiat,", "Aliquam erat volutpat", "Mauris pretium",);
		$mid = array(" a arcu imperdiet", " tempus molestie,", " porttitor ut,", " iaculis quis,", " metus id velit", " lacinia neque", " sed nisl molestie", " sit amet nibh", " consectetuer adipiscing", " turpis at pulvinar vulputate,", " erat libero tristique tellus,", " nec bibendum odio risus"," pretium quam", " ullamcorper nec,", " rutrum non,", " nonummy ac,", " augue id magna",);
		$end = array(" nulla.  "," malesuada.  "," lectus.  "," sem.  "," pulvinar.  "," faucibus fringilla.  "," dignissim sagittis.  "," egestas leo.  "," metus.  "," erat.  "," elit.  "," sit amet ante.  "," volutpat.  "," urna.  "," rutrum.  ",);

		$ipsum_text = '';
		$lines = rand(1,6);
		for ( $l = 0; $l < $lines; $l++ ) {
			$line = $start[rand(0,count($start)-1)];
			$mids = rand(1,3);
			for ( $z = 0; $z < $mids; $z++ ) $line .= $mid[rand(0,count($mid)-1)];
			$line .= $end[rand(0,count($end)-1)];
			$ipsum_text .= $line;
		}
		$ipsum_text .= "\n\n";
		return $ipsum_text;
	}

	private function get_title()
	{
		$text = $this->get_pgraph(1);
		$text = strtolower($text);
		$text = preg_replace('/[^a-z\s]/', '', $text);
		$text = explode(' ', $text);
		$words = rand(2, 8);
		$title = '';
		for ( $i = 0; $i < $words; $i++ ) {
			$title .= $text[rand(0, count($text)-1)] . ' ';
		}
		$title = ucwords(trim($title));
		return $title;
	}

	private function get_content($min, $max, $more, $features, $imgtags)
	{
		$lipsum_text = '';
		$howmany = rand($min, $max);
		for ( $i = 0; $i < $howmany; $i++) {
			$lipsum_text .= $this->get_pgraph();
			if ( isset($features['ol']) ) {
				if ( rand(1, $max - $i + 1) == 1 ) {
					$listitems = rand(3,10);
					$lipsum_text .= "<ol>\n";
					for ( $z = 0; $z < $listitems; $z++ ) {
						$lipsum_text .= "\t<li>" . $this->get_title() . "</li>\n";
					}
					$lipsum_text .= "</ol>\n";
					unset($features['ol']);
				}
			}
			if ( isset($features['ul']) ) {
				if ( rand(1, $max - $i + 1) == 1 ) {
					$listitems = rand(3,10);
					$lipsum_text .= "<ul>\n";
					for ( $z = 0; $z < $listitems; $z++ ) {
						$lipsum_text .= "\t<li>" . $this->get_title() . "</li>\n";
					}
					$lipsum_text .= "</ul>\n";
					unset($features['ul']);
				}
			}

			switch ( $more ) {
				case 'none':
					break;
				case 'some':
					if ( rand(1,2) == 1 ) break;
				case 'all':
					if ( $i==0 && $howmany > 1 ) {
						$lipsum_text .= '<!--more-->';
					}
			}
		}
		return $lipsum_text;
	}

}

?>


}
