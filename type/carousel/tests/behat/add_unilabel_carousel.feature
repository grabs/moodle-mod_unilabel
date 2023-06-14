@mod @mod_unilabel @unilabeltype_carousel
Feature: Modify content of the unilabeltype carousel

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
      | active | 1 | unilabeltype_carousel |

  @javascript @_file_upload
  Scenario: Add a unilabel as carousel with autorun active
    # Set up a unilabel.
    Given the following "activity" exists:
      | activity     | unilabel    |
      | course       | C1          |
      | idnumber     | mh1         |
      | name         | Testlabel   |
      | intro        | Hello label |
      | section      | 1           |
      | unilabeltype | carousel    |

    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    # Edit the unilabel instance.
    And I should see "Edit content"
    And I click on "Edit content" "link" in the "#section-1" "css_element"
    # Open the Carousel section.
    And I click on "#id_unilabeltype_carousel_hdr div.ftoggler > a" "css_element"
    # Activate autorun.
    And I should see "Run automatically"
    And I set the field "Run automatically" to "1"
    # Fill in the value 2 for interval.
    And I should see "Interval"
    And I click on "select#id_unilabeltype_carousel_carouselinterval" "css_element"
    And I click on "select#id_unilabeltype_carousel_carouselinterval option[value=\"2\"]" "css_element"
    # Select the value 200 from the height selection.
    And I should see "Height"
    And I click on "select#id_unilabeltype_carousel_height" "css_element"
    And I click on "select#id_unilabeltype_carousel_height option[value=\"200\"]" "css_element"
    # Fill in the color value for the background.
    And I should see "Background"
    And I set the field "unilabeltype_carousel_background" to "#0000FF"
    # Set the Caption for the first two slides to Slide-1 and Slide-2.
    # Both are defined by the css-id id_unilabeltype_carousel_slidehdr_0 and ..._1.
    # Click on the toggle "Slide-1" and open the element
    And I click on "#id_unilabeltype_carousel_slidehdr_0 div.ftoggler > a" "css_element"
    And I should see "Caption-1"
    And I set the field "unilabeltype_carousel_caption[0][text]" to "Slide-1"
    And I upload "mod/unilabel/tests/fixtures/gradient-blue.png" file to "Image-1" filemanager
    # Click on the toggle "Slide-2" and open the element
    And I click on "#id_unilabeltype_carousel_slidehdr_1 div.ftoggler > a" "css_element"
    And I should see "Caption-2"
    And I set the field "unilabeltype_carousel_caption[1][text]" to "Slide-2"
    # And I upload "mod/unilabel/tests/fixtures/gradient-red.png" file to "Image-2" filemanager
    # Save the changes.
    And I press "Save changes"

    # See that the second slide is shown.
    Then I wait "3" seconds
    And I should see "Slide-2"

  @javascript @_file_upload
  Scenario: Add a unilabel as carousel with autorun not active
    # Set up a unilabel.
    Given the following "activity" exists:
      | activity     | unilabel    |
      | course       | C1          |
      | idnumber     | mh1         |
      | name         | Testlabel   |
      | intro        | Hello label |
      | section      | 1           |
      | unilabeltype | carousel    |

    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    # Edit the unilabel instance.
    And I should see "Edit content"
    And I click on "Edit content" "link" in the "#section-1" "css_element"
    # Open the Carousel section.
    And I click on "#id_unilabeltype_carousel_hdr div.ftoggler > a" "css_element"
    # Dectivate autorun.
    And I should see "Run automatically"
    And I set the field "Run automatically" to "0"
    # Check there is no interval setting.
    And I should not see "Interval"
    # Select the value 200 from the height selection.
    And I should see "Height"
    And I click on "select#id_unilabeltype_carousel_height" "css_element"
    And I click on "select#id_unilabeltype_carousel_height option[value=\"200\"]" "css_element"
    # Fill in the color value for the background.
    And I should see "Background"
    And I set the field "unilabeltype_carousel_background" to "#0000FF"
    # Set the Caption for the first two slides to Slide-1 and Slide-2.
    # Both are defined by the css-id id_unilabeltype_carousel_slidehdr_0 and ..._1.
    # Click on the toggle "Slide-1" and open the element
    And I click on "#id_unilabeltype_carousel_slidehdr_0 div.ftoggler > a" "css_element"
    And I should see "Caption-1"
    And I set the field "unilabeltype_carousel_caption[0][text]" to "Slide-1"
    And I upload "mod/unilabel/tests/fixtures/gradient-blue.png" file to "Image-1" filemanager
    # Click on the toggle "Slide-2" and open the element
    And I click on "#id_unilabeltype_carousel_slidehdr_1 div.ftoggler > a" "css_element"
    And I should see "Caption-2"
    And I set the field "unilabeltype_carousel_caption[1][text]" to "Slide-2"
    # And I upload "mod/unilabel/tests/fixtures/gradient-red.png" file to "Image-2" filemanager
    # Save the changes.
    And I press "Save changes"

    # See that the second slide is shown.
    Then I wait "3" seconds
    And I should not see "Slide-2"
    And I click on "div.unilabeltype_carousel.carousel.slide a.carousel-control-next" "css_element"
    And I wait "2" seconds
    And I should see "Slide-2"

  @javascript @_file_upload
  Scenario: Add more slides to the content
    # Set up a unilabel.
    Given the following "activity" exists:
      | activity     | unilabel    |
      | course       | C1          |
      | idnumber     | mh1         |
      | name         | Testlabel   |
      | intro        | Hello label |
      | section      | 1           |
      | unilabeltype | carousel    |

    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    # Edit the unilabel instance.
    And I should see "Edit content"
    And I click on "Edit content" "link" in the "#section-1" "css_element"
    # Open the Carousel section.
    And I click on "#id_unilabeltype_carousel_hdr div.ftoggler > a" "css_element"
    # Activate autorun.
    And I should see "Run automatically"
    And I set the field "Run automatically" to "1"
    # Fill in the value 2 for interval.
    And I should see "Interval"
    And I click on "select#id_unilabeltype_carousel_carouselinterval" "css_element"
    And I click on "select#id_unilabeltype_carousel_carouselinterval option[value=\"2\"]" "css_element"
    # Select the value 200 from the height selection.
    And I should see "Height"
    And I click on "select#id_unilabeltype_carousel_height" "css_element"
    And I click on "select#id_unilabeltype_carousel_height option[value=\"200\"]" "css_element"
    # Fill in the color value for the background.
    And I should see "Background"
    And I set the field "unilabeltype_carousel_background" to "#0000FF"
    # Set the Caption for the first two slides to Slide-1 and Slide-2.
    # Both are defined by the css-id id_unilabeltype_carousel_slidehdr_0 and ..._1.
    # Click on the toggle "Slide-1" and open the element
    And I click on "#id_unilabeltype_carousel_slidehdr_0 div.ftoggler > a" "css_element"
    And I should see "Caption-1"
    And I set the field "unilabeltype_carousel_caption[0][text]" to "Slide-1"
    And I upload "mod/unilabel/tests/fixtures/gradient-blue.png" file to "Image-1" filemanager
    # Click on the toggle "Slide-2" and open the element
    And I click on "#id_unilabeltype_carousel_slidehdr_1 div.ftoggler > a" "css_element"
    And I should see "Caption-2"
    And I set the field "unilabeltype_carousel_caption[1][text]" to "Slide-2"
    And I press "Add more slides"

    Then I should see "Slide-4"
    And I should see "Slide-5"
    And I should see "Slide-6"

  @javascript @_file_upload
  Scenario: Pick a url from activity picker
    # Set up a unilabel and two pages.
    Given the following "activity" exists:
      | activity     | unilabel    |
      | course       | C1          |
      | idnumber     | mh1         |
      | name         | Testlabel   |
      | intro        | Hello label |
      | section      | 1           |
      | unilabeltype | carousel    |
    And the following "activities" exist:
      | activity | name       | intro      | course | idnumber |
      | page     | PageName1  | PageDesc1  | C1     | PAGE1    |
      | page     | PageName2  | PageDesc2  | C1     | PAGE2    |

    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    # Edit the unilabel instance.
    And I should see "Edit content"
    And I click on "Edit content" "link" in the "#section-1" "css_element"
    # Open the Slide-1 section.
    And I click on "#id_unilabeltype_carousel_slidehdr_0 div.ftoggler > a" "css_element"
    # Open the modal with the activity picker.
    And I should see "Choose url from activity..."
    And I click on "Choose url from activity..." "link" in the "#fitem_id_unilabeltype_carousel_activitypickerbutton_0" "css_element"
    And I wait "1" seconds
    And I should see "PageName1"
    And I should see "PageName2"
    # Click on a page link to insert its url into the url element in the formular.
    And I click on "PageName1" "link" in the "#unilabel-activity-picker-list" "css_element"
    And the focused element is "Url-1" "field"
    Then the "value" attribute of "Url-1" "field" should contain "mod/page/view.php"
