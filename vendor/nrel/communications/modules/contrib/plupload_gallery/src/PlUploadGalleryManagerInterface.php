<?php

namespace Drupal\plupload_gallery;

use Drupal\node\NodeInterface;


/**
 * Provides an interface defining a plupload gallery manager
 */
interface PlUploadGalleryManagerInterface {

  /**
   * Determines if an entity has a plupload gallery widget
   *
   *
   *
   * @param \Drupal\node\NodeInterface $node
   *   The source entity
   *
   * @return bool
   *   TRUE if an entity has a widget, FALSE otherwise.
   */
  public function checkEntityHasWidget(NodeInterface $node);

}
