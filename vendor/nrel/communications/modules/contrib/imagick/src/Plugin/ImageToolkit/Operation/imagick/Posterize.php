<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick solarize operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_posterize",
 *   toolkit = "imagick",
 *   operation = "posterize",
 *   label = @Translation("Posterize"),
 *   description = @Translation("Posterizes an image.")
 * )
 */
class Posterize extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'colors' => array(
        'description' => 'Color levels per channel.',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $resource->posterizeImage($arguments['colors'], TRUE);
  }

}
