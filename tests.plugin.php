<?php

class TestsPlugin extends Plugin
{

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


	public function filter_adminhandler_post_loadplugins_main_menu( array $menu )
	{
		$item_menu = array( 'tests' =>
			array(
				'url' => URL::get( 'admin', 'page=tests'),
				'title' => _t('Tests'),
				'text' => _t('Tests'),
				'hotkey' => 'E',
				'selected' => false
			)
		);

		$slice_point = array_search( 'groups', array_keys( $menu ) ); // Element will be inserted before "groups"
		$pre_slice = array_slice( $menu, 0, $slice_point);
		$post_slice = array_slice( $menu, $slice_point);

		$menu = array_merge( $pre_slice, $item_menu, $post_slice );

		return $menu;
	}


	public function action_admin_theme_get_tests( AdminHandler $handler, Theme $theme )
	{
		$url = $this->get_url('/index.php?c=symbolic&d=1');
		$testlist = new SimpleXMLElement(file_get_contents($url));

		$output = '';
		$units = array();
		foreach($testlist->unit as $unit) {
			$units[] = $unit->attributes();
		}

		$theme->content = $output;
		$theme->units = $units;
		$theme->table = self::make_table( $units );
		$theme->display('header');
		$theme->display('tests_admin');
		$theme->display('footer');
		exit;
	}

	private function make_table( $tests = array() ) {
		$rows = "";
		$header = "<tr><th>Name</th><th>Tests completed</th><th>Passing</th><th>Failing</th></tr>";
		foreach( $tests as $test ) {
			$rows .= "<tr><td>{$test['name']}</td><td>{$test['complete']}</td><td>{$test['pass']}</td><td>{$test['fail']}</td></tr>";
		}
		return "<table><thead>{$header}</thead><tbody>{$rows}</tbody></table>";
	}
}

?>
