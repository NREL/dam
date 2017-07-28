<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Drupal\system\Plugin\ImageToolkit\Operation\gd\Desaturate as GdDesaturate;
use Imagick;

/**
 * Defines imagick desaturate operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_desaturate",
 *   toolkit = "imagick",
 *   operation = "desaturate",
 *   label = @Translation("desaturate"),
 *   description = @Translation("Desaturate an image.")
 * )
 */
class Desaturate extends GdDesaturate {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $resource->setImageType(Imagick::IMGTYPE_GRAYSCALEMATTE);
  }

}
