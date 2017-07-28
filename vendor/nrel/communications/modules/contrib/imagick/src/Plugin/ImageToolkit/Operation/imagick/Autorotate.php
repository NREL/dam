<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;
use ImagickPixel;

/**
 * Defines imagick autorotate operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_autorotate",
 *   toolkit = "imagick",
 *   operation = "autorotate",
 *   label = @Translation("Autorotate"),
 *   description = @Translation("Autorotates an image using EXIF data.")
 * )
 */
class Autorotate extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $orientation = $resource->getImageOrientation();

    switch ($orientation) {
      case Imagick::ORIENTATION_BOTTOMRIGHT:
        $resource->rotateimage(new ImagickPixel(), 180); // rotate 180 degrees
        break;
      case Imagick::ORIENTATION_RIGHTTOP:
        $resource->rotateimage(new ImagickPixel(), 90); // rotate 90 degrees CW
        break;
      case Imagick::ORIENTATION_LEFTBOTTOM:
        $resource->rotateimage(new ImagickPixel(), -90); // rotate 90 degrees CCW
        break;
    }

    // Now that it's auto-rotated, make sure the EXIF data is correct in case the EXIF gets saved with the image!
    $resource->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
  }

}
