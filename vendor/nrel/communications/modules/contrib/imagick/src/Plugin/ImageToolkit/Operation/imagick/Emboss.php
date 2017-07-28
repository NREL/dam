<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick emboss operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_emboss",
 *   toolkit = "imagick",
 *   operation = "emboss",
 *   label = @Translation("Emboss"),
 *   description = @Translation("Applies the emboss effect on an image")
 * )
 */
class Emboss extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'radius' => array(
        'description' => 'The radius of the emboss effect.',
      ),
      'sigma' => array(
        'description' => 'The sigma of the emboss effect.',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $resource->embossImage($arguments['radius'], $arguments['sigma']);
  }

}
