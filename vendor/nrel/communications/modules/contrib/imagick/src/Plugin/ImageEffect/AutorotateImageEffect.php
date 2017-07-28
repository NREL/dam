<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Image\ImageInterface;
use Drupal\image\ImageEffectBase;

/**
 * Autorotates an image resource.
 *
 * @ImageEffect(
 *   id = "image_autorotate",
 *   label = @Translation("Autorotate"),
 *   description = @Translation("Autorotates an image using EXIF data.")
 * )
 */
class AutorotateImageEffect extends ImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('autorotate')) {
      $this->logger->error('Image autorotate failed using the %toolkit toolkit on %path (%mimetype)', array(
        '%toolkit' => $image->getToolkitId(),
        '%path' => $image->getSource(),
        '%mimetype' => $image->getMimeType()
      ));
      return FALSE;
    }
    return TRUE;
  }

}
