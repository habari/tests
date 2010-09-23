<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

/**
 * Test retrieving posts by tags.
 * These aren't in postsTest because they have different setUp(). Possibly should be combined.
 * Also note that all these tests will change if $post->tags returns a Tags object. See #1235.
 */
class system_classes_PostsTagsTest extends PHPUnit_Framework_TestCase
{
	protected $posts;
	protected $tag_sets;

	protected $types = array('entry', 'page');
	protected $statuses = array('published', 'draft');

	protected function setUp()
	{
		set_time_limit(0);

		$this->posts = array();

		$user = User::get_by_name( 'posts_test' );
		if ( !$user ) {
			$user = User::create(array (
				'username'=>'posts_test',
				'email'=>'posts_test@example.com',
				'password'=>md5('q' . rand(0,65535)),
			));
		}

		$time = time() - 160;

		$this->tag_sets = array(
			array('one'),
			array('two'),
			array('one', 'two'),
			array('three'),
		);

		foreach ( $this->tag_sets as $tags ) {
			$time = $time - rand(3600, 3600*36);
			$this->posts[] = Post::create(array(
				'title' => $this->get_title(),
				'content' => $this->get_content(1, 3, 'some', array('ol'=>1, 'ul'=>1), 'cat'),
				'user_id' => $user->id,
				'status' => Post::status('published'),
				'content_type' => Post::type('entry'),
				'tags' => $tags,
				'pubdate' => HabariDateTime::date_create( $time ),
			));
		}

	}

	protected function tearDown()
	{
		foreach ( $this->posts as $post ) {
			$post->delete();
		}
		unset($this->posts);
		$user = User::get_by_name( 'posts_test' );
		$user->delete();
	}

	/**
	 * Test retrieval of posts by a single tag
	 */
	public function test_get_posts_by_single_tag()
	{
		// Get posts via a single tag
		$want_tag = 'one';
		$want_count = 0;

		foreach ( $this->tag_sets as $tags ) {
			$want_count += in_array( $want_tag, $tags ) ? 1 : 0;
		}

		$got = Posts::get( array( 'tag' => $want_tag ) );

		$this->assertType( 'Posts', $got, 'Result should be of type Posts' );
		$this->assertEquals( $want_count, count( $got ), 'The correct number of posts with the requested tag should be returned' );

		foreach ( $got as $g ) {
			$this->assertType( 'Post', $g, 'Items should be of type Post' );
			$values = array();
			foreach( $g->tags as $key => $tag ) {
				$values[] = $tag->tag_text;
			}
			$this->assertTrue( in_array( $want_tag, $values ), 'The post should have the requested tag' );
		}
	}

	/**
	 * Test retrieval of posts by multiple tags
	 */
	public function test_get_posts_by_multiple_tags()
	{
		// Get posts via multiple OR'd tags
		$want_tags = array( 'one', 'two' );
		$want_count = 0;

		foreach ( $this->tag_sets as $tags ) {
			foreach ( $want_tags as $want_tag ) {
				if ( in_array( $want_tag, $tags ) ) {
					// This is a post with this tag
					$want_count++;
					// We don't want to count it twice if it matches more than one tag
					break;
				}
			}
		}

		$got = Posts::get( array( 'tag' => $want_tags ) );

		$this->assertType( 'Posts', $got, 'Result should be of type Posts' );
		$this->assertEquals( $want_count, count( $got ), 'The correct number of posts with the requested tags should be returned' );

		foreach ( $got as $g ) {
			$this->assertType( 'Post', $g, 'Items should be of type Post' );
			$values = array();
			foreach( $g->tags as $tag ) {
				$values[] = $tag->tag_text;
			}
			$this->assertGreaterThan( 0, count(array_intersect($want_tags, $values ) ), 'The post should have one of the requested tags' );
		}

		// Get posts via multiple AND'd tags
		$want_tags = array( 'one', 'two' );
		$want_count = 0;

		foreach ( $this->tag_sets as $tags ) {
			if ( count( $want_tags ) == count( array_intersect( $want_tags, $tags ) ) ) {
				// This is a post with all these tag
				$want_count++;
			}
		}

		$got = Posts::get( array( 'all:tag' => $want_tags ) );

		$this->assertType( 'Posts', $got, 'Result should be of type Posts' );
		$this->assertEquals( count( $got ), $want_count, 'The correct number of posts with the requested tags should be returned' );

		foreach ( $got as $g ) {
			$this->assertType( 'Post', $g, 'Items should be of type Post' );
			$values = array();
			foreach( $g->tags as $tag ) {
				$values[] = $tag->tag_text;
			}
			$this->assertEquals( count( $want_tags ), count( array_intersect( $want_tags, $values ) ), 'The post should have all of the requested tags' );
		}
	}

	/**
	 * Methods to create post data
	 */

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
