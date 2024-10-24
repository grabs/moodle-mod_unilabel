## Release notes

### Release 4.4.1
* Type Collapsed text now uses the label name as title
* Fix deprecated code definition

### Release 4.4.0
* Apply new coding style rules
* Carousel caption has new settings for its style (#44)

### Release 4.3.4
* Optimize activity picker, so users can see whether or not the used link comes from an activity
* Carousel caption now can handle images
* Fix hyphenation for wrapping long words - Thanks to Florian Dagner (#37)
* Fix missing draftitemid in carousel

### Release 4.3.3
* Fix error while deleting elements in grid and carousel

### Release 4.3.2
* Fixed wrong Font Awesome in css
* Type collapsedtext now use Moodle css classes to show the carets for collapsed and expanded.
* Introduce Drag-and-Drop for element with items like "accordion", "carousel" and "grid"
* Introduce adding and removing of elements by ajax
* New option to open a url in a new window.
* Fix issue "Always show manual completion" (Thanks to Stefa Hanauska) (#39)

### Release 4.3.1
* mod_unilabel: prevent loading activities like inline folder (#36)

### Release 4.3.0
* Add the great new unilabel type imageboard from Andreas Schenkel (https://github.com/andreasschenkel/moodle-unilabeltype_imageboard)
* Apply new coding style rules
* Optimize colourpicker

### Release 4.2.5
* Fix conflict with mod folder (#36)

### Release 4.2.4
* Fix multiple availability infos (#34)

### Release 4.2.3
* Fix restore of internal urls in grid and carousel
* Add activity picker to choose an activity url in grid and carousel (Thanks to Andreas Schenkel for this really good idea!)

### Release 4.2.2
* Fix new Font Awesome caret in collapsed text

### Release 4.2.1
* Compatibility to Font Awesome 6 Free

### Release 4.2.0
* Compatibility to Moodle 4.2

### Version 2022042000 Release 4.1.1 (Build: 2022122400)
* Add cmid to "uniqid" to avoid conflicts with multiple instances

### Version 2022042000 Release 4.1.0 (Build: 2022113000)
* Compatibility improvements for Moodle 4.0 and 4.1
* New amd modules for better handling cascaded elements in topicteaser.
* Topicteaser now loads the content of a topic asynchronously.
* Small fixes on coding style and behat testing

### Version 2022041601 Release 4.0.2 (Build: 2022050900)
* MBS-6386 (Stefan Hanuska): Fix redirect in view.php

### Version 2022041601
* Compatibility to Moodle 4

### Version 2022030200
* Integrated a new type "Accordion" (Thanks to Stefan Hanuska)
* Add phpunit test and behat acceptance test to unilabeltype_accordion

### Version 2022012202
* MBS-6151 (Stefan Hanuska): Make adding new slides consistent to grid
* Add phpunit test and behat acceptance test
* Add github actions as replacement for travis ci

### Version 2022012201
* MBS-6088 (Stefan Hanauska): Fix behaviour for slides without images in type carousel and fix alt-attribute containing in type carousel

### Version 2022012200
* All types using carousel have a new option to run the carousel automatically or not.

### Version 2021052300
* The internal name can now be edited.

### Version 2020110703
* moved fivecolumns css to styles.css
* small fixes in coding style

### Version 2020061003
* fix bug with carousel nav buttons overlapping the nav drawer

### Version 2020061002
* fix bug with modal started from another modal e.g. format_grid

### Version 2020061001
* fix indentation bug

### Version 2020061000
* fix referenced bootstrap javascript pointing to the new postion in Moodle 3.9

### Version 2020022900
* fix output of images without img source in unilabletype_grid.
* add new options to define count of columns for normal, middle and small devices separately

### Version 2019050900
* set default capability mod/unilabel:edit for managers to allow
* add new option to define the carousel interval for course and topic teaser per instance
* fix small typos

### Version 2019030703
* add capability check while defining course and topic teaser

### Version 2019030702
* missing field "column" in backup for course and topic teaser
* optimize phpdoc comments

### Version 2019030701
* no features but optimized code

### Version 2019030700
* Add option to define the columns for grid representation in course and topic teaser
* Change behaviour. If the unilabel shows a topic teaser of its own course it will ignore the topic
it is in it by its self.
* Clean the code to respect the moodle code guidelines

### Version 2019020901
* fix typo which stopped working the carousel buttons

### Version 2019020900
* respecting the new class "core_course_list_element"
* add setting options to activate or deactivate content types
* add setting options for carousel types to choose different carousel buttons
* add a litle javascript to ensure the start of the carousel
