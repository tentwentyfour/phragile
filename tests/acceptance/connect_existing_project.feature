Feature: Connect Existing Sprint Project
  In order to see Phragile sprint overviews for existing Phabricator sprints
  As a scrum master
  I want to connect an existing project with a sprint

  Background:
    Given I am logged in
    And I submit a valid Conduit API Token
    And the "Wikidata" project exists
    And I am on the "Wikidata" project page

  Scenario: Connect existing project
    Given a sprint "Test Sprint" exists for the "Wikidata" project in Phabricator but not in Phragile
    When I click "Add sprint"
    And I fill in "title" with "Test Sprint"
    And I fill in "sprint_start" with "2015-04-01"
    And I fill in "sprint_end" with "2015-04-14"
    And I press "Add sprint"
    Then I should see "Connected \"Test Sprint\" with an existing Phabricator project"

  Scenario: Sprint already exists in Phragile
    Given a sprint "Test Sprint" exists for the "Wikidata" project
    When I click "Add sprint"
    And I fill in "title" with "Test Sprint"
    And I fill in "sprint_start" with "2015-04-01"
    And I fill in "sprint_end" with "2015-04-14"
    And I press "Add sprint"
    Then I should see "The title has already been taken"

  Scenario: Connect using a Phabricator ID
    Given a sprint "Test Sprint" exists for the "Wikidata" project in Phabricator but not in Phragile
    And I copied the "Test Sprint" Phabricator ID from Phabricator
    When I click "Add sprint"
    And I paste the copied Phabricator ID
    And I fill in "sprint_start" with "2015-04-01"
    And I fill in "sprint_end" with "2015-04-14"
    And I press "Add sprint"
    Then I should see "Connected \"Test Sprint\" with an existing Phabricator project"
    And I should see "Test Sprint" in the "title" element
