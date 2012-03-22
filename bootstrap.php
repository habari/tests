<?php

/**
* Habari unit test bootstrap file
*
* How to use:
* Step 1: Create a symlink to the tests directory within the htdocs directory
* Step 2: Include this file at the beginning of a test
**/

/**
 * Options:
 *   -d : Dry-run, don't execute tests.
 *   -c {console|html|symbolic} : Output type.
 *   -t {testname|linenumber} : Run a specific test, or multiple separated by commas
 *   -r {path} : Set the root path for habari.
 *   -o : Display output.
 *   -i : Display method timers.
 *   -u {unitname} : Run only the specified units.
 *   -v : Do code coverage.
 */

if(defined('STDIN') && function_exists( 'getopt' ) ) {
	$shortopts = 'u::d::c::t::r::o::vi';
	$options = getopt($shortopts);
}
if(!isset($options) || !$options) {
	$options = array();
}
global $querystring_options;
if(!isset($querystring_options)) {
	$querystring_options = array_intersect_key($_GET, array('o'=>1,'t'=>'','c'=>'','d'=>'','u'=>'','i'=>1,'v'=>''));
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
				throw new \Exception("Couldn't find Habari's index.php");
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

	public $messages = array();
	public $result;

	private $exceptions = array();
	private $checks = array();
	private $asserted_exception = null;
	protected $show_output = false;
	protected $conditions = array();
	protected $methods = array();
	protected $timer_track = array();
	protected $timers = array();

	public function assert_something($true, $message, $file = null, $line = null, $type = self::FAIL)
	{
		if(empty($file) && empty($line)) {
			$trace = debug_backtrace(false);
			$fn = current($trace);
			while($fn['function'] == 'assert_something' || !isset($fn['file']) || basename($fn['file']) == 'bootstrap.php') {
				$fn = next($trace);
			}
			$file = $fn['file'];
			$line = $fn['line'];
		}
		if($true !== true) {
			$this->messages[] = array('type' => self::FAIL, 'message' => $message, 'file' => $file, 'line' => $line);
			$this->result->fail_count++;
		}
		else {
			$this->result->pass_count++;
		}
	}

	public function assert_true($value, $message = 'Assertion failed')
	{
		$this->assert_something(true == $value, $message);
	}

	public function assert_false($value, $message = 'Assertion failed')
	{
		$this->assert_something($value === false, $message);
	}

	public function assert_equal($value1, $value2, $message = 'Assertion failed')
	{
		$this->assert_something($value1 == $value2, $message);
	}

	public function assert_not_equal($value1, $value2, $message = 'Assertion failed')
	{
		$this->assert_something($value1 != $value2, $message);
	}

	public function assert_identical($value1, $value2, $message = 'Assertion failed')
	{
		$this->assert_something($value1 === $value2, $message);
	}

	public function assert_not_identical($value1, $value2, $message = 'Assertion failed')
	{
		$this->assert_something($value1 !== $value2, $message);
	}

	public function assert_exception($exception = '', $message = 'Expected exception')
	{
		$this->asserted_exception = array($exception, $message);
	}

	public function assert_type( $type, $object, $message = 'Types not equal' )
	{
		$class = get_class( $object );
		$this->assert_something($type == $class, $message);
	}

	public function mark_test_incomplete( $message = 'Tests not implemented' )
	{
		$this->messages[] = array( 'type' => self::INCOMPLETE, 'message' => $message);
		$this->result->incomplete_count++;
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

	public function skip_all()
	{
		foreach($this->methods as $method => $data) {
			$this->methods[$method]['go'] = 'Skipping all tests.';
		}
	}

	public function skip_test($name, $reason = 'This test is explicitly skipped.')
	{
		if(isset($this->methods['test_' . $name])) {
			$name = 'test_' . $name;
		}
		if(isset($this->methods[$name])) {
			$this->methods[$name]['go'] = $reason;
		}
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
			$this->result->fail_count++;
			$this->messages[] = array('type' => self::FAIL, 'message' => $this->asserted_exception[1] . ': ' . $this->asserted_exception[0]);
		}
		foreach($this->checks as $check => $message) {
			$this->result->fail_count++;
			$this->messages[] = array('type' => self::FAIL, 'message' => $message);
		}
	}

	public function run_init()
	{
		// Get the list of methods that qualify as tests and mark them as "to test"
		$methods = get_class_methods($this);
		$methods = array_filter($methods, array($this, 'named_test_filter'));

		foreach($methods as $method) {
			$ref_method = new ReflectionMethod($this, $method);
			$method_data = array(
				'go' => true,
				'start_line' => $ref_method->getStartLine(),
				'end_line' => $ref_method->getEndLine(),
				'method_name' => $ref_method->getName(),
			);
			$this->methods[$method] = $method_data;
		}

		// Get class info and build a result object, which will be returned
		$class = new ReflectionClass( get_class( $this ) );
		$this->result = new TestResult(get_class($this), $class->getFileName());
	}

	public function timer_start($name)
	{
		$this->timer_track[$name] = microtime(true);
	}

	public function timer_stop($name)
	{
		$this->timers[$name] = microtime(true) - $this->timer_track[$name];
		unset($this->timer_track[$name]);
	}

	public function run()
	{
		global $options;
		$this->options = $options;

		$this->run_init();

		// Execute any module setup that might exist
		if(method_exists($this, 'module_setup')) {
			$this->module_setup();
		}

		if(isset($options['v'])) {
			xdebug_start_code_coverage( XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE );
		}

		// If specific tests are specified to run within this unit, get that list
		if(isset($options['t'])) {
			$options['t'] = explode(',', $options['t']);
			if(count($options['t']) == 0) {
				unset($options['t']);
			}
		}

		$dryrun = isset($options['d']);

		// Attempt to execute test methods in this unit
		foreach($this->methods as $method => $run_status) {
			$this->messages = array();
			$this->show_output = false;
			$this->result->total_case_count++;

			$do_skip = false;

			// Get test line numbers and skip the test if it's not specified
			if(isset($options['t'])) {
				$found = false;
				foreach($options['t'] as $line) {
					if($line > $run_status['start_line'] && $line < $run_status['end_line']) {
						$found = true;
						break;
					}
					if(strtolower($run_status['method_name']) == strtolower($line)) {
						$found = true;
						break;
					}
				}
				if(!$found) {
					$do_skip = 'Skipped by request';
				}
			}

			/**
			 * If a test module includes a line such as $this->add_condition('mysql', 'Skipping mysql tests');
			 * then the test suite will skip any test named with the prefix test_mysql_*
			 **/
			if(count($this->conditions) > 0) {
				if(preg_match('%^test_(' . implode('|', array_keys($this->conditions)) . ')_.+%i', $method, $condition_matches)) {
					if(isset($this->conditions[$condition_matches[1]]) && is_string($this->conditions[$condition_matches[1]])) {
						$do_skip = $this->conditions[$condition_matches[1]];
					}
				}
			}

			/**
			 * methods (keys) in the method list must have a value of 1
			 * If not, they will be skipped using a message of the value in the list.
			 */
			if($this->methods[$method]['go'] !== true) {
				$do_skip = $this->methods[$method]['go'];
			}


			// Skip tests that are not meant to be run
			if($do_skip) {
				$this->messages[] = array('type' => self::SKIP, 'message' => $do_skip);
				$this->result->skipped_count++;
			}
			else {
				// Reset test results and run any per-test setup method that might exist in the unit
				if(!$dryrun) {
					$this->pre_test();
					if(method_exists($this, 'setup')) {
						$this->timer_start('setup');
						$this->setup();
						$this->timer_stop('setup');
					}
				}


				// Run the actual test
				try {
					ob_start();
					if(!$dryrun) {
						$this->timer_start('run');
						$this->$method();
						$this->timer_stop('run');
					}
					$output = ob_get_clean();
				}
				// If exceptions occurred, determine if we were asserting them or not
				catch(Exception $e) {
					if(strpos($e->getMessage(), $this->asserted_exception[0]) !== false || get_class($e) == $this->asserted_exception[0]) {
						$this->result->pass_count++;
						$this->asserted_exception = null;
					}
					else {
						$this->result->exception_count++;
						$trace = $e->getTrace();
						$ary = current($trace);
						while( !isset($ary['file']) || strpos($ary['file'], 'error.php') != false ) {
							$ary = next($trace);
						}
						$ary = current($trace);
						$this->messages[] = array('type' => self::FAIL, 'message' => get_class($e) . ':' . $e->getMessage(), 'file' => $ary['file'], 'line' => $ary['line']);
					}
				}

				// Run any per-test teardown method that might be specified,
				// then run post_test to check expected exception assertion counts
				if(!$dryrun) {
					if(method_exists($this, 'teardown')) {
						$this->timer_start('teardown');
						$this->teardown();
						$this->timer_stop('teardown');
					}
					$this->post_test();
				}

					if($this->show_output) {
					$this->messages[]['output'] = $output;
				}
			}

			$this->result->method_results($method, $this->messages);
			$this->result->method_timers($method, $this->timers);

			$this->result->case_count++;
		}

		if(isset($options['v'])) {
			$this->result->record_code_coverage(xdebug_get_code_coverage());

			xdebug_stop_code_coverage();
		}

		if(method_exists($this, 'module_teardown')) {
			$this->module_teardown();
		}

		return $this->result;
	}

	public static function run_one() {
		/**
		 * @stub
		 * @todo remove this method
		 **/

	}
}

