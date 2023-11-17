@mod @mod_unilabel @unilabeltype @unilabeltype_imageboard
Feature: Modify content of the unilabeltype imageboard

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
    And the following config values are set as admin:
      | active  | 1   | unilabeltype_imageboard |
      | columns | 4   | unilabeltype_imageboard |

  @javascript @_file_upload
  Scenario: Add content to the unilabel as imageboard
    # Set up a unilabel.
    Given the following "activity" exists:
      | activity     | unilabel    |
      | course       | C1          |
      | idnumber     | mh1         |
      | name         | Testlabel   |
      | intro        | Hello label |
      | section      | 1           |
      | unilabeltype | imageboard  |

    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    # Edit the unilabel instance.
    And I should see "Edit content"
    And I click on "Edit content" "link" in the "#section-1" "css_element"
    # Open the imageboard section.
    And I click on "#id_unilabeltype_imageboard_hdr div.ftoggler > a" "css_element"
    # Activate autorun.
    And I should see "Witdh of canvas"
    And I set the field "Witdh of canvas" to "400"

    And I should see "Height of canvas"
    And I set the field "Height of canvas" to "300"

    And I should see "Backgroundimage"

    # Set the Caption for the four images.
    # Both are defined by the css-id id_unilabeltype_imageboard_imghdr_0 and ..._1.
    # Click on the toggle "Image-1" and open the element
    And I click on "#id_unilabeltype_imageboard_imagehdr_0 div.ftoggler > a" "css_element"
    And I should see "Title-1"
    And I set the field "Title-1" to "Title-Element-1"

    # Save the changes.
    And I press "Save changes"

# There are comming more features and steps.
