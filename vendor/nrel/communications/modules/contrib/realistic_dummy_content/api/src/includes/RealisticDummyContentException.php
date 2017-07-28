<?php

namespace Drupal\realistic_dummy_content_api\includes;

use Drupal\realistic_dummy_content_api\Framework\Framework;

// When returning the caller of the function which resulted in the exception
// we need to go 4 levels deep. When returning the called function, we also
// need to 4 levels deep, but call GetCaller() through another function which
// adds a level (GetCalled()).
define('REALISTIC_DUMMY_CONTENT_API_EXCEPTION_BACKTRACE_LEVEL', 4);

/**
 * An Exception.
 */
class RealisticDummyContentException extends \Exception {

  /**
   * Constructor.
   */
  public function __construct($message) {
    parent::__construct($message);
    $this->log();
  }

  /**
   * Logs a message.
   */
  public function log() {
    Framework::instance()->debug($this->getMessage() . ' (' . $this->getCaller() . ' called ' . $this->getCalled() . ')');
  }

  /**
   * Returns the calling function through a backtrace.
   */
  public static function getCaller() {
    // A funciton x has called a function y which called this
    // see stackoverflow.com/questions/190421.
    $caller = debug_backtrace();
    $caller = $caller[REALISTIC_DUMMY_CONTENT_API_EXCEPTION_BACKTRACE_LEVEL];
    $r = $caller['function'] . '()';
    if (isset($caller['class'])) {
      $r .= ' in ' . $caller['class'];
    }
    if (isset($caller['object'])) {
      $r .= ' (' . get_class($caller['object']) . ')';
    }
    return $r;
  }

  /**
   * Returns the called function through a backtrace.
   */
  public static function getCalled() {
    // Get caller will return the called function because the simple fact
    // of using another function will make the backtrace one-level deeper.
    return self::getCaller();
  }

}
