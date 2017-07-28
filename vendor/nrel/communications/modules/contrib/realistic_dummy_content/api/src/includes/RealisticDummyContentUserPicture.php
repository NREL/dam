<?php

namespace Drupal\realistic_dummy_content_api\includes;

/**
 * Represents the user picture.
 */
class RealisticDummyContentUserPicture extends RealisticDummyContentProperty {

  /**
   * {@inheritdoc}
   */
  public function getExtensions() {
    return $this->getImageExtensions();
  }

  /**
   * {@inheritdoc}
   */
  public function implementValueFromFile($file) {
    $file = $this->imageSave($file);
    if ($file) {
      return $file;
    }
  }

}
