<?php

include '../bootstrap.php';

class FormatTest extends UnitTestCase
{
	public function test_simple_autop()
	{
		$this->assert_equal( '<p>foo</p>', Format::autop( 'foo' ) );
	}

	public function test_autop()
	{
		$data = $this->autop_data_provider();
		foreach( $data as $index => $datum ) {
			$result = ( trim( $datum['want'] ) === trim( Format::autop( $datum['in'] ) ) ? true : false );

			if( ! $result ) {
				$this->output( ( sprintf( '<h2>Test %s</h2><strong>Input:</strong><textarea>%s</textarea><strong>Expected:</strong><textarea>%s</textarea><strong>Got:</strong><br><textarea>%s</textarea>',
					$index,
					nl2br( Utils::htmlspecialchars( $datum['in'] ) ),
					nl2br( Utils::htmlspecialchars( $datum['want'] ) ),
					nl2br( Utils::htmlspecialchars( Format::autop( $datum['in'] ) ) )
				) ) );
			}
			$this->assert_true( $result, sprintf("Output does not match desired output in %s", $index) );
		}
	}

	public function autop_data_provider()
	{
		$autop_data_path = dirname(dirname( __FILE__ )) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'autop';
		$data = array();
		for ( $n = 1; $n <= 99; $n++ ) {
			$suff = str_pad( $n, 2, '0', STR_PAD_LEFT );
			$inputFile = $autop_data_path . '/text' . $suff . '.input.txt';
			$wantedFile = $autop_data_path . '/text' . $suff . '.wanted.txt';
			if ( !is_readable( $inputFile ) ) {
				break; // no need to keep looping
			}
			$data[basename($wantedFile)] = array( 'in' => file_get_contents( $inputFile ), 'want' => file_get_contents( $wantedFile ) );
		}
		return $data;
		
	}
}

FormatTest::run_one( 'FormatTest' );

?>
