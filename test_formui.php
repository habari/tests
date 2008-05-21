<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2008
 */

include 'bootstrap.php';

class FormUITest extends UnitTestCase
{

	function test_basic_form() {
		$form = new FormUI('test');
		$form->add('myfield', 'text', 'option:test', 'My Field:');
		$form->out();
	}

}

UnitTestCase::run_one('UnitTestCase');

?>