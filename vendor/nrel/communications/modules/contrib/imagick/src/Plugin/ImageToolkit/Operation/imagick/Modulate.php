<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick modulate operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_modulate",
 *   toolkit = "imagick",
 *   operation = "modulate",
 *   label = @Translation("Modulate"),
 *   description = @Translation("Modulates the image.")
 * )
 */
class Modulate extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'brightness' => array(
        'description' => 'Brightness in percentage.',
      ),
      'saturation' => array(
        'description' => 'Saturation in percentage.',
      ),
      'hue' => array(
        'description' => 'Hue in percentage.',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $resource->modulateImage($arguments['brightness'], $arguments['saturation'], $arguments['hue']);
  }

}
