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
		$this->tag = new Tag( array( 'tag_text' => $this->text, 'tag_slug' => $this->slug ) );
	}

	public function teardown()
	{
		unset( $this->tag );
	}

	public function test_construct_tag()
	{
		// Create a tag from a parameter array
		$params = array(
			'tag_slug' => $this->slug,
			'tag_text' => $this->text
		);
		$t = new Tag($params);

		$this->assertType('Tag', $t, 'Result should be of type Tag');
		$this->assertEquals($this->slug, $t->tag_slug, 'The slug should equal the slug value passed in.' );
		$this->assertEquals($this->text, $t->tag_text, 'The text should equal the text value passed in.' );

		// Create a tag from a Term
		$term = new Term($this->text);
		$t = new Tag($term);

		$this->assertType('Tag', $t, 'Result should be of type Tag');
		$this->assertEquals($this->text, $t->tag_text, 'The text should equal the text value passed in.' );
	}

	public function test_create_tag()
	{
		$t = Tag::create( array( 'tag_text' => $this->text, 'tag_slug' => $this->slug ) );
		$this->assertType( 'Tag', $t );
		// Check the tag's id is set.
		$this->assertGreaterThan(0, (int)$t->id, 'The Tag id should be greater than zero');
		$this->assertEquals($this->slug, $t->tag_slug, 'The slug should equal the slug value passed in.' );
		$this->assertEquals($this->text, $t->tag_text, 'The text should equal the text value passed in.' );
		$t->delete();
	}

	public function test_insert_tag()
	{
		$count = count( Tags::get() );
		$res = $this->tag->insert();
		if ( $res ) {
			$this->assertType( 'Tag', $res );
			$this->assertEquals( $count + 1, count( Tags::get() ) );
			$t = Tag::get( $this->text );
			$this->assertType( 'Tag', $t );
			$this->assertEquals( $this->text, $t->tag_text );
			$t->delete();
		}
		else {
			$this->assertEquals( $res, FALSE );
		}

	}

	public function test_update_tag()
	{
		$this->tag->insert();
		$t = Tag::get( $this->tag->tag_text );
		$t->tag_text = 'Updated Test Tag';
		$t->update();
		$new_tag = Tag::get( $t->id );
		$this->assertEquals( $new_tag->tag, $t->tag_text );
		$t->delete();
	}

	public function test_delete_tag()
	{
		$count = count( Tags::get() );
		$t = $this->tag->insert();
		$this->assertEquals( $count + 1, count( Tags::get() ) );
		$t->delete();
		$this->assertEquals( $count, count( Tags::get() ) );
	}

	public function test_get_tag()
	{
		$this->tag->insert();
		// Get tag by text
		$t = Tag::get( $this->text );
		$this->assertEquals( $t->tag_text, $this->tag->tag_text );
		// Get tag by id
		$t = Tag::get( $t->id );
		$this->assertEquals( $t->tag_text, $this->tag->tag_text );
		$t->delete();
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
