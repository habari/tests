<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

class system_classes_BitmaskTest extends PHPUnit_Framework_TestCase
{

	public function setup()
	{
		$this->access_names = array( 'read', 'edit', 'delete', 'create' );
		$this->bitmask = new Bitmask($this->access_names);
	}

	public function test_constructor()
	{
		$mask = new Bitmask( array( 'dog', 'cat' ), 3 );
		$this->assertTrue( $mask->dog );
		$this->assertTrue( $mask->cat );
		
		$mask = new Bitmask( array( 'dog', 'cat' ), '3' );
		$this->assertTrue( $mask->dog );
		$this->assertTrue( $mask->cat );

		$mask = new Bitmask( array( 'dog', 'cat' ), 'dog' );
		$this->assertTrue( $mask->dog );
		$this->assertFalse( $mask->cat );

		$mask = new Bitmask( array( 'dog', 'cat' ), 'full' );
		$this->assertTrue( $mask->dog );
		$this->assertTrue( $mask->cat );

		$mask = new Bitmask( array( 'flags' ) );
		$this->assertFalse( $mask->flags );

		$mask = new Bitmask( array( 'flags' ), 'flags' );
		$this->assertTrue( $mask->flags );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_constructor_invalid_first_argument()
	{
		$mask = new Bitmask( 'brute' );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_constructor_invalid_flag_name_full()
	{
		$mask = new Bitmask( array( 'full' ) );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_constructor_invalid_flag_name_value()
	{
		$mask = new Bitmask( array( 'value' ) );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_constructor_duplicate_flag_name()
	{
		$mask = new Bitmask( array( 'foo', 'foo' ) );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_constructor_non_string_flag_name()
	{
		$mask = new Bitmask( array( 1 ) );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_constructor_invalid_second_argument_nonexistent_flag_name()
	{
		$mask = new Bitmask( array( 'dog', 'cat' ), 'giraffe' );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_constructor_invalid_second_argument_flags()
	{
		$mask = new Bitmask( array( 'dog', 'cat' ), 'flags' );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_constructor_invalid_second_argument_array()
	{
		$mask = new Bitmask( array( 'dog', 'cat' ), array() );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_constructor_invalid_second_argument_int_too_small()
	{
		$mask = new Bitmask( array( 'dog', 'cat' ), -1 );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_constructor_invalid_second_argument_int_too_large()
	{
		$mask = new Bitmask( array( 'dog', 'cat' ), 4 );
	}

	public function test_write_by_name()
	{
		$this->bitmask->read = true;
		$this->assertEquals(1, $this->bitmask->value);

		$this->bitmask->edit = true;
		$this->assertEquals(3, $this->bitmask->value);

		$this->bitmask->delete = true;
		$this->assertEquals(7, $this->bitmask->value);

		$this->bitmask->create = true;
		$this->assertEquals(15, $this->bitmask->value);

		$this->bitmask->read = false;
		$this->assertEquals(14, $this->bitmask->value);

		$this->bitmask->edit = false;
		$this->assertEquals(12, $this->bitmask->value);

		$this->bitmask->delete = false;
		$this->assertEquals(8, $this->bitmask->value);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_write_by_name_non_bool()
	{
		$this->bitmask->read = 1;
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
		$this->assertTrue($this->bitmask->edit);
		
		$this->bitmask->value = 0;
		$this->assertFalse($this->bitmask->create);
		$this->assertFalse($this->bitmask->read);
		$this->assertFalse($this->bitmask->delete);
		$this->assertFalse($this->bitmask->edit);
		
		$this->bitmask->value = '8';
		$this->assertTrue($this->bitmask->create);
		$this->assertFalse($this->bitmask->read);
		$this->assertFalse($this->bitmask->delete);
		$this->assertFalse($this->bitmask->edit);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_write_by_value_array()
	{
		$this->bitmask->value = array();
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_write_by_value_int_too_small()
	{
		$this->bitmask->value = -1;
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_write_by_value_int_too_big()
	{
		$this->bitmask->value = 16;
	}

	public function test_write_by_full()
	{
		$this->bitmask->full = true;
		$this->assertEquals( 15, $this->bitmask->value );
		$this->bitmask->full = false;
		$this->assertEquals( 0, $this->bitmask->value );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_write_by_full_non_boolean()
	{
		$this->bitmask->full = 1;
	}

	public function test_write_by_array()
	{
		$mask = array(true, false, false, true);
		$this->bitmask->value = $mask;

		$this->assertEquals(9, $this->bitmask->value);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_write_by_array_non_bool()
	{
		$mask = array( 1, 0, 0, 1 );
		$this->bitmask->value = $mask;
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_write_by_array_too_short()
	{
		$mask = array( 1 );
		$this->bitmask->value = $mask;
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_write_by_array_too_long()
	{
		$mask = array( 1, 0, 0, 1, 1 );
		$this->bitmask->value = $mask;
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_write_nonexistent()
	{
		$this->bitmask->bogus = true;
	}

	public function test_get()
	{
		$this->assertEquals( $this->bitmask->full, 15 );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_get_nonexistent()
	{
		$foo = $this->bitmask->bogus;
	}

	public function test_isset()
	{
		$this->assertTrue( isset( $this->bitmask->full ) );
		$this->assertTrue( isset( $this->bitmask->value ) );
		$this->assertTrue( isset( $this->bitmask->read ) );
		$this->assertFalse( isset( $this->bitmask->bogus ) );
	}

	public function test__tostring()
	{
		$this->bitmask->value = 1;
		$this->assertEquals('read', (string)$this->bitmask);

		$this->bitmask->value = 0;
		$this->assertEquals( 'none', (string)$this->bitmask );

		$this->bitmask->value = 15;
		$this->assertEquals( 'full', (string)$this->bitmask );
	}
}
?>
