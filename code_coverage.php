<?php

namespace Habari;

	include 'bootstrap.php';
	
	// prevent it from running
	UnitTestCase::$run_all = true;
	
	include( 'test_multibyte_native.php' );
	
	// now let it run
	UnitTestCase::$run_all = false;
	
	$results = MultiByteNativeTest::run_one('MultiByteNativeTest');
	
	echo '<pre>';
	$results->code_coverage();
	echo '</pre>';
	
?>