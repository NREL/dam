<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick wave operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_wave",
 *   toolkit = "imagick",
 *   operation = "wave",
 *   label = @Translation("Wave"),
 *   description = @Translation("Adds a wave effect to an image.")
 * )
 */
class Wave extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'amplitude' => array(
        'description' => 'The amplitude of the wave.',
      ),
      'length' => array(
        'description' => 'The length of the wave.',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $resource->waveImage($arguments['amplitude'], $arguments['length']);
  }

}
