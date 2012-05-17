<?php

/*
	Copyright 2011 3e software house & interactive agency

	Licensed under the Apache License, Version 2.0 (the "License");
	you may not use this file except in compliance with the License.
	You may obtain a copy of the License at

	http://www.apache.org/licenses/LICENSE-2.0

	Unless required by applicable law or agreed to in writing, software
	distributed under the License is distributed on an "AS IS" BASIS,
	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	See the License for the specific language governing permissions and
	limitations under the License.

	http://code.google.com/p/php-webdriver-bindings/
*/


class Keys {
	const ENTER = "\x07";
	const TAB = 0x004;
	const SPACE = 0x00D;
	const BACK_SPACE = 0x003;
}

class LocatorStrategy {
	/**Returns an element whose class name contains the search value; compound class names are not permitted.*/
	const className="class name";

	/**Returns an element matching a CSS selector.*/
	const cssSelector="css selector";

	/**Returns an element whose ID attribute matches the search value.*/
	const id="id";

	/**Returns an element whose NAME attribute matches the search value.*/
	const name="name";

	/**Returns an anchor element whose visible text matches the search value.*/
	const linkText="link text";

	/**Returns an anchor element whose visible text partially matches the search value.*/
	const partialLinkText="partial link text";

	/**Returns an element whose tag name matches the search value.*/
	const tagName="tag name";

	/**Returns an element matching an XPath expression.*/
	const xpath="xpath";
}

class WebDriverException extends Exception {

	public function __construct($message, $code, $previous = null) {
		parent::__construct($message, $code);
	}
}

class NoSuchElementException extends WebDriverException {
	private $json_response;
	public function __construct($json_response) {
		parent::__construct("No such element exception", WebDriverResponseStatus::NoSuchElement, null);
		$this->json_response = $json_response;
	}
}

class WebDriver extends WebDriverBase {

	function __construct($host, $port) {
		parent::__construct("http://" . $host . ":" . $port . "/wd/hub");
	}

	/**
	 * Connects to Selenium server.
	 * @param $browserName The name of the browser being used; should be one of {chrome|firefox|htmlunit|internet explorer|iphone}.
	 * @param $version 	The browser version, or the empty string if unknown.
	 * @param $caps  array with capabilities see: http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session
	 */
	public function connect($browserName="firefox", $version="", $caps=array()) {
		$request = $this->requestURL . "/session";
		$session = $this->curlInit($request);
		$allCaps =
			array_merge(
				array(
					'javascriptEnabled' => true,
					'nativeEvents'=>false,
				),
				$caps,
				array(
					'browserName'=>$browserName,
					'version'=>$version,
				)
			);
		$params = array( 'desiredCapabilities' =>	$allCaps );
		$postargs = json_encode($params);
		$this->preparePOST($session, $postargs);
		curl_setopt($session, CURLOPT_HEADER, true);
		$response = curl_exec($session);
		$header = curl_getinfo($session);
		$this->requestURL = $header['url'];
	}

	/**
	 * Delete the session.
	 */
	public function close() {
		$request = $this->requestURL;
		$session = $this->curlInit($request);
		$this->prepareDELETE($session);
		$response = curl_exec($session);
		$this->curlClose();
	}

	/**
	 * Refresh the current page.
	 */
	public function refresh() {

		$request = $this->requestURL . "/refresh";
		$session = $this->curlInit($request);
		$this->preparePOST($session, null);
		curl_exec($session);
	}

	/**
	 * Navigate forwards in the browser history, if possible.
	 */
	public function forward() {

		$request = $this->requestURL . "/forward";
		$session = $this->curlInit($request);
		$this->preparePOST($session, null);
		curl_exec($session);
	}

	/**
	 * Navigate backwards in the browser history, if possible.
	 */
	public function back() {

		$request = $this->requestURL . "/back";
		$session = $this->curlInit($request);
		$this->preparePOST($session, null);
		curl_exec($session);
	}

	/**
	 * Get the element on the page that currently has focus.
	 * @return JSON object WebElement.
	 */
	public function getActiveElement() {
		$response = $this->execute_rest_request_GET($this->requestURL . "/element/active");
		return $this->extractValueFromJsonResponse($response);
	}

