<?php

	/**
	 * A unit test to verify that turning off the MultiByte library works.
	 * 
	 * Kind of a sanity check to make sure that the inverse of all the MultiByte tests is also true.
	 */
	class MultiByteTest extends UnitTestCase {
		
		// our test strings, in numeric entities to prevent editor or os butchering
		private $test_strings = array(
			'lowercase' => '&#1087;&#1088;&#1080;&#1074;&#1077;&#1090;',	// привет
			'ucfirst' => '&#1055;&#1088;&#1080;&#1074;&#1077;&#1090;',		// Привет
			'uppercase' => '&#1055;&#1056;&#1048;&#1042;&#1045;&#1058;',	// ПРИВЕТ
			'international' => 'n2&#226;7t I&#241;t&#235;rn&#226;ti&#244;n&#224;liz&#230;ti&#248;n l13iz&#230;42ti&#248;n',	// n2â7t Iñtërnâtiônàlizætiøn l13izæ42tiøn
			'international_substr_1_3' => '2&#226;7',	// 2â7
			'international_substr_5' => ' I&#241;t&#235;rn&#226;ti&#244;n&#224;liz&#230;ti&#248;n l13iz&#230;42ti&#248;n',	// note the leading space -  Iñtërnâtiônàlizætiøn l13izæ42tiøn
			'strpos' => '&#1080;',	// и
			'strpos2' => '&#226;', // â
			'lowercase_sentence' => '&#1082;&#1086;&#1088;&#1086;&#1074;&#1099; &#1080;&#1076;&#1091;&#1090; &#1084;&#1091;',	// коровы идут му
			'ucwords_sentence' => '&#1050;&#1086;&#1088;&#1086;&#1074;&#1099; &#1048;&#1076;&#1091;&#1090; &#1052;&#1091;',	// Коровы Идут Му
			'jis' => '&#65410;&#65381;',	// ¥ｵｷｻ
		);
		
		// stores the library in use so we can restore it in tearDown()
		private $old_library;
	
		/**
		 * Setup: Decode our test strings aheads of time and disable the MultiByte library.
		 */
		protected function setup ( ) {
	
			$convmap = array( 0x0080, 0xffff, 0, 0xffff );
	
			foreach ( $this->test_strings as $key => $value ) {
	
				$this->test_strings[ $key ] = mb_decode_numericentity( $value, $convmap, 'utf-8' );
	
			}
	
		}
		
		/**
		 * Teardown: Restore the previous MutliByte library so later tests don't fail.
		 */
		protected function teardown ( ) {
	
			// restore the multibyte library in use so other tests don't fail
			MultiByte::library( $this->old_library );
	
		}
		
		public function test_hab_encoding ( ) {
			
			// see what the current value is
			$original = MultiByte::hab_encoding();
			
			// make sure it equals the static value
			$this->assert_equal( $original, MultiByte::$hab_enc );
			
			// change it, saving the old
			$old = MultiByte::hab_encoding( 'foo' );
			
			// make sure the old value is equal to the original value - it should return the previous when we set it
			$this->assert_equal( $old, $original );
			
			// set the original value back
			MultiByte::hab_encoding( $original );
			
			// make sure that the original really did get set - really a duplicate of the prior assertion, but you know...
			$this->assert_equal( $original, MultiByte::hab_encoding() );
			
		}
		
		public function test_substr ( ) {
			
			// test that a substring with a starting and ending value works correctly
			$this->assert_equal( MultiByte::substr( $this->test_strings['international'], 1, 3 ), $this->test_strings['international_substr_1_3'] );
	
			// test that a substring with only a starting value works correctly
			$this->assert_equal( MultiByte::substr( $this->test_strings['international'], 5 ), $this->test_strings['international_substr_5'] );
			
		}
		
		public function test_strlen ( ) {
			
			$this->assert_equal( MultiByte::strlen( $this->test_strings['lowercase'] ), 6 );	// it's 12 bytes long, each character is 2-bytes wide
			$this->assert_equal( MultiByte::strlen( 'abcd' ), 4 );
			
		}
		
		public function test_strtolower ( ) {
			
			$this->assert_equal( MultiByte::strtolower( $this->test_strings['ucfirst'] ), $this->test_strings['lowercase'] );
			
		}
		
		public function test_strtoupper ( ) {
			
			$this->assert_equal( MultiByte::strtoupper( $this->test_strings['lowercase'] ), $this->test_strings['uppercase'] );
			
		}
		
		public function test_ucfirst ( ) {
			
			$this->assert_equal( MultiByte::ucfirst( $this->test_strings['lowercase'] ), $this->test_strings['ucfirst'] );
			$this->assert_equal( MultiByte::ucfirst( 'abcd' ), 'Abcd' );
			
		}
		
		public function test_lcfirst ( ) {
			
			// test the emultated native method (lcfirst is only in 5.3+)
			$this->assert_equal( MultiByte::lcfirst( $this->test_strings['ucfirst'] ), $this->test_strings['lowercase'] );
			$this->assert_equal( MultiByte::lcfirst( 'Abcd' ), 'abcd' );
			
		}
		
		public function test_strpos ( ) {
			
			// there should probably be a better effort to come up with a meaningful test
	
			// make sure a simple strpos works
			$this->assert_equal( MultiByte::strpos( $this->test_strings['lowercase'], $this->test_strings['strpos'] ), 2 );
			$this->assert_equal( MultiByte::strpos( $this->test_strings['international'], $this->test_strings['strpos2'] ), 2 );	// this one works because the characters before it are native!
	
			// make sure a strpos with an offset works
			$this->assert_equal( MultiByte::strpos( $this->test_strings['lowercase'], $this->test_strings['strpos'], 1 ), 2 );
			$this->assert_equal( MultiByte::strpos( $this->test_strings['international'], $this->test_strings['strpos2'], 4 ), 12 );
	
			// make sure a non-esistant strpos works - the character does not exist after the offset
			$this->assert_equal( MultiByte::strpos( $this->test_strings['lowercase'], $this->test_strings['strpos'], 3 ), false );
			$this->assert_equal( MultiByte::strpos( $this->test_strings['international'], $this->test_strings['strpos2'], 14 ), false );
	
			// and perform a single test with an ascii string for code coverage - this one should still work fine!
			$this->assert_equal( MultiByte::strpos( 'abcd', 'c', null, 'ascii' ), 2 );
			
		}
		
		public function test_strrpos ( ) {
			
			$this->assert_equal( MultiByte::strrpos( $this->test_strings['lowercase'], $this->test_strings['strpos'] ), 2 );
			$this->assert_equal( MultiByte::strrpos( $this->test_strings['international'], 'n' ), 38 );		// the string is 48 BYTES long, only 38 characters long
			
		}
		
		public function test_ucwords ( ) {
			
			$this->assert_equal( MultiByte::ucwords( $this->test_strings['lowercase_sentence'] ), $this->test_strings['ucwords_sentence'] );
			
		}
		
		public function test_detect_encoding ( ) {
			
			$this->assert_equal( MultiByte::detect_encoding( 'foo' ), 'ASCII' );
			$this->assert_equal( MultiByte::detect_encoding( $this->test_strings['jis'] ), 'JIS' );
			//echo MultiByte::detect_encoding( '' ); die();
			
		}
		
	}
?>
