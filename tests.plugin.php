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

		$units = array();
		$testnames = array('All tests');
		foreach($testlist->unit as $unit) {
			$units[] = $unit->attributes();
			$testnames[] = (string)$unit->attributes()->name;
		}

		$form = new FormUI('Test Chooser');
		$form->properties['method'] = 'get';
		$fieldset = $form->append('wrapper', 'tests', 'tests');
		$fieldset->class = 'container settings';
		$tests = $fieldset->append('select', 'test', 'test', '&nbsp;');
		$tests->options = $testnames;

		$form->append('submit', 'run', _t('Run'), 'admincontrol_submit');
		$form->on_success(array($this, 'run_tests'));

		if ($form->submitted) {
			$theme->results = true;
		}
		$theme->form = $form;
		$theme->units = $units;
		$theme->display('header');
		$theme->display('tests_admin');
		$theme->display('footer');
		exit;
	}

}

?>
