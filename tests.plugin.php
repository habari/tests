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
		foreach ($testlist->unit as $unit) {
			$units[] = $unit->attributes();
		}

		if (isset($_GET['run']) && isset($_GET['test'])) {
			if ($_GET['test'] == 'all') {
				$theme->results = $units;
			}
			else {
				foreach ($units as $unit) {
					if ($unit->name == $_GET['test']) {
						$theme->results = array($unit);
					}
				}
			}
		}
		$theme->content = $output;
		$theme->units = $units;
		$theme->display('header');
		$theme->display('tests_admin');
		$theme->display('footer');
		exit;
	}
}

?>
