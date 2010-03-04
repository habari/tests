<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

class system_classes_ColorUtilsTest extends PHPUnit_Framework_TestCase
{

	function setup()
	{
		$this->green= ColorUtils::rgb_rgbarr( 127, 255, 64 );
		$this->green_hex= ColorUtils::rgb_hex( $this->green );
		$this->green_hsv= ColorUtils::rgb_hsv( $this->green );
		$this->green_from_hsv= ColorUtils::hsv_rgb( $this->green_hsv );
		$this->orange= ColorUtils::hex_rgb( '#ed691f' );
		$this->cyan= ColorUtils::hex_rgb( '8bc' );
		$this->red= ColorUtils::hex_rgb( 'f0' );
		$this->invalid = ColorUtils::hex_rgb( 'violet' );
}
	function test_RGB_to_Array()
	{
		$this->assertEquals($this->green, array ( 'r' => 127, 'g' => 255, 'b' => 64, ) );
	}

	function test_RGB_to_HEX()
	{
		$this->assertEquals($this->green_hex, '7fff40');
	}

	function test_RGV_to_HSV()
	{
		$this->assertEquals($this->green_hsv, array ( 'h' => 100, 's' => 75, 'v' => 255, ) );
	}

	function test_HSV_back_to_RGB()
	{
		//(conversion introduces rounding errors)
		$this->assertEquals($this->green_from_hsv, array ( 'r' => 128, 'g' => 255, 'b' => 64, ) );
	}

	function test_HEX_to_RGB()
	{
		$this->assertEquals($this->orange, array ( 'r' => 237, 'g' => 105, 'b' => 31, ) );
		$this->assertEquals($this->cyan, array ( 'r' => 136, 'g' => 187, 'b' => 204, ) );
		$this->assertEquals($this->red, array ( 'r' => 240, 'g' => 0, 'b' => 0, ) );
	}
	function test_HEX_to_RGB_exception()
	{
	    $x=ColorUtils::hex_rgb( 'violet' );
	    $this->assertEquals( 'Not a valid hex color.', $x->getMessage() );
	}
}
?>