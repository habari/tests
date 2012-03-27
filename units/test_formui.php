<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2008
 */

class FormUITest extends UnitTestCase
{

	function setup()
	{
		Options::set('test__username', 'test username');
		Options::set('test__about', 'About my site');
	}

	function test_create_a_form()
	{
		$form = new FormUI('test1');
		$form->out();
	}

	function test_add_a_control1()
	{
		$form = new FormUI('test2');
		$form->append('text', 'firstname', 'option:test__username', 'Firstname');
		$form->append('submit', 'save', 'Save');
		$form->out();
	}

	function test_add_a_control2()
	{
		$form = new FormUI('test3');
		$form->append(new FormControlText('firstname', 'option:test__username', 'Firstname') );
		$form->append(new FormControlSubmit('save', 'Save') );
		$form->out();
	}

	function test_modify_a_control1()
	{
		$form = new FormUI('test4');
		$form->append('text', 'firstname', 'option:test__username', 'Firstname');
		$form->firstname->caption = 'First Name';
		$form->append(new FormControlSubmit('save', 'Save') );
		$form->out();
	}

	function test_modify_a_control2()
	{
		$form = new FormUI('test5');
		$firstname_control = $form->append('text', 'firstname', 'option:test__username', 'Firstname');
		$firstname_control->caption = 'First Name';
		$form->append(new FormControlSubmit('save', 'Save') );
		$form->out();
	}

	function test_initial_control_values()
	{
		$form = new FormUI('test6');
		$form->append('text', 'firstname', 'null:unused', 'Firstname:');
		$form->firstname->value = 'Bob';
		$form->append('submit', 'save', 'Save');
		$form->out();
	}

	function test_initial_control_values2()
	{
		$form = new FormUI('test7');
		$form->append('text', 'firstname', 'username', 'Firstname:');

		$form->firstname->value = 'Alice';

		if($form->firstname->value == 'Bob') {
		  $form->append('text', 'lastname', 'lastname', 'Lastname:');
		}

		$form->append('submit', 'save', 'Save');
		$form->out();
	}

	function test_form_post_processing1()
	{
		$this->check('callback', 'Did not call callback.');

		$form = new FormUI('test8');
		$form->append('text', 'firstname', 'username', 'Firstname:');
		$form->append('submit', 'save', 'Save');
		$form->on_success( array( $this, 'my_callback1' ), 'Bob' );
		$form->out();
	}

	function my_callback1( $form, $special_name )
	{
		$this->pass_check('callback');
		// Perform normal save routines
		$form->save();
		// Display the form normally with the default confirmation message
		return false;
	}

	function test_form_post_processing2()
	{
		Plugins::register(array($this, 'filter_my_callback'), 'filter', 'my_callback');

		$this->check('callback', 'Did not call callback - submit the form to test.');

		$form = new FormUI('test9');
		$form->append('text', 'firstname', 'username', 'Firstname:');
		$form->append('submit', 'save', 'Save');
		$form->on_success('my_callback', 'Bob');
		$form->out();
	}

	function filter_my_callback($success_html, $form, $special_name)
	{
		$this->pass_check('callback');
		// Perform normal save routines
		$form->save();

		$success_html = 'OK';
		return $success_html;
	}

	function test_using_form_values()
	{
		$form = $this->get_form1();
		$this->assert_true($form->about == 'About my site');
	}

	function get_form1()
	{
		$form = new FormUI('test10');
		$form->append('text', 'about', 'test__about', 'About');
		return $form;
	}

	function test_create_a_custom_control()
	{
		Plugins::register(array($this, 'filter_available_templates'), 'filter', 'available_templates');
		Plugins::register(array($this, 'filter_include_template_file'), 'filter', 'include_template_file');
		$this->check('template', 'Template not loaded.');

		$form = new FormUI('test10');
		$form->append('custom', 'test', 'test__about', 'About');
		$form->out();
	}

	function filter_available_templates( $list )
	{
		$list = array_merge($list, array('my_special_control_template'));
		return $list;
	}

	function filter_include_template_file($filename, $template)
	{
		if($template == 'my_special_control_template') {
  		$this->pass_check('template');
  		return dirname(__FILE__) . '/data/formcontrol_custom2.php';
		}
		return $filename;
	}

	function teardown()
	{
		Options::delete('test__username');
		Options::delete('test__about');
	}

}

if(class_exists('FormUI')):
	class FormControlCustom extends FormControl {
		function get( $forvalidation = true ) {
			// Get the theme object to use to render this control
			$theme = $this->get_theme( $forvalidation );
			// Tell the theme what template to render for this control
			return $theme->fetch( 'my_special_control_template' );
		}
	}

endif;

?>