	/**
	 * Change focus to another frame on the page. If the frame ID is null, the server should switch to the page's default content.
	 */
	public function focusFrame($frameId) {

		$request = $this->requestURL . "/frame";
		$session = $this->curlInit($request);
		$args = array('id' => $frameId);
		$this->preparePOST($session, json_encode($args));
		curl_exec($session);

	}

	/**
	 * Navigate to a new URL
	 * @param string $url The URL to navigate to.
	 */
	public function get($url) {
		$request = $this->requestURL . "/url";
		$session = $this->curlInit($request);
		$args = array('url' => $url);
		$this->preparePOST($session, json_encode($args));
		$response = curl_exec($session);
	}

	/**
	 * Get the current page title.
	 * @return string The current URL.
	 */
	public function getCurrentUrl() {
		$response = $this->execute_rest_request_GET($this->requestURL . "/url");
		return $this->extractValueFromJsonResponse($response);
	}

	/**
	 * Get the current page title.
	 * @return string current page title
	 */
	public function getTitle() {
		$response = $this->execute_rest_request_GET($this->requestURL . "/title");
		return $this->extractValueFromJsonResponse($response);
	}

	/**
	 * Get the current page source.
	 * @return string page source
	 */
	public function getPageSource() {
		$request = $this->requestURL . "/source";
		$response = $this->execute_rest_request_GET($request);
		return $this->extractValueFromJsonResponse($response);
	}

	/**
	 * Get the current user input speed. The server should return one of {SLOW|MEDIUM|FAST}.
	 * How these constants map to actual input speed is still browser specific and not covered by the wire protocol.
	 * @return string {SLOW|MEDIUM|FAST}
	 */
	public function getSpeed() {
		$request = $this->requestURL . "/speed";
		$response = $this->execute_rest_request_GET($request);
		return $this->extractValueFromJsonResponse($response);
	}

	public function setSpeed($speed) {
		$request = $this->requestURL . "/speed";
		$session = $this->curlInit($request);
		$args = array('speed' => $speed);
		$jsonData = json_encode($args);
		$this->preparePOST($session, $jsonData);
		$response = curl_exec($session);
		return $this->extractValueFromJsonResponse($response);
	}


	/**
	Change focus to another window. The window to change focus to may be specified
	by its server assigned window handle, or by the value of its name attribute.
	 */
	public function selectWindow($windowName) {
		$request = $this->requestURL . "/window";
		$session = $this->curlInit($request);
		$args = array('name' => $windowName);
		$jsonData = json_encode($args);
		$this->preparePOST($session, $jsonData);
		$response = curl_exec($session);
		return $this->extractValueFromJsonResponse($response);
	}

	/**
	Close the current window.
	 */
	public function closeWindow() {
		$request = $this->requestURL . "/window";
		$session = $this->curlInit($request);
		$this->prepareDELETE($session);
		$response = curl_exec($session);
		$this->curlClose();
	}

	/**
	 * Retrieve all cookies visible to the current page.
	 * @return array array with all cookies
	 */
	public function getAllCookies() {
		$response = $this->execute_rest_request_GET($this->requestURL . "/cookie");
		return $this->extractValueFromJsonResponse($response);
	}

	/**
	 * Set a cookie.
	 */
	public function setCookie($name, $value, $cookie_path='/', $domain='', $secure=false, $expiry='') {
		$request = $this->requestURL . "/cookie";
		$session = $this->curlInit($request);
		$cookie = array('name'=>$name, 'value'=>$value, 'secure'=>$secure);
		if (!empty($cookie_path)) $cookie['path']=$cookie_path;
		if (!empty($domain)) $cookie['domain']=$domain;
		if (!empty($expiry)) $cookie['expiry']=$exipry;
		$args = array('cookie' => $cookie );
		$jsonData = json_encode($args);
		$this->preparePOST($session, $jsonData);
		$response = curl_exec($session);
		return $this->extractValueFromJsonResponse($response);
	}


