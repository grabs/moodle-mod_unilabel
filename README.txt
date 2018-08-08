General
=======
The Moodle plugin "mod_unilabel" enables you to include some nice formated text on the course- or frontpage.
There are 5 different content types included (extendable sub plugins):
- Simple text
- Carousel
- Collapsed text
- Course teaser
- Topic teaser

Installation
============
Copy all files into the folder "mod/unilabel" inside your moodle installation.
Run the installation process in moodle.
You can find more detailes to the installation process here: https://docs.moodle.org/35/en/Installing_plugins#Installing_a_plugin

Usage
=====
The configuration consists of two steps (except the "Simple text" type).
1) The creation of a new instance by using the activity chooser.
2) The configuration of the content depending on the content type you chose in the first step.

Description of the content types
================================

Simple text
-----------
This content type just show the label as you already know

Carousel
--------
In this content type you can define a series of images.
Each image is shown in a slide show.
You can also define a caption to each image that is show inside the slide item.
For each image you can define a url what makes the image to a clickable button.
The carousel is by default responsive to different screen sizes.
To optimize the responsivity to each of the images you can assigne a mobile optimized image.
This mobile image is shown on small devices smaller than 768 px.

Collapsed text
--------------
This content type offers you two options:
1) a folded content
2) a modal dialog containing the content.
Both types can be used with or without animation

Course teaser
-------------
Mainly intended to show on the frontpage it shows the titles and images of selected courses.
The presentation can be a carousel or a grid.
Each Item is a clickable button that brings the user to the related course.

Topic teaser
------------
Mainly intended to show on the frontpage it shows the description of topics of a selected course.
The topics will be shown as carousel or as grid.
If you click on such a shown topic a modal dialog shows the topic content.

