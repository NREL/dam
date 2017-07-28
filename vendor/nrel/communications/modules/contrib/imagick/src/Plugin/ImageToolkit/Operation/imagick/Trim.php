<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick trim operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_trim",
 *   toolkit = "imagick",
 *   operation = "trim",
 *   label = @Translation("Trim"),
 *   description = @Translation("Remove edges that are the background color from the image.")
 * )
 */
class Trim extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'fuzz' => array(
        'description' => 'The fuzz tolerance.',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $resource->trimImage($arguments['fuzz']);

    // Reset image dimensions
    $dimensions = $resource->getImageGeometry();
    $resource->setImagePage($dimensions['width'], $dimensions['height'], 0, 0);
  }

}
