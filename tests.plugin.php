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
		$header = "<div class='item clear'><h2>Tests</h2><h3><span class='pct30 last'>Name</span><span class='pct10'>Complete</span><span class='pct10'>Passed</span><span class='pct10'>Failed</span></h3></div>";
		foreach( $tests as $test ) {
			$rows .= "<div class='item settings clear' id='{$test['name']}'><span class='pct30'>{$test['name']}</span><span class='pct10'>{$test['complete']}</span><span class='pct10'>{$test['pass']}</span><span class='pct10'>{$test['fail']}</span></div>";
		}
		return "{$header}{$rows}";
	}
}

?>
