<?php

/**
 * Test for the ColorUtils class.
 */
include('../htdocs/system/classes/colorutils.php');

function bs( $v ) { return $v ? 'TRUE' : 'FALSE'; }

$tests_failed= array();

$tests= array(
	'R, G, B to Array' => array(
		"\$green == array ( 'r' => 127, 'g' => 255, 'b' => 64, )",
	),
	'RGB to HEX' => array(
		"\$green_hex == '7fff40'",
	),
	'RGV to HSV' => array(
		"\$green_hsv == array ( 'h' => 100, 's' => 75, 'v' => 255, )",
	),
	'HSV back to RGB (conversion introduces rounding errors)' => array(
		"\$green_from_hsv == array ( 'r' => 128, 'g' => 255, 'b' => 64, )",
	),
	'HEX to RGB' => array(
		"\$orange == array ( 'r' => 237, 'g' => 105, 'b' => 31, )",
		"\$cyan == array ( 'r' => 136, 'g' => 187, 'b' => 204, )",
		"\$red == array ( 'r' => 240, 'g' => 0, 'b' => 0, )",
	),
);

$green= ColorUtils::rgb_rgbarr( 127, 255, 64 );
$green_hex= ColorUtils::rgb_hex( $green );
$green_hsv= ColorUtils::rgb_hsv( $green );
$green_from_hsv= ColorUtils::hsv_rgb( $green_hsv );
$orange= ColorUtils::hex_rgb( '#ed691f' );
$cyan= ColorUtils::hex_rgb( '8bc' );
$red= ColorUtils::hex_rgb( 'f0' );

print( "<h1>Running tests</h1>\n" );

foreach ( $tests as $name => $group ) {
	print( "<h2>{$name}</h2>\n" );
	foreach ( $group as $test ) {
		$result= eval( 'return (' . $test . ');' );
		printf( "<p><strong>%s</strong> == ( %s )</p>\n", bs( $result ), $test );
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
			print( "<p>{$test}</p>\n" );
		}
	}
}
else {
	print( "<h1>All tests successful</h1>\n" );
}

?>
