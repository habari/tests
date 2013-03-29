<?php
namespace Habari;

class HTMLDocTest extends UnitTestCase
{

	public function setup()
	{
		$this->html = <<< HTML_CONTENT
<form action="" method="POST">
	<div id="success" data-form-success="true">
		The form was successfully submitted.
	</div>
	<input id="foo" type="text" name="foo" value="" class="formcontrol">
	<div id="bar_error" data-show-on-error="bar" class="error" id="errors">
		<input id="required_bar" type="text" name="bar" data-validators="validate_required" class="formcontrol required">
		<div id="bar_error_list" data-control-errors="bar"></div>
	</div>
	<button id="submit" class="formcontrol">Submit</button>
</form>
HTML_CONTENT;
		$this->doc = HTMLDoc::create($this->html);

	}

	public function test_find_class()
	{
		$node = $this->doc->find_one('.error');
		$this->assert_equal($node->id, 'bar_error');
	}

	public function test_find_id()
	{
		$node = $this->doc->find_one('#foo');
		$this->assert_equal($node->id, 'foo');
	}

	public function test_find_element()
	{
		$node = $this->doc->find_one('button');
		$this->assert_equal($node->id, 'submit');
	}

	public function test_find_compound()
	{
		$node = $this->doc->find_one('input.required');
		$this->assert_equal($node->id, 'required_bar');
	}

	public function test_find_ancestor()
	{
		$node = $this->doc->find_one('#bar_error input');
		$this->assert_equal($node->id, 'required_bar');
	}

	public function test_find_child()
	{
		$node = $this->doc->find_one('div > div');
		$this->assert_equal($node->id, 'bar_error_list');
	}

	public function test_output()
	{
		$node = $this->doc->find_one('button');
		$this->assert_equal($node->get(), '<button id="submit" class="formcontrol">Submit</button>');
	}

	public function test_add_id()
	{
		$node = $this->doc->find_one('form');
		$node->id = 'the_form';
		$node2 = $this->doc->find_one('#the_form');
		$this->assert_equal($node2->id, 'the_form');
	}

	public function test_selector_or()
	{
		$sel = new HTMLSelector('p,div');
		$this->output($sel->toXPath() . "<br>\n");

		$sel = new HTMLSelector('#bar_error input');
		$this->output($sel->toXPath() . "<br>\n");

		$sel = new HTMLSelector('div > div');
		$this->output($sel->toXPath() . "<br>\n");
	}

}