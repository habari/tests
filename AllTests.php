<?php
/*
	HOW TO USE THIS FILE FOR TESTING
	================================

	1. Create a tests directory inside your Habari root
	(inside the htdocs directory, not below the htdocs directory
	where this file is usually found)
	2. Checkout the tests directory from the Habari repo to the new
	tests directory you created, or copy the contents of an already
	checked out tests directory to the new one.
	3. Add the PHPUnit source to the include_path in your php.ini
	4. Execute this file with your php executable, like so:
	      php -f AllTests.php

	If your php executable isn't in your system path, you may need
	to specify the full path to the php executable.  Also, you may
	need to specify the full path to the AllTests.php file.

	This test may require an active database, and may render that
	database subsequently inoperable.
*/

require_once 'PHPUnit/TextUI/TestRunner.php';

require 'phpunit_suite.php'; // Habari_full_TestSuite

// PHPEdit Inclusions -- dot not remove this comment
// /PHPEdit Inclusions -- dot not remove this comment

PHPUnit_TextUI_TestRunner::run( Habari_full_TestSuite::suite() );

?>