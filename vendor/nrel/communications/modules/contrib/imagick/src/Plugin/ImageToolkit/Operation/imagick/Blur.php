<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Drupal\imagick\ImagickConst;
use Imagick;

/**
 * Defines imagick blur operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_blur",
 *   toolkit = "imagick",
 *   operation = "blur",
 *   label = @Translation("Blur"),
 *   description = @Translation("Blurs an image, different methods can be used.")
 * )
 */
class Blur extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'type' => array(
        'description' => 'The type of blur used',
      ),
      'radius' => array(
        'description' => 'The radius of the Gaussian, in pixels, not counting the center pixel.',
      ),
      'sigma' => array(
        'description' => 'The standard deviation of the Gaussian, in pixels',
      ),
      'angle' => array(
        'description' => 'The angle of the blur',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    switch ($arguments['type']) {
      case ImagickConst::NORMAL_BLUR:
        $resource->blurImage($arguments['radius'], $arguments['sigma']);
        break;
      case ImagickConst::ADAPTIVE_BLUR:
        $resource->adaptiveBlurImage($arguments['radius'], $arguments['sigma']);
        break;
      case ImagickConst::GAUSSIAN_BLUR:
        $resource->gaussianBlurImage($arguments['radius'], $arguments['sigma']);
        break;
      case ImagickConst::MOTION_BLUR:
        $resource->motionBlurImage($arguments['radius'], $arguments['sigma'], $arguments['angle']);
        break;
      case ImagickConst::RADIAL_BLUR:
        $resource->radialBlurImage($arguments['angle']);
        break;
    }
  }

}
