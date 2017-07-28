<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick noise operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_noise",
 *   toolkit = "imagick",
 *   operation = "noise",
 *   label = @Translation("Noise"),
 *   description = @Translation("Adds noise to the image.")
 * )
 */
class Noise extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'type' => array(
        'description' => 'The type of noise being used.',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $resource->addNoiseImage($arguments['type']);
  }

}
