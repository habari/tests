<?php
namespace Habari;

	/**
	 * Tests for the EventLog class
	 *
	 * @todo Test the following parameters to EventLog::get().
	 * - offset => amount by which to offset returned posts, used in conjunction with limit
	 * - count => return the number of posts that would be returned by this request
	 * - fetch_fn => the function used to fetch data, one of 'get_results', 'get_row', 'get_value', 'get_query'
	 */
class EventLogTest extends UnitTestCase
{
	private $user;
	private $types;
	/**
	 * Set up for the whole test suite
	 */
	protected function module_setup()
	{
		set_time_limit(0);
		if($user = User::get_by_name( 'eventlog_test' )) {
			$this->user = $user;
			//$this->skip_all("User {$user->id} is required 'posts_test' user.");
		}
		else {
			$this->user = User::create(array (
				'username'=>'eventlog_test',
				'email'=>'eventlog_test@example.com',
				'password'=>md5('q' . rand( 0,65535 ) ),
			) );
		}
		$this->types = array(
		    'testing' => 'unit_tests',
		    'testing2' => 'unit_tests_2',
		    'testing3' => 'unit_tests_3',
		    'testing4' => 'unit_tests_4',
		    'testing5' => 'unit_tests_5',
		);
		foreach($this->types as $type => $module) {
			EventLog::register_type( $type, $module );
		}
	}

	/**
	 * Teardown for the whole test suite
	 */
	protected function module_teardown()
	{
		$this->user->delete();
		foreach($this->types as $type => $module) {
			EventLog::unregister_type( $type, $module );
		}
		$this->types = null;
	}

	/**
	 * Setup for each test
	 */
	protected function setup()
	{
		EventLog::get(array('nolimit' => 1))->purge();
	}

	/**
	 * Teardown for each test
	 */
	protected function teardown()
	{
		EventLog::get(array('nolimit' => 1))->purge();
//		EventLog::unregister_type( 'testing', 'unit_tests' );
	}

	/**
	 * Get a log entry by a single id
	 */
	public function test_get_log_by_id()
	{
		$expected = EventLog::log( 'Test get_log_by_id entry.', 'info', 'default', 'habari');
		$result = EventLog::get(array('id' => $expected->id));

		$this->assert_true( $result instanceof EventLog, 'Result should be of type EventLog');
		$this->assert_true($result->onelogentry, 'A single LogEntry should be returned if a single id is passed in');

		$result = $result[0];
		$this->assert_true( $result instanceof LogEntry, 'Items should be of type LogEntry' );
		$this->assert_equal( $result->id, $expected->id, 'id of returned LogEntry should be the one we asked for' );
	}

	/**
	 * Get log entries by multiple ids
	 */
	public function test_get_logs_by_ids()
	{
		$expected = array();
		$expected[] = EventLog::log('Test get_logs_by_id entry #1', 'info', 'default', 'habari');
		$expected[] = EventLog::log('Test get_logs_by_id entry #2', 'info', 'default', 'habari');

		$ids = array();
		foreach ( $expected as $entry ) {
			$ids[] = $entry->id;
		}

		$result = EventLog::get( array('id' => $ids ) );

		$this->assert_true( $result instanceof EventLog, 'Result should be of type EventLog' );
		// @todo This currently isn't true, because the options limit is respected. Should it be?
		//$this->assert_equal( count( $result ), count( $expected ), 'The number of posts we asked for should be returned' );

		foreach ( $result as $r ) {
			$this->assert_true( $r instanceof LogEntry, 'Items should be of type LogEntry' );
			$this->assert_true( in_array( $r->id, $ids ), 'id of returned LogEntry should be in the list of the ones we asked for' );
		}

	}

