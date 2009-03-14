<?php

/**
 * Test for the InputFilter class.
 */
include 'bootstrap.php';

class InputFilterTest extends UnitTestCase
{

	function test_strings_with_nulls()
	{
		$this->assert_equal(InputFilter::strip_nulls( 'This string has NULL char\0act\0ers!' ), 'This string has NULL characters!');
	}

	function test_valid_entities()
	{
		$this->assert_equal(InputFilter::strip_illegal_entities( 'Valid: &#160;' ), 'Valid: &#160;');
		$this->assert_equal(InputFilter::strip_illegal_entities( 'Valid: &#x0A;' ), 'Valid: &#10;');
		$this->assert_equal(InputFilter::strip_illegal_entities( 'Valid: &reg;' ), 'Valid: &reg;');
	}

	function test_valid_entities_corner_cases()
	{
		$this->assert_equal(InputFilter::strip_illegal_entities( 'This is valid: &reg;.' ), 'This is valid: &reg;.');
		$this->assert_equal(InputFilter::strip_illegal_entities( 'This is valid: &reg<br />.' ), 'This is valid: &reg;<br />.');
		$this->assert_equal(InputFilter::strip_illegal_entities( 'This is valid: &reg\nDee-dum.' ), 'This is valid: &reg;\nDee-dum.');
	}

	function test_invalid_entity_name()
	{
		$this->assert_equal(InputFilter::strip_illegal_entities( 'This entity does not exist: &zomg;.' ), 'This entity does not exist: .');
	}

	function test_invalid_entity_numeric()
	{
		$this->assert_equal(InputFilter::strip_illegal_entities( 'This entity is invalid: &#XfFdE9;.' ), 'This entity is invalid: .');
	}

	function test_url_parsing()
	{
		$this->assert_equal(InputFilter::parse_url( 'http://hey:there@moeffju.net:8137/foo/bar?baz=quux#blah' ), array ( 'scheme' => 'http', 'host' => 'moeffju.net', 'port' => '8137', 'user' => 'hey', 'pass' => 'there', 'path' => '/foo/bar', 'query' => 'baz=quux', 'fragment' => 'blah', 'is_relative' => false, 'is_pseudo' => false, 'is_error' => false, 'pseudo_args' => '', ) );
		$this->assert_equal(InputFilter::parse_url( 'http://localhost/blog/' ), array ( 'scheme' => 'http', 'host' => 'localhost', 'port' => '', 'user' => '', 'pass' => '', 'path' => '/blog/', 'query' => '', 'fragment' => '', 'is_relative' => false, 'is_pseudo' => false, 'is_error' => false, 'pseudo_args' => '', ) );
		$this->assert_equal(InputFilter::parse_url( 'http:moeffju.net/blog/' ), array ( 'scheme' => 'http', 'host' => 'moeffju.net', 'port' => '', 'user' => '', 'pass' => '', 'path' => '/blog/', 'query' => '', 'fragment' => '', 'is_relative' => false, 'is_pseudo' => false, 'is_error' => false, 'pseudo_args' => '', ) );
		//$this->assert_equal(InputFilter::parse_url( 'file://Z:/Habari/User Manual/index.html' ), array ( 'scheme' => 'file', 'host' => '', 'port' => '', 'user' => '', 'pass' => '', 'path' => 'Z:/Habari/User Manual/index.html', 'query' => '', 'fragment' => '', 'is_relative' => false, 'is_pseudo' => false, 'is_error' => false, 'pseudo_args' => '', ) );
		$this->assert_equal(InputFilter::parse_url( 'blog/' ), array ( 'scheme' => '', 'host' => '', 'port' => '', 'user' => '', 'pass' => '', 'path' => 'blog/', 'query' => '', 'fragment' => '', 'is_relative' => true, 'is_pseudo' => false, 'is_error' => false, 'pseudo_args' => '', ) );
		$this->assert_equal(InputFilter::parse_url( '/furanzen/bla' ), array ( 'scheme' => '', 'host' => '', 'port' => '', 'user' => '', 'pass' => '', 'path' => '/furanzen/bla', 'query' => '', 'fragment' => '', 'is_relative' => true, 'is_pseudo' => false, 'is_error' => false, 'pseudo_args' => '', ) );
		$this->assert_equal(InputFilter::parse_url( '?bla=barbaz&foo' ), array ( 'scheme' => '', 'host' => '', 'port' => '', 'user' => '', 'pass' => '', 'path' => '', 'query' => 'bla=barbaz&foo', 'fragment' => '', 'is_relative' => true, 'is_pseudo' => false, 'is_error' => false, 'pseudo_args' => '', ) );
		$this->assert_equal(InputFilter::parse_url( '#' ), array ( 'scheme' => '', 'host' => '', 'port' => '', 'user' => '', 'pass' => '', 'path' => '', 'query' => '', 'fragment' => '', 'is_relative' => true, 'is_pseudo' => false, 'is_error' => false, 'pseudo_args' => '', ) );
		$this->assert_equal(InputFilter::parse_url( 'about:blank' ), array ( 'scheme' => 'about', 'host' => '', 'port' => '', 'user' => '', 'pass' => '', 'path' => '', 'query' => '', 'fragment' => '', 'is_relative' => false, 'is_pseudo' => true, 'is_error' => false, 'pseudo_args' => 'blank', ) );
		$this->assert_equal(InputFilter::parse_url( 'javascript:alert(document.cookie)' ), array ( 'scheme' => 'javascript', 'host' => '', 'port' => '', 'user' => '', 'pass' => '', 'path' => '', 'query' => '', 'fragment' => '', 'is_relative' => false, 'is_pseudo' => true, 'is_error' => false, 'pseudo_args' => 'alert(document.cookie)', ) );
		$this->assert_equal(InputFilter::parse_url( 'javascript:alert(\'/hey/there/foo?how=about#bar\')' ), array ( 'scheme' => 'javascript', 'host' => '', 'port' => '', 'user' => '', 'pass' => '', 'path' => '', 'query' => '', 'fragment' => '', 'is_relative' => false, 'is_pseudo' => true, 'is_error' => false, 'pseudo_args' => 'alert(\'/hey/there/foo?how=about#bar\')', ) );
	}

