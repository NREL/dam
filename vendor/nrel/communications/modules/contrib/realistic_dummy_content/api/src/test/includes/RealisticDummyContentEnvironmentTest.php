<?php

namespace Drupal\realistic_dummy_content_api\Test;

require_once './api/src/includes/RealisticDummyContentEnvironment.php';

use Drupal\realistic_dummy_content_api\includes\RealisticDummyContentEnvironment;

/**
 * Dummy file, used to test how fields manage files.
 *
 * @group realistic_dummy_content
 */
class RealisticDummyContentUnitTestCaseDummyFile {
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
 * Tests for ...\includes\RealisticDummyContentEnvironment.
 */
class RealisticDummyContentEnvironmentTest extends \PHPUnit_Framework_TestCase {

  /**
   * Test that file names are properly parsed and combined.
   */
  public function testFiles() {
    $data = array(
      'one.txt' => new \stdClass(),
      'reAdme.txt' => new \stdClass(),
      'README.md' => new \stdClass(),
      'readme.jpg' => new \stdClass(),
      'two.txt' => new \stdClass(),
      'two.notanattribute.txt' => new \stdClass(),
      'two.txt.attribute.txt' => new \stdClass(),
      'two.txt.attribute1.txt' => new \stdClass(),
      'three.png' => new \stdClass(),
      'three.png.alt.txt' => new \stdClass(),
    );
    try {
      $parsed = RealisticDummyContentEnvironment::sortCandidateFiles($data);
      $parsed_images = RealisticDummyContentEnvironment::sortCandidateFiles($data, array('png'));
    }
    catch (Exception $e) {
      $this->assertFalse(TRUE, 'Got exception ' . $e->getMessage());
    }
    $this->assertTrue(count($parsed) == 4, '4 parsed files are returned, which excludes the readme riles (4 == ' . count($parsed) . ')');
    $this->assertTrue(is_object($parsed['one.txt']['file']));
    $this->assertTrue(is_object($parsed['two.txt']['file']));
    $this->assertTrue(is_object($parsed['two.txt']['attributes']['attribute']));
    $this->assertTrue(is_object($parsed['two.txt']['attributes']['attribute1']));
    $this->assertTrue(is_object($parsed['three.png']['file']));
    $this->assertTrue(is_object($parsed['three.png']['attributes']['alt']));
    $this->assertFalse(isset($parsed_images['two.txt']['attributes']['attribute1']));
    $this->assertTrue(is_object($parsed_images['three.png']['file']));
    $this->assertTrue(is_object($parsed_images['three.png']['attributes']['alt']));
    $this->assertTrue(is_object($parsed['two.notanattribute.txt']['file']));
  }

}