class FeatureTestCase extends UnitTestCase
{
	public $feature_file;
	public $scenarios = array();
	public $features = array();
	public $steps = array();
	public $feature_contexts = array();

	public function __construct($feature_file)
	{
		$this->feature_file = $feature_file;
	}

	public function run_init()
	{
		// Parse the feature file
		// Create lambda functions for scenarios
		// Add lambdas to the methods array
		$feature_file = file_get_contents($this->feature_file);
		$feature_file = explode("\n", $feature_file);

		$state = '*';
		$features = array();
		$ln = 0;

		foreach($feature_file as $line) {
			$ln++;
			$line = trim($line);
			if(preg_match('#^\s*Feature:\s*(.+)$#i', $line, $matches)) {
				$features[trim($matches[1])] = array(
					'background' => array(),
					'scenarios' => array(),
					'description' => array(array($line, $ln)),
					'full' => array(),
				);
				$feature = trim($matches[1]);
				$state = 'feature';
			}
			elseif(preg_match('#^\s*Background:\s*$#i', $line, $matches)) {
				$features[$feature]['background'] = array(array($line, $ln));
				$state = 'background';
			}
			elseif(preg_match('#^\s*Scenario:\s*(.+)$#i', $line, $matches)) {
				$features[$feature]['scenarios'][trim($matches[1])] = array(array($line, $ln));
				$state = 'scenario';
				$scenario = trim($matches[1]);
			}
			elseif(trim($line) == '' || substr(trim($line), 1, 1) == '#') {
				// Do nothing with this
			}
			elseif($state == 'feature') {
				$features[$feature]['description'][] = array($line, $ln);
			}
			elseif($state == 'background') {
				$features[$feature]['background'][] = array($line, $ln);
			}
			elseif($state == 'scenario') {
				$features[$feature]['scenarios'][$scenario][] = array($line, $ln);
			}
			if($feature != '') {
				$features[$feature]['full'][$ln] = $file;
			}
		}

		// Get the list of methods that qualify as tests and mark them as "to test"
		$methods = array();
		foreach($features as $feature => $feature_content) {
			foreach($feature_content['scenarios'] as $scenario => $scenario_content) {
				$scenario = trim($scenario);
				$fname = strtolower('scenario_' . preg_replace('#[^a-z0-9]+#i', '_', $scenario));
				while(isset($this->scenarios[$fname])) {
					$fname .= '_';
				}
				$this->scenarios[$fname] = array('feature' => $feature, 'scenario' => $scenario);
				$this->methods[$fname] = array(
					'go' => true,
					'start_line' => 0,
					'end_line' => 0,
					'method_name' => $scenario,
				);
			}
		}
		$this->features = $features;

		// Get class info and build a result object, which will be returned
		$class = new ReflectionClass( get_class( $this ) );
		$this->result = new TestResult(get_class($this), $class->getFileName());
	}

