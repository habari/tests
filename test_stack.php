<?php

include 'bootstrap.php';

class StackTest extends UnitTestCase
{
	function setup()
	{
	}
	
	function test_stack_order()
	{
		Stack::add( 'test_stack', 'a',				'a' );
		Stack::add( 'test_stack', 'after(a)',		'b', 'a' );
		Stack::add( 'test_stack', 'after(b,d,f)',	'c', array('b','d','f') );
		Stack::add( 'test_stack', 'after(b)',		'd', 'b' );
		Stack::add( 'test_stack', 'after(b)',		'e', 'b' );
		Stack::add( 'test_stack', 'after(b)',		'f', 'b' );
		Stack::add( 'test_stack', 'after(e)',		'g', 'e');
		$sorted = Stack::get_sorted_stack('test_stack');
		$first = array_shift($sorted);
		$this->output($sorted);
		$this->assert_true( ($first == 'a') );
	}
}

StackTest::run_one( 'StackTest' );

?>
