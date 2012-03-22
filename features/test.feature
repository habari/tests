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