	/**
	Delete the cookie with the given name. This command should be a no-op if there is no such cookie visible to the current page.
	 */
	public function deleteCookie($name) {
		$request = $this->requestURL . "/cookie/".$name;
		$session = $this->curlInit($request);
		$this->prepareDELETE($session);
		$response = curl_exec($session);
		$this->curlClose();
	}

	/**
	Delete all cookies visible to the current page.
	 */
	public function deleteAllCookies($name) {
		$request = $this->requestURL . "/cookie";
		$session = $this->curlInit($request);
		$this->prepareDELETE($session);
		$response = curl_exec($session);
		$this->curlClose();
	}


	/**
	 * Gets the text of the currently displayed JavaScript alert(), confirm(), or prompt() dialog.
	 * @return string The text of the currently displayed alert.
	 */
	public function getAlertText() {
		$response = $this->execute_rest_request_GET($this->requestURL . "/alert_text");
		return $this->extractValueFromJsonResponse($response);
	}

	/**
	 * Sends keystrokes to a JavaScript prompt() dialog.
	 */
	public function sendAlertText($text) {
		$request = $this->requestURL . "/alert_text";
		$session = $this->curlInit($request);
		$args = array('keysToSend' => $text);
		$jsonData = json_encode($args);
		$this->preparePOST($session, $jsonData);
		$response = curl_exec($session);
		return $this->extractValueFromJsonResponse($response);
	}

	/**
	 * Get the current browser orientation. The server should return a valid orientation value as defined in ScreenOrientation: LANDSCAPE|PORTRAIT.
	 * @return string The current browser orientation corresponding to a value defined in ScreenOrientation: LANDSCAPE|PORTRAIT.
	 */
	public function getOrientation() {
		$response = $this->execute_rest_request_GET($this->requestURL . "/orientation");
		return $this->extractValueFromJsonResponse($response);
	}

	/**
	 * Set the browser orientation. The orientation should be specified as defined in ScreenOrientation: LANDSCAPE|PORTRAIT.
	 */
	public function setOrientation($orientation) {
		$request = $this->requestURL . "/orientation";
		$session = $this->curlInit($request);
		$args = array('orientation' => $orientation);
		$jsonData = json_encode($args);
		$this->preparePOST($session, $jsonData);
		curl_exec($session);
	}

	/**
	 * Accepts the currently displayed alert dialog. Usually, this is equivalent to clicking on the 'OK' button in the dialog.
	 */
	public function acceptAlert() {
		$request = $this->requestURL . "/accept_alert";
		$session = $this->curlInit($request);
		$this->preparePOST($session, '');
		$response = curl_exec($session);
		return $this->extractValueFromJsonResponse($response);
	}

	/**
	 *     Dismisses the currently displayed alert dialog. For confirm() and prompt() dialogs,
	 *	this is equivalent to clicking the 'Cancel' button. For alert() dialogs, this is equivalent to clicking the 'OK' button.
	 */
	public function dismissAlert() {
		$request = $this->requestURL . "/dismiss_alert";
		$session = $this->curlInit($request);
		$this->preparePOST($session, '');
		$response = curl_exec($session);
		return $this->extractValueFromJsonResponse($response);
	}

	/**
	Inject a snippet of JavaScript into the page for execution in the context of the currently selected frame.
	 * The executed script is assumed to be synchronous and the result of evaluating the script
	 * is returned to the client.
	 * @return Object result of evaluating the script is returned to the client.
	 */
	public function execute($script, $script_args) {
		$request = $this->requestURL . "/execute";
		$session = $this->curlInit($request);
		$args = array('script' => $script, 'args' => $script_args);
		$jsonData = json_encode($args);
		$this->preparePOST($session, $jsonData);
		$response = curl_exec($session);
		return $this->extractValueFromJsonResponse($response);
	}

	/**
	Inject a snippet of JavaScript into the page for execution in the context of the currently selected frame.
	 * The executed script is assumed to be synchronous and the result of evaluating the script
	 * is returned to the client.
	 * @return Object result of evaluating the script is returned to the client.
	 */
	public function executeScript($script, $script_args) {
		$request = $this->requestURL . "/execute";
		$session = $this->curlInit($request);
		$args = array('script' => $script, 'args' => $script_args);
		$jsonData = json_encode($args);
		$this->preparePOST($session, $jsonData);
		$response = curl_exec($session);
		return $this->extractValueFromJsonResponse($response);
	}

