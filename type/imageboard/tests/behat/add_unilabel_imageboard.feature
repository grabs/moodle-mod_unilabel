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

    # Set the base settings
    And I should see "Canvas width"
    And I should see "Canvas height"

    And I set the field "Canvas width" to "400"
    And I set the field "Canvas height" to "300"
    And I should see "Backgroundimage"
    And I upload "mod/unilabel/tests/fixtures/gradient-green.png" file to "Backgroundimage" filemanager

    # I couldn't make it to upload more than one file in a behat step, so I press save and edit it again
    # to upload another image.

    # Save the changes.
    And I press "Save changes"

    And I should see "Edit content"
    And I click on "Edit content" "link" in the "#section-1" "css_element"

    # Adding two images

    And I click on "Add element" "button"
    And I should see "Image-1"
    # Set the values and image for elements.
    # Click on the toggle "Image-1" and open the element
    And I click on "#id_singleelementheader_0 div.ftoggler > a" "css_element"
    And I set the field "Title-1" to "Title-Element-1"
    And I upload "mod/unilabel/tests/fixtures/gradient-blue.png" file to "Image-1" filemanager
    And I set the field with xpath "//input[@type='text' and @name='unilabeltype_imageboard_xposition[0]']" to "100"
    And I set the field "unilabeltype_imageboard_xposition[0]" to "100"
    And I set the field "unilabeltype_imageboard_yposition[0]" to "100"
    And I set the field "unilabeltype_imageboard_targetwidth[0]" to "100"
    And I set the field "unilabeltype_imageboard_border[0]" to "2"

    # Save the changes.
    And I press "Save changes"
    And I should see "Title-Element-1"

    And I should see "Edit content"
    And I click on "Edit content" "link" in the "#section-1" "css_element"

    And I click on "Add element" "button"
    And I should see "Image-2"
    # Click on the toggle "Image-2" and open the element
    And I click on "#id_singleelementheader_1 div.ftoggler > a" "css_element"
    And I set the field "Title-2" to "Title-Element-2"
    And I upload "mod/unilabel/tests/fixtures/gradient-red.png" file to "Image-2" filemanager
    And I set the field "unilabeltype_imageboard_xposition[1]" to "300"
    And I set the field "unilabeltype_imageboard_yposition[1]" to "100"
    And I set the field "unilabeltype_imageboard_targetwidth[1]" to "100"
    And I set the field "unilabeltype_imageboard_border[1]" to "2"

    # Save the changes.
    And I press "Save changes"
    And I should see "Title-Element-2"

# There are comming more features and steps.
