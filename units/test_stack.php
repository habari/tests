<?php

class StackTest extends UnitTestCase
{
	function setup()
	{
	}

	function test_stack_order()
	{
		Stack::add( 'test_stack', 'a', 'a' );
		Stack::add( 'test_stack', 'b after(a)', 'b', 'a' );
		$sorted = Stack::get_sorted_stack('test_stack');
		$this->assert_equal( implode(', ', $sorted), 'a, b after(a)' );

		Stack::add( 'test_stack', 'c after(b,d,f)', 'c', array('b','d','f') );
		$sorted = Stack::get_sorted_stack('test_stack');
		$this->assert_equal( implode(', ', $sorted), 'a, b after(a), c after(b,d,f)' );

		Stack::add( 'test_stack', 'd after(b)', 'd', 'b' );
		$sorted = Stack::get_sorted_stack('test_stack');
		$this->assert_equal( implode(', ', $sorted), 'a, b after(a), d after(b), c after(b,d,f)' );

		Stack::add( 'test_stack', 'e after(b)', 'e', 'b' );
		$sorted = Stack::get_sorted_stack('test_stack');
		$this->assert_equal( implode(', ', $sorted), 'a, b after(a), d after(b), c after(b,d,f), e after(b)' );

		Stack::add( 'test_stack', 'f after(b)', 'f', 'b' );
		$sorted = Stack::get_sorted_stack('test_stack');
		$this->assert_equal( implode(', ', $sorted), 'a, b after(a), d after(b), f after(b), e after(b), c after(b,d,f)' );

		Stack::add( 'test_stack', 'g after(e)', 'g', 'e');
		$sorted = Stack::get_sorted_stack('test_stack');
		$this->output(implode(', ', $sorted));
		$this->assert_equal( implode(', ', $sorted), 'a, b after(a), d after(b), f after(b), c after(b,d,f), e after(b), g after(e)' );
	}

	function test_stack_items()
	{
		StackItem::register('jquery', '1.7');
		StackItem::register('jquery.ui', '1.3')->add_dependency('jquery', '1.7');

		Stack::add('test_stack', StackItem('jquery'));
	}
}
?>
