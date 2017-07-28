<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Drupal\imagick\ImagickConst;
use Imagick;

/**
 * Defines imagick convert operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_convert",
 *   toolkit = "imagick",
 *   operation = "convert",
 *   label = @Translation("Convert"),
 *   description = @Translation("Converts image's filetype and quality")
 * )
 */
class Convert extends ImagickOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'format' => array(
        'description' => 'Image format.',
      ),
      'quality' => array(
        'description' => 'Image quality.',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(array $arguments = array()) {
    /* @var $resource \Imagick */
    $resource = $this->getToolkit()->getResource();
    $formats = ImagickConst::imagick_file_formats();

    $format = $arguments['format'];
    $quality = $arguments['quality'];

    // Set a white background color when converting to JPG because this file
    // format does not support transparency
    if ($format == 'image/jpeg') {
      $background = new Imagick();
      $background->newImage($resource->getImageWidth(), $resource->getImageHeight(), 'white');

      $resource->compositeImage($background, Imagick::COMPOSITE_DSTOVER, 0, 0);
    }

    $resource->setImageFormat($formats[$format]);
    $resource->setImageProperty('quality', (int) $quality);

    return $resource;
  }

}