	public function __call($name, $params)
	{
		$scenario = $this->scenarios[$name];
		$background = $this->features[$scenario['feature']]['background'];
		$steps = $this->features[$scenario['feature']]['scenarios'][$scenario['scenario']];

		// Construct the story for this scenario
		$story = array();
		foreach($this->features[$scenario['feature']]['description'] as $description) {
			list($description, $linenumber) = $description;
			$story_description[$linenumber] = $description;
		}
		foreach($background as $step) {
			list($step, $linenumber) = $step;
			$story[$linenumber] = $step;
		}
		foreach($steps as $step) {
			list($step, $linenumber) = $step;
			$story[$linenumber] = $step;
		}

		foreach($story as $linenumber => $step) {
			if(preg_match('#(Given|When|Then|And|But)\s+(.+)$#i', $step, $matches)) {
				if(!$this->execute_step($matches[2])) {
					$file = basename($this->feature_file);
					throw new Exception("Step '{$matches[0]}' is not defined in {$file}:#{$linenumber}");
				}
			}
		}

		$this->output($story);
	}

	public function assign_step_map($steps, $classes)
	{
		$this->steps = $steps;
		foreach($classes as $class) {
			$this->feature_contexts[$class] = new $class($this);
		}
	}

	public function execute_step($stripped_step)
	{
		foreach($this->steps as $regex => $fn) {
			if(preg_match($regex, $stripped_step, $matches)) {
				array_shift($matches);
				$fn[0] = $this->feature_contexts[$fn[0]];
				call_user_func_array($fn, $matches);
				return true;
			}
		}
		return false;
	}
}