	/**
	 * Get log entries by user id
	 */
	public function test_get_logs_by_user_id()
	{
		$message = 'Expected entry test_get_logs_by_user_id';

		$decoy = User::create(array(
			'username'=>'decoy',
			'email'=>'decoy@example.com',
			'password'=>md5('q' . rand( 0,65535 )),
		));
		$expected = new LogEntry(array(
		    'user_id' => $this->user->id,
		    'type' => 'default',
		    'severity' => 'info',
		    'module' => 'habari',
		    'message' => $message
		));
		$expected->insert();

		$unexpected = new LogEntry(array(
		    'user_id' => $decoy->id,
		    'type_' => 'default',
		    'severity' => 'info',
		    'module' => 'habari',
		    'message' => 'Unexpected entry test_get_logs_by_user_id'
		));
		$unexpected->insert();

		$result = EventLog::get( array( 'user_id' => $this->user->id ) );

		$this->assert_true( $result instanceof EventLog, 'Result should be of type EventLog' );
		$this->assert_equal( 1, count($result), 'The expected number of log entries should be returned' );

		foreach ( $result as $r ) {
			$this->assert_true( $r instanceof LogEntry, 'Items should be of type LogEntry' );
			$this->assert_true( $r->user_id= $this->user->id, 'Returned posts should belong to the expected users');
		}

		$decoy->delete();
 	}

	/**
	 * Get log entries by an array of user ids
	 */
	public function test_get_logs_by_user_ids()
	{
		$message = 'Expected entry test_get_logs_by_user_id';

		$decoy = User::create(array(
			'username'=>'decoy',
			'email'=>'decoy@example.com',
			'password'=>md5('q' . rand( 0,65535 )),
		));

		$expected = array();
		$entry = new LogEntry(array(
		    'user_id' => $this->user->id,
		    'type' => 'default',
		    'severity' => 'info',
		    'module' => 'habari',
		    'message' => $message
		));
		$entry->insert();
		$expected[] = $entry;

		$entry = new LogEntry(array(
		    'user_id' => $decoy->id,
		    'type' => 'default',
		    'severity' => 'info',
		    'module' => 'habari',
		    'message' => 'Unexpected entry test_get_logs_by_user_id'
		));
		$entry->insert();
		$expected[] = $entry;

		$result = EventLog::get(array('user_id' => array($this->user->id, $decoy->id)));

		$this->assert_true($result instanceof EventLog, 'Result should be of type EventLog');
		$this->assert_equal(count($expected), count($result), 'The expected number of log entries should be returned');

		$ids = array();
		foreach ( $result as $r ) {
			$ids[] = $r->user_id;
			$this->assert_true($r instanceof LogEntry, 'Items should be of type LogEntry');
			$this->assert_true(in_array($r->user_id, array($this->user->id, $decoy->id)), 'Returned posts should belong to the expected users');
		}
		$decoy->delete();
	}

	/**
	 * Get log entries by severity
	 */
	public function test_get_logs_by_severity()
	{
		$message = 'Expected entry test_get_logs_by_severity';
		$severities = array('debug', 'err', 'crit', 'warning', 'alert');

		for( $i=0; $i<5; $i++) {
			$entry = new LogEntry(array(
			    'user_id' => $this->user->id,
			    'type' => 'default',
			    'severity' => $severities[$i],
			    'module' => 'habari',
			    'message' => $message . $i
			));
			$entry->insert();
		}
		$entry = new LogEntry(array(
		    'user_id' => $this->user->id,
		    'type' => 'default',
		    'severity' => 'crit',
		    'module' => 'habari',
		    'message' => $message . ++$i
		));
		$entry->insert();

		$entries = EventLog::get(array('severity' => 'crit'));
		$this->assert_true(2 == count($entries));
		$entries = EventLog::get(array('severity' => 'alert'));
		$this->assert_true( $entries->onelogentry);
	}

	/**
	 * Get log entries by module name
	 */
	public function    test_get_logs_by_module()
	{
		$message = 'Expected entry test_get_logs_by_module';
		$types = array_keys($this->types);
		$modules = array_values($this->types);
		$entries = array();

		for( $i=0; $i<3; $i++) {
			$entries[] = new LogEntry(array(
			    'user_id' => $this->user->id,
			    'severity' => 'info',
			    'module' => $modules[$i],
			    'type' => $types[$i],
			    'message' => $message . $i
			));
		}
		$entries[] = new LogEntry(array(
		    'user_id' => $this->user->id,
		    'severity' => 'crit',
		    'type' => 'testing',
		    'module' => 'unit_tests',
		    'message' => $message . ++$i
		));

		foreach($entries as $entry) {
			$entry->insert();
		}

		$entries = EventLog::get(array('module' => 'unit_tests'));
		$this->assert_true(2 == count($entries));
		$entries = EventLog::get(array('module' => 'unit_tests_2'));
		$this->assert_true( $entries->onelogentry);

		foreach($entries as $entry) {
			$entry->delete();
		}
	}

