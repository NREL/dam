<?php

namespace Drupal\file_download_tracker;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class FileDownloadEntityStorage extends SqlContentEntityStorage implements FileDownloadEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(FileDownloadEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {file_download_entity_revision} WHERE id=:id ORDER BY vid',
      array(':id' => $entity->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {file_download_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      array(':uid' => $account->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(FileDownloadEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {file_download_entity_field_revision} WHERE id = :id AND default_langcode = 1', array(':id' => $entity->id()))
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('file_download_entity_revision')
      ->fields(array('langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED))
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
