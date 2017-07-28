<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

/**
 * Defines imagick scale operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_scale",
 *   toolkit = "imagick",
 *   operation = "scale",
 *   label = @Translation("Scale"),
 *   description = @Translation("Scale an image to the given dimensions (ignoring aspect ratio).")
 * )
 */
class Scale extends Resize {

  /**
   * {@inheritdoc}
   */
  protected function validateArguments(array $arguments) {
    // Assure at least one dimension.
    if (empty($arguments['width']) && empty($arguments['height'])) {
      throw new \InvalidArgumentException("At least one dimension ('width' or 'height') must be provided to the image 'scale' operation");
    }

    // Calculate one of the dimensions from the other target dimension,
    // ensuring the same aspect ratio as the source dimensions. If one of the
    // target dimensions is missing, that is the one that is calculated. If both
    // are specified then the dimension calculated is the one that would not be
    // calculated to be bigger than its target.
    $aspect = $this->getToolkit()->getHeight() / $this->getToolkit()
        ->getWidth();
    if (($arguments['width'] && !$arguments['height']) || ($arguments['width'] && $arguments['height'] && $aspect < $arguments['height'] / $arguments['width'])) {
      $arguments['height'] = (int) round($arguments['width'] * $aspect);
    }
    else {
      $arguments['width'] = (int) round($arguments['height'] / $aspect);
    }

    // Assure integers for all arguments.
    $arguments['width'] = (int) round($arguments['width']);
    $arguments['height'] = (int) round($arguments['height']);

    // Fail when width or height are 0 or negative.
    if ($arguments['width'] <= 0) {
      throw new \InvalidArgumentException($this->t("Invalid width (@value) specified for the image 'scale' operation", array('@value' => $arguments['width'])));
    }
    if ($arguments['height'] <= 0) {
      throw new \InvalidArgumentException($this->t("Invalid height (@value) specified for the image 'scale' operation", array('@value' => $arguments['height'])));
    }

    return $arguments;
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(array $arguments = array()) {
    if ($arguments['width'] !== $this->getToolkit()
        ->getWidth() || $arguments['height'] !== $this->getToolkit()
        ->getHeight()
    ) {
      return parent::execute($arguments);
    }

    return NULL;
  }

}
