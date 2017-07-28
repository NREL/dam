ABOUT

Provides integration between Slick carousel and Media entity. Slick media allows
richer slideshow/carousel contents with a mix of text, image and video.

This provides a Slick media formatter for the supported Media entities:
  o Media entity image
  o Media entity embeddable video


REQUIREMENTS
[1] http://dgo.to/slick
[2] http://dgo.to/media_entity_image
[3] http://dgo.to/media_entity_embeddable_video

The last 2 modules depend on Media entity.


INSTALLATION
Install the module as usual, more info can be found on:
http://drupal.org/documentation/install/modules-themes/modules-7

Enable Slick media module under "Slick" package:
/admin/modules#edit-modules-slick

USAGE / CONFIGURATION
- Enable this module and its dependencies mentioned above.

- At admin/config/people/accounts/fields, Content types or any fieldable entity,
  -- click "Manage display".

- Under "Format", choose "Slick media" for Media entity reference field, and
  click the "Configure" icon.
  Adjust formatter options accordingly, including your optionset.
  Be sure a Media entity reference field is already added with the expected
  fields for title, caption, image and video. And they are not hidden at the
  chosen View mode.

The more complex is your slide, the more options are available.


OPTIONSET
To create your optionsets, go to:

"admin/config/media/slick"


SLIDE LAYOUT
The slide layout option depends on at least a skin selected. No skin, just DIY.
A Media entity is fieldable entity so you can add custom field to hold layout
options. While core image field supports several caption placements/ layout that
affect the entire slides, the fieldable entity may have unique layout per slide
using a dedicated "List (text)" type with the following supported/pre-defined
keys:
top, right, bottom, left, center, below, e.g:

Option #1
---------

bottom|Caption bottom
top|Caption top
right|Caption right
left|Caption left
center|Caption center
center-top|Caption center top
below|Caption below the slide


Option #2
---------

If you have complex slide layout via Media entity with overlay video or images
within slide captions, also supported:

stage-right|Caption left, stage right
stage-left|Caption right, stage left


Option #3
---------

If you choose skin Split, additional layout options supported:

split-right|Caption left, stage right, split half
split-left|Caption right, stage left, split half


Split means image and caption are displayed side by side.

Specific to split layout, be sure to get consistent options (left and right)
per slide, and also choose optionset with skin Split to have a context per
slideshow. Otherwise layout per slideshow with reusable Media files will be
screwed up.

Except the "Caption below the slide" option, all is absolutely positioned aka
overlayed on top of the main slide image/ background for larger monitor.
Those layouts are ideally applied to large displays, not multiple small slides,
nor small carousels, except "Caption below the slide" which is reasonable with
small slides.


Option #4
---------

Merge all options as needed.


TROUBLESHOOTING
Be sure to first update Blazy and Slick prior to this module update.


AUTHOR/MAINTAINER/CREDITS
gausarts


READ MORE
See the project page on drupal.org: http://drupal.org/project/slick_media.

