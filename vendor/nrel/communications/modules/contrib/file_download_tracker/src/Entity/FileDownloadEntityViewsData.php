<?php

namespace Drupal\file_download_tracker\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for File download entity entities.
 */
class FileDownloadEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