class TestSuite {

	static $features = array();
	static $step_files = array();
	static $steps = array();
	static $classes = array();
	static $run_all = false;

	public static function run_all()
	{
		global $options;

		if(isset($options['u'])) {
			$options['u'] = explode(',', $options['u']);
			if(count($options['u']) == 0) {
				unset($options['u']);
			}
		}

		$pass_count = 0;
		$fail_count = 0;
		$exception_count = 0;
		$case_count = 0;

		self::$run_all = true;
		$classes = get_declared_classes();
		$classes = array_unique($classes);
		sort($classes);

		$results = new TestResults();
		foreach($classes as $class) {
			if(isset($options['u']) && !in_array($class, $options['u'])) {
				continue;
			}
			$parents = class_parents($class, false);
			if(in_array('UnitTestCase', $parents)) {
				$obj = new $class();
				$results[$class] = $obj->run();
			}
		}

		foreach(self::$features as $feature_file) {
			if(isset($options['u']) && !in_array(basename($feature_file), $options['u'])) {
				continue;
			}
			self::load_steps();
			$obj = new FeatureTestCase($feature_file);
			$obj->assign_step_map(self::$steps, self::$classes);
			$results[$feature_file] = $obj->run();
		}

		while(ob_get_level()) {
			ob_end_clean();
		}
		echo $results;
	}

