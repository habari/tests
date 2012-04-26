<?php

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
	}

	public function action_admin_header( $theme )
	{
		if ( $theme->page == 'tests' ) {
			Stack::add( 'admin_stylesheet', array( $this->get_url() . '/admin.css', 'screen' ), 'admin-css' );
		}
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
	public function action_admin_theme_get_tests( AdminHandler $handler, Theme $theme )
	{
		$url = $this->get_url('/index.php?c=symbolic&o=1');
		$test_list = new SimpleXMLElement(preg_replace("/^\n/", "", file_get_contents($url.'&d=1')));

		$output = '';
		$unit_names = array();
		foreach ($test_list->unit as $unit) {
			$unit_names[] = (string)$unit->attributes()->name;
		}
		$theme->unit_names = $unit_names;

		if (isset($_GET['run']) && isset($_GET['unit'])) {
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
			$results = preg_replace("/^\n/", "", file_get_contents($url));

			$results_array = array();
			$parsed_xml = true;
			try {
				$xmldata = file_get_contents($url);
				$results = @new SimpleXMLElement(preg_replace("/^\n/", "", $xmldata));
			}
			catch(Exception $e) {
				$theme->error = var_export($e->getMessage(), true) . '<textarea style="width:100%;height: 20em;">' . htmlentities($xmldata) . '</textarea>';
				$parsed_xml = false;
			}
			$theme->xmldata = $xmldata;

			if($parsed_xml) {
				$theme->connection_string = $results['connection_string'];
				$theme->symbolic_url = $url;
				$theme->direct_url = str_replace('c=symbolic', 'c=html', $url);
				$dom = dom_import_simplexml($results)->ownerDocument;
				$dom->formatOutput = true;
				$theme->xmldata = $dom->saveXML();
				foreach ($results->unit as $result) {
					$result_array = (array)$result->attributes();
					$result_array = array_shift($result_array);

					$result_array['methods'] = array();
					foreach ($result->method as $method) {
						$method_array = (array)$method;
						$output_array = array();
						if( isset( $method->output ) ) { // output is on, and output can appear whether passing or failing.
							foreach( $method->output as $outputz ) {
								$output_array[] = "<div class='method_output'>{$method->output}</div>";
							}
						}

						if( ! isset( $method->message ) ) { // no <message> means the method passed
							$result_array['methods'][] = array_merge( array_shift($method_array), array( "result" => "Pass", "output" => implode( " ", $output_array ) ) );
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
