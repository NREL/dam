<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Drupal\Core\ImageToolkit\ImageToolkitOperationBase;

abstract class ImagickOperationBase extends ImageToolkitOperationBase {

  /**
   * The correctly typed image toolkit for GD operations.
   *
   * @return \Drupal\imagick\Plugin\ImageToolkit\ImagickToolkit
   */
  protected function getToolkit() {
    return parent::getToolkit();
  }

}
