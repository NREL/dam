<?php

namespace Drupal\realistic_dummy_content_api\includes;

/**
 * Abstract dummy content generator class.
 *
 * This module can generate dummy content using an extensible system of
 * "generators", which are subclasses of this class.
 */
abstract class RealisticDummyContentGenerator {
  private $type;
  private $bundle;
  private $num;
  private $more;

  /**
   * Constructor.
   *
   * @param array $more
   *   Can contain:
   *     kill => TRUE|FALSE.
   */
  public function __construct($type, $bundle, $num, $more) {
    $this->type = $type;
    $this->bundle = $bundle;
    $this->num = $num;
    if (isset($more['kill']) && $more['kill']) {
      $this->kill = TRUE;
    }
    else {
      $this->kill = FALSE;
    }
  }

  /**
   * Getter for the bundle property.
   */
  public function getBundle() {
    return $this->bundle;
  }

  /**
   * Getter for the type property.
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Getter for the kill property.
   */
  public function getKill() {
    return $this->kill;
  }

  /**
   * Getter for the num propert.
   */
  public function getNum() {
    return $this->num;
  }

  /**
   * Generate content.
   */
  public function generate() {
    $this->implementGenerate();
  }

}