	/**
	Inject a snippet of JavaScript into the page for execution
	 * in the context of the currently selected frame. The executed script
	 * is assumed to be asynchronous and must signal that is done by invoking
	 * the provided callback, which is always provided as the final argument
	 * to the function. The value to this callback will be returned to the client.
	 * @return Object result of evaluating the script is returned to the client.
	 */
	public function executeAsyncScript($script, $script_args) {
		$request = $this->requestURL . "/execute_async";
		$session = $this->curlInit($request);
		$args = array('script' => $script, 'args' => $script_args);
		$jsonData = json_encode($args);
		$this->preparePOST($session, $jsonData);
		$response = curl_exec($session);
		return $this->extractValueFromJsonResponse($response);
	}

	/**
	 * Take a screenshot of the current page.
	 * @return string The screenshot as a base64 encoded PNG.
	 */
	public function getScreenshot() {
		$request = $this->requestURL . "/screenshot";
		$response = $this->execute_rest_request_GET($request);
		return $this->extractValueFromJsonResponse($response);
	}

	/**
	 * Take a screenshot of the current page and saves it to png file.
	 * @param $png_filename filename (with path) where file has to be saved
	 * @return bool result of operation (false if failure)
	 */
	public function getScreenshotAndSaveToFile($png_filename) {
		$img = $this->getScreenshot();
		$data = base64_decode($img);
		$success = file_put_contents($png_filename, $data);
	}

}

class WebDriverBase {

	protected $requestURL;
	protected $_curl;

	function __construct($_seleniumUrl) {
		$this->requestURL = $_seleniumUrl;
	}

	protected function &curlInit( $url ) {
		if( $this->_curl === null ) {
			$this->_curl = curl_init( $url );
		} else {
			curl_setopt( $this->_curl, CURLOPT_HTTPGET, true );
			curl_setopt( $this->_curl, CURLOPT_URL, $url );
		}
		curl_setopt( $this->_curl, CURLOPT_HTTPHEADER, array("application/json;charset=UTF-8"));
		curl_setopt( $this->_curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->_curl, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $this->_curl, CURLOPT_HEADER, false );
//		print_r($url."\n");
		return $this->_curl;
	}

	protected function curlClose() {
		if( $this->_curl !== null ) {
			curl_close( $this->_curl );
			$this->_curl = null;
		}
	}

	protected function preparePOST($session, $postargs) {
		curl_setopt($session, CURLOPT_POST, true);
		if ($postargs) {
			curl_setopt($session, CURLOPT_POSTFIELDS, $postargs);
		}
	}

	/**
	 * Execute POST request
	 * @param string $request URL REST request
	 * @param string $postargs POST data
	 * @return string $response Response from POST request
	 */
	protected function execute_rest_request_POST($request, $postargs) {
		$session = $this->curlInit($request);
		$this->preparePOST($session, $postargs);
		$response = trim(curl_exec($session));
		return $response;
	}

	protected function prepareGET( $session ) {

		//curl_setopt($session, CURLOPT_GET, true);
	}

	protected function prepareDELETE($session) {
		curl_setopt($session, CURLOPT_CUSTOMREQUEST, 'DELETE');
	}

	/**
	 * Execute GET request
	 * @param string $request URL REST request
	 * @return string $response Response from GET request
	 */
	protected function execute_rest_request_GET($request) {
		$session = $this->curlInit($request);
		$this->prepareGET($session);
		$response = curl_exec($session);
		return $response;
	}

	/**
	 * Function checks if there was error in last command excecution.
	 * If there was an error - new Exception is thrown.
	 * @param Curl_session $session
	 */
	protected function handleError($session, $response) {
		$last_error = curl_errno($session);
		print_r('last_error = ' . $last_error);
		if ($last_error == 500) { // selenium error
			print_r($response);
			throw new WebDriverException($message, $code, $previous);
		} else
			if ($last_error != 0) { // unknown error
				print_r($response);
				throw new WebDriverException($message, $code, $previous);
			}
	}

