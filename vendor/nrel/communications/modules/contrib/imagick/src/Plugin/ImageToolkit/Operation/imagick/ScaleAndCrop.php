<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Drupal\system\Plugin\ImageToolkit\Operation\gd\ScaleAndCrop as GdScaleAndCrop;


/**
 * Defines imagick scale and crop operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_scale_and_crop",
 *   toolkit = "imagick",
 *   operation = "scale_and_crop",
 *   label = @Translation("Scale and crop"),
 *   description = @Translation("Scales and crops an image")
 * )
 */
class ScaleAndCrop extends GdScaleAndCrop {

}
