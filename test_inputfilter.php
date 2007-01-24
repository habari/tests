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
	'filtering malicious html' => array(
		"InputFilter::filter_html_elements( '<p onclick=\"window.alert(\\'boo\\')\">Hey.</p><a href=\"#\" style=\"position: absolute; left: 1px; top: 3px;\">Whee!</a>' ) == '<p>Hey.</p><a href=\"#\">Whee!</a>'",
		"InputFilter::filter_html_elements( '<a href=\"javascript:alert(\\'yay\\')\" style=\"text-decoration: none;\">Whee!</a>' ) == '<a>Whee!</a>'",
	),
//	'complete filtering run' => array(
//		"InputFilter::filter( '' ) = ''",
//	),
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
