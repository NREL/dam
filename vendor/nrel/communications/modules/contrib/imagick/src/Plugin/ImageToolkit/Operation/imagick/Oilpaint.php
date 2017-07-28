<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick oilpaint operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_oilpaint",
 *   toolkit = "imagick",
 *   operation = "oilpaint",
 *   label = @Translation("Oilpaint"),
 *   description = @Translation("Oilpaints the image.")
 * )
 */
class Oilpaint extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'radius' => array(
        'description' => 'The threshold of the oilpaint effect.',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $resource->oilPaintImage($arguments['radius']);
  }

}
