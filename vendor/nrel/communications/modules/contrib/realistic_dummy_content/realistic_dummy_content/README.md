Every module can have a `./realistic_dummy_content` folder. Realistic Dummy
Content API will look for this folder to find content with which to replace
stock generated content for fields in entities of various bundles and types.

This folder contains replacement data to make generated elements more realistic.
It also can contain a "recipe" subfolder which contains a recipe for generating
dummy content (the order and number of each item to create). The only other
subfolder which is used currently is "fields", but one could imagine replacing
other data as well (menus, etc.).
[Check the issue queue or open an issue](https://drupal.org/project/issues/2253941?categories=All)
if you would like to work on that!
