<?php
require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

/**
 * class to get fields/newfields to test logic
 */
class TestQueryRecord extends QueryRecord
{
	public function get_fields()
	{
		return $this->fields;
	}

	public function get_newfields()
	{
		return $this->newfields;
	}
}

/**
 * Test class for Comment.
 */
class system_classes_QueryRecordTest extends PHPUnit_Framework_TestCase
{
	protected $queryrecord;
	protected $test_data = array(
		'foo' => 'bar',
		'id' => 1,
		'long' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi posuere tellus 
			eget ipsum euismod sagittis. Phasellus id purus metus, nec placerat purus. Cras
			porttitor mollis dapibus. Ut laoreet dui tellus, vel pulvinar sapien. Nulla vitae sem
			ut ligula condimentum auctor sed vitae turpis. Sed id nibh in elit tincidunt aliquet.
			Duis pulvinar dolor et nisi tempus condimentum. Pellentesque habitant morbi tristique
			senectus et netus et malesuada fames ac turpis egestas. Class aptent taciti sociosqu ad
			litora torquent per conubia nostra, per inceptos himenaeos. Pellentesque posuere blandit
			elit, eu aliquam eros bibendum nec. Ut non porttitor sem. Pellentesque habitant morbi
			tristique senectus et netus et malesuada fames ac turpis egestas. Nunc lorem lorem,
			accumsan eu semper sit amet, mollis a justo",
		'bool' => FALSE,
		'null' => NULL,
	);

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->queryrecord = new QueryRecord($this->test_data);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		unset( $this->queryrecord );
	}

	/**
	 * 
	 */
	public function test__get()
	{
		foreach ( $this->test_data as $key => $val ) {
			$this->assertEquals( $val, $this->queryrecord->$key );
		}
	}

	/**
	 * 
	 */
	public function test__set()
	{
		$setqueryrecord = new QueryRecord();
		foreach ( $this->test_data as $key => $val ) {
			$setqueryrecord->$key = $val;
		}
		// test contruct and __set match
		foreach ( $this->test_data as $key => $val ) {
			$this->assertEquals( $this->queryrecord->$key, $setqueryrecord->$key );
		}
		// test __set matches
		foreach ( $this->test_data as $key => $val ) {
			$this->assertEquals( $val, $setqueryrecord->$key );
		}
	}

	/**
	 *
	 */
	public function test__isset() {
		foreach ( $this->test_data as $key => $val ) {
			switch ($key) {
				case 'null':
					$this->assertFalse( isset( $this->queryrecord->$key ), "isset( '$key' )" );
					break;
				default:
					$this->assertTrue( isset( $this->queryrecord->$key ), "isset( '$key' )" );
					break;
			}
		}
	}

	/**
	 *
	 */
	public function testNewfields__construct()
	{
		$testqueryrecord = new TestQueryRecord($this->test_data);
		$fields = $testqueryrecord->get_fields();
		$newfields = $testqueryrecord->get_newfields();

		$this->assertEquals( array(), $newfields );

		// make sure new vals are in newfields
		$new_val = 'newbar';
		foreach ( array_keys($this->test_data) as $key ) {
			$testqueryrecord->$key = $new_val;
		}
		$fields = $testqueryrecord->get_fields();
		$newfields = $testqueryrecord->get_newfields();
		foreach ( $this->test_data as $key => $val ) {
			$this->assertEquals( $new_val, $newfields[$key] );
			$this->assertEquals( $val, $fields[$key] );
		}
		
	}

	/**
	 *
	 */
	public function testNewfields__set()
	{
		$testqueryrecord = new TestQueryRecord;
		foreach ( $this->test_data as $key => $val ) {
			$testqueryrecord->$key = $val;
		}
		$fields = $testqueryrecord->get_fields();
		$newfields = $testqueryrecord->get_newfields();

		$this->assertEquals( array(), $newfields );

		// make sure new vals are in newfields
		$new_val = 'newbar';
		foreach ( array_keys($this->test_data) as $key ) {
			$testqueryrecord->$key = $new_val;
		}
		$fields = $testqueryrecord->get_fields();
		$newfields = $testqueryrecord->get_newfields();
		foreach ( $this->test_data as $key => $val ) {
			$this->assertEquals( $new_val, $newfields[$key] );
			$this->assertEquals( $val, $fields[$key] );
		}

	}

	/**
	 *
	 */
	public function testExclude_fields()
	{
		$fields = array('id', 'null');
		$this->queryrecord->exclude_fields($fields);
		$excluded = $this->queryrecord->list_excluded_fields();
		foreach ( $fields as $field ) {
			$this->assertTrue( isset( $excluded[$field] ) );
		}

		$field = 'id';
		$this->queryrecord->exclude_fields($field);
		$excluded = $this->queryrecord->list_excluded_fields();
		$this->assertTrue( isset( $excluded[$field] ) );
	}

	/**
	 *
	 */
	public function testTo_array()
	{
		$this->assertEquals( $this->test_data, $this->queryrecord->to_array() );
	}

	/**
	 * 
	 */
	public function testGet_url_args()
	{
		$this->assertEquals( $this->test_data, $this->queryrecord->get_url_args() );
	}

	/**
	 * @todo Implement testInsert().
	 */
	public function testInsert() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}
	
	/**
	 * @todo Implement testUpdate().
	 */
	public function testUpdate() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testDelete().
	 */
	public function testDelete() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}
}
?>
