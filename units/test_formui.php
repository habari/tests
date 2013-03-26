<?php
namespace Habari;

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
		$form->append('label', 'label_for_firstname')->set_label('Firstname')->append('text', 'firstname', 'option:test__username');
		$form->append('submit', 'save')->set_caption('Save');
		$form->out();
	}

	function test_add_a_control2()
	{
		$form = new FormUI('test3');
		$form->append(FormControlLabel::wrap('Firstname', FormControlText::create('firstname', 'option:test__username')));
		$form->append(FormControlSubmit::create('save')->set_caption('Save'));
		$form->out();
	}

	function test_modify_a_control1()
	{
		$form = new FormUI('test4');
		$form->append('label', 'label_for_firstname')->set_label('Firstname')->append('text', 'firstname', 'option:test__username');
		$form->append('submit', 'save')->set_caption('Save');
		$form->append(new FormControlSubmit('save', 'Save') );

		$form->label_for_firstname->set_label('First Name');
		$form->out();
	}

	function test_modify_a_control2()
	{
		$form = new FormUI('test5');
		$form->append(FormControlLabel::wrap('Firstname', FormControlText::create('firstname', 'option:test__username')));
		$form->append(FormControlSubmit::create('save')->set_caption('Save'));

		$form->label_for_firstname->set_label('First Name');
		$form->out();
	}

	function test_initial_control_values()
	{
		$form = new FormUI('test6');
		$form->append('text', 'firstname');
		$form->firstname->value = 'Bob';
		$form->append('submit', 'save');
		$form->out();
	}

	function test_initial_control_values2()
	{
		$form = new FormUI('test7');
		$form->append('text', 'firstname');

		$form->firstname->value = 'Alice';

		if($form->firstname->value == 'Bob') {
		  $form->append('text', 'lastname');
		}

		$form->append('submit', 'save');
		$form->out();
	}

	function test_form_post_processing_by_method()
	{
		$this->check('callback', 'Did not call callback.');

		$form = new FormUI('test8');
		$form->append('text', 'firstname');
		$form->append('submit', 'save');
		$form->on_success( array( $this, 'my_callback1' ), 'Bob' );
		$form->simulate(array(), true);
		$form->get();
	}

	function my_callback1( $form, $special_name )
	{
		$this->pass_check('callback');
		// Display the form normally with the default confirmation message
		return false;
	}

	function test_form_post_processing_by_filter()
	{
		Plugins::register(array($this, 'filter_my_callback'), 'filter', 'my_callback');

		$this->check('callback', 'Did not call callback.');

		$form = new FormUI('test9');
		$form->append('text', 'firstname');
		$form->append('submit', 'save', 'Save');
		$form->on_success('my_callback', 'Bob');
		$form->simulate(array(), true);
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
		$form->get();
		$this->assert_true($form->about->value == Options::get('test__about'));
	}

	function get_form1()
	{
		$form = new FormUI('test10');
		$form->append('text', 'about', 'test__about');
		return $form;
	}

	function test_create_a_custom_control()
	{
		$form = new FormUI('test10');

		// filter_available_templates gets called too early in the chain to use here, so this instead:
		$form->get_theme()->add_template('control.customz', 'This value gets replaced in filter_include_template_file()');
		Plugins::register(array($this, 'filter_include_template_file'), 'filter', 'include_template_file');
		$this->check('template', 'Template not loaded.');

		$form->append('custom', 'test', 'test__about');
		$form->out();
	}

	function filter_include_template_file($filename, $template)
	{
		if($template == 'control.customz') {
  		$this->pass_check('template');
  		return dirname(__FILE__) . '/../data/formcontrol_custom2.php';
		}
		return $filename;
	}

	function test_inline_control_template()
	{
		$myform = new FormUI('my_identifier');
		$myform->get_theme()->add_template('control.text.custom', dirname(__FILE__).'/../data/formcontrol_custom2.php');
		$firstname = FormControlText::Create('firstname', 'user:username')->set_template('control.text.custom');
		$myform->append(FormControlLabel::wrap('Firstname:', $firstname));
		$myform->append(FormControlSubmit::create('save')->set_caption('Save') );
		$html = $myform->get();
		$this->output($html);

		$this->assert_true(strpos($html, 'custom:formcontrol_custom2.php') !== false, 'Could not find content of custom template in form output');
	}

	function teardown()
	{
		Options::delete('test__username');
		Options::delete('test__about');
	}

}

if(class_exists('\Habari\FormUI')):
	class FormControlCustom extends FormControl {
		function get( Theme $theme ) {
			// Tell the theme what template to render for this control
			$this->set_template('control.customz');
			return parent::get($theme);
		}
	}

endif;

?>
