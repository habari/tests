<?php

// Path to Habari testcase framework. In this case it is in samples/../
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Framework.php';

/**
 * Functionality tests must not require a full bootstrap of a Habari setup, but
 * can only be ran on running and working Habari setups. When the habari setup
 * is the local instance, it might have been bootstrapped if it is not installed
 * yet, but this test can also be use to verify some functionality in a remote
 * server.
 */
class samples_loginadduserTest extends Habari_Functionality_TestCase
{
  /**
   * Declare some test information, used only for the Testing Framework UI
   * interface. This function is optional and is not required to run the
   * test manually or automatically.
   * @return array
   */
  public static function caseInfo( ) {
    return array(
      'name' => 'My first functionality test',
      'description' => 'Sample test with basics Habari functionality testsuite functions',
      'category' => 'Example'
    );
  }

  protected function setUp() {
    // Call parent.
    parent::setUp();

   /**
    * By default, Functionality testcases will work on the ../htdocs folder
    * where the testing framework is located. Uncomment the following block
    * if you want to make this tests passing into another instance of habari.
    $options = array(
      'base_url' => 'http://192.168.1.1',
      'username' => 'admin',
      'password' => 'habari'
    );
    $this->habari_set_endpoint($options);
    *
    */
  }

  /**
   * Test that an admin user can login and create an user using the web
   * interface
   * @return void
   */
  public function testloginadduser()
  {

    /**
     * Habari_functionality_TestCase will use the defined endpoint for this
     * action, but habari_login also accepts an array of arguments in the
     * form:
     *   array ( 'username' => 'user, 'password' => 'pass', 'endpoint' => '')
     *
     * An empty endpoint would do login in the self defined endpoint.
     *
     * Login using default arguments.
     * $this->http_login()
     *
     * Login with other username and password
     * http_login(
     *   array (
     *     'username' => 'admin',
     *     'password' => 'habari',
     *   ));
     *
     * Login in a different habari instance, and do not snapshot neither
     * try to bootstrap the habari instance. Do this test in other host with
     * a Habari setup already running.
     * http_login(
     *   array (
     *     'username' => 'admin',
     *     'password' => 'habari',
     *     'endpoint' => 'http://192.168.1.2/habari/',
     *     'snapshot' => FALSE,
     *   ));
     *
     * This time test using current habari setup located at ../htdocs
     *
     * http_login verifies the user has already logged in the habari instance
     * internally, returning test fails on errors.
     */
    $this->http_login(
      array(
        'username' => 'admin',
        'password' => 'habari',
      )
    );

    /**
     * Now go to admin/user page and submit the 'Add user' form with random
     * values.
     */

    /**
     * First, we declare the form information. For this, we need to know the
     * internal names of the fields (it is <input type.... name="THISNAME"..>
     * preparing all this fields in an array.
     */
    // Now define a new user
    $newuser = array(
      'username'  => $this->randomName(),
      'new_email' => $this->randomName() . '@' . $this->randomName() . '.com',
      'new_pass1' => '1234',
      'new_pass2' => '1234',
    );

    /**
     * With this information, now we submit the form. We must indicate the page
     * where the form is located (or NULL to use current last visited URL); an
     * array with the forl fields, and the title of the submit button (or image)
     * where to click to submit this form.
     *
     * http_submit_form will look for the forms in the page, verify that all
     * fields exist (using testing assertions), fill the fields with the values
     * submitted by the user, and get other form fields defined. Once all the
     * fields are completed, it will look for the submit button with the label
     * indicated in the third argument, as there might be more than one submit
     * button, and submit the form using this method.
     */
    $this->http_submit_form(
       'admin/users',
       $newuser ,
       'Add User'
    );
    
    /**
     * Habary_functionality_TestCase will take care of errors during the form
     * submission, but we are able to check additional errors or wrong behaviour
     * if we want to.
     */

    /**
     * To finish this simple test, we will make sure that the new user has been
     * created, by searching its name in the html returned by the server.
     */
    $this->assertContains( 
      $newuser->username,
      $this->get_http_content(),
      'Username has been created successfully'
    );

    /**
     * Lets try if this is true.. really..
     */

    // Logout current user
    $this->http_logout();

    // Try to login in the site using the new created user..
    $this->http_login(
      array(
        'username' => $newuser->username,
        'password' => '1234',
      )
    );
    // If there is any error, http_login would mark the test as failed.

  }

}

?>
