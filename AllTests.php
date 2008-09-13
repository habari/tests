<?php

require_once 'PHPUnit/Framework.php';

require_once 'phpunit/tests/htdocs/user/classes/AllTests.php';

/**
* 
*/
class AllTests
{
	
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite("Project");
		$suite->addTest( HTDocs_User_Classes_AllTests::suite() );
		
		return $suite;
	}
	
}


?>