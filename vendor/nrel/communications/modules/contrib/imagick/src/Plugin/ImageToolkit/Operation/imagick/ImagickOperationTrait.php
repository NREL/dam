<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Drupal\imagick\ImagickException;
use Imagick;

/**
 * Class ImagickOperationTrait
 */
trait ImagickOperationTrait {

  /**
   * {@inheritdoc}
   */
  protected function execute(array $arguments) {
    /* @var $resource Imagick */
    $resource = $this->getToolkit()->getResource();

    // If preferred format is set, use it as prefix for writeImage
    // If not this will throw a ImagickException exception
    try {
      $image_format = strtolower($resource->getImageFormat());
    } catch (ImagickException $e) {}

    if (isset($image_format) && in_array($image_format, ['gif'])) {
      // Get each frame in the GIF
      $resource = $resource->coalesceImages();
      do {
        $this->process($resource, $arguments);
      } while ($resource->nextImage());

      $resource->deconstructImages();
    }
    else {
      $this->process($resource, $arguments);
    }

    // Set the processed resource
    $this->getToolkit()->setResource($resource);
  }

}
