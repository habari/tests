<?php

include '../bootstrap.php';

class BitmaskTest extends UnitTestCase

{

	public function setup()
	{
		$this->access_names = array( 'read', 'edit', 'delete', 'create' );
		$this->bitmask = new Bitmask($this->access_names);
	}

	public function test_constructor()
	{
		$mask = new Bitmask( array( 'dog', 'cat' ), 3 );
		$this->assert_true( $mask->dog );
		$this->assert_true( $mask->cat );
		
		$mask = new Bitmask( array( 'dog', 'cat' ), '3' );
		$this->assert_true( $mask->dog );
		$this->assert_true( $mask->cat );

		$mask = new Bitmask( array( 'dog', 'cat' ), 'dog' );
		$this->assert_true( $mask->dog );
		$this->assert_false( $mask->cat );

		$mask = new Bitmask( array( 'dog', 'cat' ), 'full' );
		$this->assert_true( $mask->dog );
		$this->assert_true( $mask->cat );

		$mask = new Bitmask( array( 'flags' ) );
		$this->assert_false( $mask->flags );

		$mask = new Bitmask( array( 'flags' ), 'flags' );
		$this->assert_true( $mask->flags );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_constructor_invalid_first_argument()
	{
		$this->assert_exception('InvalidArgumentException');
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
		$this->assert_exception('InvalidArgumentException');
		$mask = new Bitmask( array( 'dog', 'cat' ), 'giraffe' );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_constructor_invalid_second_argument_flags()
	{
		$this->assert_exception('InvalidArgumentException');
		$mask = new Bitmask( array( 'dog', 'cat' ), 'flags' );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_constructor_invalid_second_argument_array()
	{
		$this->assert_exception('InvalidArgumentException');
		$mask = new Bitmask( array( 'dog', 'cat' ), array() );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_constructor_invalid_second_argument_int_too_small()
	{
		$this->assert_exception('InvalidArgumentException');
		$mask = new Bitmask( array( 'dog', 'cat' ), -1 );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_constructor_invalid_second_argument_int_too_large()
	{
		$this->assert_exception('InvalidArgumentException');
		$mask = new Bitmask( array( 'dog', 'cat' ), 4 );
	}

	public function test_write_by_name()
	{
		$this->bitmask->read = true;
		$this->assert_equal(1, $this->bitmask->value);

		$this->bitmask->edit = true;
		$this->assert_equal(3, $this->bitmask->value);

		$this->bitmask->delete = true;
		$this->assert_equal(7, $this->bitmask->value);

		$this->bitmask->create = true;
		$this->assert_equal(15, $this->bitmask->value);

		$this->bitmask->read = false;
		$this->assert_equal(14, $this->bitmask->value);

		$this->bitmask->edit = false;
		$this->assert_equal(12, $this->bitmask->value);

		$this->bitmask->delete = false;
		$this->assert_equal(8, $this->bitmask->value);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_write_by_name_non_bool()
	{
		$this->assert_exception('InvalidArgumentException');
		$this->bitmask->read = 1;
	}

	public function test_write_by_value()
	{
		$this->bitmask->value = 1;
		$this->assert_true($this->bitmask->read, 'Read bit should be true and is not.');
		$this->assert_false($this->bitmask->edit, 'Edit bit should be false and is not.');

		$this->bitmask->value = 2;
		$this->assert_true($this->bitmask->edit);
		$this->assert_false($this->bitmask->delete);

		$this->bitmask->value = 4;
		$this->assert_true($this->bitmask->delete);
		$this->assert_false($this->bitmask->create);

		$this->bitmask->value = 8;
		$this->assert_true($this->bitmask->create);
		$this->assert_false($this->bitmask->read);

		$this->bitmask->value = 14;
		$this->assert_true($this->bitmask->create);
		$this->assert_false($this->bitmask->read);
		$this->assert_true($this->bitmask->delete);
		$this->assert_true($this->bitmask->edit);
		
		$this->bitmask->value = 0;
		$this->assert_false($this->bitmask->create);
		$this->assert_false($this->bitmask->read);
		$this->assert_false($this->bitmask->delete);
		$this->assert_false($this->bitmask->edit);
		
		$this->bitmask->value = '8';
		$this->assert_true($this->bitmask->create);
		$this->assert_false($this->bitmask->read);
		$this->assert_false($this->bitmask->delete);
		$this->assert_false($this->bitmask->edit);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_write_by_value_array()
	{
		$this->assert_exception('InvalidArgumentException');
		$this->bitmask->value = array();
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_write_by_value_int_too_small()
	{
		$this->assert_exception('InvalidArgumentException');
		$this->bitmask->value = -1;
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_write_by_value_int_too_big()
	{
		$this->assert_exception('InvalidArgumentException');
		$this->bitmask->value = 16;
	}

	public function test_write_by_full()
	{
		$this->bitmask->full = true;
		$this->assert_equal( 15, $this->bitmask->value );
		$this->bitmask->full = false;
		$this->assert_equal( 0, $this->bitmask->value );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_write_by_full_non_boolean()
	{
		$this->assert_exception('InvalidArgumentException');
		$this->bitmask->full = 1;
	}

	public function test_write_by_array()
	{
		$mask = array(true, false, false, true);
		$this->bitmask->value = $mask;

		$this->assert_equal(9, $this->bitmask->value);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_write_by_array_non_bool()
	{
		$this->assert_exception('InvalidArgumentException');
		$mask = array( 1, 0, 0, 1 );
		$this->bitmask->value = $mask;
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_write_by_array_too_short()
	{
		$this->assert_exception('InvalidArgumentException');
		$mask = array( 1 );
		$this->bitmask->value = $mask;
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_write_by_array_too_long()
	{
		$this->assert_exception('InvalidArgumentException');
		$mask = array( 1, 0, 0, 1, 1 );
		$this->bitmask->value = $mask;
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_write_nonexistent()
	{
		$this->assert_exception('InvalidArgumentException');
		$this->bitmask->bogus = true;
	}

	public function test_get()
	{
		$this->assert_equal( $this->bitmask->full, 15 );
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_get_nonexistent()
	{
		$this->assert_exception('InvalidArgumentException');
		$foo = $this->bitmask->bogus;
	}

	public function test_isset()
	{
		$this->assert_true( isset( $this->bitmask->full ) );
		$this->assert_true( isset( $this->bitmask->value ) );
		$this->assert_true( isset( $this->bitmask->read ) );
		$this->assert_false( isset( $this->bitmask->bogus ) );
	}

	public function test__tostring()
	{
		$this->bitmask->value = 1;
		$this->assert_equal('read', (string)$this->bitmask);

		$this->bitmask->value = 0;
		$this->assert_equal( 'none', (string)$this->bitmask );

		$this->bitmask->value = 15;
		$this->assert_equal( 'full', (string)$this->bitmask );
	}
}
BitmaskTest::run_one( 'BitmaskTest' );
?>
