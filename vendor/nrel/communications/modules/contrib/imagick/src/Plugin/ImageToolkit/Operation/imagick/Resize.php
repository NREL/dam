<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Drupal\system\Plugin\ImageToolkit\Operation\gd\Resize as GdResize;
use Imagick;

/**
 * Defines imagick resize operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_resize",
 *   toolkit = "imagick",
 *   operation = "resize",
 *   label = @Translation("Resize"),
 *   description = @Translation("Resizes an image to the given dimensions (ignoring aspect ratio).")
 * )
 */
class Resize extends GdResize {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $filter = \Drupal::config('imagick.config')
      ->get('resize_filter');

    if ($filter == -1) {
      $resource->scaleImage($arguments['width'], $arguments['height']);
    }
    else {
      $resource->resizeImage($arguments['width'], $arguments['height'], $filter, 1);
    }
  }

}