	public static function load_steps()
	{
		foreach(self::$step_files as $step_file) {
			include $step_file;
			$phpfile = file_get_contents($step_file);
			$tokens = token_get_all($phpfile);
			$state = null;
			$comment = '';
			foreach($tokens as $token_value) {
				if(count($token_value) > 1) {
					list($token, $value) = $token_value;
				}
				else {
					list($token) = $token_value;
				}

				switch($token) {
					case T_DOC_COMMENT:
						$comment = $value;
						$state = 'comment';
						break;
					case T_FUNCTION:
						switch($state) {
							case 'comment':
								$state = 'function';
								break;
							case 'function':;
							case 'class':
								$state = '';
								break;
						}
						break;
					case T_CLASS:
						$state = 'class';
						break;
					case T_EXTENDS:
					case T_IMPLEMENTS:
						$state = '';
						break;
					case T_STRING:
						switch($state) {
							case 'class':
								$class = $value;
								self::$classes[] = $class;
								break;
							case 'function':
								self::register_step($comment, $class, $value);
								$state = '';
								break;
						}
						break;
					case '{':
						$state = '';
						break;
				}
			}
		}
	}

	public static function register_step($comment, $class, $value)
	{
		if(preg_match('#@\s*(Given|When|Then)\s+(/.+?/)#i', $comment, $matches)) {
			if(isset(self::$steps[$matches[1]])) {
				// @todo Re-defined a step!  Throw an error!
			}
			else {
				self::$steps[$matches[2]] = array($class, $value);
			}
		}
	}

	public static function run_one($classname)
	{
		if(self::$run_all) {
			return;
		}
		$testobj = new $classname();

		$results = $testobj->run();
		echo $results;
	}

	public static function run_dir($directory = null)
	{
		self::$run_all = true;
		if(!isset($directory)) {
			$directory = dirname(__FILE__);
		}
		// Find unit tests, include them
		$unit_tests = glob($directory . '/units/test_*.php');
		foreach($unit_tests as $test) {
			include($test);
		}

		// Find feature steps, include them
		$feature_steps = glob($directory . '/steps/step_*.php');
		foreach($feature_steps as $step) {
			include($step);
		}

		// Find feature files, list them
		self::$features = glob($directory . '/features/*.feature');

		// Find step files, list them
		self::$step_files = glob($directory . '/features/step_definitions/*.php');

		self::run_all();
	}

}

class FeatureContext
{
	/** @var UnitTestCase $testcase */
	public $testcase;

	public function __construct($testcase)
	{
		$this->testcase = $testcase;
	}

	public static function create()
	{
		$class = get_called_class();
		$args = func_get_args();
		$r_class = new \ReflectionClass($class);
		return $r_class->newInstanceArgs( $args );
	}

	public function __call($method, $args)
	{
		if(method_exists($this->testcase, $method)) {
			call_user_func_array(array($this->testcase, $method), $args);
		}
		else {
			$class = get_called_class();
			throw new Exception("Method ->{$method} does not exist in test case for '{$class}'.");
		}
	}
}

class TestResult
{
	public $methods = array();
	public $timers = array();
	public $test_name = '';
	public $file = '';
	public $summaries = array();
	private $options = array();
	private $type;
	public $code_coverage = array();

	function __construct($test_name, $file = null)
	{
		global $options;
		$this->options = $options;
		$this->options['HABARI_PATH'] = HABARI_PATH;
		$this->test_name = $test_name;
		$this->file = ltrim(str_replace(dirname(__FILE__), '', $file), '\\/');

		$this->summaries = array(
			'pass_count' => 0,
			'fail_count' => 0,
			'incomplete_count' => 0,
			'exception_count' => 0,
			'case_count' => 0,
			'total_case_count' => 0,
			'skipped_count' => 0,
		);
	}

	function __set($key, $value)
	{
		return $this->summaries[$key] = $value;
	}

	function __get($key)
	{
		if(!isset($this->summaries[$key])) {
			return 0;
		}
		return $this->summaries[$key];
	}

	function method_results($method, $results)
	{
		$this->methods[$method] = $results;
	}

	function method_timers($method, $timers)
	{
		$this->timers[$method] = $timers;
	}

	function summary($values)
	{
		$this->summaries = array_merge($this->summaries, $values);
	}

