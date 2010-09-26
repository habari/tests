<?php

// Path to Habari testcase framework. In this case it is in samples/../
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Framework.php';

/**
 * This test does not require any bootstrapping. It will attempt to install
 * a Habari instance.
 */
class functionality_SQLiteInstallTest extends Habari_Functionality_TestCase
{
  /**
   * Test information.
   * @return array
   */
  public static function caseInfo( ) {
    return array(
      'name' => 'SQLite install test',
      'description' => 'Installs a Habari instance using SQLite driver',
      'category' => 'Functionality'
    );
  }

  /**
   * Test that an admin user can login and create an user using the web
   * interface
   * @return void
   */
  public function testSQLiteInstall()
  {
    $this->base_url = 'http://localhost/habari-test/htdocs/';
   
    // Verify Habari is not already installed.
    $result = $this->http_get('/');
    if ( !strpos( $result, 'Database Setup' ) ) {
      $this->markTestSkipped('Habari is already installed. Skipping...');
    }

    /**
     * Try to install Habari
     */

    // Database Setup
    $install = array (
      'db_type'      => 'sqlite',
      'db_file'      => $this->randomName(5). '.db',
      'table_prefix' => 'habari__',
    );

    // Site configuration
    $install += array (
      'blog_title'     => $this->randomName(12),
      'admin_username' => $this->randomName(8),
      'admin_pass1'     => $this->randomName(5),
      'admin_email'    => 'test@habari.com',
    );
    $install += array (
      'admin_pass2'     => $install['admin_pass1']
    );

    // We leave default plugins activated.
    $result = $this->http_form_submit(
       '/',
       $install,
       'Install Habari'
    );

    /**
     * Verify installation
     */
    $this->assertContains(
      'a state-of-the-art publishing platform!',
      $result,
      'Habari successfully installed'
    );

    $this->http_login(
      array(
        'username' => $install['admin_username'],
        'password' => $install['admin_pass1'],
      )
    );

    // Logout current user
    $this->http_logout();

  }

}

?>
