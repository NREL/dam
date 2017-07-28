<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick inverse operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_inverse",
 *   toolkit = "imagick",
 *   operation = "inverse",
 *   label = @Translation("Inverse"),
 *   description = @Translation("Inverses the image's colors")
 * )
 */
class Inverse extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    return $resource->negateImage(FALSE);
  }

}
