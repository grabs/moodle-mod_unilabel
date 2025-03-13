@mod @mod_unilabel @unilabeltype_accordion
Feature: Modify content of the unilabeltype accordion

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
      | active | 1 | unilabeltype_accordion |

  @javascript
  Scenario: Add and delete segments to and from the accordion
    # Set up a unilabel.
    Given the following "activity" exists:
      | activity     | unilabel    |
      | course       | C1          |
      | idnumber     | mh1         |
      | name         | Testlabel   |
      | intro        | Hello label |
      | section      | 1           |
      | unilabeltype | accordion   |

    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on

    # Edit the unilabel instance.
    And I should see "Edit content"
    And I click on "Edit content" "link" in the "#section-1" "css_element"
    And I should see "Add element"

    # Add a new segment
    And I click on "Add element" "button"
    And I should see "Segment-1"

    # Click on the toggle "Segment-1" and open the element
    And I click on "#id_singleelementheader_0 div.ftoggler > a" "css_element"
    And I should see "Heading"
    And I should see "Content"

    # Fill out the heading for segment-1.
    And I set the field "unilabeltype_accordion_heading[0][text]" to "Heading-1"
    And I set the field "unilabeltype_accordion_content[0][text]" to "Content-1"

    # Click on the toggle "Segment-1" and close the element and add another segment
    And I click on "#id_singleelementheader_0 div.ftoggler > a" "css_element"
    And I click on "Add element" "button"
    And I should see "Segment-2"

    # Click on the toggle "Segment-2" and open the element
    And I click on "#id_singleelementheader_1 div.ftoggler > a" "css_element"
    And I should see "Heading"
    And I should see "Content"

    # Fill out the heading for segment-2.
    And I set the field "unilabeltype_accordion_heading[1][text]" to "Heading-2"
    And I set the field "unilabeltype_accordion_content[1][text]" to "Content-2"

    # Click on the toggle "Segment-2" and close the element and add another segment
    And I click on "#id_singleelementheader_1 div.ftoggler > a" "css_element"
    And I click on "Add element" "button"
    And I should see "Segment-3"

    # Click on the toggle "Segment-3" and open the element
    And I click on "#id_singleelementheader_2 div.ftoggler > a" "css_element"
    And I should see "Heading"
    And I should see "Content"

    # Fill out the heading for segment-3.
    And I set the field "unilabeltype_accordion_heading[2][text]" to "Heading-3"
    And I set the field "unilabeltype_accordion_content[2][text]" to "Content-3"

    # Save the changes.
    And I press "Save changes"

    # See that the segments are shown.
    Then I should see "Heading-1"
    And I should see "Heading-2"
    And I should see "Heading-3"
    # Click on the first header to open the first segment.
    And I click on "Heading-1" "button" in the "#section-1 .unilabel-content .accordion" "css_element"
    And I should see "Content-1"
    # Click on the second header to open the second segment and close the first one.
    And I click on "Heading-2" "button" in the "#section-1 .unilabel-content .accordion" "css_element"
    And I should see "Content-2"
    And I should not see "Content-1"
    # Click on the third header to open the third segment and close the second one.
    And I click on "Heading-3" "button" in the "#section-1 .unilabel-content .accordion" "css_element"
    And I should see "Content-3"
    And I should not see "Content-1"
    And I should not see "Content-2"

    # Deleting a segment
    And I click on "Edit content" "link" in the "#section-1" "css_element"
    And I click on "Delete element-1" "button"
    And I should see "Confirm"
    And I should see "Delete element 1"
    And I click on "Yes" "button"
    And I should not see "Segment-1"
    And I press "Save changes"

    Then I should not see "Heading-1"
    And I should see "Heading-2"
    And I should see "Heading-3"
