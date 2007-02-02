<?php

include('../htdocs/system/classes/utils.php');
include('../htdocs/system/classes/error.php');

// test string
$str= 'Hello, World';

// create digest
$crypt= Utils::crypt( $str );

// verify
Utils::debug( array(
	'crypt' => $crypt,
	'invalid' => Utils::crypt( 'failure', $crypt ),
	'valid' => Utils::crypt( $str, $crypt ),
	'legacy' => Utils::crypt( $str, sha1( $str ) ),
) );

?>