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
			'features' => array( 'hierarchical' )
		);
		$v = new Vocabulary($params);
		$v->insert();

		$this->assertEquals($vocab_count + 1, count(Vocabulary::names()), 'Count of names should increase by one');
		$this->assertTrue(in_array($this->vocab_name, Vocabulary::names()), 'Test vocabulary name should be in the list of names');

		$new_v = new Vocabulary( $params );
		$results = $new_v->insert();
		$this->assertEquals( $results, FALSE );

		// Clean up
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

	public function test_get_vocabularies()
	{
		 // Use SQL to get a count of rows in the Vocabularies table
		 $sql_count = DB::get_value( "SELECT COUNT(*) FROM {vocabularies};");
		// Retrieve the vocabularies
		$vocabularies = Vocabulary::get_all();

		$this->assertEquals( $sql_count, count( $vocabularies ) );

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

		$t = new Term( $this->term_name );
		$this->assertType('Term', $t);
		$this->assertEquals($t->term, Utils::slugify($this->term_name));
		$this->assertEquals($t->term_display, $this->term_name);
	}

	public function test_add_term()
	{
		if( Vocabulary::get( 'numbers') ) {
			Vocabulary::get( 'numbers' )->delete();
		}

		$v = Vocabulary::create( array(
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
		$four = $v->get_term( $four->id );
		$this->assertEquals( $four->mptt_left - 1, $three->mptt_right, 'When $before is true the new Term should be inserted before $target_term');

		// clean up
		$v->delete();
	}

	public function test_delete_term()
	{
		if( Vocabulary::get( 'numbers') ) {
			Vocabulary::get( 'numbers' )->delete();
		}

		$v = Vocabulary::create( array(
			'name' => 'numbers',
			'description' => 'Some integers.',
		));

		$one = $v->add_term( 'one' );
		$two = $v->add_term( 'two' );

		$this->assertEquals( 2, count( $v->get_tree() ), 'The vocabulary should contain two terms' );
		$v->delete_term( $one );
		$this->assertEquals( 1, count( $v->get_tree() ), 'The vocabulary should contain one term' );

		$this->assertEquals( 1, count( $v->get_tree() ), 'The vocabulary should contain one term' );
		$v->delete_term( $two->term_display );
		$this->assertEquals( 0, count( $v->get_tree() ), 'The vocabulary should contain zero terms' );

		// clean up
		$v->delete();
	}

		public function test_move_term()
	{
		if( Vocabulary::get( 'numbers') ) {
			Vocabulary::get( 'numbers' )->delete();
		}

		$v = Vocabulary::create( array(
			'name' => 'numbers',
			'description' => 'Some integers.',
		));

		$this->assertType( 'Vocabulary', $v, 'Vocabulary without features should be flat');
		$fale = $v->move_term( 'new_term' );
		$this->assertFalse( $fale, 'Return false for an empty vocabulary' );

		$one = $v->add_term( 'one' );
		$this->assertEquals( 1, $one->mptt_left, 'The first term should have mptt_left 1');
		$this->assertEquals( 2, $one->mptt_right, 'The first term should have mptt_right 2, as long as it is the only term');

		$five = $v->add_term( 'five' );
		$two = $v->add_term( 'two' );
		$four = $v->add_term( 'four' );
		$three = $v->add_term( 'three' );
		$v->move_term( $three, $four, true );
		$three = $v->get_term( $three->id ); // not updated otherwise?
		$four = $v->get_term( $four->id ); // not updated otherwise?

		$this->assertEquals( $four->mptt_left - 1, $three->mptt_right, 'When $before is true the Term should be inserted before $target_term');

		$v->move_term( $five );

		$four = $v->get_term( $four->id ); // not updated otherwise?otherwise
		$five = $v->get_term( $five->id ); // not updated otherwise?
		$this->assertEquals( $four->mptt_right + 1, $five->mptt_left, 'Without arguments the Term should be moved all the way to the right');

		// clean up
		$v->delete();
}

	public function test_get_terms()
	{
		if( Vocabulary::get( 'foods') ) {
			Vocabulary::get( 'foods' )->delete();
		}

		$v = Vocabulary::create( array(
			'name' => 'foods',
			'description' => 'Types of foods you might eat.',
			'features' => array( 'hierarchical' )
		) );

		$fruit = $v->add_term( 'Fruit' );
		$red_apples = $v->add_term( new Term( array( 'term' => 'red_apples', 'term_display' => 'Red Apples' ) ), $fruit );
		$v->add_term( 'green_tomatoes', $fruit, FALSE );
		$v->add_term( 'Les oranges', $fruit );

		$root = $v->get_term();

		$this->assertType( 'Term', $root, 'A term should be of type Term' );
		$this->assertEquals( 'Fruit', $root->term_display, 'The first term entered should be the root' );

		$descendants = $root->descendants();
		$this->assertEquals( 3, count( $descendants ), 'Number of descendants of the root should equal the number terms added after the root' );
		$this->assertType( 'Term', $descendants[0], 'Descendants should be of type Term' );

		$term = $v->get_term( Utils::slugify( 'Les oranges' ) );
		$this->assertType( 'Term', $term, 'Should be able to retrieve terms by term' );

		$parent = $term->parent();
		$this->assertEquals( $root, $parent, 'Should be able to retrieve a term\'s parent' );

		// clean up
		$v->delete();
	}

	public function test_ancestors()
	{
		if( Vocabulary::get( 'animals') ) {
			Vocabulary::get( 'animals' )->delete();
		}

		$v = Vocabulary::create( array(
			'name' => 'animals',
			'description' => 'Types of animals.',
			'features' => array( 'hierarchical' )
		) );

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
		$snail = $v->add_term( 'Snail', $v->get_term( $mollusk->id ) );
		$clam = $v->add_term( 'Clam', $v->get_term( $mollusk->id ) );
		$insect = $v->add_term( 'Insect', $v->get_term( $legs->id) );
		$spider = $v->add_term( 'Spider', $v->get_term( $legs->id) );
		$crustacean= $v->add_term( 'Crustacean', $v->get_term( $legs->id) );

		$ancestors = $v->get_term( $snail->id )->ancestors();
		$s = array();
		foreach( $ancestors as $el ) {
			$s[] = (string)$el;
		}

		$expected = array( $mollusk, $no_backbone, $root );

		$this->assertEquals( 3, count( $ancestors ), sprintf( 'Found: %s', implode( ', ', $s ) ) );

		$e = array();;
		foreach($expected as $el ) {
			$e[] = (string)$el;
		}
		$this->assertTrue( 0 == count( array_diff( $s, $e ) ), sprintf( 'Found: %s', implode( ', ', array_diff( $s, $e ) ) ) );

		// clean up
		$v->delete();
	}

	public function test_not_descendants()
	{
		if( Vocabulary::get( 'animals') ) {
			Vocabulary::get( 'animals' )->delete();
		}

		$v = Vocabulary::create( array(
			'name' => 'animals',
			'description' => 'Types of animals.',
			'features' => array( 'hierarchical' )
		) );

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
		$snail = $v->add_term( 'Snail', $v->get_term( $mollusk->id ) );
		$clam = $v->add_term( 'Clam', $v->get_term( $mollusk->id ) );
		$insect = $v->add_term( 'Insect', $v->get_term( $legs->id) );
		$spider = $v->add_term( 'Spider', $v->get_term( $legs->id) );
		$crustacean= $v->add_term( 'Crustacean', $v->get_term( $legs->id) );

		$not_descendants = Term::get( $v, $backbone->id )->not_descendants();
		$s = array();
		foreach($not_descendants as $el ) {
			$s[] = (string)$el;
		}
		$expected = array( $root, $no_backbone, $starfish, $mollusk, $legs, $snail, $clam, $insect, $spider, $crustacean );
		$this->assertTrue( 10 == count( $not_descendants ), sprintf( 'Found: %s', implode( ', ', $s ) ) );
		$e = array();;
		foreach($expected as $el ) {
			$e[] = (string)$el;
		}
		$this->assertTrue( 0 == count( array_diff( $s, $e ) ) );

		// clean up
		$v->delete();
	}

	public function test_not_ancestors()
	{
		if( Vocabulary::get( 'animals') ) {
			Vocabulary::get( 'animals' )->delete();
		}

		$v = Vocabulary::create( array(
			'name' => 'animals',
			'description' => 'Types of animals.',
			'features' => array( 'hierarchical' )
		) );

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
		$snail = $v->add_term( 'Snail', $v->get_term( $mollusk->id ) );
		$clam = $v->add_term( 'Clam', $v->get_term( $mollusk->id ) );
		$insect = $v->add_term( 'Insect', $v->get_term( $legs->id) );
		$spider = $v->add_term( 'Spider', $v->get_term( $legs->id) );
		$crustacean= $v->add_term( 'Crustacean', $v->get_term( $legs->id) );

		$not_ancestors = $v->get_term( $snail->id )->not_ancestors();
		$s = array();
		foreach( $not_ancestors as $el ) {
			$s[] = (string)$el;
		}

		$expected = array($clam, $insect, $spider, $crustacean, $legs, $starfish,
			$backbone, $mammal, $lungs, $reptile, $bird, $gills, $fish, $amphibian );

		$this->assertTrue( 14 == count( $not_ancestors ), sprintf( 'Found: %s', implode( ', ', $s ) ) );

		$e = array();;
		foreach($expected as $el ) {
			$e[] = (string)$el;
		}
		$this->assertTrue( 0 == count( array_diff( $s, $e ) ), sprintf( 'Found: %s', implode( ', ', array_diff( $s, $e ) ) ) );

		// clean up
		$v->delete();
	}

	public function test_is_descendant_of()
	{
		if( Vocabulary::get( 'animals') ) {
			Vocabulary::get( 'animals' )->delete();
		}
		if( Vocabulary::get( 'plants') ) {
			Vocabulary::get( 'plants' )->delete();
		}

		$v = Vocabulary::create( array(
			'name' => 'animals',
			'description' => 'Types of animals.',
			'features' => array( 'hierarchical' )
		) );

		$root = $v->add_term( 'Animal Kingdom' );
		$backbone = $v->add_term( 'Backbone', $root );
		$mammal = $v->add_term( 'Mammal', $backbone );
		$zebra = $v->add_term( 'Zebra', $mammal );
		$zorse = $v->add_term( 'Zorse', $zebra );
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
		$snail = $v->add_term( 'Snail', $v->get_term( $mollusk->id ) );
		$clam = $v->add_term( 'Clam', $v->get_term( $mollusk->id ) );
		$insect = $v->add_term( 'Insect', $v->get_term( $legs->id) );
		$spider = $v->add_term( 'Spider', $v->get_term( $legs->id) );
		$crustacean= $v->add_term( 'Crustacean', $v->get_term( $legs->id) );

		$v2 = Vocabulary::create( array(
			'name' => 'plants',
			'description' => 'Types of plants.',
			'features' => array( 'hierarchical' )
		) );
		$plant_root = $v2->add_term( 'Flowering Plants' );
		$zebra_plant = $v2->add_term( 'Zebra Plant', $plant_root );

		$zebra_plant = $v2->get_term( $zebra_plant->id );
		$mammal = $v->get_term( $mammal->id );
		// must get these again since mptt_left and mptt_right values have changed since insertion
		$zebra_plant = $v2->get_term( $zebra_plant->id );
		$mammal = $v->get_term( $mammal->id );
		$zebra = $v->get_term( $zebra->id );
		$root = $v->get_term( $root->id );
		$zorse = $v->get_term( $zorse->id );
		$spider = $v->get_term( $spider->id );
		$backbone = $v->get_term( $backbone->id );

		$this->assertFalse( $zebra_plant->is_descendant_of( $mammal ), 'Should fail for different vocabularies' );
		$this->assertTrue( $zebra->is_descendant_of( $mammal ), 'Zebra is a child of Mammal' );
		$this->assertTrue( $zebra->is_descendant_of( $backbone ), 'Zebra is a grandchild of Backbone' );
		$this->assertTrue( $zebra->is_descendant_of( $root ), 'Zebra is a great-grandchild of Animal Kingdom' );
		$this->assertFalse( $zebra->is_descendant_of( $zorse ), 'Zebra does not descend from Zorse, but vice-versa' );
		$this->assertTrue( $zorse->is_descendant_of( $zebra ), 'Zorse does descend from Zebra (Mate a Horse and a Zebra and you get a Zorse)' );
		$this->assertFalse( $spider->is_descendant_of( $backbone ), 'Spider does not descend from Backbone' );

		// clean up
		$v->delete();
		$v2->delete();
	}

	public function test_is_ancestor_of()
	{
		if( Vocabulary::get( 'animals') ) {
			Vocabulary::get( 'animals' )->delete();
		}
		if( Vocabulary::get( 'plants') ) {
			Vocabulary::get( 'plants' )->delete();
		}

		$v = Vocabulary::create( array(
			'name' => 'animals',
			'description' => 'Types of animals.',
			'features' => array( 'hierarchical' )
		) );

		$root = $v->add_term( 'Animal Kingdom' );
		$backbone = $v->add_term( 'Backbone', $root );
		$mammal = $v->add_term( 'Mammal', $backbone );
		$zebra = $v->add_term( 'Zebra', $mammal );
		$zorse = $v->add_term( 'Zorse', $zebra );
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
		$snail = $v->add_term( 'Snail', $v->get_term( $mollusk->id ) );
		$clam = $v->add_term( 'Clam', $v->get_term( $mollusk->id ) );
		$insect = $v->add_term( 'Insect', $v->get_term( $legs->id) );
		$spider = $v->add_term( 'Spider', $v->get_term( $legs->id) );
		$crustacean= $v->add_term( 'Crustacean', $v->get_term( $legs->id) );

		$v2 = Vocabulary::create( array(
			'name' => 'plants',
			'description' => 'Types of plants.',
			'features' => array( 'hierarchical' )
		) );
		$plant_root = $v2->add_term( 'Flowering Plants' );
		$zebra_plant = $v2->add_term( 'Zebra Plant', $plant_root );

		$zebra_plant = $v2->get_term( $zebra_plant->id );
		$mammal = $v->get_term( $mammal->id );
		// must get these again since mptt_left and mptt_right values have changed since insertion
		$zebra_plant = $v2->get_term( $zebra_plant->id );
		$mammal = $v->get_term( $mammal->id );
		$zebra = $v->get_term( $zebra->id );
		$root = $v->get_term( $root->id );
		$zorse = $v->get_term( $zorse->id );
		$spider = $v->get_term( $spider->id );
		$backbone = $v->get_term( $backbone->id );

		$this->assertFalse( $root->is_ancestor_of( $zebra_plant ), 'Should fail for different vocabularies' );
		$this->assertTrue( $mammal->is_ancestor_of( $zebra ), 'Zebra is a child of Mammal' );
		$this->assertTrue( $backbone->is_ancestor_of( $zebra ), 'Zebra is a grandchild of Backbone' );
		$this->assertTrue( $root->is_ancestor_of( $zebra ), 'Zebra is a great-grandchild of Animal Kingdom' );
		$this->assertFalse( $zorse->is_ancestor_of( $zebra ), 'Zebra does not descend from Zorse, but vice-versa' );
		$this->assertTrue( $zebra->is_ancestor_of( $zorse ), 'Zorse does descend from Zebra (Mate a Horse and a Zebra and you get a Zorse)' );
		$this->assertFalse( $backbone->is_ancestor_of( $spider ), 'Spider does not descend from Backbone' );

		// clean up
		$v->delete();
		$v2->delete();
	}

	public function test_term__get()
	{
		if( Vocabulary::get( 'animals') ) {
			Vocabulary::get( 'animals' )->delete();
		}

		$v = Vocabulary::create( array(
			'name' => 'animals',
			'description' => 'Types of animals.',
			'features' => array( 'hierarchical' )
		) );

		$root = $v->add_term( 'Animal Kingdom' );
		$vocabulary = $root->vocabulary;
		$this->assertType( 'Vocabulary', $vocabulary );
		$this->assertEquals( $v->name, $vocabulary->name );
		$this->assertEquals( $v->description, $vocabulary->description );
		$this->assertEquals( $v->id, $vocabulary->id );

		$v->delete();
	}

	public function test_object_type()
	{
		 $name = 'unit_test';
		 Vocabulary::add_object_type( $name );
		 $sql_id = DB::get_value( "SELECT id FROM {object_types} WHERE name = :vocab_name", array( 'vocab_name' => $name ) );
		 $id = Vocabulary::object_type_id( $name );
		 $this->assertEquals( $sql_id, $id, 'The sql id should equal the id returned.' );
		 DB::delete( '{object_types}', array( 'name' => $name ) );
	}

	public function test_object_terms()
	{
		$post = Post::create( array(
		'title' => 'Unit Test Post',
		'content' => 'This is a unit test post to test setting and getting terms.',
		'user_id' => 1,
		'status' => Post::status( 'draft' ),
		'content_type' => Post::type( 'entry' ),
		'pubdate' => HabariDateTime::date_create(),
		) );

		$v = Vocabulary::get( 'tags' );

		// Test setting terms with strings
		$new_terms = array( 'habari', 'unit test' );
		$v->set_object_terms( 'post', $post->id, $new_terms );
		$terms = $v->get_object_terms( 'post', $post->id );
		$t = array();
		foreach( $terms as $term ) {
			$t[] = (string)$term;
		}

		$this->assertEquals( 2, count( $terms ) );
		$this->assertEquals( 0, count( array_diff( $new_terms, $t ) ) );

		// Test get_all_object_terms
		$nv = Vocabulary::create( array(
		'name' => 'animals',
		'description' => 'Types of animals.',
		'features' => array( 'hierarchical' )
		) );

		$root = $nv->add_term( 'Animal Kingdom' );
		$nv->set_object_terms( 'post', $post->id, $root );

		$terms = Vocabulary::get_all_object_terms( 'post', $post->id );
		$new_terms[] = 'Animal Kingdom';
		$t = array();
		foreach( $terms as $term ) {
			$t[] = (string)$term;
		}

		$this->assertEquals( 3, count( $terms ) );
		$this->assertEquals( 0, count( array_diff( $new_terms, $t ) ) );

		$post->delete();
		$nv->delete();
	}

	public function teardown()
	{
	}
	
}
?>
