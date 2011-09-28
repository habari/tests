<?php

/**
* Habari unit test bootstrap file
*
* How to use:
* Step 1: Create a symlink to the tests directory within the htdocs directory
* Step 2: Include this file at the beginning of a test
**/

if( function_exists( 'getopt' ) ) {
	$shortopts = 'c::t::r::o';
	$longopts = array();
	$options = getopt($shortopts, $longopts);
}
if(!isset($options) || !$options) {
	$options = array();
}
global $querystring_options;
if(!isset($querystring_options)) {
	$querystring_options = array_intersect_key($_GET, array('o'=>1,'t'=>'','c'=>''));
	$options = array_merge($options, $querystring_options);
}

if(!defined('HABARI_PATH')) {
	if(isset($options['r'])) {
		define('HABARI_PATH', $options['r']);
	}
	else {
		// Try traversing up until we find an index.php
		$dirname = dirname(dirname( __FILE__ ));
		while(!file_exists($dirname . '/index.php')) {
			$dirname = dirname($dirname);
			if(strlen($dirname) <= 1) {
				throw new Exception("Couldn't find Habari's index.php");
			}
		}
		define('HABARI_PATH', $dirname );
	}
}

if(!defined('UNIT_TEST')) {
	define('UNIT_TEST', true);
}
if(!defined('DEBUG')) {
	define('DEBUG', true);
}

if(!class_exists('UnitTestCase')):

class UnitTestCase
{
	const FAIL = 0;
	const INCOMPLETE = 1;
	const SKIP = 2;

	static $run_all = false;

	public $messages = array();
	public $pass_count = 0;
	public $fail_count = 0;
	public $incomplete_count = 0;
	public $exception_count = 0;
	public $case_count = 0;
	public $total_case_count = 0;
	public $skipped_count = 0;

	private $exceptions = array();
	private $checks = array();
	private $asserted_exception = null;
	protected $show_output = false;
	protected $conditions = array();

	public function assert_true($value, $message = 'Assertion failed')
	{
		if($value !== true) {
			$this->messages[] = array(self::FAIL, $message, debug_backtrace());
			$this->fail_count++;
		}
		else {
			$this->pass_count++;
		}
	}

	public function assert_false($value, $message = 'Assertion failed')
	{
		if($value !== false) {
			$this->messages[] = array(self::FAIL, $message, debug_backtrace());
			$this->fail_count++;
		}
		else {
			$this->pass_count++;
		}
	}

	public function assert_equal($value1, $value2, $message = 'Assertion failed')
	{
		if($value1 != $value2) {
			$this->messages[] = array(self::FAIL, $message, debug_backtrace());
			$this->fail_count++;
		}
		else {
			$this->pass_count++;
		}
	}

	public function assert_not_equal($value1, $value2, $message = 'Assertion failed')
	{
		if($value1 == $value2) {
			$this->messages[] = array(self::FAIL, $message, debug_backtrace());
			$this->fail_count++;
		}
		else {
			$this->pass_count++;
		}
	}

	public function assert_identical($value1, $value2, $message = 'Assertion failed')
	{
		if($value1 !== $value2) {
			$this->messages[] = array(self::FAIL, $message, debug_backtrace());
			$this->fail_count++;
		}
		else {
			$this->pass_count++;
		}
	}

	public function assert_exception($exception = '', $message = 'Expected exception')
	{
		$this->asserted_exception = array($exception, $message);
	}

	public function assert_type( $type, $object, $message = 'Types not equal' )
	{
		$class = get_class( $object );
		if( $class != $type ) {
			$this->messages[] = array(self::FAIL, $message, debug_backtrace());
			$this->fail_count++;
		}
		else {
			$this->pass_count++;
		}
	}

	public function mark_test_incomplete( $message = 'Tests not implemented' )
	{
		$this->messages[] = array( self::INCOMPLETE, $message);
		$this->incomplete_count++;
	}

	public function output($v)
	{
		$this->show_output = true;
		print_r($v);
		echo "\n";
	}

	public function check($checkval, $message = 'Expected check')
	{
		$this->checks[$checkval] = $message;
	}

