<?php

require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'phpunit_bootstrap.php';

class system_classes_VersionTest extends PHPUnit_Framework_TestCase
{

	public function testGet_dbversion()
	{
		$this->assertEquals( Version::DB_VERSION, Version::get_dbversion() );
	}

	public function testGet_apiversion()
	{
		$this->assertEquals( Version::API_VERSION, Version::get_apiversion() );
	}

	public function testGet_habariversion()
	{
		$this->assertEquals( Version::HABARI_VERSION, Version::get_habariversion() );
	}

	public function testIs_devel()
	{
		$this->assertEquals( strpos(Version::HABARI_SVN_HEAD_URL, '/trunk/') !== false || strpos(Version::HABARI_SVN_HEAD_URL, '/branches/') !== false, Version::is_devel() );
	}

	public function testSave_dbversion()
	{
		Options::delete( 'db_version' );
		Version::save_dbversion();
		$this->assertEquals( Version::DB_VERSION, Options::get( 'db_version' ) );
	}

	public function testRequires_upgrade()
	{
		Options::set( 'db_version', Version::DB_VERSION - 1 );
		$this->assertEquals( true, Version::requires_upgrade() );
		Options::set( 'db_version', Version::DB_VERSION );
		$this->assertEquals( false, Version::requires_upgrade() );
	}

	public function testGet_svn_revision()
	{
		/* the below is copied verbatim from version.php. This may not be optimal testing. */
		$rev = 0;
		// Cheating!
		$stash_file = HABARI_PATH . '/.svn/entries';
		if(file_exists($stash_file)) {
			$info = file_get_contents($stash_file);
			$info = explode("\n", $info);
			if(strpos($info[4], 'svn.habariproject.org/habari/') !== false) {
				$rev = intval(trim($info[3]));
			}
		}
		if($rev == 0) {
			$rev = intval(preg_replace('/[^0-9]/', '', Version::HABARI_SVN_REV));
		}
		$this->assertEquals( $rev, Version::get_svn_revision() );
	}
}
?>