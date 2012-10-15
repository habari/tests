<?php

class VersionTest extends UnitTestCase
{
	function test_get_dbversion()
	{
		$this->assert_equal( Version::DB_VERSION, Version::get_dbversion() );
	}

	function test_get_apiversion()
	{
		$this->assert_equal( Version::API_VERSION, Version::get_apiversion() );
	}

	function test_get_habariversion()
	{
		$this->assert_equal( Version::HABARI_MAJOR_MINOR . Version::HABARI_RELEASE, Version::get_habariversion() );
	}

//	function test_is_devel()
//	{
//		$this->assert_equal( strpos(Version::HABARI_SVN_HEAD_URL, '/trunk/') !== false || strpos(Version::HABARI_SVN_HEAD_URL, '/branches/') !== false, Version::is_devel() );
//	}

	function test_save_dbversion()
	{
		Options::delete( 'db_version' );
		Version::save_dbversion();
		$this->assert_equal( Version::DB_VERSION, Options::get( 'db_version' ) );
	}

	function test_requires_upgrade()
	{
		Options::set( 'db_version', Version::DB_VERSION - 1 );
		$this->assert_equal( true, Version::requires_upgrade() );
		Options::set( 'db_version', Version::DB_VERSION );
		$this->assert_equal( false, Version::requires_upgrade() );
	}

//	function test_get_svn_revision()
//	{
		/* the below is copied verbatim from version.php. This may not be optimal testing. */
//		$rev = 0;
		// Cheating!
//		$stash_file = HABARI_PATH . '/.svn/entries';
//		if(file_exists($stash_file)) {
//			$info = file_get_contents($stash_file);
//			$info = explode("\n", $info);
//			if(strpos($info[4], 'svn.habariproject.org/habari/') !== false) {
//				$rev = intval(trim($info[3]));
//			}
//		}
//		if($rev == 0) {
//			$rev = intval(preg_replace('/[^0-9]/', '', Version::HABARI_SVN_REV));
//		}
//		$this->assert_equal( $rev, Version::get_svn_revision() );
//	}
}
?>