	function test_filtering_malicious_html()
	{
		$this->assert_equal(InputFilter::filter_html_elements( '<p onclick=\"window.alert(\'boo\')\">Hey.</p><a href=\"#\" style=\"position: absolute; left: 1px; top: 3px;\">Whee!</a>' ), '<p>Hey.</p><a href=\"#\">Whee!</a>');
		$this->assert_equal(InputFilter::filter_html_elements( '<a href=\"javascript:alert(\'yay\')\" style=\"text-decoration: none;\">Whee!</a>' ), '<a>Whee!</a>');
	}

	function test_complete_filtering_run()
	{
		$this->assert_equal(InputFilter::filter( '<p>I am <div><script src=\"ohnoes\" /><a>not a paragraph.</a><p CLASS=old><span> Or am I?</span>' ), '<p>I am <div><a>not a paragraph.</a><p><span> Or am I?</span>');
		$this->assert_equal(InputFilter::filter( '<p onClick=\"window.alert(\'stole yer cookies!\');\">Do not click here.</p>\n<script>alert(\"See this?\")</script>' ), '<p>Do not click here.</p>\n');
		// http://ha.ckers.org/blog/20070124/stopping-xss-but-allowing-html-is-hard/
		$this->assert_equal(InputFilter::filter( '<IMG src=\"http://ha.ckers.org/\" style\"=\"style=\"a/onerror=alert(String.fromCharCode(88,83,83))//\" &ampgt;`&gt' ), 'onerror=alert(String.fromCharCode(88,83,83))//\" &`&gt');
		$this->assert_equal(InputFilter::filter( '<b>Hello world</b>\n\nThis is a <test>test</test> post.\n\nHere\'s a first XSS attack. <<SCRIPT>alert(\'XSS\');//<</SCRIPT>\n\nHere\'s a second try at a <a href=\"#\">second link</a>.\n\nHere\'s a second XSS attack. <IMG SRC=\" &#14;  javascript:alert(\'XSS\');\">\n\nHere\'s a third link hopefully <a href=\"#\">it won\'t get removed</a>.\n\n<em>Thanks!</em>' ), '<b>Hello world</b>\n\nThis is a  post.\n\nHere\'s a first XSS attack. ');
		$this->assert_equal(InputFilter::filter( '<<test>script>alert(\'boom\');</test>' ), '');
		$this->assert_equal(InputFilter::filter( '<<test></test>script>alert(\'boom\');' ), '');
		$this->assert_equal(InputFilter::filter( '<<test><</test>script>alert(\'boom\');' ), '');
		$this->assert_equal(InputFilter::filter( '<ScRIpT>alert(\'whee\');</SCRiPT>' ), '');

	}

}

InputFilterTest::run_one('InputFilterTest');

?>
