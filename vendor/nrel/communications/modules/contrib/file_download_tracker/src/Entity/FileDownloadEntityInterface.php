<?php

namespace Drupal\file_download_tracker\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining File download entity entities.
 *
 * @ingroup file_download_tracker
 */
interface FileDownloadEntityInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the File download entity name.
   *
   * @return string
   *   Name of the File download entity.
   */
  public function getName();

  /**
   * Sets the File download entity name.
   *
   * @param string $name
   *   The File download entity name.
   *
   * @return \Drupal\file_download_tracker\Entity\FileDownloadEntityInterface
   *   The called File download entity entity.
   */
  public function setName($name);

  /**
   * Gets the File download entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the File download entity.
   */
  public function getCreatedTime();

  /**
   * Sets the File download entity creation timestamp.
   *
   * @param int $timestamp
   *   The File download entity creation timestamp.
   *
   * @return \Drupal\file_download_tracker\Entity\FileDownloadEntityInterface
   *   The called File download entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the File download entity published status indicator.
   *
   * Unpublished File download entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the File download entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a File download entity.
   *
   * @param bool $published
   *   TRUE to set this File download entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\file_download_tracker\Entity\FileDownloadEntityInterface
   *   The called File download entity entity.
   */
  public function setPublished($published);

  /**
   * Gets the File download entity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the File download entity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\file_download_tracker\Entity\FileDownloadEntityInterface
   *   The called File download entity entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the File download entity revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the File download entity revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\file_download_tracker\Entity\FileDownloadEntityInterface
   *   The called File download entity entity.
   */
  public function setRevisionUserId($uid);

}
