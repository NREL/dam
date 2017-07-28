<?php

namespace Drupal\permissions_by_term;

use \Drupal\Core\Form\FormState;

/**
 * Defines an interface for access storage classes.
 */
interface AccessStorageInterface {

  /**
   * Checks if the submitted users are existing.
   *
   * If an user isn't existing, set an error message.
   *
   * @param \Drupal\Core\Form\FormState $form_state
   */
  public function checkIfUsersExists(FormState $form_state);

  /**
   * Gets user term permissions by tid.
   *
   * @param int $term_id
   *
   * @return mixed
   *   Existing term permissions.
   */
  public function getExistingUserTermPermissionsByTid($term_id);

  /**
   * Gets role term permissions by tid.
   *
   * @param int $term_id
   *
   * @return mixed
   *   Existing role term permissions.
   */
  public function getExistingRoleTermPermissionsByTid($term_id);

  /**
   * Gets single user id by user name.
   *
   * @param string $sUsername
   *   An user name.
   *
   * @return int
   *   User id.
   */
  public function getUserIdByName($sUsername);

  /**
   * Gets multiple user ids by user names.
   *
   * @param array $aUserNames
   *   An array with user names.
   *
   * @return array
   *   User ids.
   */
  public function getUserIdsByNames($aUserNames);

  /**
   * Gets the user names from users.
   *
   * Users which have granted access for a taxonomy term.
   *
   * @param int $term_id
   *
   * @return mixed
   *   Gets user ids, which are allowed to access.
   */
  public function getAllowedUserIds($term_id);

  /**
   * Deletes term permissions by user id.
   *
   * @param array $aUserIdsAccessRemove
   *   An array with user ids, which access will be removed.
   * @param int $term_id
   *   The term id to remove.
   */
  public function deleteTermPermissionsByUserIds($aUserIdsAccessRemove, $term_id);

  /**
   * Deletes term permissions by role ids.
   *
   * @param array $aRoleIdsAccessRemove
   *   An array with role ids, that will be removed.
   * @param int $term_id
   *   The term id.
   */
  public function deleteTermPermissionsByRoleIds($aRoleIdsAccessRemove, $term_id);

  /**
   * Adds term permissions by user ids.
   *
   * @param array $aUserIdsGrantedAccess
   *   The user ids which will get granted access.
   * @param int $term_id
   *
   * @throws \Exception
   */
  public function addTermPermissionsByUserIds($aUserIdsGrantedAccess, $term_id);

  /**
   * Adds term permissions by role ids.
   *
   * @param array $aRoleIdsGrantedAccess
   *   The role ids which will gain access.
   * @param int $term_id
   *
   * @throws \Exception
   */
  public function addTermPermissionsByRoleIds($aRoleIdsGrantedAccess, $term_id);

  /**
   * Gets the term id by term name.
   *
   * @param string $sTermName
   *   The term name.
   *
   * @return int
   *   The term id.
   */
  public function getTermIdByName($sTermName);

  /**
   * Gets the taxonomy name by id.
   *
   * @param int $term_id
   *   The taxonomy term id.
   *
   * @return string
   *   Gets a term name by an id.
   */
  public function getTermNameById($term_id);

  /**
   * Saves term permissions by users.
   *
   * Opposite to save term permission by roles.
   *
   * @param \Drupal\Core\Form\FormState $form_state
   * @param int $term_id
   *
   * @return array
   *   Data for database queries.
   */
  public function saveTermPermissions(FormState $form_state, $term_id);

  /**
   * Prepares the data which has to be applied to the database.
   *
   * @param array $aExistingUserPermissions
   *   The permissions for existing user.
   * @param array $aSubmittedUserIdsGrantedAccess
   *   The user ids which get access.
   * @param array $aExistingRoleIdsGrantedAccess
   *   The existing role ids.
   * @param array $aSubmittedRolesGrantedAccess
   *   The user roles which get access.
   *
   * @return array
   *   User ID and role data.
   */
  public function getPreparedDataForDatabaseQueries($aExistingUserPermissions,
                                                    $aSubmittedUserIdsGrantedAccess,
                                                    $aExistingRoleIdsGrantedAccess,
                                                    $aSubmittedRolesGrantedAccess);

  /**
   * The form value for allowed users as string to be shown to the user.
   *
   * @param \Drupal\user\Entity\User[] $aAllowedUsers
   *   An array with the allowed users.
   *
   * @return null|string
   *   Either null or the user name.
   */
  public function getUserFormValue($aAllowedUsers);

}