	/**
	 * Get log entries by type name
	 */
	public function test_get_logs_by_type()
	{
		$message = 'Expected entry test_get_logs_by_type';
		$types = array_keys($this->types);
		$modules = array_values($this->types);
		$entries = array();

		for( $i=0; $i<5; $i++) {
			$entries[] = new LogEntry(array(
			    'user_id' => $this->user->id,
			    'module' => $modules[$i],
			    'type' => $types[$i],
			    'severity' => 'info',
			    'message' => $message . $i
			));
		}
		$entries[] = new LogEntry(array(
		    'user_id' => $this->user->id,
		    'type' => 'testing',
		    'module' => 'unit_tests',
		    'severity' => 'crit',
		    'message' => $message . ++$i
		));

		foreach($entries as $entry) {
			$entry->insert();
		}
		$entries = EventLog::get(array('type' => 'testing'));
		$this->assert_true(2 == count($entries));
		$entries = EventLog::get(array('type' => 'testing2'));
		$this->assert_true( $entries->onelogentry);

		foreach($entries as $entry) {
			$entry->delete();
		}
	}

	/**
	 * Get log entries by type id
	 */
	public function test_get_logs_by_type_id()
	{
		$entry = new LogEntry(array(
		    'user_id' => $this->user->id,
		    'type' => 'testing',
		    'module' => 'unit_tests',
		    'severity' => 'crit',
		    'message' => 'Message for test_get_logs_by_type_id'
		));
		$entry->insert();

		$entries = EventLog::get(array('type_id' => $entry->type_id));
		$this->assert_true( 'unit_tests' == $entries[0]->get_event_module());
		$this->assert_true( 'testing' == $entries[0]->get_event_type());
	}

	/**
	 * Get log entries by ip
	 */
	public function test_get_logs_by_ip()
	{
		$this->mark_test_incomplete();
		
	}

	/**
	 * Get log entries by return_data
	 * - if set, returns data associated with the log entry
	 */
	public function test_get_logs_by_return_data()
	{
		$entry = new LogEntry(array(
		    'user_id' => $this->user->id,
		    'type' => 'default',
		    'module' => 'habari',
		    'severity' => 'crit',
		    'message' => 'Message for test_get_logs_by_return_data',
		    'data' => array('one' => 'first', 'two'=> 'second', 'three' => 'third')
		));
		$entry->insert();

		$entries = EventLog::get(array('type_id' => $entry->type_id, 'return_data' => 1));
		$this->assert_true( isset($entries[0]->data));

		$data = unserialize($entries[0]->data);
		if(is_array($data)) {
			$this->assert_true($data['one'] == 'first');
		}

		$entry->delete();
	}

