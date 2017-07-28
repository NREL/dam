<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick mirror operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_mirror",
 *   toolkit = "imagick",
 *   operation = "mirror",
 *   label = @Translation("Mirror"),
 *   description = @Translation("Mirrors the image.")
 * )
 */
class Mirror extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'flip' => array(
        'description' => 'Mirror image verticaly.',
      ),
      'flop' => array(
        'description' => 'Mirror image horizontaly.',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    if ($arguments['flip']) {
      $resource->flipImage();
    }
    if ($arguments['flop']) {
      $resource->flopImage();
    }
  }

}
