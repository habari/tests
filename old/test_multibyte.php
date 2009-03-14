<?php

/**
 * Unit Test for the MultiByte class.
 */

include 'bootstrap.php';

class MultiByteTest extends UnitTestCase
{
//	static $test_str = 'Iñtërnâtiônàlizætiøn';
	static $test_str = 'n2â7t Iñtërnâtiônàlizætiøn l13izæ42tiøn';
	static $test_str_slug = 'n2â7t Iñtërnâtiônàlizætiøn l13izæ42tiøn';
	static $test_str_slug_asc = 'fasfgreig rr23 34vfg9';
//	static $test_str = 'Track';
//	static $test_str = '汉字测试';
//	static $test_str = 'بما في ذلك الكلمات المستخدمة في صفحات التيكت والويكي';

	function test_hab_encoding()
	{
		$this->assert_equal( MultiByte::hab_encoding(), 'UTF-8' );

		printf( "MultiByte internal encoding: %s <br>", MultiByte::hab_encoding() );
		printf( "mbstring internal encoding: %s <br>", mb_internal_encoding() );
	}

	function test_convert_encoding()
	{
		printf( "Test string: %s <br>", self::$test_str );

		$this->assert_equal( MultiByte::convert_encoding( self::$test_str ), mb_convert_encoding( self::$test_str, 'UTF-8', mb_detect_encoding( self::$test_str ) ) );

		printf( "After being converted to MultiByte's encoding: %s <br>", MultiByte::convert_encoding( self::$test_str ) );
		printf( "After being converted  by mbstring to MultiByte's encoding without detecting encoding: %s <br>", mb_convert_encoding( self::$test_str, MultiByte::hab_encoding() ) );
		printf( "After being converted  by mbstring to MultiByte's encoding after detecting encoding: %s <br>", mb_convert_encoding( self::$test_str, MultiByte::hab_encoding(), mb_detect_encoding( self::$test_str ) ) );

		$this->assert_equal( MultiByte::convert_encoding( self::$test_str, 'ASCII' ), mb_convert_encoding( self::$test_str, 'ASCII', mb_detect_encoding( self::$test_str ) ) );

		printf( "MultiByte convert to ASCII: %s <br>", MultiByte::convert_encoding( self::$test_str, 'ASCII' ) );
		printf( "mbstring convert to ASCII without detecting encoding: %s <br>", mb_convert_encoding( self::$test_str, 'ASCII' ) );
		printf( "mbstring convert to ASCII after detecting encoding: %s <br>", mb_convert_encoding( self::$test_str, 'ASCII', mb_detect_encoding( self::$test_str) ) );
	}

	function test_detect_encoding()
	{
		$this->assert_equal( MultiByte::detect_encoding( self::$test_str ), mb_detect_encoding( self::$test_str ) );
		printf( "MultiByte detected encoding: %s <br>", MultiByte::detect_encoding( self::$test_str ) );
		printf( "mbstring detect order: %s <br>",  implode( ', ', mb_detect_order() ) );
		printf( "mbstring detected encoding: %s <br>", mb_detect_encoding( self::$test_str ) );
	}

	function test_substr()
	{
		printf( "Test string: %s <br>", self::$test_str );
		printf("Habari encoding: %s <br>", MultiByte::hab_encoding() );
		printf( "mb_internal_encoding: %s <br>", mb_internal_encoding() );
		printf( "MultiByte detected encoding of test string: %s <br>", MultiByte::detect_encoding( self::$test_str ) );
		printf( "mbstring detected encoding of test string: %s <br>", mb_detect_encoding( self::$test_str ) );

		$this->assert_equal( MultiByte::substr( self::$test_str, 1, 3 ), mb_substr( self::$test_str, 1, 3 ) );
		$this->assert_equal( MultiByte::substr( self::$test_str, 1, 3 ), mb_substr( self::$test_str, 1, 3, mb_detect_encoding( self::$test_str ) ) );
		$this->assert_equal( MultiByte::substr( self::$test_str, 5 ), mb_substr( self::$test_str, 5 ) );

		printf( " MultiByte substring (begin-1 end-3): %s <br>", MultiByte::substr( self::$test_str, 1, 3 ) );
		printf( " MultiByte substring 2 (begin-5 end-null): %s <br>", MultiByte::substr( self::$test_str, 5 ) );
		printf( " mbstring substring without encoding detected (begin-1 end-3): %s <br>", mb_substr( self::$test_str, 1, 3 ) );
		printf( " mbstring substring with encoding detected (begin-1 end-3): %s <br>", mb_substr( self::$test_str, 1, 3, mb_detect_encoding( self::$test_str ) ) );
		printf( " mbstring substring 2 without encoding detected(begin-5 end-null): %s <br>", mb_substr( self::$test_str, 5 ) );
	}

	function test_strlen()
	{
		$this->assert_equal( MultiByte::strlen( self::$test_str ), mb_strlen( self::$test_str, mb_detect_encoding( self::$test_str ) ) );

		printf( "Test string: %s <br>", self::$test_str );
		printf( "MultiByte string length: %d <br>", MultiByte::strlen( self::$test_str ) );
		printf( "mbstring string length without detecting encoding: %d <br>", mb_strlen( self::$test_str ) );
		printf( "mbstring string length with detecting encoding: %d <br>", mb_strlen( self::$test_str, mb_detect_encoding( self::$test_str ) ) );
	}

	function test_strtoupper()
	{
		$this->assert_equal( MultiByte::strtoupper( self::$test_str ), mb_strtoupper( mb_convert_encoding(self::$test_str, 'UTF-8', mb_detect_encoding( self::$test_str ) ), 'UTF-8' ) );

		printf( "Test string: %s <br>", self::$test_str );
		printf( "MultiByte strtoupper: %s <br>", MultiByte::strtoupper( self::$test_str ) );
		printf( "mbstring strtoupper without detecting encoding: %s <br>", mb_strtoupper( self::$test_str ) );
		printf( "mstring strtoupper with detecting encoding: %s <br>", mb_strtoupper( self::$test_str, mb_detect_encoding( self::$test_str ) ) );
	}

	function test_strtolower()
	{
		$this->assert_equal( MultiByte::strtolower( self::$test_str ), mb_strtolower( mb_convert_encoding(self::$test_str, 'UTF-8', mb_detect_encoding( self::$test_str ) ), 'UTF-8' ) );

		printf( "Test string: %s <br>", self::$test_str );
		printf( "MultiByte strtolower: %s <br>", MultiByte::strtolower( self::$test_str ) );
		printf( "mbstring strtolower without detecting encoding: %s <br>", mb_strtolower( self::$test_str ) );
		printf( "mstring strtolower with detecting encoding: %s <br><br>", mb_strtolower( self::$test_str, mb_detect_encoding( self::$test_str ) ) );
	}
/*
	function test_slugify()
	{
		print_r( Utils::slugify( self::$test_str_slug ) );
		echo "\r\n : ";
		print_r( urlencode( Utils::slugify( self::$test_str_slug ) ) );
		echo "\r\n : ";
		print_r( urldecode( urlencode( Utils::slugify( self::$test_str_slug ) ) ) );
		echo "\r\n : ";
		print_r( Utils::slugify( self::$test_str_slug_asc ) );
		echo "\r\n : ";
		print_r( urlencode( Utils::slugify( self::$test_str_slug_asc ) ) );
	}
*/
}

MultiByteTest::run_one('MultiByteTest');
?>