	public function pass_check($checkval)
	{
		unset($this->checks[$checkval]);
	}

	public function add_condition($condition, $reason = false)
	{
		$this->conditions[$condition] = $reason;
	}

	public function named_test_filter( $function_name )
	{
		return preg_match('%^test_%', $function_name);
	}

	private final function pre_test()
	{
		$this->asserted_exceptions = array();
		$this->exceptions = array();
		$this->checks = array();
	}

	private final function post_test()
	{
		if(isset($this->asserted_exception)) {
			$this->fail_count++;
			$this->messages[] = array(self::FAIL, $this->asserted_exception[1] . ': ' . $this->asserted_exception[0]);
		}
		foreach($this->checks as $check => $message) {
			$this->fail_count++;
			$this->messages[] = array(self::FAIL, $message);
		}
	}

	public function run($results)
	{
		global $options;
		$this->options = $options;

		$methods = get_class_methods($this);
		$methods = array_filter($methods, array($this, 'named_test_filter'));
		$cases = 0;

		$class = new ReflectionClass( get_class( $this ) );

		$results->test(get_class($this), $class->getFileName());

		if(method_exists($this, 'module_setup')) {
			$this->module_setup();
		}
		if(isset($options['t'])) {
			$options['t'] = explode(',', $options['t']);
			if(count($options['t']) == 0) {
				unset($options['t']);
			}
		}

		foreach($methods as $method) {
			$this->messages = array();
			$this->show_output = false;
			$this->total_case_count++;

			if(isset($options['t'])) {
				$ref_method = new ReflectionMethod($this, $method);
				$start_line = $ref_method->getStartLine();
				$found = false;
				foreach($options['t'] as $line) {
					if($line > $ref_method->getStartLine() && $line < $ref_method->getEndLine()) {
						$found = true;
						break;
					}
					if(strtolower($ref_method->getName()) == strtolower($line)) {
						$found = true;
						break;
					}
				}
				if(!$found) {
					continue;
				}
			}

			$do_skip = false;
			if(count($this->conditions) > 0) {
				if(preg_match('%^test_(' . implode('|', array_keys($this->conditions)) . ')_.+%i', $method, $condition_matches)) {
					if(isset($this->conditions[$condition_matches[1]]) && is_string($this->conditions[$condition_matches[1]])) {
						$this->messages[] = array(self::SKIP, $this->conditions[$condition_matches[1]]);
						$do_skip = true;
						$this->skipped_count++;
					}
				}
			}

			if(!$do_skip) {
				$this->pre_test();
				if(method_exists($this, 'setup')) {
					$this->setup();
				}

				try {
					ob_start();
					$this->$method();
					$output = ob_get_clean();
				}
				catch(Exception $e) {
					if(strpos($e->getMessage(), $this->asserted_exception[0]) !== false || get_class($e) == $this->asserted_exception[0]) {
						$this->pass_count++;
						$this->asserted_exception = null;
					}
					else {
						$this->exception_count++;
						$trace = $e->getTrace();
						$ary = current($trace);
						while( !isset($ary['file']) || strpos($ary['file'], 'error.php') != false ) {
							$ary = next($trace);
						}
						$ary = current($trace);
						$this->messages[] = array(self::FAIL, get_class($e) . ':' . $e->getMessage(), array($ary['file'] . ':' . $ary['line']));
	//					echo '<div><em>Exception '. get_class($e) .':</em> ' . $e->getMessage() . '<br/>' . $ary['file'] . ':' . $ary['line'] . '</div>';
	//					echo '<pre>' . print_r($trace, 1) . '</pre>';
					}
				}

				if(method_exists($this, 'teardown')) {
					$this->teardown();
				}
				$this->post_test();

				if($this->show_output) {
					$this->messages[] = $output;
				}
			}

			$results->method_results(get_class($this), $method, $this->messages);

			$this->case_count++;
		}
		
		if(method_exists($this, 'module_teardown')) {
			$this->module_teardown();
		}

		$results->summary(get_class($this), get_object_vars($this));

		return $results;
	}

