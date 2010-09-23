<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

class system_classes_FormatTest extends PHPUnit_Framework_TestCase
{

	public function testSimpleAutop()
	{
		$this->assertEquals('<p>foo</p>', Format::autop('foo'));
	}

	public function autopDataProvider()
	{
		$autopTestDataPath = dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'autop';
		$data = array();
		for ($n=1; $n<=99; $n++) {
			$suff = str_pad($n, 2, '0', STR_PAD_LEFT);
			$inputFile = $autopTestDataPath . '/text' . $suff . '.input.txt';
			$wantedFile = $autopTestDataPath . '/text' . $suff . '.wanted.txt';
			if (!is_readable($inputFile)) {
				break; // no need to keep looping
			}
			$data[] = array(file_get_contents($inputFile), file_get_contents($wantedFile));
		}
		return $data;
	}

	/**
	 * @dataProvider autopDataProvider
	 */
	public function testAutop($in, $out)
	{
		$this->assertEquals(trim($out), trim(Format::autop($in)));
	}
}
?>