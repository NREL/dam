<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick edge operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_edge",
 *   toolkit = "imagick",
 *   operation = "edge",
 *   label = @Translation("Edge"),
 *   description = @Translation("Applies the edge effect on an image.")
 * )
 */
class Edge extends ImagickOperationBase {

  use ImagickOperationTrait;

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'radius' => array(
        'description' => 'The radius of the edge operation.',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $resource->edgeImage($arguments['radius']);
  }

}