	function record_code_coverage($coverage)
	{
		foreach($coverage as $filename => $lines) {
			if(!isset($this->code_coverage[$filename])) {
				$this->code_coverage[$filename] = array();
			}
			foreach($lines as $line_number => $result) {
				if(isset($this->code_coverage[$filename][$line_number])) {
					$result = max($this->code_coverage[$filename][$line_number], $result);
				}
				$this->code_coverage[$filename][$line_number] = $result;
			}
		}
	}
}


class TestResults extends ArrayObject
{
	private $options = array();
	private $type = array();

	function __construct()
	{
		global $options;
		$this->options = $options;
		$this->type = array(
			'Fail',
			'Incomplete',
			'Skipped',
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
				case 'symbolic':;
				case 's':;
				case 'x':;
				case 'xml':;
					$default_output = 'symbolic';
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
			case 'symbolic':
				header('content-type: text/xml');
				return $this->out_symbolic();
		}
	}

	function initial_results()
	{
		return array('total_case_count'=>0, 'case_count'=>0, 'fail_count'=>0, 'pass_count'=>0, 'exception_count'=>0, 'incomplete_count'=>0, 'skipped_count'=>0);
	}

	function out_html()
	{
		$has_output = false;
		$totals = $this->initial_results();

		switch($this->count()) {
			case 0:
				$title = 'No tests were run.';
				break;
			case 1:
				$title = "Test Results for " . reset($this)->test_name;
				break;
			default:
				$title = "Test Results for " . $this->count() . " tests";
				break;
		}

		$output = "<!DOCTYPE HTML><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"><title>{$title}</title>" .
			'<link rel="stylesheet" type="text/css" href="style.css">' .
			"</head><body>";
		foreach($this as /* TestRestult */ $test) {
			$output .= "<h1>{$test->test_name}<a href=\"index.php?u={$test->test_name}\" style=\"font-size: xx-small;font-weight: normal;margin-left: 20px;\">Run only {$test->test_name}</a></h1>";

			if(!isset($test->methods)) {
				$test->methods = array();
				$test->summaries = $this->initial_results();
			}

			foreach($test->methods as $methodname => $messages) {
				$output .= "<div class=\"method\"><h2>{$methodname}</h2>";
				if(isset($this->options['i'])) {
					$output .= "<span class=\"timers\">(";
					$comma = '';
					foreach($test->timers[$methodname] as $k => $v) {
						$output .= $comma . $k . ':' . sprintf('%.2f', $v * 1000);
						$comma = ',';
					}
					$output .= ")</span>";
				}
				foreach($messages as $message) {
					if(isset($message['output'])) {
						if(isset($this->options['o'])) {
							$output .= "<div style=\"white-space:pre;border: 1px solid #ccc;padding: 0px 10px 10px;background: #efefef;\"><h3>Output</h3>{$message['output']}</div>";
						}
						else {
							$has_output = true;
						}
					}
					if(isset($message['type']) && isset($message['message'])) {
						$output .= "<div><em>{$this->type[$message['type']]}: </em> {$message['message']}";
						if(isset($message['file'])) {
							$output .= '<br/>' . $message['file'] . ':' . $message['line'];
						}
						$output .= '</div>';
					}
				}
				$output .= '</div>';
			}

			$summary = $test->summaries;
			foreach($summary as $k => $v) {
				if(isset($totals[$k]) && is_numeric($v)) {
					$totals[$k] += $v;
				}
			}
			$output .= "<div class=\"test complete\"><p>{$summary['case_count']}/{$summary['total_case_count']} tests complete.  {$summary['fail_count']} failed assertions.  {$summary['pass_count']} passed assertions.  {$summary['exception_count']} exceptions.  {$summary['incomplete_count']} incomplete tests.  {$summary['skipped_count']} skipped tests.</p></div>";
		}

		if( count($test->code_coverage) ) {
			ksort($test->code_coverage);
			$output .= '<h1>Code Coverage</h1>';

			// @todo what about @covers comments?
			// @todo and @codeCoverageIgnore, @codeCoverageIgnoreStart, and @codeCoverageIgnoreEnd?
			$file_id = 0;
			foreach ( $test->code_coverage as $file => $coverage ) {

				$file_id++;
				$output .= '<h4>'. $file . '</h4>';

				if(!file_exists($file)) {
					$output .= '<div>File could not be opened to display coverage.</div>';
					continue;
				}
				$lines = file( $file );

				$output_file = '';
				$executed = 0;
				$executable = 0;
				$inaccessible = 0;

				$output_file .= '<table class="coverage" id="coverage_' . $file_id . '">';
				for($i = 0; $i < count($lines); $i++) {
					$line_number = $i + 1;
					$line = $lines[$i];
					if ( isset($coverage[$line_number]) ) {
						$result = $coverage[$line_number];
						if ( $result == -2 && (trim($line) != '}') ) {  // This code is inaccessible
							$class = 'inaccessible';
							$inaccessible++;
							$executable++;
						}
						else if ( $result > 0 ) {  // This code executed
							$class = 'executed';
							$executed++;
							$executable++;
						}
						else if ( $result == -1 ) {  // This code did not execute
							$class = 'unexecuted';
							$executable++;
						}
						else {
							$class = 'whitespace';
						}
					}
					else {
						$class = 'unknown';
					}
					$output_file .=  "<tr><td class=\"line_number\">{$line_number}</td><td class=\"codeline {$class}\">" . htmlentities( $line ) . "</td></tr>";
				}
				$output_file .= '</table>';

				$output .= '<details><summary>' . $executed . ' executed';
				if($inaccessible > 0) {
					$output .= ', ' . $inaccessible . ' inaccessible';
				}
				$pct = round($executed * 100 / $executable);
				$output .= ' out of ' . $executable . ' lines -- ' . $pct . '%</summary>';
				$output .= $output_file;
				$output .= '</details>';
			}
		}

		$output .= '<footer><h3>Results</h3>';
		$output.= sprintf('<div class="all test complete">%d units containing %d tests. %d failed assertions.  %d passed assertions.  %d exceptions.  %d incomplete tests.  %d skipped tests.</div>', $this->count(), $totals['case_count'], $totals['fail_count'], $totals['pass_count'], $totals['exception_count'], $totals['incomplete_count'], $totals['skipped_count']);

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

		switch($this->count()) {
			case 0:
				$title = 'No tests were run.';
				break;
			case 1:
				$title = "Test Results for " . reset($this)->test_name;
				break;
			default:
				$title = "Test Results for " . $this->count() . " tests";
				break;
		}

		$output = array();
		$output[] = "==== {$title} ====";
		foreach($this as /* TestRestult */ $test) {
			$output[]= "\n=== {$test->test_name} ===";

			if(!isset($test->methods)) {
				$test->methods = array();
				$test->summaries = $this->initial_results();
			}

			foreach($test->methods as $methodname => $messages) {
				$output[]= "  {$methodname}";
				foreach($messages as $message) {
					if(isset($message['output'])) {
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
					if(isset($message['message'])) {
						$output[]= str_pad($this->type[$message['type']] . ': ', 10, ' ', STR_PAD_LEFT ) . $message['message'];
						if(isset($message['line'])) {
							$output[]= '      ' . $message['file'] . ':' . $message['line'];
						}
					}
				}
			}

			$summary = $test->summaries;
			foreach($summary as $k => $v) {
				if(isset($totals[$k]) && is_numeric($v)) {
					$totals[$k] += $v;
				}
			}
			$output[]= sprintf("\n%d/%d tests complete.  %d failed assertions.  %d passed assertions.  %d exceptions.  %d incomplete tests.", $summary['case_count'], $summary['total_case_count'], $summary['fail_count'], $summary['pass_count'], $summary['exception_count'], $summary['incomplete_count']);
		}

		$output[]= "\n=== Results ===";
		$output[]= sprintf('%d units containing %d tests.  %d tests run.  %d failed assertions.  %d passed assertions.  %d exceptions.  %d incomplete tests.', $this->count(), $totals['total_case_count'], $totals['case_count'], $totals['fail_count'], $totals['pass_count'], $totals['exception_count'], $totals['incomplete_count']);
		if($has_output) {
			$output[]= "\nSome tests have output.  Run again with -o to see output.";
		}

		$output[]= "\n=== Options ===";
		foreach($this->options as $k => $v) {
			$output[]= "  {$k}: {$v}";
		}

		return implode("\n", $output) . "\n";
	}

	function out_symbolic()
	{
		$has_output = false;
		$totals = $this->initial_results();

		$xml = new SimpleXMLElement('<results></results>');

		$xml->addAttribute('unit_count', $this->count());

		$totals = $this->initial_results();

		$timers = array();

		foreach($this as $test) {
			$xunit = $xml->addChild('unit');
			$xunit->addAttribute('name', $test->test_name);

			if(!isset($test->methods)) {
				$test->methods = array();
				$test->summaries = $this->initial_results();
			}

			$unit_timers = array();

			foreach($test->methods as $methodname => $messages) {
				$xmethod = $xunit->addChild('method');
				$xmethod->addAttribute('name', $methodname);

				foreach($test->timers[$methodname] as $k => $v) {
					if(isset($this->options['i'])) {
						$xtimer = $xmethod->addChild('timer');
						$xtimer->addAttribute('name', $k);
						$xtimer->addAttribute('value', $v * 1000);
					}
					$unit_timers[$k] = isset($unit_timers[$k]) ? $unit_timers[$k] + $v : $v;
				}

				$has_output = 0;
				foreach($messages as $message) {
					if(isset($message['output'])) {
						if(isset($this->options['o'])) {
							$xmethod->addChild('output', $message['output']);
						}
						$has_output = 1;
					}
					if(isset($message['message'])) {
						$xmessage = $xmethod->addChild('message', $message['message']);
						$xmessage->addAttribute('type', $this->type[$message['type']]);
						if(isset($message['line']) && isset($message['file'])) {
							$xmessage->addAttribute('file', $message['file']);
							$xmessage->addAttribute('line', $message['line']);
						}
					}
				}
				$xmethod->addAttribute('has_output', $has_output);
			}

			foreach($unit_timers as $k => $v) {
				if(isset($this->options['i'])) {
					$xtimer = $xunit->addChild('timer');
					$xtimer->addAttribute('name', $k);
					$xtimer->addAttribute('value', $v * 1000);
				}
				$timers[$k] = isset($timers[$k]) ? $timers[$k] + $v : $v;
			}

			$summary = $test->summaries;
			foreach($summary as $k => $v) {
				if(isset($totals[$k]) && is_numeric($v)) {
					$totals[$k] += $v;
				}
			}
			$xunit->addAttribute('cases', $summary['case_count']);
			$xunit->addAttribute('complete', $summary['total_case_count']);
			$xunit->addAttribute('fail', $summary['fail_count']);
			$xunit->addAttribute('pass', $summary['pass_count']);
			$xunit->addAttribute('exception', $summary['exception_count']);
			$xunit->addAttribute('incomplete', $summary['incomplete_count']);
/*
			$coverage = $this->code_coverage[0]; // this part here doesn't work yet.
			$xunit->addAttribute('coverage', $coverage ); // the admin page doesn't do anything with this yet.
*/
		}

		foreach($timers as $k => $v) {
			if(isset($this->options['i'])) {
				$xtimer = $xml->addChild('timer');
				$xtimer->addAttribute('name', $k);
				$xtimer->addAttribute('value', $v * 1000);
			}
		}

		$xml->addAttribute('complete', $totals['total_case_count']);
		$xml->addAttribute('fail', $totals['fail_count']);
		$xml->addAttribute('pass', $totals['pass_count']);
		$xml->addAttribute('exception', $totals['exception_count']);
		$xml->addAttribute('incomplete', $totals['incomplete_count']);

		return $xml->asXML();
	}

	function summary($test, $values)
	{
		$this->summaries[$test] = $values;
	}

}

include HABARI_PATH . '/index.php';

endif;

?>
