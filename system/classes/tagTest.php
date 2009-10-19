<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

class system_classes_TagTest extends PHPUnit_Framework_TestCase
{
	private $text = 'Test Tag';
	private $slug;

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
		$params = array(
			'tag_slug' => $this->slug,
			'tag_text' => $this->text
		);
		$t = new Tag($params);

		$this->assertType('Tag', $t);
		$this->assertEquals($t->tag_slug, $this->slug );
		$this->assertEquals($t->tag_text, $this->text );
	}

	public function test_create_tag()
	{
		$t = Tag::create( array( 'tag_text' => $this->text, 'tag_slug' => $this->slug ) );
		$this->assertType( 'Tag', $t );
		$this->assertEquals( $t->tag_slug, $this->slug );
		$this->assertEquals( $t->tag_text, $this->text );
		$t->delete();
	}

	/**
	 * @todo assertType() fails
	 */
	public function test_insert_tag()
	{
		$count = count( Tags::get() );
		$res = $this->tag->insert();
		$this->assertEquals( $res, TRUE );
		$this->assertEquals( $count + 1, count( Tags::get() ) );
		$t = Tag::get( $this->slug );
		$this->assertType( 'Tag', $t );
		$this->assertEquals( $t->tag, $this->text );
		$this->tag->delete();

	}

	public function test_update_tag()
	{

		$this->tag->insert();
		$t = Tag::get( $this->tag->tag_text );
		$t->tag_text = 'Updated Test Tag';
		$t->update();
		$new_tag = Tag::get( $this->tag->tag_slug );
		$this->assertEquals( $new_tag->tag, $t->tag_text );
		$t->delete();
	}

	public function test_delete_tag()
	{
		$count = count( Tags::get() );
		$this->tag->insert();
		$this->assertEquals( $count + 1, count( Tags::get() ) );
		$this->tag->delete();
		$this->assertEquals( $count, count( Tags::get() ) );
	}

	public function test_get_tag()
	{
		$this->tag->insert();
		$t = Tag::get( $this->text );
		$this->assertEquals( $t->tag, $this->tag->tag_text );
		$this->tag->delete();
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
