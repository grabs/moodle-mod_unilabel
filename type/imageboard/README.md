About this subplugin

This subplugin for the moodle plugin unilabel is based on the code of the subplugin grid by Andres Grabs.
The code was copied and than adapted to be able to position images on a canvas instead of positioning them in grid format like the grid plugin.

![grafik](https://github.com/andreasschenkel/moodle-unilabeltype_imageboard/assets/31856043/eea34642-5717-49d0-bb4d-14b4690d8a78)

Form:  
![grafik](https://github.com/andreasschenkel/moodle-unilabeltype_imageboard/assets/31856043/9e6f8bbb-fd5f-4653-9085-9c5fe80bd6d5)

CHANGELOG

[v3.0.1] 2023101500:

- move duplicate code to resize function 
- Make the imageboard_container just 20px larger than the imageboard.
- If targetwidth is not 0 then width of title is targetwidth of image.

[v3.0.0] 2023101400:

- Hide scrollbars
- codeformatings in template
- only resize imageboard and NOT the buttons
- optimize some margings and paddings during resizing


2023092700:

- "better support of xaxis and yaxis in helpergrid
- changes in form: grouping xposition and yposition
- changes in form: grouping width and height"


2023092501:
-  backgroundcolor for title


2023092400: 
- do not use dummy as variablename
- some csschanges for controllbuttons, space below controllbuttons, imagegrid with coordinates 
- switch helpergrid from class to id, add coordinates to the grid
- remove unused files 
- fix: hardcoded langstring in mustache
- hardcoded labelś for buttons, position and size values are in side by side in form, some refactoring of helpergrid
- Add fontsize setting for title of images
- Sorting langstings in languagfile

2023092500:
- titlebacgroundcolor