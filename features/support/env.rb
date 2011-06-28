require 'capybara'
require 'capybara/cucumber'
require 'capybara/mechanize'
require 'cucumber/formatter/unicode'
require 'rspec/expectations'
require 'test/unit/assertions'
require 'socket'
require 'mysql'

World(Test::Unit::Assertions)

Capybara.run_server = false
Capybara.app_host = 'http://localhost'

Capybara.default_selector = :css
Capybara.default_driver = :mechanize
Capybara.app = 'test'

Capybara.javascript_driver = :selenium
Capybara.register_driver :selenium do |app|
  Capybara::Driver::Selenium.new(app,
    :browser => :remote,
    :url => "http://127.0.0.1:4001/wd/hub",
    :desired_capabilities => :firefox)
end

@@database_hostname = '127.0.0.1'
@@database_name = 'test'
@@database_username = 'test'
@@database_password = 'test'

$my = Mysql.new( @@database_hostname, @@database_username, @@database_password, @@database_name )

# global functions
def get_server
	protocol = "http://"
	domain = "habari-test"
  protocol + domain
end
