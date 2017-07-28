********************************************************************
DRUPAL MODULE
********************************************************************
	Name: PlUpload Gallery
	Author: Kent Shelley
	Sponsor: Dermpedia [www.dermpedia.org]
	Drupal: 8.0.x
********************************************************************
DESCRIPTION:

    A module which uses the Plupload jquery library to enable multiple simultaneous
         upload of images into a gallery
    Two different gallery configurations are possible:
    1.  Where the images are added to an image field on the entity (Field Gallery)
    2.  Where the images are added as separate entities via entity reference field on the entity (Entity Gallery)


********************************************************************
INSTALLATION:

      Dependencies: plupload and plupload_widget

	1. Place the plupload_gallery directory into your Drupal
        modules/directory.

	2. Enable the plupload_gallery module by navigating to:

	   Administer > Modules

	Click the 'Save configuration' button at the bottom to commit your
    changes.

********************************************************************
DETAILS:

  This module creates three new tabs on an entity when you use a plupload gallery widget
  1.  Upload Files - Here you upload multiple files into your field
  2.  Manage Files - Here you can edit the files already uploaded to the field
  3.  Gallery - Here you display the images in a suitable gallery

  The module creates a gallery in a similar manner to the node_gallery module

********************************************************************
CONFIGURATION:

  source entity = the entity in which you display the gallery
  target entity = the entity you reference to from the source entity
  (applies only to Entity Gallery)

  Both Field Gallery and Entity Gallery

  1. In the source entity create a form mode to use for managing images tab.

  2. In the source entity create a view mode to use for the gallery tab

  Field Gallery

	1. Create an image field in an entity to use for your gallery.
	   Set allowed number of values to unlimited

	2. Create a form mode to use with Upload Files tab.  Add it to your source entity Manage Form Display
	   This should use the plupload gallery widget and only show your image field.

	3. Add the image field and only the image field to the form mode for the managing images tab.

	4. Add the image field and only the image field to the view mode for the gallery tab.
	   Configure to display nicely in a gallery, eg use https://www.drupal.org/project/galleryformatter

	5. Add the Plupload Gallery Widget to the image field in the new form mode and configure as follows:
	   a) Select the form mode for the uploads tab
	   b) Select the form mode for the manage tab
	   c) Select the view mode for the gallery tab
	   d) Other settings as desired
	   SAVE

  Entity Gallery

	1. Create an image entity type and add an image field to use for your gallery.
	   This will be the target entity

	2. Create an entity reference field in the source entity that references the target entity type.
	   Only select ONE entity type bundle.  The module cannot support multiple target bundles.
	   Set allowed number of values to unlimited

	3. Create a form mode to use with Upload Files tab.  Add it to your source entity Manage Form Display
	   This should use the plupload gallery widget and only show your image field.

	4. Add the image field and only the image field to the form mode for the managing images tab.

	5. Add the image field and only the image field to the view mode for the gallery tab.
	   Configure to display nicely in a gallery, eg use https://www.drupal.org/project/views_field_formatter
	   with https://www.drupal.org/project/views_slideshow

	4. Add the Plupload Gallery Entity Reference widget and configure the entity reference field in the new form mode as
	follows:
	   a) Set the field in the target entity that has the images that you wish to use for the gallery
	   b) Set any other fields you wish to provide values for at time of upload.
	      Note that all target entities created will have the same value for that field
	      This is useful if you are doing a bulk upload of images of type = clinical for instance
	   c) Select the form mode for the uploads tab
	   d) Select the form mode for the manage tab
	   e) Select the view mode for the gallery tab
	   f) Other settings as desired
	   SAVE

  Result

  1. The plupload field should now be available in the Uploads tab
  2. The image or entity reference fields should be available in the Manage tab
  3. The gallery should be viewable in the Gallery tab


