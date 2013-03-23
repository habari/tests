<?php
namespace Habari;

class ColorUtilsTest extends UnitTestCase
{
	function module_setup()
	{
		$this->green = ColorUtils::rgb_rgbarr( 127, 255, 64 );
		$this->green_hex = ColorUtils::rgb_hex( $this->green );
		$this->green_hsv = ColorUtils::rgb_hsv( $this->green );
		$this->green_from_hsv = ColorUtils::hsv_rgb( $this->green_hsv );
		$this->orange = ColorUtils::hex_rgb( '#ed691f' );
		$this->cyan = ColorUtils::hex_rgb( '8bc' );
		$this->red = ColorUtils::hex_rgb( 'f0' );
	}

	function test_RGB_to_Array()
	{
		$this->assert_equal( $this->green, array ( 'r' => 127, 'g' => 255, 'b' => 64, ) );
	}

	function test_RGB_to_HEX()
	{
		$this->assert_equal( $this->green_hex, '7fff40');
	}

	function test_RGB_to_HSV()
	{
		$this->output($this->green_hsv);
		$this->assert_equal( $this->green_hsv, array ( 'h' => 90, 's' => 191, 'v' => 255, ) );
	}

	function test_HSV_back_to_RGB()
	{
		//(conversion introduces rounding errors)
		$this->assert_equal( $this->green_from_hsv, array ( 'r' => 128, 'g' => 255, 'b' => 64, ) );
	}

	function test_HEX_to_RGB()
	{
		$this->assert_equal( $this->orange, array ( 'r' => 237, 'g' => 105, 'b' => 31, ) );
		$this->assert_equal( $this->cyan, array ( 'r' => 136, 'g' => 187, 'b' => 204, ) );
		$this->assert_equal( $this->red, array ( 'r' => 240, 'g' => 0, 'b' => 0, ) );
	}

	function test_HEX_to_RGB_exception()
	{
		$invalid_hex_color = ColorUtils::hex_rgb( 'violet' );
		$this->assert_equal( 'Not a valid hex color.', $invalid_hex_color->getMessage() );
		$invalid_color_format = ColorUtils::hex_rgb( '1234567' );
		$this->assert_equal( 'Not a valid color format.', $invalid_color_format->getMessage() );
	}
}
?>
