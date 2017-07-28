<?php

namespace Drupal\realistic_dummy_content_api\includes;

use Drupal\realistic_dummy_content_api\Framework\Framework;

/**
 * Generic Drupal field.
 *
 * Represents a generic field which appears in an entity object as
 * array('value' => 'xyz').
 */
class RealisticDummyContentValueField extends RealisticDummyContentField {

  /**
   * {@inheritdoc}
   */
  public function implementValueFromFile($file) {
    $value = $file->Value();
    if ($value === NULL) {
      return;
    }
    return Framework::instance()->formatProperty('value', $value);
  }

}