	public static function run_one($classname)
	{
		if(self::$run_all) {
			return;
		}
		$testobj = new $classname();

		$testobj->run($results = new UnitTestResults());
		echo $results;
	}

	public static function run_all()
	{
		$pass_count = 0;
		$fail_count = 0;
		$exception_count = 0;
		$case_count = 0;

		self::$run_all = true;
		$classes = get_declared_classes();
		$classes = array_unique($classes);
		sort($classes);
		$results = new UnitTestResults();
		foreach($classes as $class) {
			$parents = class_parents($class, false);
			if(in_array('UnitTestCase', $parents)) {
				$obj = new $class();
				$obj->run($results);

				$pass_count += $obj->pass_count;
				$fail_count += $obj->fail_count;
				$exception_count += $obj->exception_count;
				$case_count += $obj->case_count;
			}
		}

		echo $results;
	}

	public static function run_dir($directory = null)
	{
		self::$run_all = true;
		if(!isset($directory)) {
			$directory = dirname(__FILE__);
		}
		$tests = glob($directory . '/test_*.php');
		foreach($tests as $test) {
			include($test);
		}
		self::run_all();
	}
}

class UnitTestResults
{
	private $methods = array();
	private $tests = array();
	private $summaries = array();
	private $options = array();
	private $type;

	function __construct()
	{
		global $options;
		$this->options = $options;
		$this->options['HABARI_PATH'] = HABARI_PATH;
		$this->type = array(
		    'Fail: ',
		    'Incomplete: ',
		    'Skipped: ',
		);
	}

	function __toString()
	{
		global $options;
		$default_output = defined('STDIN') ? 'console' : 'html';
		if(isset($options['c'])) {
			switch($options['c']) {
				case 'console':
				case 'c':
					$default_output = 'console';
					break;
				case 'html':
				case 'h':
					$default_output = 'html';
					break;
			}
		}
		switch($default_output) {
			case 'console':
				header('content-type: text/plain');
				return $this->out_console();
			case 'html':
				header('content-type: text/html');
				return $this->out_html();
		}
	}

	function test($test, $file = null)
	{
		$file = ltrim(str_replace(dirname(__FILE__), '', $file), '\\/');
		$this->tests[$test] = $file;
	}

	function initial_results()
	{
		return array('total_case_count'=>0, 'case_count'=>0, 'fail_count'=>0, 'pass_count'=>0, 'exception_count'=>0, 'incomplete_count'=>0, 'skipped_count'=>0);
	}

	function out_html()
	{
		$has_output = false;
		$totals = $this->initial_results();

		if(count($this->tests) > 1) {
			$title = "Test Results for " . count($this->tests) . " tests";
		}
		else {
			$title = "Test Results for " . reset($this->tests);
		}

		$output = "<!DOCTYPE HTML><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"><title>{$title}</title>" .
			'<link rel="stylesheet" type="text/css" href="style.css">' .
			"</head><body>";
		foreach($this->tests as $test => $file) {
			$output .= "<h1>{$test}<a href=\"{$file}\" style=\"font-size: xx-small;font-weight: normal;margin-left: 20px;\">{$file}</a></h1>";

			if(!isset($this->methods[$test])) {
				$this->methods[$test] = array();
				$this->summaries[$test] = $this->initial_results();
			}

			foreach($this->methods[$test] as $methodname => $messages)
			{
				$output .= "<h2>{$methodname}</h2>";
				foreach($messages as $message) {
					if(is_string($message)) {
						if(isset($this->options['o'])) {
							$output .= "<div style=\"white-space:pre;border: 1px solid #ccc;padding: 0px 10px 10px;background: #efefef;\"><h3>Output</h3>{$message}</div>";
						}
						else {
							$has_output = true;
						}
					}
					else {
						$output .= "<div><em>{$this->type[$message[0]]}</em> {$message[1]}";
						if(count($message) > 2) {
							$output .= '<br/>' . $message[2][0]['file'] . ':' . $message[2][0]['line'];
						}
						$output .= '</div>';
					}
				}
			}

			$summary = $this->summaries[$test];
			foreach($summary as $k => $v) {
				if(isset($totals[$k]) && is_numeric($v)) {
					$totals[$k] += $v;
				}
			}
			$output .= "<div class=\"test complete\"><p>{$summary['case_count']}/{$summary['total_case_count']} tests complete.  {$summary['fail_count']} failed assertions.  {$summary['pass_count']} passed assertions.  {$summary['exception_count']} exceptions.  {$summary['incomplete_count']} incomplete tests.  {$summary['skipped_count']} skipped tests.</p></div>";
		}

		$output .= '<footer><h3>Results</h3>';
		$output.= sprintf('<div class="all test complete">%d units containing %d tests. %d failed assertions.  %d passed assertions.  %d exceptions.  %d incomplete tests.  %d skipped tests.</div>', count($this->tests), $totals['case_count'], $totals['fail_count'], $totals['pass_count'], $totals['exception_count'], $totals['incomplete_count'], $totals['skipped_count']);

		if($has_output) {
			$output .= "<div class=\"has_output\">Some tests have output.  <a href=\"?o=1\">Turn on the output option</a> to see output.</div>";
		}

		$output .= '<h3>Options</h3><table>';
		foreach($this->options as $k => $v) {
			$output .= "<tr><th>{$k}</th><td>{$v}</td></tr>";
		}
		$output .= '</table></footer>';

		$output .= '</body></html>';

		return $output;
	}

