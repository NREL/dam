<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Image\ImageInterface;
use Drupal\image\ImageEffectBase;

/**
 * Inverses the image's colors.
 *
 * @ImageEffect(
 *   id = "image_inverse",
 *   label = @Translation("Inverse"),
 *   description = @Translation("Inverses the image's colors.")
 * )
 */
class InverseImageEffect extends ImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('inverse')) {
      $this->logger->error('Image inverse failed using the %toolkit toolkit on %path (%mimetype)', array(
        '%toolkit' => $image->getToolkitId(),
        '%path' => $image->getSource(),
        '%mimetype' => $image->getMimeType()
      ));
      return FALSE;
    }
    return TRUE;
  }

}
