<?php
namespace Habari;

class SeleniumFeatureContext extends FeatureContext
{
	/** @var WebDriver $webdriver */
	public $webdriver = null;

	public function init_webdriver() {
		if(empty($this->webdriver)) {
			$this->webdriver = new \WebDriver('localhost', '4444');
//			$this->webdriver->connect('firefox');
			$this->webdriver->connect('chromium');
		}
	}

	public function __destruct() {
		if(!empty($this->webdriver)) {
			$this->webdriver->close();
		}
	}

	/**
	 * @Given /selenium is running$/
	 * Dunno what this step is for, really.
	 * @todo make selenium start only if you use selenium-based steps
	 */
	function selenium_is_running() {
		$this->init_webdriver();
		$this->assert_not_empty($this->webdriver, '{step}');
	}

	/**
	 * @When /I visit the URL (.+)$/
	 */
	function i_visit_the_url($url) {
		$this->init_webdriver();
		$this->webdriver->get($url);
	}

	/**
	 * @When /I type "(.+)" into the (.+) field$/
	 */
	function i_type_into_the_field($text, $field) {
		$this->init_webdriver();
		$element = $this->webdriver->findElementBy(\LocatorStrategy::name, $field);
		if(empty($element)) {
			$element = $this->webdriver->findElementBy(\LocatorStrategy::id, $field);
			if(empty($element)) {
				$this->assert_true(false, sprintf('{step}Could not find the element with the id "%s"', $field));
				return;
			}
		}
		$element->sendKeys(array($text));
	}
	/**
	 * @When /I submit the (.+) form$/
	 */
	function i_submit_the_form($form) {
		$this->init_webdriver();
		$element = $this->webdriver->findElementBy(\LocatorStrategy::name, $form);
		if(empty($element)) {
			$element = $this->webdriver->findElementBy(\LocatorStrategy::id, $form);
			if(empty($element)) {
				$this->assert_true(false, sprintf('{step}Could not find the form with the id "%s"', $form));
				return;
			}
		}
		$element->submit();
	}

	/**
	 * @Then /I should see the element (.+)$/
	 */
	function i_should_see($thing) {
		$this->init_webdriver();
		$element = $this->webdriver->findElementBy(\LocatorStrategy::id, $thing);
		$this->assert_not_empty($element, '{step}');
	}

	/**
	 * @When /I click the element (.+)$/
	 */
	function i_click($thing) {
		$this->init_webdriver();
		$element = $this->webdriver->findElementBy(\LocatorStrategy::id, $thing);
		if(empty($element)) {
			$this->assert_true(false, sprintf('{step}Could not find the element with the id "%s"', $thing));
		}
		else {
			$element->click();
		}
	}

	/**
	 * @Then /I should see the text "(.+)"$/
	 */
	function i_should_see_the_text($text) {
		$this->init_webdriver();
		$source = $this->webdriver->getPageSource();
		$this->assert_true(strpos($source, $text) !== false, '{step}');
	}


}