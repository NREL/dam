<?php

namespace Drupal\realistic_dummy_content_api\includes;

/**
 * Represents a text property like a node title or user name.
 */
class RealisticDummyContentTextProperty extends RealisticDummyContentProperty {

  /**
   * {@inheritdoc}
   */
  public function implementValueFromFile($file) {
    return $file->Value();
  }

}
