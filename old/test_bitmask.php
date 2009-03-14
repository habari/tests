<?php

// Test the bitmask class

include 'bootstrap.php';

class BitmaskTest
{

	function test_bitmask()
	{
		define('POST_FLAG_ALLOWS_COMMENTS'  ,1);
		define('POST_FLAG_ALLOWS_TRACKBACKS',1 << 1);
		define('POST_FLAG_ALLOWS_PINGBACKS' ,1 << 2);

		$flags= array(
		        'allows_comments'=>POST_FLAG_ALLOWS_COMMENTS
		      , 'allows_trackbacks'=>POST_FLAG_ALLOWS_TRACKBACKS
		      , 'allows_pingbacks'=>POST_FLAG_ALLOWS_PINGBACKS);

		$bitmask= new Bitmask($flags);

		$bitmask->allows_comments= true;
		$bitmask->allows_trackbacks= false;
		$bitmask->allows_pingbacks= true;

		$this->assert_true($bitmask->allows_comments);
		$this->assert_false($bitmask->allows_trackbacks);
		$this->assert_true($bitmask->allows_pingbacks);
	}

}


?>
