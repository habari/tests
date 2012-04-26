Feature: Working features in the test suite
	In order to produce working software
	As a Habari developer
	I want to ensure that the test suite works

	Background:
		Given Habari is installed
		And the test suite is running

	Scenario: Run a feature test
		Given a feature exists
		When the feature test runs
		Then some output should exist

	Scenario: Open the browser to Google
		Given selenium is running
		When I visit the URL http://google.com
		Then I should see the text "google"

	Scenario: Search for Habari
		Given selenium is running
		When I visit the URL http://google.com
		And I type "habari" into the q field
		And I submit the q form
		Then I should see the text "habariproject.org"