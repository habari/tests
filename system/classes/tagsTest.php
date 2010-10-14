<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

class system_classes_TagsTest extends PHPUnit_Framework_TestCase
{
	public function setup()
	{
	}

	public function teardown()
	{
	}

	public function test_construct_tags()
	{
		// Construct tags from tag string
		$tags = new Tags('one, two, three');

		$this->assertType('Tags', $tags);
		$this->assertEquals(count($tags), 3);

		// Construct tags from tag array
		$tags = new Tags(array('one', 'two', 'three'));

		$this->assertType('Tags', $tags);
		$this->assertEquals(count($tags), 3);
	}
}
?>
