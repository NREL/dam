<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;
use ImagickDraw;

/**
 * Defines imagick oilpaint operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_polaroid",
 *   toolkit = "imagick",
 *   operation = "polaroid",
 *   label = @Translation("Polaroid"),
 *   description = @Translation("Adds a polaroid effect to the image.")
 * )
 */
class Polaroid extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'angle' => array(
        'description' => 'The angle of the polaroid effect.',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $angle = $arguments['angle'];
    // Generate a random angle when field is empty
    if (empty($angle)) {
      $angle = mt_rand(-30, 30);
    }

    $resource->polaroidImage(new ImagickDraw(), $angle);
  }

}
