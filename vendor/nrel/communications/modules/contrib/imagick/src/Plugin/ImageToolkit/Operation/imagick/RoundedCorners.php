<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick rounded corners operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_rounded_corners",
 *   toolkit = "imagick",
 *   operation = "rounded_corners",
 *   label = @Translation("Rounded corners"),
 *   description = @Translation("Adds rounded corners to the image.")
 * )
 */
class RoundedCorners extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'x_rounding' => array(
        'description' => 'The x rounding of the corners.',
      ),
      'y_rounding' => array(
        'description' => 'The y rounding of the corners.',
      ),
      'stroke_width' => array(
        'description' => 'The stroke width of the corners.',
      ),
      'displace' => array(
        'description' => 'The displace of the corners.',
      ),
      'size_correction' => array(
        'description' => 'The size correction of the corners.',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $resource->roundCorners($arguments['x_rounding'], $arguments['y_rounding'], $arguments['stroke_width'], $arguments['displace'], $arguments['size_correction']);
  }

}
