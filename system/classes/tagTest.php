<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

class system_classes_TagTest extends PHPUnit_Framework_TestCase
{
	private $text = 'Test Tag';
	private $slug;
	private $tag;

	public function setup()
	{
		$this->slug = Utils::slugify( $this->text );
		$this->tag = new Tag( array( 'term_display' => $this->text, 'term' => $this->slug ) );
	}

	public function teardown()
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

		$this->assertType('Tag', $t, 'Result should be of type Tag');
		$this->assertEquals($this->slug, $t->term, 'The slug should equal the slug value passed in.' );
		$this->assertEquals($this->text, $t->term_display, 'The text should equal the text value passed in.' );

	}

	public function test_create_tag()
	{
		$t = Tag::create( array( 'term_display' => $this->text, 'term' => $this->slug ) );
		$this->assertType( 'Tag', $t );
		// Check the tag's id is set.
		$this->assertGreaterThan(0, (int)$t->id, 'The Tag id should be greater than zero');
		$this->assertEquals($this->slug, $t->term, 'The slug should equal the slug value passed in.' );
		$this->assertEquals($this->text, $t->term_display, 'The text should equal the text value passed in.' );
		$this->assertGreaterThan( 0, (int)$t->mptt_left , 'The Tag mptt_left should be greater than zero' );
		$this->assertGreaterThan( 0, (int)$t->mptt_right , 'The Tag mptt_right should be greater than zero' );
		Tags::vocabulary()->delete_term( $t );
	}

	public function test_insert_tag()
	{
		$count = Tags::vocabulary()->count_total();
		$res = Tags::vocabulary()->add_term( $this->tag );
		if ( $res ) {
			$this->assertType( 'Tag', $res );
			$this->assertEquals( $count + 1, Tags::vocabulary()->count_total() );
			$t = Tags::vocabulary()->get_term( $this->text, 'Tag' );
			$this->assertType( 'Tag', $t );
			$this->assertEquals( $this->text, $t->term_display );
			Tags::vocabulary()->delete_term( $t );
		}
		else {
			$this->assertEquals( $res, FALSE );
		}

	}

	public function test_update_tag()
	{
		Tags::vocabulary()->add_term( $this->tag );
		$t = Tags::vocabulary()->get_term( $this->tag->term_display, 'Tag' );
		$t->term_display = 'Updated Test Tag';
		$t->update();
		$new_tag = Tags::vocabulary()->get_term( $t->id, 'Tag' );
		$this->assertEquals( $new_tag->term_display, $t->term_display );
		Tags::vocabulary()->delete_term( $t );
	}

	public function test_delete_tag()
	{
		$count = Tags::vocabulary()->count_total();
		$t = Tags::vocabulary()->add_term( $this->tag );
		$this->assertEquals( $count + 1, Tags::vocabulary()->count_total() );
		Tags::vocabulary()->delete_term( $t );
		$this->assertEquals( $count, Tags::vocabulary()->count_total() );
	}

	public function test_get_tag()
	{
		$this->tag->insert();
		// Get tag by text
		$t = Tags::vocabulary()->get_term( $this->text, 'Tag' );
		$this->assertEquals( $t->term_display, $this->tag->term_display );
		// Get tag by id
		$t = Tags::vocabulary()->get_term( (int)$t->id, 'Tag' );
		$this->assertEquals( $t->term_display, $this->tag->term_display );
		Tags::vocabulary()->delete_term( $t );
	}

	public function test_attach_to_post_tag()
	{
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	public function test_detach_from_post_tag()
	{
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

}
?>
