<?php

namespace Drupal\plupload_gallery\Access;

use Drupal\plupload_gallery\PlUploadGalleryManagerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\node\NodeInterface;

/**
 * Determines whether the node has a plupload gallery widget attached
 */
class PlUploadGalleryTabsAccess implements AccessInterface {

  /**
   * Book Manager Service.
   *
   * @var \Drupal\book\BookManagerInterface
   */
  protected $plupload_gallery_manager;

  /**
   * Constructs a BookNodeIsRemovableAccessCheck object.
   *
   * @param \Drupal\book\BookManagerInterface $book_manager
   *   Book Manager Service.
   */
  public function __construct(PlUploadGalleryManagerInterface $plupload_gallery_manager) {
    $this->plupload_gallery_manager = $plupload_gallery_manager;
  }

  /**
   * Checks access for removing the node from its book.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node requested to be removed from its book.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(NodeInterface $node) {
    //return TRUE;
    return AccessResult::allowedIf($this->plupload_gallery_manager->checkEntityHasWidget($node));
    // ->addCacheableDependency($node)
  }

}
