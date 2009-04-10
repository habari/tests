<?php


class system_classes_VocabularyTest extends PHPUnit_Framework_TestCase
{

	public function testConstruct()
	{
		$b = new Bitmask(array('hierarchical', 'unique', 'required', 'free'));
		$b->hierarchical = true;
		$v = new Vocabulary('test', 'test vocab', $b);
		
		$this->assertType('Vocabulary', $v);
		$this->assertEquals($v->name, 'test');
		$this->assertEquals($v->description, 'test vocab');
		$this->assertEquals(1, $v->required);
	}
}
?>