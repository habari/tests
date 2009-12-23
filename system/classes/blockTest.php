<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

class system_classes_BlockTest extends PHPUnit_Framework_TestCase
{
	private $title = 'Test Block Title';
	private $type = "Test Block";

	public function setup()
	{
	}

	public function teardown()
	{
	}

	public function test_construct_block()
	{
		$params = array(
			'title' => $this->title,
			'type' => $this->type
		);
		$block = new Block($params);

		$this->assertType('Block', $block);
		$this->assertEquals($block->title, $this->title );
		$this->assertEquals($block->type, $this->type );
	}

	/**
	 * @todo Should check that the new block has the right values
	 *
	 **/
	public function test_insert_block()
	{
		$params = array(
			'title' => $this->title,
			'type' => $this->type
		);
		$block = new Block($params);

		$count = DB::get_value('SELECT count(*) FROM {blocks}');

		$block->insert();

		$this->assertEquals( $count + 1, DB::get_value('SELECT count(*) FROM {blocks}') );
	}

	public function test_update_block()
	{
		$params = array(
			'title' => $this->title,
			'type' => $this->type
		);
		$block = new Block($params);

		// Should return the inserted block
		$block->insert();
		$block_id = $block->id;

		$updated_title = 'Updated Block Title';
		$updated_type = 'Updated Block Type';
		$block->title = $updated_title;
		$block->type = $updated_type;
		$block->update();

		$updated_block = DB::get_row('SELECT * FROM {blocks} WHERE id=:id', array('id' => $block_id), 'Block');
		$this->assertEquals( $updated_block->title, $updated_title );
		$this->assertEquals( $updated_block->type, $updated_type );
	}

	public function test_delete_block()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

}
?>
