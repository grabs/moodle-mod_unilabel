@mod @mod_unilabel @unilabeltype @unilabeltype_simpletext
Feature: Modify content of the unilabeltype simpletext

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | teacher1 | Teacher   | 1        |
      | student1 | Student   | 1        |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |

  @javascript @_file_upload
  Scenario: Add content to the unilabel as simpletext
    # Set up a unilabel.
    Given the following "activity" exists:
      | activity     | unilabel    |
      | course       | C1          |
      | idnumber     | mh1         |
      | name         | Testlabel   |
      | intro        | Hello label |
      | section      | 1           |
      | unilabeltype | simpletext  |

    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on

    # Edit the unilabel instance.
    Then I should see "Edit content"
