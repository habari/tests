<?php

/**
 * Test for the InputFilter class.
 */
include('../htdocs/system/classes/inputfilter.php');
include('../htdocs/system/classes/htmltokenizer.php');

include('../htdocs/system/classes/utils.php');

function bs( $v ) { return $v ? 'TRUE' : 'FALSE'; }

$tests_failed= array();

$tests= array(
	'strings with nulls' => array(
		"InputFilter::strip_nulls( 'This string has NULL char\0act\0ers!' ) == 'This string has NULL characters!'",
	),
	'valid entities' => array(
		"InputFilter::strip_illegal_entities( 'Valid: &#160;' ) == 'Valid: &#160;'",
		"InputFilter::strip_illegal_entities( 'Valid: &#x0A;' ) == 'Valid: &#10;'",
		"InputFilter::strip_illegal_entities( 'Valid: &reg;' ) == 'Valid: &reg;'",
	),
	'valid entities: corner cases' => array(
		"InputFilter::strip_illegal_entities( 'This is valid: &reg;.' ) == 'This is valid: &reg;.'",
		"InputFilter::strip_illegal_entities( 'This is valid: &reg<br />.' ) == 'This is valid: &reg;<br />.'",
		"InputFilter::strip_illegal_entities( 'This is valid: &reg\nDee-dum.' ) == 'This is valid: &reg;\nDee-dum.'",
	),
	'invalid entity: invalid name' => array(
		"InputFilter::strip_illegal_entities( 'This entity does not exist: &zomg;.' ) == 'This entity does not exist: .'",
	),
	'invalid entity: invalid numeric' => array(
		"InputFilter::strip_illegal_entities( 'This entity is invalid: &#XfFdE9;.' ) == 'This entity is invalid: .'",
	),
	'URL parsing' => array(
		"InputFilter::parse_url( 'http://hey:there@moeffju.net:8137/foo/bar?baz=quux#blah' ) == array ( 'scheme' => 'http', 'host' => 'moeffju.net', 'port' => '8137', 'user' => 'hey', 'pass' => 'there', 'path' => '/foo/bar', 'query' => 'baz=quux', 'fragment' => 'blah', 'is_relative' => false, 'is_pseudo' => false, 'is_error' => false, 'pseudo_args' => '', )",
		"InputFilter::parse_url( 'http:moeffju.net/blog/' ) == array ( 'scheme' => 'http', 'host' => 'moeffju.net', 'port' => '', 'user' => '', 'pass' => '', 'path' => '/blog/', 'query' => '', 'fragment' => '', 'is_relative' => false, 'is_pseudo' => false, 'is_error' => false, 'pseudo_args' => '', )",
		"InputFilter::parse_url( 'moeffju.net/blog/' ) == array ( 'scheme' => 'http', 'host' => 'moeffju.net', 'port' => '', 'user' => '', 'pass' => '', 'path' => '/blog/', 'query' => '', 'fragment' => '', 'is_relative' => false, 'is_pseudo' => false, 'is_error' => false, 'pseudo_args' => '', )",
		"InputFilter::parse_url( '/furanzen/bla' ) == array ( 'scheme' => '', 'host' => '', 'port' => '', 'user' => '', 'pass' => '', 'path' => '/furanzen/bla', 'query' => '', 'fragment' => '', 'is_relative' => true, 'is_pseudo' => false, 'is_error' => false, 'pseudo_args' => '', )",
		"InputFilter::parse_url( '?bla=barbaz&foo' ) == array ( 'scheme' => '', 'host' => '', 'port' => '', 'user' => '', 'pass' => '', 'path' => '', 'query' => 'bla=barbaz&foo', 'fragment' => '', 'is_relative' => true, 'is_pseudo' => false, 'is_error' => false, 'pseudo_args' => '', )",
		"InputFilter::parse_url( '#' ) == array ( 'scheme' => '', 'host' => '', 'port' => '', 'user' => '', 'pass' => '', 'path' => '', 'query' => '', 'fragment' => '', 'is_relative' => true, 'is_pseudo' => false, 'is_error' => false, 'pseudo_args' => '', )",
		"InputFilter::parse_url( 'about:blank' ) == array ( 'scheme' => 'about', 'host' => '', 'port' => '', 'user' => '', 'pass' => '', 'path' => '', 'query' => '', 'fragment' => '', 'is_relative' => false, 'is_pseudo' => true, 'is_error' => false, 'pseudo_args' => 'blank', )",
		"InputFilter::parse_url( 'javascript:alert(document.cookie)' ) == array ( 'scheme' => 'javascript', 'host' => '', 'port' => '', 'user' => '', 'pass' => '', 'path' => '', 'query' => '', 'fragment' => '', 'is_relative' => false, 'is_pseudo' => true, 'is_error' => false, 'pseudo_args' => 'alert(document.cookie)', )",
		"InputFilter::parse_url( 'javascript:alert(\'/hey/there/foo?how=about#bar\')' ) == array ( 'scheme' => 'javascript', 'host' => '', 'port' => '', 'user' => '', 'pass' => '', 'path' => '', 'query' => '', 'fragment' => '', 'is_relative' => false, 'is_pseudo' => true, 'is_error' => false, 'pseudo_args' => 'alert(\'/hey/there/foo?how=about#bar\')', )",
	),
	'filtering malicious html' => array(
		"InputFilter::filter_html_elements( '<p onclick=\"window.alert(\\'boo\\')\">Hey.</p><a href=\"#\" style=\"position: absolute; left: 1px; top: 3px;\">Whee!</a>' ) == '<p>Hey.</p><a href=\"#\">Whee!</a>'",
		"InputFilter::filter_html_elements( '<a href=\"javascript:alert(\\'yay\\')\" style=\"text-decoration: none;\">Whee!</a>' ) == '<a>Whee!</a>'",
	),
	'complete filtering run' => array(
		"InputFilter::filter( '<p>I am <div><script src=\"ohnoes\" /><a>not a paragraph.</a><p CLASS=old><span> Or am I?</span>' ) == '<p>I am <div><a>not a paragraph.</a><p><span> Or am I?</span>'",
		"InputFilter::filter( '<p onClick=\"window.alert(\\'stole yer cookies!\\');\">Do not click here.</p>\n<script>alert(\"See this?\")</script>' ) == '<p>Do not click here.</p>\n'",
		// http://ha.ckers.org/blog/20070124/stopping-xss-but-allowing-html-is-hard/
		"InputFilter::filter( '<IMG src=\"http://ha.ckers.org/\" style\"=\"style=\"a/onerror=alert(String.fromCharCode(88,83,83))//\" &ampgt;`&gt' ) == ''",
		"InputFilter::filter( '<b>Hello world</b>\n\nThis is a <test>test</test> post.\n\nHere\\'s a first XSS attack. <<SCRIPT>alert(\\'XSS\\');//<</SCRIPT>\n\nHere\\'s a second try at a <a href=\"#\">second link</a>.\n\nHere\\'s a second XSS attack. <IMG SRC=\" &#14;  javascript:alert(\\'XSS\\');\">\n\nHere\\'s a third link hopefully <a href=\"#\">it won\\'t get removed</a>.\n\n<em>Thanks!</em>' ) == '<b>Hello world</b>\n\nThis is a test post.\n\nHere\\'s a first XSS attack. alert(\\'XSS\\');//SCRIPT>\n\nHere\\'s a second try at a <a href=\"#\">second link</a>.\n\nHere\\'s a second XSS attack. \n\nHere\\'s a third link hopefully <a href=\"#\">it won\\'t get removed</a>.\n\n<em>Thanks!</em>'",
	),
);

print( "<h1>Running tests</h1>\n" );

foreach ( $tests as $name => $group ) {
	print( "<h2>{$name}</h2>\n" );
	foreach ( $group as $test ) {
		$result= eval( 'return (' . $test . ');' );
		printf( "<p><strong>%s</strong> == ( %s )</p>\n", bs( $result ), htmlspecialchars( $test ) );
		if ( ! $result ) {
			$tests_failed[$name][]= $test;
		}
	}
}

if ( count( $tests_failed ) ) {
	print( "<h1>Failed tests</h1>\n" );
	foreach ( $tests_failed as $name => $tests ) {
		print( "<h2>{$name}</h2>\n" );
		foreach ( $tests as $test ) {
			list( $lhs, )= explode( '==', $test );
			$result= eval( 'return (' . $lhs . ');' );
			printf( "<p><tt>expected</tt> %s<br /><tt>actual&nbsp;&nbsp;</tt> %s == %s</p>\n", htmlspecialchars( $test ), htmlspecialchars( $lhs ), htmlspecialchars( var_export( $result, TRUE ) ) );
		}
	}
}
else {
	print( "<h1>All tests successful</h1>\n" );
}

?>
