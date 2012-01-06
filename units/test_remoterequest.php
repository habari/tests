<?php

include '../bootstrap.php';

class RemoteRequestTest extends UnitTestCase {

	function do_stupid_things_in_global_scope() {
		/**
		 * Test for the RemoteRequest class.
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
			'GET http://test.habariproject.org/' => array(
				"\$res",
			),
			'GET http://test.habariproject.org/get' => array(
				"\$res_get",
			),
			'POST http://test.habariproject.org/post' => array(
				"\$res_post",
			),
		);

		print( "<h1>Running tests</h1>\n" );

		$processors= array(
			new CURLRequestProcessor,
			new SocketRequestProcessor,
		);

		foreach ( $processors as $processor ) {
			$rr= new RemoteRequest( 'http://test.habariproject.org/' );
			$rr->__set_processor( $processor );
			$res= $rr->execute();
			if ( $res === TRUE ) {
				$results[]= array( get_class( $processor ), $rr->get_response_headers(), substr( $rr->get_response_body(), 0 ) );
			}
			else {
				$results[]= array( get_class( $processor ), $res, );
			}

			$rr= new RemoteRequest( 'http://test.habariproject.org/get' );
			$rr->__set_processor( $processor );
			$rr->set_params( array (
				'query' => 'var',
				'another' => 'variable',
			) );
			$res_get= $rr->execute();
			if ( $res_get === TRUE ) {
				$results[]= array( get_class( $processor ), $rr->get_response_headers(), substr( $rr->get_response_body(), 0 ) );
			}
			else {
				$results[]= array( get_class( $processor ), $res_get, );
			}

			$rr= new RemoteRequest( 'http://test.habariproject.org/post', 'POST' );
			$rr->__set_processor( $processor );
			$rr->set_body( 'If you can read this, the test was successful.' );
			$res_post= $rr->execute();
			if ( $res_post === TRUE ) {
				$results[]= array( get_class( $processor ), $rr->get_response_headers(), substr( $rr->get_response_body(), 0 ) );
			}
			else {
				$results[]= array( get_class( $processor ), $res_post, );
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
	}

	function test_make_request() {
		$this->mark_test_incomplete('No tests in this unit are valid yet.');
	}
}

RemoteRequestTest::run_one( 'RemoteRequestTest' );

?>