	/**
	 * Function analyses status attribute of the response.
	 * For some statuses it throws exception (for example NoSuchElementException).
	 * @param string $json_response
	 */
	protected function handleResponse($json_response) {
		$status = $json_response->{'status'};
		switch ($status) {
			case WebDriverResponseStatus::Success:
				return;
				break;
			case WebDriverResponseStatus::NoSuchElement:
				throw new NoSuchElementException($json_response);
				break;
			default:
				print_r($json_response);
				throw new WebDriverException($status, 99, null);
				break;
		}
		/*
						 * 0 	Success 	The command executed successfully.
		7 	NoSuchElement 	An element could not be located on the page using the given search parameters.
		8 	NoSuchFrame 	A request to switch to a frame could not be satisfied because the frame could not be found.
		9 	UnknownCommand 	The requested resource could not be found, or a request was received using an HTTP method that is not supported by the mapped resource.
		10 	StaleElementReference 	An element command failed because the referenced element is no longer attached to the DOM.
		11 	ElementNotVisible 	An element command could not be completed because the element is not visible on the page.
		12 	InvalidElementState 	An element command could not be completed because the element is in an invalid state (e.g. attempting to click a disabled element).
		13 	UnknownError 	An unknown server-side error occurred while processing the command.
		15 	ElementIsNotSelectable 	An attempt was made to select an element that cannot be selected.
		17 	JavaScriptError 	An error occurred while executing user supplied JavaScript.
		19 	XPathLookupError 	An error occurred while searching for an element by XPath.
		23 	NoSuchWindow 	A request to switch to a different window could not be satisfied because the window could not be found.
		24 	InvalidCookieDomain 	An illegal attempt was made to set a cookie under a different domain than the current page.
		25 	UnableToSetCookie 	A request to set a cookie's value could not be satisfied.
		28 	Timeout 	A command did not complete before its timeout expired.
						 */
	}

	/**
	 * Search for an element on the page, starting from the document root.
	 * @param string $locatorStrategy
	 * @param string $value
	 * @return WebElement found element
	 */
	public function findElementBy($locatorStrategy, $value) {
		$request = $this->requestURL . "/element";
		$session = $this->curlInit($request);
		//$postargs = "{'using':'" . $locatorStrategy . "', 'value':'" . $value . "'}";
		$args = array('using' => $locatorStrategy, 'value' => $value);
		$postargs = json_encode($args);
		$this->preparePOST($session, $postargs);
		$response = curl_exec($session);
		$json_response = json_decode(trim($response));
		if (!$json_response) {
			return null;
		}
		$this->handleResponse($json_response);
		$element = $json_response->{'value'};
		/*
						if (!$element || !isset($element->ELEMENT)) {
								return null;
						}*/
		return new WebElement($this, $element, null);
	}

	/**
	 * Search for an element on the page, starting from the document root.
	 * @return WebElement found element
	 */
	public function findActiveElement() {
		$request = $this->requestURL . "/element/active";
		$session = $this->curlInit($request);
		$this->preparePOST($session, null);
		$response = curl_exec($session);
		$json_response = json_decode(trim($response));
		if (!$json_response) {
			return null;
		}
		$this->handleResponse($json_response);
		$element = $json_response->{'value'};

		return new WebElement($this, $element, null);
	}



	/**
	 * 	Search for multiple elements on the page, starting from the document root.
	 * @param string $locatorStrategy
	 * @param string $value
	 * @return array of WebElement
	 */
	public function findElementsBy($locatorStrategy, $value) {
		$request = $this->requestURL . "/elements";
		$session = $this->curlInit($request);
		//$postargs = "{'using':'" . $locatorStrategy . "', 'value':'" . $value . "'}";
		$args = array('using' => $locatorStrategy, 'value' => $value);
		$postargs = json_encode($args);
		$this->preparePOST($session, $postargs);
		$response = trim(curl_exec($session));
		$json_response = json_decode($response);
		$elements = $json_response->{'value'};
		$webelements = array();
		foreach ($elements as $key => $element) {
			$webelements[] = new WebElement($this, $element, null);
		}
		return $webelements;
	}


