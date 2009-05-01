<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

class system_classes_TaxonomyTest extends PHPUnit_Framework_TestCase
{
	private $vocab_name;
	private $vocab_desc;
	private $vocab_rename;

	private $term_name;

	public function setup()
	{
		$this->vocab_name = 'test';
		$this->vocab_desc = 'test vocabulary';
		$this->vocab_rename = 'test_rename';

		$this->term_name = 'Test Term';
	}

	/* Vocabulary tests */
	public function test_construct_vocabulary()
	{
		// Features are 'hierarchical', 'unique', 'required', 'free'
		$params = array(
			'name' => $this->vocab_name,
			'description' => $this->vocab_desc,
			'feature_mask' => Vocabulary::feature_mask(true, false, false, false)
		);
		$v = new Vocabulary($params);

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

	public function test_insert_vocabulary()
	{
		$vocab_count = count(Vocabulary::names());
		$params = array(
			'name' => $this->vocab_name,
			'description' => $this->vocab_desc,
			'feature_mask' => Vocabulary::feature_mask(true, false, false, false)
		);
		$v = new Vocabulary($params);
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

		$this->assertTrue(in_array($this->vocab_rename, Vocabulary::names()), 'New vocabulary name should be in list of vocabulary names');
		$this->assertFalse(in_array($this->vocab_name, Vocabulary::names()), 'Old vocabulary name should not be in list of vocabulary names');
		$this->assertEquals($vocab_count, count(Vocabulary::names()), 'Number of vocabularies should not change on rename');
	}

	public function test_delete_vocabulary()
	{
		$vocab_count = count(Vocabulary::names());
		$v = Vocabulary::get($this->vocab_rename);
		$v->delete();

		$this->assertEquals($vocab_count - 1, count(Vocabulary::names()), 'Number of vocabularies should decrease by one');
		$this->assertFalse(in_array($this->vocab_name, Vocabulary::names()), 'Deleted vocabulary name should not be in list of vocabulary names');
	}

	/* Term tests */
	public function test_construct_term()
	{
		$params = array(
			'term' => Utils::slugify($this->term_name),
			'term_display' => $this->term_name
		);
		$t = new Term($params);

		$this->assertType('Term', $t);
		$this->assertEquals($t->term, Utils::slugify($this->term_name));
		$this->assertEquals($t->term_display, $this->term_name);
	}

	public function teardown()
	{
	}
	
}
?>
