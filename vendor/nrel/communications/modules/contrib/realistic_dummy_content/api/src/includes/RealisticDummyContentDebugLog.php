<?php

namespace Drupal\realistic_dummy_content_api\includes;

/**
 * This log class can be used whenever you need to log data.
 */
class RealisticDummyContentDebugLog implements RealisticDummyContentLogInterface {

  /**
   * {@inheritdoc}
   */
  public function log($text, $vars = array()) {
    // @codingStandardsIgnoreStart
    // Cannot pass a litteral to t() here.
    debug(t($text, $vars));
    // @codingStandardsIgnoreEnd
  }

  /**
   * {@inheritdoc}
   */
  public function error($text, $vars = array()) {
    // @codingStandardsIgnoreStart
    // Cannot pass a litteral to t() here.
    debug(t($text, $vars));
    // @codingStandardsIgnoreEnd
  }

}
