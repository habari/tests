<?php
namespace Habari;

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

	function test_stack_item_versions()
	{
		StackItem::register('foo', 'foo2', '2.0');
		StackItem::register('foo', 'foo1', '1.0');

		$item = StackItem::get('foo');
		$this->assert_equal( (string)$item, 'foo2' );
	}

	function test_stack_item_lambda()
	{
		StackItem::register('bar', function(){
			return 'true';
		});

		$item = StackItem::get('bar');
		$this->assert_equal( (string)$item, 'true' );
	}

	function test_stack_duplicate_dependencies()
	{
		Stack::add( 'test_duplicates', 'http://example.com/deep.js', 'deep' );
		Stack::add( 'test_duplicates', 'http://example.com/dependent.js', 'dependent', 'deep' );
		Stack::add( 'test_duplicates', 'http://localhost/one.js', 'one', 'dependent' );
		Stack::add( 'test_duplicates', 'http://localhost/two.js', 'two', 'dependent' );

		$sorted = Stack::get_sorted_stack('test_duplicates');
		$this->output(implode(', ', $sorted));
		$this->assert_equal( implode(', ', $sorted), 'http://example.com/deep.js, http://example.com/dependent.js, http://localhost/one.js, http://localhost/two.js' );
	}

	function test_stack_circular_dependencies()
	{
		// Don't do this!  But it shouldn't lock Habari up.
		Stack::add( 'test_circular', 'http://example.com/first.js', 'first', 'second' );
		Stack::add( 'test_circular', 'http://example.com/second.js', 'second', 'first' );

		// If the sort fails, the system locks up.
		$sorted = Stack::get_sorted_stack('test_circular');
		$this->output(implode(', ', $sorted));
		$this->assert_equal( implode(', ', $sorted), 'http://example.com/first.js, http://example.com/second.js' );

		// More complex
		Stack::add( 'test_circular_2', 'http://example.com/circular_a.js', 'circular_a', 'circular_b' );
		Stack::add( 'test_circular_2', 'http://example.com/circular_b.js', 'circular_b', 'circular_c' );
		Stack::add( 'test_circular_2', 'http://example.com/circular_c.js', 'circular_c', 'circular_a' );

		// If the sort fails, the system locks up.
		$sorted = Stack::get_sorted_stack('test_circular_2');
		$this->output(implode(', ', $sorted));
		$this->assert_equal( implode(', ', $sorted), 'http://example.com/circular_a.js, http://example.com/circular_c.js, http://example.com/circular_b.js' );
	}

	function test_stack_duplicate_names()
	{
		Stack::add( 'test_dupe_names', 'http://example.com/dupedep.js', 'dupedep' );
		Stack::add( 'test_dupe_names', 'http://example.com/dupe1.js', 'dupe', 'dupedep' );
		Stack::add( 'test_dupe_names', 'http://example.com/dupe2.js', 'dupe', 'dupedep' );

		$sorted = Stack::get_sorted_stack('test_dupe_names');
		$this->output(implode(', ', $sorted));
		$this->assert_equal( implode(', ', $sorted), 'http://example.com/dupedep.js, http://example.com/dupe2.js' );

	}

}
?>
