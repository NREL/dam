<?php

namespace Drupal\permissions_by_term;

use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Form\FormState;

/**
 * Class AccessStorage.
 *
 * Defines an API to the database in the term access context.
 *
 * The "protected" class methods are meant for protection regarding Drupal's
 * forms and presentation layer.
 *
 * The "public" class methods can be used for extensions.
 *
 * @package Drupal\permissions_by_term
 */
class AccessStorage implements AccessStorageInterface {

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $oDatabase;

  /**
   * The term name for which the access is set.
   *
   * @var string
   */
  protected $sTermName;

  /**
   * The user ids which gain granted access.
   *
   * @var array
   */
  protected $aUserIdsGrantedAccess;

  /**
   * The roles with granted access.
   *
   * @var array
   */
  protected $aSubmittedRolesGrantedAccess;

  /**
   * AccessStorageService constructor.
   *
   * @param \Drupal\Core\Database\Driver\mysql\Connection $database
   *   The connection to the database.
   */
  public function __construct(Connection $database) {
    $this->oDatabase  = $database;
  }

  /**
   * Gets submitted roles with granted access from form.
   *
   * @return array
   *   An array with chosen roles.
   */
  public function getSubmittedRolesGrantedAccess(FormState $form_state) {
    $aRoles       = $form_state->getValue('access')['role'];
    $aChosenRoles = array();
    foreach ($aRoles as $sRole) {
      if ($sRole !== 0) {
        $aChosenRoles[] = $sRole;
      }
    }
    return $aChosenRoles;
  }