	function out_console()
	{
		$has_output = false;
		$totals = $this->initial_results();

		if(count($this->tests) > 1) {
			$title = "Test Results for " . count($this->tests) . " tests";
		}
		else {
			$title = "Test Results for " . reset($this->tests);
		}

		$output = array();
		$output[] = "==== {$title} ====";
		foreach($this->tests as $test => $file) {
			$output[]= "\n=== {$test} ===";

			if(!isset($this->methods[$test])) {
				$this->methods[$test] = array();
				$this->summaries[$test] = $this->initial_results();
			}

			foreach($this->methods[$test] as $methodname => $messages)
			{
				$output[]= "  {$methodname}";
				foreach($messages as $message) {
					if(is_string($message)) {
						if(isset($this->options['o'])) {
							$output[]= "\n          == Begin Output ==\n";
							$message = explode("\n", $message);
							$message = array_map(create_function('$s', 'return "          " . $s;'), $message);
							$output = array_merge($output, $message);
							$output[]= "          ==  End Output  ==";
						}
						else {
							$has_output = true;
						}
					}
					else {
						$output[]= str_pad($this->type[$message[0]], 10, ' ', STR_PAD_LEFT ) . $message[1];
						if(count($message) > 2) {
							$output[]= '      ' . $message[2][0]['file'] . ':' . $message[2][0]['line'];
						}
					}
				}
			}

			$summary = $this->summaries[$test];
			foreach($summary as $k => $v) {
				if(isset($totals[$k]) && is_numeric($v)) {
					$totals[$k] += $v;
				}
			}
			$output[]= sprintf("\n%d/%d tests complete.  %d failed assertions.  %d passed assertions.  %d exceptions.  %d incomplete tests.", $summary['case_count'], $summary['total_case_count'], $summary['fail_count'], $summary['pass_count'], $summary['exception_count'], $summary['incomplete_count']);
		}

		$output[]= "\n=== Results ===";
		$output[]= sprintf('%d units containing %d tests.  %d tests run.  %d failed assertions.  %d passed assertions.  %d exceptions.  %d incomplete tests.', count($this->tests), $totals['total_case_count'], $totals['case_count'], $totals['fail_count'], $totals['pass_count'], $totals['exception_count'], $totals['incomplete_count']);
		if($has_output) {
			$output[]= "\nSome tests have output.  Run again with -o to see output.";
		}

		$output[]= "\n=== Options ===";
		foreach($this->options as $k => $v) {
			$output[]= "  {$k}: {$v}";
		}

		return implode("\n", $output) . "\n";
	}

	function method_results($test, $method, $results)
	{
		$this->methods[$test][$method] = $results;
	}

	function summary($test, $values)
	{
		$this->summaries[$test] = $values;
	}
}

include HABARI_PATH . '/index.php';

endif;

?>
