<?php

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
				$this->output( ( sprintf( '<h2>Test %s</h2><br><strong>Input:</strong><br><textarea>%s</textarea><br><strong>Expected:</strong><br><textarea>%s</textarea><br><strong>Got:</strong><br><textarea>%s</textarea>',
					$index,
					nl2br( Utils::htmlspecialchars( $datum['in'] ) ),
					nl2br( Utils::htmlspecialchars( $datum['want'] ) ),
					nl2br( Utils::htmlspecialchars( Format::autop( $datum['in'] ) ) )
				) ) );
			}
			$this->assert_true( $result, sprintf("Output does not match desired output in %s", $index) );
		}
	}

	public function test_format_priority()
	{
		Format::apply(function($v){return $v . '7';}, 'test_filter_7');
		Format::apply(function($v){return $v . '8';}, 'test_filter');
		$result = Plugins::filter('test_filter', 'test');
		$this->assert_equal('test78', $result);

		Format::apply(function($v, $c){return $v . '7' . $c;}, 'test_filter2_7', 'a');
		Format::apply(function($v, $c){return $v . '8' . $c;}, 'test_filter2', 'b');
		$result = Plugins::filter('test_filter2', 'test');
		$this->assert_equal('test7a8b', $result);

		Format::apply_with_hook_params(function($v, $h, $c){return $v . '7' . $h . $c;}, 'test_filter3_7', 'a');
		Format::apply_with_hook_params(function($v, $h, $c){return $v . '8' . $h . $c;}, 'test_filter3', 'b');
		$result = Plugins::filter('test_filter3', 'test', 'h');
		$this->assert_equal('test7ha8hb', $result);
	}

	public function test_term_tree()
	{
		// create a vocabulary.
		if( Vocabulary::get( 'format_test' ) ) {
			Vocabulary::get( 'format_test' )->delete();
		}

		$v = new Vocabulary( array(
			'name' => 'format_test',
			'description' => "Vocabulary used for testing Format::term_tree()",
			'features' => array( 'hierarchical' )
		) );

		// nest some terms.

		/**
		 * A
		 * | \
		 * B  C
		 * |  | \
		 * D  E  F 		// E has no descendants!
		 * | \   | \
		 * G  H  I  J	// G has no descendants!
		 *   / \  \   \
		 *  K   L  M   N
		 **/

		$a = $v->add_term( "A" );
		$b = $v->add_term( "B", $v->get_term( $a->id ) );
		$c = $v->add_term( "C", $v->get_term( $a->id ) );
		$d = $v->add_term( "D", $v->get_term( $b->id ) );
		$e = $v->add_term( "E", $v->get_term( $c->id ) );
		$f = $v->add_term( "F", $v->get_term( $c->id ) );
 		$g = $v->add_term( "G", $v->get_term( $d->id ) );
		$h = $v->add_term( "H", $v->get_term( $d->id ) );
		$i = $v->add_term( "I", $v->get_term( $f->id ) );
		$j = $v->add_term( "J", $v->get_term( $f->id ) );
		$k = $v->add_term( "K", $v->get_term( $h->id ) );
		$l = $v->add_term( "L", $v->get_term( $h->id ) );
		$m = $v->add_term( "M", $v->get_term( $i->id ) );
		$n = $v->add_term( "N", $v->get_term( $j->id ) );

		$this->output( Format::term_tree( $v->get_tree(), 'hi' ) );
		// define expected output.
		// check actual output.

		// clean up
		$v->delete();
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
?>
