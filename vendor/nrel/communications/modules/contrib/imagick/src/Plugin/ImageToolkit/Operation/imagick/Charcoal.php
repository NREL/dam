<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick charcoal operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_charcoal",
 *   toolkit = "imagick",
 *   operation = "charcoal",
 *   label = @Translation("Charcoal"),
 *   description = @Translation("Applies the charcoal effect on an image")
 * )
 */
class Charcoal extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'radius' => array(
        'description' => 'The radius of the charcoal effect.',
      ),
      'sigma' => array(
        'description' => 'The sigma of the charcoal effect.',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    return $resource->charcoalImage($arguments['radius'], $arguments['sigma']);
  }

}
