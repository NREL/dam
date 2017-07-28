<?php

namespace Drupal\file_download_tracker;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\file_download_tracker\Entity\FileDownloadEntityInterface;

/**
 * Defines the storage handler class for File download entity entities.
 *
 * This extends the base storage class, adding required special handling for
 * File download entity entities.
 *
 * @ingroup file_download_tracker
 */
interface FileDownloadEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of File download entity revision IDs for a specific File download entity.
   *
   * @param \Drupal\file_download_tracker\Entity\FileDownloadEntityInterface $entity
   *   The File download entity entity.
   *
   * @return int[]
   *   File download entity revision IDs (in ascending order).
   */
  public function revisionIds(FileDownloadEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as File download entity author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   File download entity revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\file_download_tracker\Entity\FileDownloadEntityInterface $entity
   *   The File download entity entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(FileDownloadEntityInterface $entity);

  /**
   * Unsets the language for all File download entity with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
