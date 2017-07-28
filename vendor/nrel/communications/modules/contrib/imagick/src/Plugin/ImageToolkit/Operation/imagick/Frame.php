<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;
use ImagickPixel;

/**
 * Defines imagick frame operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_frame",
 *   toolkit = "imagick",
 *   operation = "frame",
 *   label = @Translation("Frame"),
 *   description = @Translation("Frames an image with a border.")
 * )
 */
class Frame extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'matte_color' => array(
        'description' => 'The string representing the matte color',
      ),
      'width' => array(
        'description' => 'The width of the border',
      ),
      'height' => array(
        'description' => 'The height of the border',
      ),
      'inner_bevel' => array(
        'description' => 'The angle of the blur',
      ),
      'outer_bevel' => array(
        'description' => 'The angle of the blur',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $color = new ImagickPixel($arguments['matte_color']);

    $resource->frameImage($color, $arguments['width'], $arguments['height'], $arguments['inner_bevel'], $arguments['outer_bevel']);
  }

}
