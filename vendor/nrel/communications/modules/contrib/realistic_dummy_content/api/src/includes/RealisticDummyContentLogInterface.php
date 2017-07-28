<?php

namespace Drupal\realistic_dummy_content_api\includes;

/**
 * Interface for a log class.
 */
interface RealisticDummyContentLogInterface {

  /**
   * Add a log entry for normal execution.
   *
   * @param string $text
   *   Text for the log entry.
   * @param array $vars
   *   Variables to insert in the text.
   */
  public function log($text, $vars = array());

  /**
   * Add a log entry for an error.
   *
   * @param string $text
   *   Text for the log entry.
   * @param array $vars
   *   Variables to insert in the text.
   */
  public function error($text, $vars = array());

}
