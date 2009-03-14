<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

class system_classes_UtilsTest extends PHPUnit_Framework_TestCase
{
	function setup()
	{
		// For crypt tests
		$this->plaintext = 'Hello, World';
	}

	public function testGet_params()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testEnd_in_slash()
	{
		$str = "foo";
		$this->assertEquals( "foo/", Utils::end_in_slash($str) );

		$str = "foo/";
		$this->assertEquals( "foo/", Utils::end_in_slash($str) );
	}

	public function testRedirect()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testAtomtime()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testNonce()
	{
		$str = Utils::nonce();
		$this->assertEquals( 12, strlen($str) );
	}

	public function testWSSE()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testStripslashes()
	{
		$str = '\\"this string\\"';
		$result = Utils::stripslashes( $str );
		$this->assertEquals('"this string"', $result );

		$arr = array(
			'\\"this string\\" is',
			'act\\"ually\\" an\\" array\\"',
			'of \\\'strings\\\'',
		);
		$result = Utils::stripslashes( $arr );
		$expected = array( '"this string" is', 'act"ually" an" array"', "of 'strings'" );
		$this->assertEquals($expected, $result);
	}

	public function testAddslashes()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testDe_amp()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testGlue_url()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testRevert_magic_quotes_gpc()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testQuote_spaced()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testImplode_quoted()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testPlaceholder_string()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testArchive_pages()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testMap_array()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testDebug_reveal()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testDebug()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testFiredebug()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testFirebacktrace()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testCrypt()
	{
		$crypt = Utils::crypt( $this->plaintext );
		$this->assertRegExp( '/^{SSHA512}/', $crypt );
		$this->assertEquals( 101, strlen($crypt) );

		// Test the hash-compare works
		$hash = "{SSHA512}p9F5CeA1xrB2ypnI6tl9B2ol/+Et7qObtZfUawRRNck5MxFQtnRkdfAgPwbUlGMf3GcTwvPcv1/fNAEtHxrcozekoMU=";

		$this->assertTrue( Utils::crypt($this->plaintext, $hash) );
	}

	public function testSha1()
	{
		$crypt = Utils::sha1( $this->plaintext );
		$this->assertEquals( $crypt, '{SHA1}907d14fb3af2b0d4f18c2d46abe8aedce17367bd' );
	}

	public function testMd5()
	{
		$crypt = Utils::md5( $this->plaintext );
		$this->assertEquals( $crypt, '{MD5}82bb413746aee42f89dea2b59614f9ef' );
	}

	public function testSsha()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testSsha512()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testGetdate()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testLocale_date()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testSlugify()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testHtml_select()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testHtml_checkboxes()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testTruncate()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testPhp_check_syntax()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testPhp_check_file_syntax()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testGlob()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testHuman_size()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testTruncate_log()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testSingle_array()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testMimetype()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testTrail()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testMail()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testRandom_password()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testArray_or()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testRor()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
?>