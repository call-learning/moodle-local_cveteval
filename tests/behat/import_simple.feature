@local_cveteval @local
Feature: As an admin I can import new set of planning and enable it

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | obs1     | Obs1      | Obs1     | obs1@example.com  |
      | obs2     | Obs2      | Obs2     | obs2@example.com  |
      | obs3     | Obs3      | Obs3     | obs3@example.com  |
      | resp1    | Resp1     | Resp1    | resp1@example.com |
      | resp2    | Resp2     | Resp2    | resp2@example.com |
      | resp3    | Resp3     | Resp3    | resp3@example.com |
      | etu1     | Etu1      | Etu1     | etu1@example.com  |
      | etu2     | Etu2      | Etu2     | etu2@example.com  |
      | etu3     | Etu3      | Etu3     | etu3@example.com  |
      | etu4     | Etu4      | Etu4     | etu4@example.com  |

  @javascript @_file_upload
  Scenario: As an admin I can import a simple planning
    Given I log in as "admin"
    When I navigate to "Plugins > Local plugins > CompetVetEval plugin" in site administration
    And I follow "New Import"
    And I set the field "Import Identifier" to "New behat import"
    And I upload "local/cveteval/tests/fixtures/behat/Planning.csv" file to "Planning" filemanager
    # This is a hack to fix the issue when we have several file uploaders on the same page
    # See https://moodle.org/mod/forum/discuss.php?d=315865
    And I press "Start import"
    And I upload "local/cveteval/tests/fixtures/behat/Grouping.csv" file to "Grouping" filemanager
    And I press "Start import"
    And I upload "local/cveteval/tests/fixtures/behat/Situations.csv" file to "Situation" filemanager
    And I press "Start import"
    And I set the field "CSV Delimiter" to ";"
    And I press "Start import"
    Then I should see "Imported group : 2, group assignments: 4"
    And  I should see "Imported roles: 7, planning situations : 3"
    And  I should see "Imported plans: 6, planning events : 12"
    And I click on "Continue" "button"
    And I should see "Import logs"
    And I click on "Continue" "button"
    And I should see "New behat import"

