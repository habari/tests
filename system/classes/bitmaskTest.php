<?php


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
		print_r($this->bitmask);
		print_r($this->bitmask->read);
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
}
?>