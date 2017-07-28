<?php

namespace Drupal\file_download_tracker;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class FileDownloadTracker.
 *
 * @package Drupal\file_download_tracker.
 */
class FileDownloadTracker extends Event {
  protected $fileID;
  const SUBMIT = 'event.submit';

  /**
   * {@inheritdoc}
   */
  public function __construct($fileID) {
    $this->fileID = $fileID;
  }

  /**
   * {@inheritdoc}
   */
  public function getFileID() {
    return $this->fileID;
  }

}
