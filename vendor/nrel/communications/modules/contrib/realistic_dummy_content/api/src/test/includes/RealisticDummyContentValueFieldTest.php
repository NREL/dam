<?php

namespace Drupal\realistic_dummy_content_api\includes;

/**
 * Dummy required parent class.
 */
class RealisticDummyContentField {}

namespace Drupal\realistic_dummy_content_api\Test;

require_once './api/src/includes/RealisticDummyContentValueField.php';

/**
 * Dummy file, used to test how fields manage files.
 *
 * @group realistic_dummy_content
 */
class DummyFile {
  private $value;

  /**
   * Constructor.
   *
   * @param mixed $value
   *   The value to return.
   */
  public function __construct($value) {
    $this->value = $value;
  }

  /**
   * Returns the dummy value.
   *
   * @return mixed
   *   The value we used when creating this object.
   */
  public function value() {
    return $this->value;
  }

}

/**
 * Tests for ...\includes\RealisticDummyContentValueField.
 */
class RealisticDummyContentValueFieldTest extends \PHPUnit_Framework_TestCase {

  /**
   * Test that empty files and non-existing files are treated differently.
   */
  public function testEmpty() {
    $field = new RealisticDummyContentValueField('ignore entity', 'ignore name');
    $null = new DummyFile(NULL);
    $empty = new DummyFile('');

    $this->assertFalse(is_array($field->implementValueFromFile($null)), 'No applicable field value is represented by NULL.');
    $this->assertTrue(is_array($field->implementValueFromFile($empty)), 'An empty string is considered a valid value.');
  }

}
