<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick solarize operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_solarize",
 *   toolkit = "imagick",
 *   operation = "solarize",
 *   label = @Translation("Solarize"),
 *   description = @Translation("Solarizes an image.")
 * )
 */
class Solarize extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'threshold' => array(
        'description' => 'The threshold of the solarize effect.',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $resource->solarizeImage($arguments['threshold']);
  }

}
