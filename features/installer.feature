Feature: Installer
  In order to use Habari
  As an admin
  I want to install Habari

Background:
  Given Habari is not installed
  And I visit the installer

Scenario: Navigating to Habari when not installed
  Then I should not see "Before you install Habari"
  And I should see "Locale"
  And I should see "Database Setup"

Scenario: Requirements met, show the installer
  Given the installation requirements are met 
  Then I should not see "Before you install Habari"
  And I should see "Locale"
  And I should see "Database Setup"

@javascript
Scenario: Check invalid credentials
  When I input the Database Host "localhost"
  And I input the Username "test"
  And I input the Password "wrong password"
  And I input the Database Name "test"
  And I check the database connection
  Then I should see the error message "Access denied. Make sure these credentials are valid."

Scenario: Check valid credentials
  When I input the Database Host "localhost"
  And I input the Username "test"
  And I input the Password "test"
  And I input the Database Name "test"
  And I check the database connection
  Then I should not see the error message "Access denied. Make sure these credentials are valid."