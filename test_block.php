<?php

include 'bootstrap.php';

class BlockTest extends UnitTestCase
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
		$this->assert_true( $block instanceof Block, 'Created object should be a Block');
		$this->assert_true( isset( $block->title ) );
		$this->assert_false( isset( $block->thiskeydoesnotexist ) );
		$this->assert_equal($block->title, $this->title, 'Block title should be the title passed to the constructor' );
		$this->assert_equal($block->type, $this->type, 'Block type should be the type passed to the construtor' );
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

		$this->assert_equal( $count + 1, DB::get_value('SELECT count(*) FROM {blocks}'), 'Count of blocks should increase by one' );

		$block->delete();
	}

	public function test_update_block()
	{
		$params = array(
			'title' => $this->title,
			'type' => $this->type
		);
		$block = new Block($params);

		$block->insert();
		$block_id = $block->id;

		$updated_title = 'Updated Block Title';
		$updated_type = 'Updated Block Type';
		$block->title = $updated_title;
		$block->type = $updated_type;
		$block->update();

		$updated_block = DB::get_row('SELECT * FROM {blocks} WHERE id=:id', array('id' => $block_id), 'Block');
		$this->assert_equal( $updated_block->title, $updated_title, 'Block title should be updated' );
		$this->assert_equal( $updated_block->type, $updated_type, 'Block type should be updated' );

		// Try updating data as well
		$block->data_test = 'foo';
		$block->update();

		$updated_block = DB::get_row('SELECT * FROM {blocks} WHERE id=:id', array('id' => $block_id), 'Block');
		$this->assert_equal( $updated_block->data_test, $block->data_test, 'Block data should be updated' );

		$block->delete();
	}

	public function test_delete_block()
	{
		$params = array(
			'title' => $this->title,
			'type' => $this->type
		);
		$block = new Block($params);

		$block->insert();

		$count = DB::get_value('SELECT count(*) FROM {blocks}');

		$block->delete();

		$this->assert_equal( $count - 1, DB::get_value('SELECT count(*) FROM {blocks}'), 'Count of blocks should decrease by one' );
	}

}

BlockTest::run_one( 'BlockTest' );

?>
