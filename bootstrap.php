<?php

/**
* Habari unit test bootstrap file
*
* How to use:
* Step 1: Create a symlink to the tests directory within the htdocs directory
* Step 2: Include this file at the beginning of a test
**/

define('HABARI_PATH', dirname( dirname( __FILE__ ) ) );
define('UNIT_TEST', true);
define('DEBUG', true);

class UnitTestCase
{
	public $messages = array();
	public $passes = 0;
	public $fails = 0;

	public function assert_true($value, $message = 'Assertion failed')
	{
		if($value !== true) {
			$this->messages[] = array($message, debug_backtrace());
			$this->fails++;
		}
		else {
			$this->passes++;
		}
	}

	public function assert_false($value, $message = 'Assertion failed')
	{
		if($value !== false) {
			$this->messages[] = array($message, debug_backtrace());
			$this->fails++;
		}
		else {
			$this->passes++;
		}
	}

	public function named_test_filter( $function_name )
	{
		return preg_match('%^test_%', $function_name);
	}

	public function run()
	{
		$methods = get_class_methods($this);
		$methods = array_filter($methods, array($this, 'named_test_filter'));
		$cases = 0;
		echo '<h1>' . get_class($this) . '</h1>';

		foreach($methods as $method) {
			$this->messages = array();
			
			if(method_exists($this, 'setup')) {
				$this->setup();
			}

			echo '<h2>' . $method . '</h2>';

			$this->$method();

			foreach($this->messages as $message) {
				echo '<div><em>Fail:</em> ' . $message[0] . '<br/>' . $message[1][0]['file'] . ':' . $message[1][0]['line'] . '</div>';
			}

			if(method_exists($this, 'teardown')) {
				$this->teardown();
			}

			$cases++;
		}


		echo "<div>{$cases}/{$cases} complete.  {$this->fails} failed tests.  {$this->passes} passed tests.</div>";
	}
}

include '../index.php';

?>