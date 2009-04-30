<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

class system_classes_TaxonomyTest extends PHPUnit_Framework_TestCase
{
	private $vocab_name;
	private $vocab_desc;
	private $vocab_rename;

	public function setup()
	{
		$this->vocab_name = 'test';
		$this->vocab_desc = 'test vocabulary';
		$this->vocab_rename = 'test_rename';
	}

	/* Vocabulary tests */
	public function test_construct_vocabulary()
	{
		// Features are 'hierarchical', 'unique', 'required', 'free'
		$m = Vocabulary::feature_mask(true, false, false, false);
		$v = new Vocabulary($this->vocab_name, $this->vocab_desc, $m);

		$this->assertType('Vocabulary', $v);
		$this->assertEquals($v->name, $this->vocab_name);
		$this->assertEquals($v->description, $this->vocab_desc);
		$this->assertEquals(true, $v->hierarchical);
		$this->assertEquals(false, $v->free);
	}

	public function test_get_names()
	{
		$this->assertTrue(is_array(Vocabulary::names()));
	}

	public function test_add_vocabulary()
	{
		$vocab_count = count(Vocabulary::names());
		$v = new Vocabulary($this->vocab_name, $this->vocab_desc, Vocabulary::feature_mask(true, false, false, false));
		$v->insert();

		$this->assertEquals($vocab_count + 1, count(Vocabulary::names()), 'Count of names should increase by one');
		$this->assertTrue(in_array($this->vocab_name, Vocabulary::names()), 'Test vocabulary name should be in the list of names');
	}

	public function test_get_vocabulary()
	{
		$v = Vocabulary::get($this->vocab_name);

		$this->assertType('Vocabulary', $v);
		$this->assertEquals($v->name, $this->vocab_name);
		$this->assertEquals($v->description, $this->vocab_desc);
	}

	public function test_rename_vocabulary()
	{
		$vocab_count = count(Vocabulary::names());
		Vocabulary::rename($this->vocab_name, $this->vocab_rename);

		$this->assertTrue(in_array($this->vocab_rename, Vocabulary::names()));
		$this->assertFalse(in_array($this->vocab_name, Vocabulary::names()));
		$this->assertEquals($vocab_count, count(Vocabulary::names()));
	}

	public function test_delete_vocabulary()
	{
		$vocab_count = count(Vocabulary::names());
		$v = Vocabulary::get($this->vocab_name);
		$v->delete();

		$this->assertEquals($vocab_count - 1, count(Vocabulary::names()));
		$this->assertFalse(in_array($this->vocab_name, Vocabulary::names()));
	}

	/* TODO write term tests */

	public function teardown()
	{
	}
	
}
?>
