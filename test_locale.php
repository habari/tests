<?php

/**
 * Test for the InputFilter class.
 */
include('../htdocs/system/classes/locale.php');

include('../htdocs/system/classes/error.php');
include('../htdocs/system/classes/utils.php');

function bs( $v ) { return $v ? 'TRUE' : 'FALSE'; }

$tests_failed= array();

$tests= array(
	'File reading' => array(
		"Locale::__run_loadfile_test('test_locale-en.mo')",
	),
	'Plural tests' => array(
		// de
		"Locale::__run_plural_test( 'Plural-Forms: nplurals=2; plural=(n==1?0:1);' ) == '10111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111'",
		// fr
		"Locale::__run_plural_test( 'Plural-Forms: nplurals=2; plural=n>1?1:0' ) == '00111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111'",
		// lt
		"Locale::__run_plural_test( 'Plural-Forms: nplurals=3; plural=n%10==1 && n%100!=11 ? 0 : n%10>=2 && (n%100<10 || n%100>=20) ? 1 : 2;' ) == '20111111112222222222201111111120111111112011111111201111111120111111112011111111201111111120111111112011111111222222222220111111112011111111201111111120111111112011111111201111111120111111112011111111'",
		// cz
		"Locale::__run_plural_test( 'Plural-Forms: nplurals=3; plural=n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2;' ) == '20111222222222222222201112222220111222222011122222201112222220111222222011122222201112222220111222222011122222222222222220111222222011122222201112222220111222222011122222201112222220111222222011122222'",
		// hu
		"Locale::__run_plural_test( 'Plural-Forms: nplurals=1; plural=0;' ) == '00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000'",
		// ru
		"Locale::__run_plural_test( 'Plural-Forms: nplurals=3; plural=n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2;' ) == '20111222222222222222201112222220111222222011122222201112222220111222222011122222201112222220111222222011122222222222222220111222222011122222201112222220111222222011122222201112222220111222222011122222'",
		// pl
		"Locale::__run_plural_test( 'Plural-Forms: nplurals=3; plural=n==1 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2' ) == '20111222222222222222221112222222111222222211122222221112222222111222222211122222221112222222111222222211122222222222222222111222222211122222221112222222111222222211122222221112222222111222222211122222'",
		// sl
		"Locale::__run_plural_test( 'Plural-Forms: nplurals=4; plural=n%100==1 ? 0 : n%100==2 ? 1 : n%100==3 || n%100==4 ? 2 : 3' ) == '30122333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333012233333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333'",
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
			list( $lhs, )= explode( ' == ', $test );
			$result= eval( 'return (' . $lhs . ');' );
			printf( "<p><tt>expected</tt> %s<br /><tt>actual&nbsp;&nbsp;</tt> %s == %s</p>\n", htmlspecialchars( $test ), htmlspecialchars( $lhs ), htmlspecialchars( var_export( $result, TRUE ) ) );
		}
	}
}
else {
	print( "<h1>All tests successful</h1>\n" );
}

?>
