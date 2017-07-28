<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;
use ImagickPixel;

/**
 * Defines imagick coloroverlay operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_colorshift",
 *   toolkit = "imagick",
 *   operation = "colorshift",
 *   label = @Translation("Colorshift"),
 *   description = @Translation("Applies a colorshift effect on an image")
 * )
 */
class Colorshift extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'HEX' => array(
        'description' => 'The color used to shift.',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $color = $arguments['HEX'];
    $color = empty($color) ? 'none' : $color;

    $resource->colorizeImage(new ImagickPixel($color), 1);
  }

}