  /**
   * {@inheritdoc}
   */
  public function checkIfUsersExists(FormState $form_state) {
    $sAllowedUsers = $form_state->getValue('access')['user'];
    $aAllowedUsers = Tags::explode($sAllowedUsers);
    foreach ($aAllowedUsers as $sUserId) {
      $aUserId = \Drupal::entityQuery('user')
        ->condition('uid', $sUserId)
        ->execute();
      if (empty($aUserId)) {
        $form_state->setErrorByName('access][user',
          t('The user with ID %user_id does not exist.',
          array('%user_id' => $sUserId)));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getExistingUserTermPermissionsByTid($term_id) {
    return $this->oDatabase->select('permissions_by_term_user', 'pu')
      ->condition('tid', $term_id)
      ->fields('pu', ['uid'])
      ->execute()
      ->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function getExistingRoleTermPermissionsByTid($term_id) {
    return $this->oDatabase->select('permissions_by_term_role', 'pr')
      ->condition('tid', $term_id)
      ->fields('pr', ['rid'])
      ->execute()
      ->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function getUserIdByName($sUsername) {
    return $this->oDatabase->select('users_field_data', 'ufd')
      ->condition('name', $sUsername)
      ->fields('ufd', ['uid'])
      ->execute()
      ->fetchAssoc();
  }

  /**
   * {@inheritdoc}
   */
  public function getUserIdsByNames($aUserNames) {
    $aUserIds = array();
    foreach ($aUserNames as $userName) {
      $iUserId    = $this->getUserIdByName($userName)['uid'];
      $aUserIds[] = $iUserId['uid'];
    }
    return $aUserIds;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllowedUserIds($term_id) {
    $query = $this->oDatabase->select('permissions_by_term_user', 'p')
      ->fields('p', ['uid'])
      ->condition('p.tid', $term_id);

    // fetchCol() returns all results, fetchAssoc() only "one" result.
    return $query->execute()
      ->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteTermPermissionsByUserIds($aUserIdsAccessRemove, $term_id) {
    foreach ($aUserIdsAccessRemove as $iUserId) {
      $this->oDatabase->delete('permissions_by_term_user')
        ->condition('uid', $iUserId, '=')
        ->condition('tid', $term_id, '=')
        ->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteTermPermissionsByRoleIds($aRoleIdsAccessRemove, $term_id) {
    foreach ($aRoleIdsAccessRemove as $sRoleId) {
      $this->oDatabase->delete('permissions_by_term_role')
        ->condition('rid', $sRoleId, '=')
        ->condition('tid', $term_id, '=')
        ->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addTermPermissionsByUserIds($aUserIdsGrantedAccess, $term_id) {
    foreach ($aUserIdsGrantedAccess as $iUserIdGrantedAccess) {
      $this->oDatabase->insert('permissions_by_term_user')
        ->fields(['tid', 'uid'], [$term_id, $iUserIdGrantedAccess])
        ->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addTermPermissionsByRoleIds($aRoleIdsGrantedAccess, $term_id) {
    foreach ($aRoleIdsGrantedAccess as $sRoleIdGrantedAccess) {
      $this->oDatabase->insert('permissions_by_term_role')
        ->fields(['tid', 'rid'], [$term_id, $sRoleIdGrantedAccess])
        ->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTermIdByName($sTermName) {
    $aTermId = \Drupal::entityQuery('taxonomy_term')
      ->condition('name', $sTermName)
      ->execute();
    return key($aTermId);
  }

  /**
   * {@inheritdoc}
   */
  public function getTermNameById($term_id) {
    $term_name = \Drupal::entityQuery('taxonomy_term')
      ->condition('id', $term_id)
      ->execute();
    return key($term_name);
  }

  /**
   * Gets the user ids which have been submitted by form.
   *
   * Users which will gain granted access to taxonomy terms.
   *
   * @return array
   *   The user ids which have been submitted.
   */
  public function getSubmittedUserIds() {
    /* There's a $this->oFormState->getValues() method, but
     * it is loosing multiple form values. Don't know why.
     * So there're some custom lines on the $_REQUEST array. */
    $sRawUsers = $_REQUEST['access']['user'];

    if (empty($sRawUsers)) {
      return array();
    }

    $aRawUsers = explode('),', $sRawUsers);
    $aUserIds = array();
    if (!empty($aRawUsers)) {
      foreach ($aRawUsers as $sRawUser) {
        $aTempRawUser = explode(' (', $sRawUser);
        // We check the user id by user name. If we get null back, the user might
        // be the Anonymous user. In that case we get null back and then we use
        // this id, which is 0.
        if (!empty($aTempRawUser[1])) {
          $fallback_user_id = str_replace(')', '', $aTempRawUser[1]);
          $fallback_user_id = intval($fallback_user_id);
        }

        $sRawUser = trim($aTempRawUser['0']);
        $uid = $this->getUserIdByName($sRawUser)['uid'];
        if ($uid == NULL && $fallback_user_id == 0) {
          // We might want to give access to the Anonymous user.
          $aUserIds[] = 0;
        }
        else {
          $aUserIds[] = $this->getUserIdByName($sRawUser)['uid'];
        }
      }
    }

    return $aUserIds;
  }

  /**
   * {@inheritdoc}
   */
  public function saveTermPermissions(FormState $form_state, $term_id) {
    $aExistingUserPermissions       = $this->getExistingUserTermPermissionsByTid($term_id);
    $aSubmittedUserIdsGrantedAccess = $this->getSubmittedUserIds();

    $aExistingRoleIdsGrantedAccess = $this->getExistingRoleTermPermissionsByTid($term_id);
    $aSubmittedRolesGrantedAccess  = $this->getSubmittedRolesGrantedAccess($form_state);

    $aRet = $this->getPreparedDataForDatabaseQueries($aExistingUserPermissions,
      $aSubmittedUserIdsGrantedAccess, $aExistingRoleIdsGrantedAccess,
      $aSubmittedRolesGrantedAccess);

    // Run the database queries.
    $this->deleteTermPermissionsByUserIds($aRet['UserIdPermissionsToRemove'], $term_id);
    $this->addTermPermissionsByUserIds($aRet['UserIdPermissionsToAdd'], $term_id);

    $this->deleteTermPermissionsByRoleIds($aRet['UserRolePermissionsToRemove'], $term_id);
    $this->addTermPermissionsByRoleIds($aRet['aRoleIdPermissionsToAdd'], $term_id);

    return $aRet;
  }

  /**
   * Get array items to remove.
   *
   * The array items which aren't in the new items array, but are in old items
   * array, will be returned.
   *
   * @param array $aExistingItems
   *   The existing array items.
   * @param array|bool $aNewItems
   *   Either false if there're no new items or an array with items.
   *
   * @return array
   *   The array items to remove.
   */
  private function getArrayItemsToRemove($aExistingItems, $aNewItems) {
    $aRet = array();

    foreach ($aExistingItems as $existingItem) {
      if (!in_array($existingItem, $aNewItems)) {
        $aRet[] = $existingItem;
      }
    }

    return $aRet;
  }

  /**
   * Get the array items to add.
   *
   * The items in the new items array, which aren't in the existing items array,
   * will be returned.
   *
   * @param array $aNewItems
   *   The new array items.
   * @param array $aExistingItems
   *   The existing array items.
   *
   * @return array
   *   The items which needs to be added.
   */
  private function getArrayItemsToAdd($aNewItems, $aExistingItems) {
    $aRet = array();

    foreach ($aNewItems as $newItem) {
      if (!in_array($newItem, $aExistingItems)) {
        $aRet[] = $newItem;
      }
    }

    return $aRet;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreparedDataForDatabaseQueries($aExistingUserPermissions,
                                                    $aSubmittedUserIdsGrantedAccess,
                                                    $aExistingRoleIdsGrantedAccess,
                                                    $aSubmittedRolesGrantedAccess) {
    // Fill array with user ids to remove permission.
    $aRet['UserIdPermissionsToRemove'] =
      $this->getArrayItemsToRemove($aExistingUserPermissions,
        $aSubmittedUserIdsGrantedAccess);

    // Fill array with user ids to add permission.
    $aRet['UserIdPermissionsToAdd'] =
      $this->getArrayItemsToAdd($aSubmittedUserIdsGrantedAccess,
        $aExistingUserPermissions);

    // Fill array with user roles to remove permission.
    $aRet['UserRolePermissionsToRemove'] =
      $this->getArrayItemsToRemove($aExistingRoleIdsGrantedAccess,
        $aSubmittedRolesGrantedAccess);

    // Fill array with user roles to add permission.
    $aRet['aRoleIdPermissionsToAdd'] =
      $this->getArrayItemsToAdd($aSubmittedRolesGrantedAccess,
        $aExistingRoleIdsGrantedAccess);

    return $aRet;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserFormValue($aAllowedUsers) {

    $sUserInfos = '';

    if (!empty($aAllowedUsers)) {

      foreach ($aAllowedUsers as $oUser) {
        $iUid = intval($oUser->id());
        if ($iUid !== 0) {
          $sUsername = $oUser->getUsername();
        }
        else {
          $sUsername = t('Anonymous User');
        }

        $sUserInfos .= "$sUsername ($iUid), ";
      }

      // Remove space and comma at the end of the string.
      $sUserInfos = substr($sUserInfos, 0, -2);
    }

    return $sUserInfos;
  }

  /**
   * @return array
   */
  public function getAllNids()
  {
    $query = $this->oDatabase->select('node', 'n')
        ->fields('n', ['nid']);

    return $query->execute()
        ->fetchCol();
  }

  public function getTidsByNid($nid)
  {
    $node = $this->entityManager->getStorage('node')->load($nid);
    $tids = [];

    foreach ($node->getFields() as $field) {
      if ($field->getFieldDefinition()->getType() == 'entity_reference' && $field->getFieldDefinition()->getSetting('target_type') == 'taxonomy_term') {
        $aReferencedTaxonomyTerms = $field->getValue();
        if (!empty($aReferencedTaxonomyTerms)) {
          foreach ($aReferencedTaxonomyTerms as $aReferencedTerm) {
            if (isset($aReferencedTerm['target_id'])) {
              $tids[] = $aReferencedTerm['target_id'];
            }
          }
        }
      }
    }

    return $tids;
  }

  public function getAllUids()
  {
    $nodes = \Drupal::entityQuery('user')
      ->execute();

    return array_values($nodes);
  }

  public function getNodeType($nid)
  {
    $query = $this->oDatabase->select('node', 'n')
      ->fields('n', ['type'])
      ->condition('n.nid', $nid);

    return $query->execute()
      ->fetchAssoc()['type'];
  }

  public function getLangCode($nid)
  {
    $query = $this->oDatabase->select('node', 'n')
      ->fields('n', ['langcode'])
      ->condition('n.nid', $nid);

    return $query->execute()
      ->fetchAssoc()['langcode'];
  }

  public function getGidsByRealm($realm)
  {
    $query = $this->oDatabase->select('node_access', 'na')
      ->fields('na', ['gid'])
      ->condition('na.realm', $realm);

    $gids = $query->execute()->fetchCol();

    foreach ($gids as $gid) {
      $grants[$realm][] = $gid;
    }

    return $grants;
  }

  public function getAllNidsUserCanAccess($uid)
  {
    $query = $this->oDatabase->select('node_access', 'na')
      ->fields('na', ['nid'])
      ->condition('na.realm', 'permissions_by_term__uid_' . $uid);

    return $query->execute()
      ->fetchCol();
  }

  public function getNidsByTid($tid)
  {
      $query = $this->oDatabase->select('taxonomy_index', 'ti')
        ->fields('ti', ['nid'])
        ->condition('ti.tid', $tid);

      return $query->execute()->fetchCol();
  }

}
