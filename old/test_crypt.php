<?php

include('bootstrap.php');

class CryptTest extends UnitTestCase
{

	function setup()
	{
		$this->plaintext = 'Hello, World';
		$this->crypt = Utils::crypt( $this->plaintext );
	}

	function test_crypt()
	{
		$this->assert_equal($this->crypt, 'what value crypt should be');
	}

	function test_invalid()
	{
		$this->assert_equal($this->plaintext, Utils::crypt('failure', $this->crypt) );
	}

	function test_valid()
	{
		$this->assert_equal($this->plaintext, Utils::crypt($this->plaintext, $this->crypt) );
	}

	function test_legacy()
	{
		$this->assert_equal($this->plaintext, sha1($this->plaintext) );
	}

}

CryptTest::run_one('CryptTest');

?>