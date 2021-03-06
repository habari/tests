<?php

namespace Habari;

class TestsPlugin extends Plugin
{
	private $attributes = array(
		'name',
		'cases',
		'complete',
		'fail',
		'pass',
		'exception',
		'incomplete',
	);

	public function filter_admin_access( $access, $page, $post_type )
	{
		if ( $page != 'tests') {
			return $access;
		}

		return true;
	}

	public function action_init()
	{
		$this->add_template('tests_admin', dirname($this->get_file()) . '/plugin_admin.php');
		$this->add_rule(
			new RewriteRule(
				array(
					'name' => 'display_tests',
					'parse_regex' => '#^admin/tests/?$#i',
					'build_str' => 'admin/tests',
					'handler' => 'PluginHandler',
					'action' => 'display_tests',
					'priority' => 0,
					'is_active' => 0,
					'rule_class' => RewriteRule::RULE_CUSTOM,
					'description' => '',
					'parameters' => '',
				)
			),
			'display_tests'
		);
	}

	public function action_admin_header( $theme )
	{
		if ( $theme->page == 'tests' ) {
			Stack::add( 'admin_stylesheet', array( $this->get_url() . '/admin.css', 'screen' ), 'tests-admin-css' );
		}
	}

	public function filter_admin_access_tokens($require_any, $page, $type)
	{
		if($page == 'admin') {
			if(Controller::get_var('page') == 'tests') {
				$require_any = array( 'manage_plugins' => true );
			}
		}
		return $require_any;
	}

	public function filter_adminhandler_post_loadplugins_main_menu( array $menu )
	{
		$item_menu = array( 'tests' =>
			array(
				'url' => URL::get( 'admin', 'page=tests'),
				'title' => _t('Tests'),
				'text' => _t('Tests'),
				'hotkey' => 'S',
				'selected' => false
			)
		);

		$slice_point = array_search( 'groups', array_keys( $menu ) ); // Element will be inserted before "groups"
		$pre_slice = array_slice( $menu, 0, $slice_point);
		$post_slice = array_slice( $menu, $slice_point);

		$menu = array_merge( $pre_slice, $item_menu, $post_slice );

		return $menu;
	}


	/**
	 * @todo Removing initial newline shouldn't be necessary, find out what's causing it
	 */
	public function theme_route_display_tests( Theme $theme )
	{
		$url = $this->get_url('/index.php?c=symbolic&o=1&d=1');

		$loaded_tests = false;
		try{
			$raw_xml = file_get_contents($url);
			$test_list = new \SimpleXMLElement(preg_replace("/^\n/", "", $raw_xml));
			$loaded_tests = true;
		}
		catch(\Exception $e) {
			$output = $e->getMessage();
			$output .= '<pre>' . Utils::htmlspecialchars($raw_xml) . '</pre>';
		}


		if($loaded_tests) {
			$output = '';
			$unit_names = array();
			foreach ($test_list->unit as $unit) {
				$unit_names[] = (string)$unit->attributes()->name;
			}
			$theme->unit_names = $unit_names;

			if (isset($_GET['run']) && isset($_GET['unit'])) {
				$dryrun = false;
				$unit = $_GET['unit'];
				if ($unit != 'all') {
					$url = '/index.php?c=symbolic&o=1&u='.$unit;
					if (isset($_GET['test'])) {
						$test = $_GET['test'];
						$url = $url.'&t='.$test;
						$theme->test = $test;
					}
					$url = $this->get_url($url);
				}
				else {
					$url = '/index.php?c=symbolic&o=1';
					$url = $this->get_url( $url );
				}
				if($_GET['run'] == 'Dry Run') {
					$url .= '&d=1';
					$dryrun = true;
				}
			}
			else {
				$dryrun = true;
				$url = $this->get_url('/index.php?c=symbolic&o=1&d=1');
			}

			$results = preg_replace("/^\n/", "", file_get_contents($url));

			$results_array = array();
			$parsed_xml = true;

			$theme->symbolic_url = $url;
			$theme->direct_url = str_replace('c=symbolic', 'c=html', $url);

			try {
				$xmldata = file_get_contents($url);
				$results = @new \SimpleXMLElement(preg_replace("/^\n/", "", $xmldata));
			}
			catch(\Exception $e) {
				if(strpos($xmldata, 'debugtoggle(') !== false) {
					$theme->error = var_export($e->getMessage(), true) . $xmldata;
				}
				else {
					$theme->error = var_export($e->getMessage(), true) . '<textarea style="width:100%;height: 20em;">' . htmlentities($xmldata) . '</textarea>';
				}
				$parsed_xml = false;
				$theme->unit = $unit;
			}
			$theme->xmldata = $xmldata;

			if($parsed_xml) {
				$dom = dom_import_simplexml($results)->ownerDocument;
				$dom->formatOutput = true;
				$theme->xmldata = $dom->saveXML();
				$theme->connection_string = $results['connection_string'];
				foreach ($results->unit as $result) {
					$result_array = (array)$result->attributes();
					$result_array = array_shift($result_array);

					$result_array['methods'] = array();
					foreach ($result->method as $method) {
						$method_array = (array)$method;
						$output_array = array();
						if( isset( $method->output ) ) { // output is on, and output can appear whether passing or failing.
							foreach( $method->output as $outputz ) {
								$output_array[] = $outputz;
							}
						}

						if( ! isset( $method->message ) ) { // no <message> means the method passed
							$method_result = 'Pass';
							if($dryrun) {
								$method_result = 'Dry Run';
							}
							$result_array['methods'][] = array_merge( array_shift($method_array), array( "result" => $method_result, "output" => implode( " ", $output_array ) ) );
						} else {
							$message_array = array();
							$result = (string)$method->message->attributes()->type;
							foreach( $method->message as $message ) {
								$message_array[] = "{$message}" . ( $result != "Fail" ? "" : "<br><em>" . basename($message->attributes()->file) . ":{$message->attributes()->line}</em>");
							}
							$result_array['methods'][] = array_merge( array_shift($method_array), array(
								"result" => $result,
								"messages" => implode( "<br>", $message_array ),
								"output" => implode( " ", $output_array ),
							));
						}
					}
					$results_array[] = $result_array;
				}
				$theme->results = $results_array;
				$theme->unit = $unit;
			}
		}

		$theme->content = $output;
		$theme->display('header');
		$theme->display('tests_admin');
		$theme->display('footer');
		exit;
	}
}

?>