	/**
	 * Function returns value of 'value' attribute in JSON string
	 * @example extractValueFromJsonResponse("{'name':'John', 'value':'123'}")=='123'
	 * @param string $json JSON string with value attrubute to extract
	 * @return string value of 'value' attribute
	 */
	public function extractValueFromJsonResponse($json) {
		$json = json_decode(trim($json));
		if ($json && isset($json->value)) {
			return $json->value;
		}
		return null;
	}

}

class WebDriverResponseStatus {
	const Success 	= 0;    //The command executed successfully.
	const NoSuchElement 	=7;     //An element could not be located on the page using the given search parameters.
	const NoSuchFrame 	=8;     //A request to switch to a frame could not be satisfied because the frame could not be found.
	const UnknownCommand 	=9;     //The requested resource could not be found, or a request was received using an HTTP method that is not supported by the mapped resource.
	const StaleElementReference=10;   	//An element command failed because the referenced element is no longer attached to the DOM.
	const ElementNotVisible=11; 	//An element command could not be completed because the element is not visible on the page.
	const InvalidElementState=12; 	//An element command could not be completed because the element is in an invalid state (e.g. attempting to click a disabled element).
	const UnknownError=13; 	//An unknown server-side error occurred while processing the command.
	const ElementIsNotSelectable=15; 	//An attempt was made to select an element that cannot be selected.
	const JavaScriptError=17; 	//An error occurred while executing user supplied JavaScript.
	const XPathLookupError=19; 	//An error occurred while searching for an element by XPath.
	const NoSuchWindow=23; 	//A request to switch to a different window could not be satisfied because the window could not be found.
	const InvalidCookieDomain=24; 	//An illegal attempt was made to set a cookie under a different domain than the current page.
	const UnableToSetCookie=25; 	//A request to set a cookie's value could not be satisfied.
	const Timeout=28;         //A command did not complete before its timeout expired.
}

class WebElement extends WebDriverBase {

	function __construct($parent, $element, $options) {
		if (get_class($parent) == 'WebDriver') {
			$root = $parent->requestURL;
		} else {
			$root = preg_replace("(/element/.*)", "", $parent->requestURL);
		}
		parent::__construct($root . "/element/" . $element->ELEMENT);
	}

	public function sendKeys($value) {
		if (!is_array($value)) {
			throw new Exception("$value must be an array");
		}
		$request = $this->requestURL . "/value";
		$session = $this->curlInit($request);
		$args = array( 'value'=>$value );
		$postargs =json_encode($args);
		$this->preparePOST($session, $postargs);
		$response = trim(curl_exec($session));
	}

	public function getValue() {
		$request = $this->requestURL . "/value";
		$response = $this->execute_rest_request_GET($request);
		return $this->extractValueFromJsonResponse($response);
	}

	public function clear() {
		$request = $this->requestURL . "/clear";
		$session = $this->curlInit($request);
		$this->preparePOST($session, null);
		$response = trim(curl_exec($session));
	}

	public function click() {
		$request = $this->requestURL . "/click";
		$session = $this->curlInit($request);
		$this->preparePOST($session, null);
		$response = trim(curl_exec($session));
	}

	public function submit() {
		$request = $this->requestURL . "/submit";
		$session = $this->curlInit($request);
		$this->preparePOST($session, "");
		$response = trim(curl_exec($session));
	}

	public function getText() {
		$request = $this->requestURL . "/text";
		$response = $this->execute_rest_request_GET($request);
		return $this->extractValueFromJsonResponse($response);
	}

	public function getName() {
		$request = $this->requestURL . "/name";
		$response = $this->execute_rest_request_GET($request);
		return $this->extractValueFromJsonResponse($response);
	}

	/**
	 * Get the value of a the given attribute of the element.
	 */
	public function getAttribute($attribute) {
		$request = $this->requestURL . '/attribute/'.$attribute;
		$response = $this->execute_rest_request_GET($request);
		$attributeValue = $this->extractValueFromJsonResponse($response);
		return ($attributeValue);
	}