	/**
	 * Get log entries by date
	 * - year => a year of post publication
	 * - month => a month of post publication, ignored if year is not specified
	 * - day => a day of post publication, ignored if month and year are not specified
	 * - month_cts => return the number of posts published in each month
	 */
	public function test_get_logs_by_date()
	{

		// setup
		$year = 2008;
		$count = 0;
		for( $month = 1; $month <= 12; $month++ ) {
			for( $i = 0; $i <= 9; $i++ ) {
				$count++;
				$day = ( $month + 3 * $i ) % 29 + 1; // Won't result in a date > 29 until after month 2, i.e. February
				$date = "$year-$month-$day";
				$message = 'Entry for test_get_logs_by_date: ' . $count ;

				$entry = new LogEntry(array(
				    'user_id' => $this->user->id,
				    'type' => 'testing',
				    'severity' => 'info',
				    'module' => 'unit_tests',
				    'timestamp' => $date,
				    'message' => $message
				));
				$entry->insert();
			}
		}

		$month_cts = EventLog::get( array( 'month_cts' => 1, 'type' => 'testing' ) );

		for( $i = 0; $i < 12; $i++ ) {
			$this->assert_equal( $month_cts[ $i ]->year, 2008, "Log entry created in the wrong year." );
			$this->assert_equal( $month_cts[ $i ]->ct, 10, "Wrong number of log entries created." );
		}

		$entries = EventLog::get( array( 'day' => '01', 'month' => '04', 'year' => '2008', 'type' => 'testing', 'nolimit' => 1 ) );
		$this->assert_equal( count( $entries ), 0 );
		$entries = EventLog::get( array( 'day' => '03', 'month' => '04', 'year' => '2008', 'type' => 'testing', 'nolimit' => 1 ) );
		$this->assert_equal( count( $entries ), 1 );
		$entries = EventLog::get( array( 'day' => '05', 'month' => '04', 'year' => '2008', 'type' => 'testing', 'nolimit' => 1 ) );
		$this->assert_equal( count( $entries ), 1 );

		$entries = EventLog::get( array( 'month' => '01', 'year' => '2007', 'type' => 'testing', 'nolimit' => 1 ) );
		$this->assert_equal( count( $entries ), 0 );
		$entries = EventLog::get( array( 'month' => '04', 'year' => '2008', 'type' => 'testing', 'nolimit' => 1 ) );
		$this->assert_equal( count( $entries ), 10 );

		$entries = EventLog::get( array( 'year' => '2007', 'type' => 'testing', 'nolimit' => 1 ) );
		$this->assert_equal( count( $entries ), 0 );
		$entries = EventLog::get( array( 'year' => '2008', 'type' => 'testing', 'nolimit' => 1 ) );
		$this->assert_equal( count( $entries ), 120 );
	}


	/**
	 * Get log entries and specify the ordering
	 * - orderby => how to order the returned posts
	 */
	public function test_get_logs_orderby()
	{
		$this->mark_test_incomplete();
	}

	/**
	 * Get log entries and group the results
	 * - groupby => columns by which to group the returned posts, for aggregate functions
	 */
	public function test_get_logs_by_groupby()
	{
		$this->mark_test_incomplete();
	}

	/**
	 * Get log entries and specify an aggregate function
	 * - having => for selecting posts based on an aggregate function
	 */
	public function test_get_logs_by_having()
	{
		$this->mark_test_incomplete();
	}

	/**
	 * Get a log entry by a search criteria
	 * - criteria => a literal search string to match post title or content
	 */
	public function test_get_logs_by_criteria()
	{
		$this->mark_test_incomplete();
	}

	/**
	 * Limit the number of log entries returned
	 * - limit => the maximum number of posts to return, implicitly set for many queries
	 * - nolimit => do not implicitly set limit
	 */
	public function test_get_logs_with_limit()
	{
		for( $i = 1; $i <= 5; $i++ ) {
			$entry = new LogEntry(array(
			    'user_id' => $this->user->id,
			    'type' => 'default',
			    'severity' => 'err',
			    'module' => 'habari',
			    'message' => 'Event message from test_logs_with_limit' . $i
			));
			$entry->insert();
		}

		$count_entries = EventLog::get( array( 'count' => 1, 'limit' => 2, 'user_id' => $this->user->id ) );
		$this->assert_equal( $count_entries, 5, "LIMIT with a COUNT is pointless - COUNTing anything should return a single value." );

		$entries = EventLog::get( array( 'limit' => 2, 'user_id' => $this->user->id ) );
		$this->assert_equal( count( $entries ), 2 );

		$count_entries = EventLog::get( array( 'count' => 1, 'nolimit' => 1, 'user_id' => $this->user->id ) );
		$this->assert_true( $count_entries > 2 );

		$entries = EventLog::get( array( 'nolimit' => 1, 'user_id' => $this->user->id ) );
		$this->assert_true( count( $entries ) > 2 );

		// OFFSET based on page number (and limit)
		$entries = EventLog::get( array( 'limit' => 2, 'index' => 2, 'user_id' => $this->user->id ) );
		$this->assert_equal( count( $entries ), 2 );
		$entries = EventLog::get( array( 'limit' => 2, 'index' => 3, 'user_id' => $this->user->id ) );
		$this->assert_equal( count( $entries ), 1 );
	}

}

?>
