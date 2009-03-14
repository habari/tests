<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

class system_classes_BitmaskTest extends PHPUnit_Framework_TestCase
{

	public function setup()
	{
		$this->access_names = array( 'read', 'edit', 'delete', 'create' );
		$this->bitmask = new Bitmask($this->access_names);
	}

	public function test_write_by_name()
	{
		$this->bitmask->read = true;
		$this->assertEquals($this->bitmask->value, 1);

		$this->bitmask->edit = true;
		$this->assertEquals($this->bitmask->value, 3);

		$this->bitmask->delete = true;
		$this->assertEquals($this->bitmask->value, 7);

		$this->bitmask->create = true;
		$this->assertEquals($this->bitmask->value, 15);

		$this->bitmask->read = false;
		$this->assertEquals($this->bitmask->value, 14);

		$this->bitmask->edit = false;
		$this->assertEquals($this->bitmask->value, 12);

		$this->bitmask->delete = false;
		$this->assertEquals($this->bitmask->value, 8);
	}

	public function test_write_by_value()
	{
		$this->bitmask->value = 1;
		$this->assertTrue($this->bitmask->read, 'Read bit should be true and is not.');
		$this->assertFalse($this->bitmask->edit, 'Edit bit should be false and is not.');

		$this->bitmask->value = 2;
		$this->assertTrue($this->bitmask->edit);
		$this->assertFalse($this->bitmask->delete);

		$this->bitmask->value = 4;
		$this->assertTrue($this->bitmask->delete);
		$this->assertFalse($this->bitmask->create);

		$this->bitmask->value = 8;
		$this->assertTrue($this->bitmask->create);
		$this->assertFalse($this->bitmask->read);

		$this->bitmask->value = 14;
		$this->assertTrue($this->bitmask->create);
		$this->assertFalse($this->bitmask->read);
		$this->assertTrue($this->bitmask->delete);
		$this->assertFalse($this->bitmask->edit);

	}

	public function test__tostring()
	{
		$this->bitmask->value = 1;
		$this->assertEquals('read', (string)$this->bitmask);

		$this->markTestIncomplete();
	}

	/**
	 * Ported from old test suite
	 */
	function test_bitmask()
	{
		define('POST_FLAG_ALLOWS_COMMENTS'  ,1);
		define('POST_FLAG_ALLOWS_TRACKBACKS',1 << 1);
		define('POST_FLAG_ALLOWS_PINGBACKS' ,1 << 2);

		$flags= array(
		        'allows_comments'=>POST_FLAG_ALLOWS_COMMENTS
		      , 'allows_trackbacks'=>POST_FLAG_ALLOWS_TRACKBACKS
		      , 'allows_pingbacks'=>POST_FLAG_ALLOWS_PINGBACKS);

		$bitmask= new Bitmask($flags);

		$bitmask->allows_comments   = true;
		$bitmask->allows_trackbacks = false;
		$bitmask->allows_pingbacks  = true;

		$this->assertTrue($bitmask->allows_comments);
		$this->assertFalse($bitmask->allows_trackbacks);
		$this->assertTrue($bitmask->allows_pingbacks);
	}
}
?>