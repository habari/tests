<?php

include 'bootstrap.php';

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
 * Test class for QueryRecord.
 */
class QueryRecordTest extends UnitTestCase
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
	protected function setup()
	{
		$this->queryrecord = new QueryRecord( $this->test_data );
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function teardown()
	{
		unset( $this->queryrecord );
	}

	public function test__get()
	{
		foreach ( $this->test_data as $key => $val ) {
			$this->assert_equal( $val, $this->queryrecord->$key );
		}
	}

	public function test__set()
	{
		$setqueryrecord = new QueryRecord();
		foreach ( $this->test_data as $key => $val ) {
			$setqueryrecord->$key = $val;
		}
		// test contruct and __set match
		foreach ( $this->test_data as $key => $val ) {
			$this->assert_equal( $this->queryrecord->$key, $setqueryrecord->$key );
		}
		// test __set matches
		foreach ( $this->test_data as $key => $val ) {
			$this->assert_equal( $val, $setqueryrecord->$key );
		}
	}

	public function test__isset() {
		foreach ( $this->test_data as $key => $val ) {
			switch ($key) {
				case 'null':
					$this->assert_false( isset( $this->queryrecord->$key ), "isset( '$key' )" );
					break;
				default:
					$this->assert_true( isset( $this->queryrecord->$key ), "isset( '$key' )" );
					break;
			}
		}
	}

	public function test_newfields__construct()
	{
		$testqueryrecord = new TestQueryRecord( $this->test_data );
		$fields = $testqueryrecord->get_fields();
		$newfields = $testqueryrecord->get_newfields();

		$this->assert_equal( array(), $newfields );

		// make sure new vals are in newfields
		$new_val = 'newbar';
		foreach ( array_keys( $this->test_data ) as $key ) {
			$testqueryrecord->$key = $new_val;
		}
		$fields = $testqueryrecord->get_fields();
		$newfields = $testqueryrecord->get_newfields();
		foreach ( $this->test_data as $key => $val ) {
			$this->assert_equal( $new_val, $newfields[$key] );
			$this->assert_equal( $val, $fields[$key] );
		}

		// make sure brand new field goes in newfield
		$testqueryrecord->pony = 'ponies';
		$fields = $testqueryrecord->get_fields();
		$newfields = $testqueryrecord->get_newfields();
		$this->assert_true( isset( $newfields['pony'] ) );
		$this->assert_equal( 'ponies', $newfields['pony'] );
		$this->assert_true( !isset( $fields['pony'] ) );
	}

	public function test_newfields__set()
	{
		$testqueryrecord = new TestQueryRecord;
		foreach ( $this->test_data as $key => $val ) {
			$testqueryrecord->$key = $val;
		}
		$fields = $testqueryrecord->get_fields();
		$newfields = $testqueryrecord->get_newfields();

		$this->assert_equal( array(), $newfields );

		// make sure new vals are in newfields
		$new_val = 'newbar';
		foreach ( array_keys( $this->test_data ) as $key ) {
			$testqueryrecord->$key = $new_val;
		}
		$fields = $testqueryrecord->get_fields();
		$newfields = $testqueryrecord->get_newfields();
		foreach ( $this->test_data as $key => $val ) {
			$this->assert_equal( $new_val, $newfields[$key] );
			$this->assert_equal( $val, $fields[$key] );
		}

		// make sure brand new field goes in newfield
		$testqueryrecord->pony = 'ponies';
		$fields = $testqueryrecord->get_fields();
		$newfields = $testqueryrecord->get_newfields();
		$this->assert_true( isset( $newfields['pony'] ) );
		$this->assert_equal( 'ponies', $newfields['pony'] );
		$this->assert_true( !isset( $fields['pony'] ) );
	}

	public function test_exclude_fields()
	{
		$fields = array( 'id', 'null' );
		$this->queryrecord->exclude_fields( $fields );
		$excluded = $this->queryrecord->list_excluded_fields();
		foreach ( $fields as $field ) {
			$this->assert_true( isset( $excluded[$field] ) );
		}

		$field = 'id';
		$this->queryrecord->exclude_fields( $field );
		$excluded = $this->queryrecord->list_excluded_fields();
		$this->assert_true( isset( $excluded[$field] ) );
	}

	public function test_to_array()
	{
		$this->assert_equal( $this->test_data, $this->queryrecord->to_array() );
	}

	public function test_get_url_args()
	{
		$this->assert_equal( $this->test_data, $this->queryrecord->get_url_args() );
	}

	/**
	 * @todo Implement test_insert().
	 */
	public function test_insert() {
		$this->mark_test_incomplete();
	}
	
	/**
	 * @todo Implement testUpdate().
	 */
	public function test_update() {
		$this->mark_test_incomplete();
	}

	/**
	 * @todo Implement test_delete().
	 */
	public function test_delete() {
		$this->mark_test_incomplete();
	}
}

QueryRecordTest::run_one( 'QueryRecordTest' );

?>
