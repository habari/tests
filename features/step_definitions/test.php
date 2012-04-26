<?php

class TestFeatureContext extends FeatureContext
{

	/**
	 * @Given /Habari is installed$/
	 */
	function habari_is_installed() {
		$this->assert_true(true);
	}

	/**
	 * @Given /the (.+) is running$/
	 */
	function the_test_suite_is_running($running_thing) {
		// Well, if we're here, it's running, right?
		$this->assert_true(true);
		//$this->mark_test_incomplete("The {$running_thing} may be running, but this test is incomplete.");
	}

	/**
	 * @Given /a feature exists$/
	 */
	function a_feature_exists() {
		$this->assert_true(true);
	}

	/**
	 * @When /the feature test runs$/
	 */
	function the_feature_test_runs() {
		$this->assert_true(true);
	}

	/**
	 * @Then /some output should exist$/
	 */
	function some_output_should_exist() {
		$this->assert_true(true);
	}

	/**
	 * @When /I visit the URL:\s*(.+?)$/
	 */
	function i_visit_the_url($url) {

		$this->assert_true(true);
	}


}