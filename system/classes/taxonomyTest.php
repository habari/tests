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
			'features' => array('hierarchical')
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
			'features' => array('hierarchical')
		);
		$v = new Vocabulary($params);
		$v->insert();

		$this->assertEquals($vocab_count + 1, count(Vocabulary::names()), 'Count of names should increase by one');
		$this->assertTrue(in_array($this->vocab_name, Vocabulary::names()), 'Test vocabulary name should be in the list of names');

		// Clean up
		// Delete the vocabulary
		try {
			$v->delete();
		}
		catch (Exception $e) {
			echo 'Caught exception: ',$e->getMessage(), "\n";
		}
	}

	public function test_get_vocabulary()
	{
		// Set up
		// Create and insert a vocabulary
		$params = array(
			'name' => $this->vocab_name,
			'description' => $this->vocab_desc,
			'features' => array('hierarchical')
		);
		$u = new Vocabulary($params);
		$u->insert();

		// Retrieve the vocabulary
		$v = Vocabulary::get($this->vocab_name);

		$this->assertType('Vocabulary', $v);
		$this->assertEquals($v->name, $this->vocab_name);
		$this->assertEquals($v->description, $this->vocab_desc);

		// Clean up
		// Delete the vocabulary
		try {
			$v->delete();
		}
		catch (Exception $e) {
			echo 'Caught exception: ',$e->getMessage(), "\n";
		}
	}

	public function test_rename_vocabulary()
	{
		// Set up
		// Create and insert a vocabulary
		$params = array(
			'name' => $this->vocab_name,
			'description' => $this->vocab_desc,
			'features' => array('hierarchical')
		);
		$v = new Vocabulary($params);
		$v->insert();

		// Rename vocabulary
		$vocab_count = count(Vocabulary::names());
		Vocabulary::rename($this->vocab_name, $this->vocab_rename);

		$this->assertTrue(in_array($this->vocab_rename, Vocabulary::names()), 'New vocabulary name should be in list of vocabulary names');
		$this->assertFalse(in_array($this->vocab_name, Vocabulary::names()), 'Old vocabulary name should not be in list of vocabulary names');
		$this->assertEquals($vocab_count, count(Vocabulary::names()), 'Number of vocabularies should not change on rename');

		// Clean up
		// Delete the vocabulary
		try {
			$v->delete();
		}
		catch (Exception $e) {
			echo 'Caught exception: ',$e->getMessage(), "\n";
		}
	}

	public function test_delete_vocabulary()
	{
		// Set up
		// Create and insert a vocabulary
		$params = array(
			'name' => $this->vocab_name,
			'description' => $this->vocab_desc,
			'features' => array('hierarchical')
		);
		$v = new Vocabulary($params);
		$v->insert();

		// Count the number of vocabularies before deletion
		$vocab_count = count(Vocabulary::names());

		// Retrieve and delete vocabulary
		$v = Vocabulary::get($this->vocab_name);
		try {
			$v->delete();
		}
		catch (Exception $e) {
			echo 'Caught exception: ',$e->getMessage(), "\n";
		}

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

	public function test_add_term()
	{
		if( Vocabulary::get( 'numbers') )
		{
			Vocabulary::get( 'numbers' )->delete();
		}
		$v = new Vocabulary( array(
			'name' => 'numbers',
			'description' => 'Some integers.',
		));
		$this->assertType( 'Vocabulary', $v, 'Vocabulary without features should be flat');

		$one = $v->add_term( 'one' );
		$this->assertType( 'Term', $one, 'add_term should return the new Term on success');
		$this->assertEquals( 'one', $one->term_display, 'The first term entered should be the root');
		$this->assertEquals( 1, $one->mptt_left, 'The first term should have mptt_left 1');
		$this->assertEquals( 2, $one->mptt_right, 'The first term should have mptt_right 2, as long as it is the only term');

		$two = $v->add_term( 'two' );
		$four = $v->add_term( 'four' );

		$three = $v->add_term( 'three', $four, true );
		$this->assertEquals( $four->mptt_left - 1, $three->mptt_right, 'When $before is true the new Term should be inserted before $target_term');
	}

	public function test_get_terms()
	{
		$v = new Vocabulary(array(
			'name' => 'foods',
			'description' => 'Types of foods you might eat.',
			'features' => array('hierarchical')
		));

		$fruit = $v->add_term('Fruit');
		$red_apples = $v->add_term(new Term(array('term' => 'red_apples', 'term_display' => 'Red Apples')), $fruit);
		$v->add_term('green_tomatoes', $fruit, $red_apples)->term_display = 'Green Tomatoes';
		$v->add_term('Les oranges', $fruit);

		$root = $v->get_term();

		$this->assertType('Term', $root, 'A term should be of type Term');
		$this->assertEquals('Fruit', $root->term_display, 'The first term entered should be the root');

		$descendants = $root->descendants();
		$this->assertEquals(3, count($descendants), 'Number of descendants of the root should equal the number terms added after the root');
		$this->assertType('Term', $descendants[0], 'Descendants should be of type Term');

		$term = $v->get_term(Utils::slugify('Les oranges'));
		$this->assertType('Term', $term, 'Should be able to retrieve terms by term');

		$parent = $term->parent();
		$this->assertEquals($root, $parent, 'Should be able to retrieve a term\'s parent');
	}

	public function test_not_descendants()
	{
		$v = new Vocabulary( array(
			'name' => 'animals',
			'description' => 'Types of animals.',
			'features' => array( 'hierarchical' )
		) );
		$v->insert();

		$root = $v->add_term( 'Animal Kingdom' );
		$backbone = $v->add_term( 'Backbone', $root );
		$mammal = $v->add_term( 'Mammal', $backbone );
		$lungs = $v->add_term( 'Lungs', $backbone );
		$reptile = $v->add_term( 'Reptile', $backbone );
		$bird = $v->add_term( 'Bird', $backbone );
		$gills = $v->add_term( 'Gills', $backbone );
		$fish = $v->add_term( 'Fish', $gills );
		$amphibian = $v->add_term( 'Amphibian', $gills );

		$no_backbone = $v->add_term( 'No Backbone', $root );
		$starfish = $v->add_term( 'Starfish', $no_backbone );
		$mollusk = $v->add_term( 'Mollusk', $no_backbone );
		$legs = $v->add_term( 'Jointed Legs', $no_backbone );
		$snail = $v->add_term( 'Snail', $mollusk );
		$clam = $v->add_term( 'Clam', $mollusk );
		$insect = $v->add_term( 'Insect', $legs );
		$spider = $v->add_term( 'Spider', $legs );
		$crustacean  = $v->add_term( 'Crustacean', $legs );

		$not_descendants = $backbone->not_descendants();
		$s = array();
		foreach($not_descendants as $el ) {
			$s[] = (string)$el;
		}
		$expected = array( $root, $no_backbone, $starfish, $mollusk, $legs, $snail, $clam, $insect, $spider, $crustacean );
		$this->assertTrue( 10 == count( $not_descendants ), sprintf( 'Found: %s', implode( ', ', $s ) ) );
		$this->assertTrue( $not_descendants == $expected );

		$v->delete();
	}

	public function test_not_ancestors()
	{
		$v = new Vocabulary( array(
			'name' => 'animals',
			'description' => 'Types of animals.',
			'features' => array( 'hierarchical' )
		) );
		$v->insert();

		$root = $v->add_term( 'Animal Kingdom' );
		$backbone = $v->add_term( 'Backbone', $root );
		$mammal = $v->add_term( 'Mammal', $backbone );
		$lungs = $v->add_term( 'Lungs', $backbone );
		$reptile = $v->add_term( 'Reptile', $backbone );
		$bird = $v->add_term( 'Bird', $backbone );
		$gills = $v->add_term( 'Gills', $backbone );
		$fish = $v->add_term( 'Fish', $gills );
		$amphibian = $v->add_term( 'Amphibian', $gills );

		$no_backbone = $v->add_term( 'No Backbone', $root );
		$starfish = $v->add_term( 'Starfish', $no_backbone );
		$mollusk = $v->add_term( 'Mollusk', $no_backbone );
		$legs = $v->add_term( 'Jointed Legs', $no_backbone );
		$snail = $v->add_term( 'Snail', $mollusk );
		$clam = $v->add_term( 'Clam', $mollusk );
		$insect = $v->add_term( 'Insect', $legs );
		$spider = $v->add_term( 'Spider', $legs );
		$crustacean= $v->add_term( 'Crustacean', $legs );

		$not_ancestors = $snail->not_ancestors();
		$s = array();
		foreach( $not_ancestors as $el ) {
			$s[] = (string)$el;
		}

		$expected = array($clam, $insect, $spider, $crustacean, $legs, $starfish,
			$backbone, $mammal, $lungs, $reptile, $bird, $gills, $fish, $amphibian );

		$this->assertTrue( 14 == count( $not_ancestors ), sprintf( 'Found: %s', implode( ', ', $s ) ) );
		$this->assertTrue( $not_ancestors == $expected );

		$v->delete();
	}

	public function teardown()
	{
	}
	
}
?>