	/**
	 * Determine if an OPTION element, or an INPUT element of type checkbox or radiobutton is currently selected.
	 * @return boolean Whether the element is selected.
	 */
	public function isSelected() {
		$request = $this->requestURL . "/selected";
		$response = $this->execute_rest_request_GET($request);
		$isSelected = $this->extractValueFromJsonResponse($response);
		return ($isSelected == 'true');
	}

	/**
	 * Select an OPTION element, or an INPUT element of type checkbox or radiobutton.
	 *
	 */
	public function setSelected() {
		$this->click(); //setSelected is now deprecated
	}


	/**
	 * find OPTION by text in combobox
	 *
	 */
	public function findOptionElementByText($text) {
		$option = $this->findElementBy(LocatorStrategy::xpath, 'option[normalize-space(text())="'.$text.'"]');
		return $option;
	}

	/**
	 * find OPTION by value in combobox
	 *
	 */
	public function findOptionElementByValue($val) {
		$option = $this->findElementBy(LocatorStrategy::xpath, 'option[@value="'.$val.'"]');
		return $option;
	}


	/**
	 * Determine if an element is currently enabled
	 * @return boolean Whether the element is enabled.
	 */
	public function isEnabled() {
		$request = $this->requestURL . "/enabled";
		$response = $this->execute_rest_request_GET($request);
		$isSelected = $this->extractValueFromJsonResponse($response);
		return ($isSelected == 'true');
	}


	/**
	 * Determine if an element is currently displayed.
	 * @return boolean Whether the element is displayed.
	 */
	public function isDisplayed(){
		$request = $this->requestURL . "/displayed";
		$response = $this->execute_rest_request_GET($request);
		$isDisplayed = $this->extractValueFromJsonResponse($response);
		return ($isDisplayed == 'true');
	}


	/**
	 * Determine an element's size in pixels. The size will be returned as a JSON object with width and height properties.
	 * @return width:number,height:number The width and height of the element, in pixels.
	 */
	public function getSize(){

		$request = $this->requestURL . "/size";
		$response = $this->execute_rest_request_GET($request);
		$sizeValues = $this->extractValueFromJsonResponse($response);
		return $sizeValues;
	}


	/**
	 * Query the value of an element's computed CSS property. The CSS property to query should be specified using
	 * the CSS property name, not the JavaScript property name (e.g. background-color instead of backgroundColor).
	 * @return string The value of the specified CSS property.
	 */
	public function getCssProperty($propertyName){
		$request = $this->requestURL . "/css/".$propertyName;
		$response = $this->execute_rest_request_GET($request);
		$propertyValue = $this->extractValueFromJsonResponse($response);
		return $propertyValue;
	}


	/**
	 * Test if two element IDs refer to the same DOM element.
	 * @return boolean Whether the two IDs refer to the same element.
	 */
	public function isOtherId($otherId) {

		$request = $this->requestURL . "/equals/".$otherId;
		$response = $this->execute_rest_request_GET($request);
		$isOther = $this->extractValueFromJsonResponse($response);
		return ($isOther == 'true');

	}


	/**
	 * Determine an element's location on the page. The point (0, 0) refers to the upper-left corner of the page.
	 * The element's coordinates are returned as a JSON object with x and y properties.
	 * @return x:number, y:number The X and Y coordinates for the element on the page.
	 */
	public function getLocation() {

		$request = $this->requestURL . "/location";
		$response = $this->execute_rest_request_GET($request);
		$location = $this->extractValueFromJsonResponse($response);
		return $location;

	}


	/**
	 * Determine an element's location on the screen once it has been scrolled into view.
	 * The element's coordinates are returned as a JSON object with x and y properties.
	 * @return x:number, y:number The X and Y coordinates for the element.
	 */
	public function getLocationInView() {

		$request = $this->requestURL . "/location_in_view";
		$response = $this->execute_rest_request_GET($request);
		$location = $this->extractValueFromJsonResponse($response);
		return $location;

	}


}
