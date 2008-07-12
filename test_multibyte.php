<?php

/**
 * Test for the MultiByte class.
 */

include 'bootstrap.php';

class MultiByteTest extends UnitTestCase
{
	static $test_str = 'Iñtërnâtiônàlizætiøn';
	static $test_str_slug = 'n2â7t Iñtërnâtiônàlizætiøn l13izæ42tiøn';
	static $test_str_slug_asc = 'fasfgreig rr23 34vfg9';
//	static $test_str = 'Track';
//	static $test_str = '汉字测试';
//	static $test_str = 'بما في ذلك الكلمات المستخدمة في صفحات التيكت والويكي';

	function test_hab_encoding()
	{
		$this->assert_equal( MultiByte::hab_encoding(), 'UTF-8' );
		print_r(MultiByte::hab_encoding() );
		print_r(implode(', ', mb_detect_order()));
	}

	function test_convert_encoding()
	{
		$this->assert_equal( MultiByte::convert_encoding( self::$test_str ), mb_convert_encoding( self::$test_str, 'UTF-8', mb_detect_encoding( self::$test_str ) ) );
		print_r( MultiByte::convert_encoding( self::$test_str ) . ' : ' . mb_convert_encoding( self::$test_str, MultiByte::hab_encoding(), MultiByte::detect_encoding( self::$test_str) ) );

		$this->assert_equal( MultiByte::convert_encoding( self::$test_str, 'ASCII' ), mb_convert_encoding( self::$test_str, 'ASCII', mb_detect_encoding( self::$test_str ) ) );
		print_r( MultiByte::convert_encoding( self::$test_str, 'ASCII' ) . ' : ' . mb_convert_encoding( self::$test_str, 'ASCII', MultiByte::detect_encoding( self::$test_str) ) );
	}

	function test_detect_encoding()
	{
		$this->assert_equal( MultiByte::detect_encoding( self::$test_str ), mb_detect_encoding( self::$test_str ) );
		print_r( MultiByte::detect_encoding( self::$test_str ) . ' : ');
		print_r( mb_detect_encoding( self::$test_str ) );
	}

	function test_substr()
	{
		print_r( MultiByte::hab_encoding() . ' : ');
		print_r( mb_internal_encoding() . ' : ');
		print_r( MultiByte::detect_encoding( self::$test_str ) . ' : ' );
		$this->assert_equal( MultiByte::substr( self::$test_str, 1, 3 ), mb_substr( MultiByte::convert_encoding( self::$test_str ), 1, 3, MultiByte::hab_encoding() ) );
		print_r( MultiByte::substr( self::$test_str, 1, 3 ) . ' : ' );
		print_r( mb_substr( MultiByte::convert_encoding( self::$test_str ), 1, 3, MultiByte::hab_encoding() ) );
	}

	function test_strlen()
	{
		$this->assert_equal( MultiByte::strlen( self::$test_str ), mb_strlen( self::$test_str, mb_detect_encoding( self::$test_str ) ) );
		print_r( MultiByte::convert_encoding( self::$test_str ) . ' : ' . mb_detect_encoding( self::$test_str ) . ' : ' . MultiByte::detect_encoding( self::$test_str ) . ' : ' . MultiByte::strlen( self::$test_str ) . ' : ' . mb_strlen( self::$test_str, mb_detect_encoding( self::$test_str ) ) );
	}

	function test_strtoupper()
	{
		$this->assert_equal( MultiByte::strtoupper( self::$test_str ), mb_strtoupper( mb_convert_encoding(self::$test_str, 'UTF-8', mb_detect_encoding( self::$test_str ) ), 'UTF-8' ) );
		print_r( MultiByte::strtoupper( self::$test_str ) . ' : ' . mb_strtoupper( mb_convert_encoding(self::$test_str, 'UTF-8', mb_detect_encoding( self::$test_str ) ), 'UTF-8' ) );
	}

	function test_strtolower()
	{
		$this->assert_equal( MultiByte::strtolower( self::$test_str ), mb_strtolower( mb_convert_encoding(self::$test_str, 'UTF-8', mb_detect_encoding( self::$test_str ) ), 'UTF-8' ) );
		print_r( MultiByte::strtolower( self::$test_str ) . ' : ' . mb_strtolower( mb_convert_encoding(self::$test_str, 'UTF-8', mb_detect_encoding( self::$test_str ) ), 'UTF-8' ) );
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
