<?php

	require_once 'PHPUnit/Framework.php';
	
	require_once 'Calc.php';

	class CalcTest extends PHPUnit_Framework_TestCase
	{
		
		protected $calc;
		protected $a;
		protected $b;
		
		protected function setUp()
		{
			$this->a = 5;
			$this->b = 10;
			$this->calc = new Calc( $this->a, $this->b );
		}
		
		public function testAdd()
		{
			$this->assertEquals(20,$this->calc->add() );
		}
		
	}
	

?>