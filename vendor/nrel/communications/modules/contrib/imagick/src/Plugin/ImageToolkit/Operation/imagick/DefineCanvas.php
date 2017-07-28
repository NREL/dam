<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;
use ImagickPixel;

/**
 * Defines imagick define canvas operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_define_canvas",
 *   toolkit = "imagick",
 *   operation = "define_canvas",
 *   label = @Translation("Define canvas"),
 *   description = @Translation("Define the canvas of an image.")
 * )
 */
class DefineCanvas extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'HEX' => array(
        'description' => 'The color of the canvas',
      ),
      'under' => array(
        'description' => 'This will create a solid flat layer, probably totally obscuring the source image',
      ),
      'exact_measurements' => array(
        'description' => 'Do we have to use exact measurements',
      ),
      'exact' => array(
        'description' => 'Exact measurements',
      ),
      'relative' => array(
        'description' => 'Relative measurements',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick &$resource, array $arguments) {
    $color = $arguments['HEX'];
    $under = $arguments['under'];
    $exact_size = $arguments['exact_measurements'];
    $exact = $arguments['exact'];
    $relative = $arguments['relative'];

    $canvas = new Imagick();
    $canvas->setFormat('jpg');

    $color = empty($color) ? 'none' : $color;
    if ($exact_size) {
      $width = $this::imagick_percent_filter($exact['width'], $resource->getImageWidth());
      $height = $this::imagick_percent_filter($exact['height'], $resource->getImageHeight());

      list($x, $y) = explode('-', $exact['anchor']);
      $x = image_filter_keyword($x, $width, $resource->getImageWidth());
      $y = image_filter_keyword($y, $height, $resource->getImageHeight());
    }
    else {
      $width = $resource->getImageWidth() + $relative['leftdiff'] + $relative['rightdiff'];
      $height = $resource->getImageHeight() + $relative['topdiff'] + $relative['bottomdiff'];

      $x = $relative['leftdiff'];
      $y = $relative['topdiff'];
    }

    $canvas->newImage($width, $height, new ImagickPixel($color));
    if ($under) {
      $canvas->compositeImage($resource, Imagick::COMPOSITE_DEFAULT, $x, $y);
    }

    $resource = $canvas->clone();
  }

  /**
   * Helper function to calculate width from percentage
   */
  private function imagick_percent_filter($length_specification, $current_length) {
    if (strpos($length_specification, '%') !== FALSE) {
      $length_specification = $current_length !== NULL ? str_replace('%', '', $length_specification) * 0.01 * $current_length : NULL;
    }
    return $length_specification;
  }

}
