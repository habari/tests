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
		$this->assert_equal( implode(', ', $sorted), 'a, b after(a), d after(b), f after(b), c after(b,d,f), e after(b)' );

		Stack::add( 'test_stack', 'g after(e)', 'g', 'e');
		$sorted = Stack::get_sorted_stack('test_stack');
		$this->output(implode(', ', $sorted));
		$this->assert_equal( implode(', ', $sorted), 'a, b after(a), d after(b), f after(b), c after(b,d,f), e after(b), g after(e)' );
	}


	function test_stack_order_items()
	{
		StackItem::register('a', 'a');
		StackItem::register('b', 'b after(a)')->add_dependency('a');
		StackItem::register('c', 'c after(b,d,f)')->add_dependency('b')->add_dependency('d')->add_dependency('f');
		StackItem::register('d', 'd after(b)')->add_dependency('b');
		StackItem::register('e', 'e after(c)')->add_dependency('c');
		StackItem::register('f', 'f after(b,d)')->add_dependency('b')->add_dependency('d');
		StackItem::register('g', 'g after(e)')->add_dependency('e');

		Stack::add( 'test_stack_order_items', 'a');
		Stack::add( 'test_stack_order_items', 'b');
		Stack::add( 'test_stack_order_items', 'c');
		Stack::add( 'test_stack_order_items', 'd');
		Stack::add( 'test_stack_order_items', 'e');
		Stack::add( 'test_stack_order_items', 'f');
		Stack::add( 'test_stack_order_items', 'g');
		$sorted = Stack::get_sorted_stack('test_stack_order_items');
		$this->output(implode(', ', $sorted));
		$this->assert_equal( implode(', ', $sorted), 'a, b after(a), d after(b), f after(b,d), c after(b,d,f), e after(c), g after(e)' );
	}

	function test_stack_items()
	{
		StackItem::register('jquery_ui', 'http://example.com/jquery.ui.js', '1.3')->add_dependency('jquery', '1.7');
		StackItem::register('jquery', 'http://example.com/jquery.js', '1.7');

		//Stack::add( 'test_stack', StackItem::get('jquery_ui') );
		Stack::add( 'test_stack_items', 'http://localhost/my.js', 'my', array('jquery_ui') );
		$sorted = Stack::get_sorted_stack('test_stack_items');
		$this->output(implode(', ', $sorted));
		$this->assert_equal( implode(', ', $sorted), 'http://example.com/jquery.js, http://example.com/jquery.ui.js, http://localhost/my.js' );
	}
}
?>
