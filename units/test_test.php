<?php

class TestTest extends UnitTestCase
{

	public function setup()
	{
	}

	public function test_assertions()
	{
		$a = new stdClass();
		$b = &$a;
		$c = new stdClass();

		$this->assert_true(true);
		$this->assert_false(false);
		$this->assert_equal('a', 'a');
		$this->assert_identical($a, $b);
		$this->assert_equal($a, $c);
		$this->assert_not_identical($a, $c);
		$this->assert_type('stdClass', $a);
	}

}