<?php
namespace Habari;

class UtilsTest extends UnitTestCase
{
	function setup()
	{
		// For crypt tests
		$this->plaintext = 'Hello, World';
	}
/*
	function test_get_params()
	{
	}
*/
	function test_end_in_slash()
	{
		$str = "foo";
		$this->assert_equal( "foo/", Utils::end_in_slash($str) );

		$str = "foo/";
		$this->assert_equal( "foo/", Utils::end_in_slash($str) );
	}
/*
	function test_redirect()
	{
	}

	function test_atomtime()
	{
	}
*/
	function test_nonce()
	{
		$this->assert_equal( 12, strlen( Utils::nonce() ) );
		$this->assert_true( ( Utils::nonce() !== Utils::nonce() ) );
	} 
/*
	function test_wsse()
	{
	}
*/
	function test_stripslashes()
	{
		$str = '\\"this string\\"';
		$result = Utils::stripslashes( $str );
		$this->assert_equal('"this string"', $result );

		$arr = array(
			'\\"this string\\" is',
			'act\\"ually\\" an\\" array\\"',
			'of \\\'strings\\\'',
		);
		$result = Utils::stripslashes( $arr );
		$expected = array( '"this string" is', 'act"ually" an" array"', "of 'strings'" );
		$this->assert_equal($expected, $result);
	}
/*
	function test_addslashes()
	{
	}

	function test_de_amp()
	{
	}

	function test_revert_magic_quotes_gpc()
	{
	}

	function test_quote_spaced()
	{
	}

	function test_implode_quoted()
	{
	}
*/
	function test_placeholder_string()
	{
		$this->assert_equal( '?,?,?,?,?' , Utils::placeholder_string( 5 ), 'Should output as many question marks as requested' );
	}
/*
	function test_archive_pages()
	{
	}

	function test_map_array()
	{
	}

	function test_debug_reveal()
	{
	}

	function test_debug()
	{
	}

	function test_firedebug()
	{
	}

	function test_firebacktrace()
	{
	}
*/
	function test_crypt()
	{
		$crypt = Utils::crypt( $this->plaintext );
		$this->assert_true( strpos( $crypt, '{SSHA512}' ) === 0 );
		$this->assert_equal( 101, strlen($crypt) );

		// Test the hash-compare works
		$hash = "{SSHA512}p9F5CeA1xrB2ypnI6tl9B2ol/+Et7qObtZfUawRRNck5MxFQtnRkdfAgPwbUlGMf3GcTwvPcv1/fNAEtHxrcozekoMU=";

		$this->assert_true( Utils::crypt($this->plaintext, $hash) );
	}

	function test_sha1()
	{
		$crypt = Utils::sha1( $this->plaintext );
		$this->assert_equal( $crypt, '{SHA1}907d14fb3af2b0d4f18c2d46abe8aedce17367bd' );
	}

	function test_md5()
	{
		$crypt = Utils::md5( $this->plaintext );
		$this->assert_equal( $crypt, '{MD5}82bb413746aee42f89dea2b59614f9ef' );
	}
/*
	function test_ssha()
	{
	}

	function test_ssha512()
	{
	}

	function test_getdate()
	{
	}

	function test_locale_date()
	{
	}

	function test_slugify()
	{
	}

	function test_html_select()
	{
	}

	function test_html_checkboxes()
	{
	}

	function test_truncate()
	{
	}

	function test_php_check_syntax()
	{
	}

	function test_php_check_file_syntax()
	{
	}

	function test_glob()
	{
	}

	function test_human_size()
	{
	}

	function test_single_array()
	{
	}

	function test_mimetype()
	{
	}

	function test_trail()
	{
	}

	function test_mail()
	{
	}

	function test_random_password()
	{
	}

	function test_array_or()
	{
	}

	function test_ror()
	{
	}

	function test_check_request_method()
	{
	}

	function test_glob_to_regex()
	{
	}
*/
	function test_scheme_ports()
	{
		$this->assert_equal( 21, Utils::scheme_ports( 'ftp' ) );
		$this->assert_equal( 22, Utils::scheme_ports( 'ssh' ) );
		$this->assert_equal( 23, Utils::scheme_ports( 'telnet' ) );
		$this->assert_equal( 80, Utils::scheme_ports( 'http' ) );
		$this->assert_equal( 110, Utils::scheme_ports( 'pop3' ) );
		$this->assert_equal( 119, Utils::scheme_ports( 'news' ) );
		$this->assert_equal( 119, Utils::scheme_ports( 'nntp' ) );
		$this->assert_equal( 194, Utils::scheme_ports( 'irc' ) );
		$this->assert_equal( 220, Utils::scheme_ports( 'imap3' ) );
		$this->assert_equal( 443, Utils::scheme_ports( 'https' ) );
		$this->assert_equal( 563, Utils::scheme_ports( 'nntps' ) );
		$this->assert_equal( 993, Utils::scheme_ports( 'imaps' ) );
		$this->assert_equal( 995, Utils::scheme_ports( 'pop3s' ) );
		$this->assert_equal( array(
			'ftp' => 21,
			'ssh' => 22,
			'telnet' => 23,
			'http' => 80,
			'pop3' => 110,
			'nntp' => 119,
			'news' => 119,
			'irc' => 194,
			'imap3' => 220,
			'https' => 443,
			'nntps' => 563,
			'imaps' => 993,
			'pop3s' => 995,
		), Utils::scheme_ports() );
	}
/*
	function test_is_traversable()
	{
	}

	function test_get_ip()
	{
	}

	function test_htmlspecialchars()
	{
	}

	function test_regexdelim()
	{
	}
*/
}
?>
