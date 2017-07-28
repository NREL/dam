<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick encipher operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_encipher",
 *   toolkit = "imagick",
 *   operation = "encipher",
 *   label = @Translation("Encipher"),
 *   description = @Translation("Applies the encipher effect on an image")
 * )
 */
class Encipher extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'password' => array(
        'description' => 'The password to encrypt the image with.',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    return $resource->encipherImage($arguments['password']);
  }

}
