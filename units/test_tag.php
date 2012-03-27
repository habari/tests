<?php

include '../bootstrap.php';

class TagTest extends UnitTestCase
{
	private $text = 'Test Tag';
	private $slug;
	private $tag;

	public function module_setup()
	{
		$this->slug = Utils::slugify( $this->text );
		$this->tag = new Tag( array( 'term_display' => $this->text, 'term' => $this->slug ) );
	}

	public function module_teardown()
	{
		unset( $this->tag );
	}

	public function test_construct_tag()
	{
		// Create a tag from a parameter array
		$params = array(
			'term' => $this->slug,
			'term_display' => $this->text,
		);
		$t = new Tag($params);

		$this->assert_type('Tag', $t, 'Result should be of type Tag');
		$this->assert_equal($this->slug, $t->term, 'The slug should equal the slug value passed in.' );
		$this->assert_equal($this->text, $t->term_display, 'The text should equal the text value passed in.' );

	}

	public function test_create_tag()
	{
		$t = Tag::create( array( 'term_display' => $this->text, 'term' => $this->slug ) );
		$this->assert_type( 'Tag', $t );
		// Check the tag's id is set.
		$this->assert_true((int)$t->id > 0, 'The Tag id should be greater than zero');
		$this->assert_equal($this->slug, $t->term, 'The slug should equal the slug value passed in.' );
		$this->assert_equal($this->text, $t->term_display, 'The text should equal the text value passed in.' );
		$this->assert_true( (int)$t->mptt_left > 0, 'The Tag mptt_left should be greater than zero' );
		$this->assert_true( (int)$t->mptt_right > 0, 'The Tag mptt_right should be greater than zero' );
		Tags::vocabulary()->delete_term( $t );
	}

	public function test_insert_tag()
	{
		$count = Tags::vocabulary()->count_total();
		$res = Tags::vocabulary()->add_term( $this->tag );
		if ( $res ) {
			$this->assert_type( 'Tag', $res );
			$this->assert_equal( $count + 1, Tags::vocabulary()->count_total() );
			// Dies on PHP versions with the PDO bug if you try to get the tag as a Tag
			$t = Tags::vocabulary()->get_term( $this->text );
			// Settle for testing for a Term
			$this->assert_type( 'Term', $t );
			$this->assert_equal( $this->text, $t->term_display, $this->text . ' is not the same as ' . $t->term_display );
			Tags::vocabulary()->delete_term( $t );
		}
		else {
			$this->assert_equal( $res, FALSE );
		}

	}

	public function test_update_tag()
	{
		Tags::vocabulary()->add_term( $this->tag );
		$t = Tags::vocabulary()->get_term( $this->tag->term_display );
		$t->term_display = 'Updated Test Tag';
		$t->update();
		$new_tag = Tags::vocabulary()->get_term( $t->id );
		$this->assert_equal( $new_tag->term_display, $t->term_display );
		Tags::vocabulary()->delete_term( $t );
	}

	public function test_delete_tag()
	{
		$count = Tags::vocabulary()->count_total();
		$t = Tags::vocabulary()->add_term( $this->tag );
		$this->assert_equal( $count + 1, Tags::vocabulary()->count_total() );
		Tags::vocabulary()->delete_term( $t );
		$this->assert_equal( $count, Tags::vocabulary()->count_total() );
	}

	public function test_get_tag()
	{
		Tags::vocabulary()->add_term( $this->tag );
		// Get tag by text
		$t = Tags::vocabulary()->get_term( $this->text );
		$this->assert_equal( $t->term_display, $this->tag->term_display );
		// Get tag by id
		$t = Tags::vocabulary()->get_term( $t->id );
		$this->assert_equal( $t->term_display, $this->tag->term_display );
		Tags::vocabulary()->delete_term( $t );
	}

	/*
	 * @todo Implement test_attach_tag_to_post
	 */
	public function test_attach_tag_to_post()
	{
	}

	/*
	 * @todo Implement test_detach_tag_from_post
	 */
	public function test_detach_tag_from_post()
	{
	}
}

TagTest::run_one( 'TagTest' );
?>
