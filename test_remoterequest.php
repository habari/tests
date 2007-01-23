<?php

/**
 * Test for the ColorUtils class.
 */
include('../htdocs/system/classes/remoterequest.php');
include('../htdocs/system/classes/curlrequestprocessor.php');
include('../htdocs/system/classes/socketrequestprocessor.php');

include('../htdocs/system/classes/utils.php');
include('../htdocs/system/classes/error.php');

error_reporting( E_ALL | E_STRICT );

function bs( $v ) { return $v ? 'TRUE' : 'FALSE'; }

$tests_failed= array();

$tests= array(
	'GET http://habariblog.org/' => array(
		"\$res",
	),
	'GET http://google.com/search' => array(
		"\$google",
	),
);

print( "<h1>Running tests</h1>\n" );

$processors= array(
	new CURLRequestProcessor,
	new SocketRequestProcessor,
);

foreach ( $processors as $processor ) {
	$rr= new RemoteRequest( 'http://habariblog.org/' );
	$rr->__set_processor( $processor );
	$res= $rr->execute();
	if ( $res ) {
	 	$results[]= array( get_class( $processor ), $rr->get_response_headers(), substr( $rr->get_response_body(), 0 ) );
	}
	else {
		$results[]= array( get_class( $processor ), $res, );
	}
	
	$google_rr= new RemoteRequest( 'http://google.com/search', 'GET' );
	$google_rr->__set_processor( $processor );
	$google_rr->set_params( array(
		'q' => 'habari',
		'hl' => 'en',
		'btnG' => 'Search',
	) );
	$google= $google_rr->execute();
	if ( $google ) {
	 	$results[]= array( get_class( $processor ), $google_rr->get_response_headers(), substr( $google_rr->get_response_body(), 0 ) );
	}
	else {
		$results[]= array( get_class( $processor ), $google, );
	}
		
	foreach ( $tests as $name => $group ) {
		print( "<h2>{$name}</h2>\n" );
		foreach ( $group as $test ) {
			$result= eval( 'return (' . $test . ');' );
			printf( "<p><strong>%s</strong> == ( %s )</p>\n", bs( $result ), var_export( $test, TRUE ) );
			
			Utils::debug( array_shift( $results ) );
			if ( ! $result ) {
				$tests_failed[$name][]= $test;
			}
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
