<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick swirl operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_swirl",
 *   toolkit = "imagick",
 *   operation = "swirl",
 *   label = @Translation("Swirl"),
 *   description = @Translation("Adds a swirl effect to an image.")
 * )
 */
class Swirl extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'degrees' => array(
        'description' => 'The amplitude of the wave effect.',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $resource->swirlImage($arguments['degrees']);
  }

}
