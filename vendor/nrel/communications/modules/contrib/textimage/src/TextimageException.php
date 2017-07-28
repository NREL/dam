<?php

namespace Drupal\textimage;

/**
 * Exception thrown by Textimage on failure.
 */
class TextimageException extends \Exception {

  /**
   * Constructs a TextimageImagerTokenException object.
   */
  public function __construct($message, \Exception $previous = NULL) {
    parent::__construct("Textimage error: {$message}", 0, $previous);
  }

}
