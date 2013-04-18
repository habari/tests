<?php

namespace Habari;

class CronTest extends UnitTestCase {
	public function setup()
	{
	}

	public function teardown()
	{
	}

	public function test_delete_on_fail()
	{
		$job = CronTab::add_single_cron('test_delete_fail', 'this_cron_hook_doesnt_exist', DateTime::create(), 'Test Cron');
		for($z = 0; $z < 10; $z++) {
			$job = CronTab::get_cronjob('test_delete_fail');
			Options::set( 'next_cron', 0 );
			Options::delete( 'cron_running' );
			$job->execute();
		}
		$this->assert_false($job->active, 'The cron is still active after failing more than the allowed number of times.');
		CronTab::delete_cronjob('test_delete_fail');
	}

}

?>
