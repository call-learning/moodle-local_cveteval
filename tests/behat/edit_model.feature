@local_cveteval @local
Feature: As an admin I can import new set of planning and enable it

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | obs1     | Obs1      | Obs1     | obs1@example.com  |
      | obs2     | Obs2      | Obs2     | obs2@example.com  |
      | resp1    | Resp1     | Resp1    | resp1@example.com |
      | resp2    | Resp2     | Resp2    | resp2@example.com |
      | etu1     | Etu1      | Etu1     | etu1@example.com  |
      | etu2     | Etu2      | Etu2     | etu2@example.com  |
    And the following "local_cveteval > histories" exist:
      | idnumber | isactive |
      | HIST1    | 1        |
    And I set CompetVetEval default history to "HIST1"
    And the following "local_cveteval > groups" exist:
      | name |
      | G1   |
      | G2   |
    And the following "local_cveteval > group assignments" exist:
      | groupname | student |
      | G1        | etu1    |
      | G2        | etu2    |
    And the following "local_cveteval > situations" exist:
      | title       | idnumber | expectedevalsnb |
      | Situation 1 | SIT1     | 1               |
      | Situation 2 | SIT2     | 2               |
    And the following "local_cveteval > plannings" exist:
      | groupname | situationsn | starttime        | endtime          |
      | G1        | SIT1        | 24 December 2022 | 31 December 2022 |
      | G2        | SIT2        | 14 December 2022 | 21 December 2022 |
    And the following "local_cveteval > roles" exist:
      | situationsn | type      | user  |
      | SIT1        | appraiser | obs1  |
      | SIT1        | assessor  | resp1 |
      | SIT2        | appraiser | obs2  |
      | SIT2        | assessor  | resp2 |
    And the following "local_cveteval > appraisals" exist:
      | student | appraiser | target_plan                               | context               | comment |
      | etu1    | obs1      | 24 December 2022/31 December 2022/SIT1/G1 | context for appraisal | comment |
    And the following "local_cveteval > appraisal criterion" exist:
      | target_appraisal                | target_plan                               | criterionidnumber | grade | comment |
      | etu1/obs1/context for appraisal | 24 December 2022/31 December 2022/SIT1/G1 | Q001              | 1     | comment |

  @javascript
  Scenario: As an admin I can add a new situation for user "resp1" and "resp1" can see it in the list of evaluations
    Given I am on the "HIST1" "local_cveteval > Manage Model" page logged in as "admin"
    And I follow "Edit Clinical situation"
    And I click on "Add" "button"
    And I set the following fields to these values:
    | Title | Situation 4 |
    | Description | Desc Situation |
    | ID | SIT4 |
    | #Eval | 1  |
    And I click on "Save" "button"
    Then I should see "Entity added"
    And I should see "SIT4"
    Then I follow "HIST1"
    And I follow "Edit Clinical situation"
    Then I should see "Situation 4" in the "SIT4" "local_cltools > Table Row"
    Then I follow "HIST1"
    And I follow "Edit Role"
    And I click on "Add" "button"
    And I expand the "userid" autocomplete
    And I click on "Resp1 Resp1 (resp1@example.com)" item in the autocomplete list
    And I expand the "clsituationid" autocomplete
    And I click on "Situation 4" item in the autocomplete list
    And I click on "Save" "button"
    Then I should see "Entity added"
    And I am on the "local_cveteval > Evaluation" page logged in as "resp1"
    Then I should see "SIT1"
    Then I should see "SIT4"

  @javascript
  Scenario: As an admin I cannot edit situations that already have an appraisal
    Given I am on the "HIST1" "local_cveteval > Manage Model" page logged in as "admin"
    And I follow "Edit Clinical situation"
    Then "Edit" "local_cltools > row_action_button" in the "SIT1" "local_cltools > Table Row" should not be visible
    Then "Edit" "local_cltools > row_action_button" in the "SIT2" "local_cltools > Table Row" should be visible
