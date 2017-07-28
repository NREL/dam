<?php

namespace Drupal\permissions_by_term;

use Drupal\Core\Entity\EntityManager;
use Drupal\permissions_by_term\Factory\NodeAccessRecordFactory;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\Core\Database\Driver\mysql\Connection;

class NodeAccess {

  /**
   * @var array $uniqueGid
   */
  private $uniqueGid = 0;

  /**
   * @var AccessStorage $accessStorage
   */
  private $accessStorage;

  /**
   * @var User $userEntityStorage
   */
  private $userEntityStorage;

  /**
   * @var Node $node
   */
  private $node;

  /**
   * @var EntityManager $entityManager
   */
  private $entityManager;

  /**
   * @var AccessCheck $accessCheck
   */
  private $accessCheck;

  /**
   * @var int $loadedUid
   */
  private $loadedUid;

  /**
   * @var User $userInstance
   */
  private $userInstance;

  /**
   * @var Connection $database
   */
  private $database;

  public function __construct(AccessStorage $accessStorage, NodeAccessRecordFactory $nodeAccessRecordFactory, EntityManager $entityManager, AccessCheck $accessCheck, Connection $database) {
    $this->accessStorage = $accessStorage;
    $this->nodeAccessRecordFactory = $nodeAccessRecordFactory;
    $this->entityManager = $entityManager;
    $this->userEntityStorage = $this->entityManager->getStorage('user');
    $this->node = $this->entityManager->getStorage('node');
    $this->accessCheck = $accessCheck;
    $this->database = $database;
  }

  public function createRealm($uid) {
    return 'permissions_by_term__uid_' . $uid;
  }

  public function createGrants($nid, $uid = FALSE) {
    if (empty($uid)) {
      $uids = $this->accessStorage->getAllUids();
    } else {
      $uids[] = $uid;
    }

    $grants = [];
    foreach ($uids as $uid) {
      if ($this->accessCheck->canUserAccessByNodeId($nid, $uid)) {
        $realm = $this->createRealm($uid);
        $nodeType = $this->accessStorage->getNodeType($nid);
        $langcode = $this->accessStorage->getLangCode($nid);
        $grants[] = $this->nodeAccessRecordFactory->create($realm, $this->createUniqueGid(), $nid, $langcode, $this->getGrantUpdate($uid, $nodeType, $nid), $this->getGrantDelete($uid, $nodeType, $nid));
      }
    }

    return $grants;
  }

  public function createUniqueGid() {
    $uniqueGid = $this->getUniqueGid();
    $uniqueGid++;
    $this->setUniqueGid($uniqueGid);
    return $this->getUniqueGid();
  }

  /**
   * @return array
   */
  public function getUniqueGid() {
    return $this->uniqueGid;
  }

  /**
   * @param array $uniqueGid
   */
  public function setUniqueGid($uniqueGid) {
    $this->uniqueGid = $uniqueGid;
  }

  public function canUserUpdateNode($uid, $nodeType, $nid) {
    $user = $this->getUserInstance($uid);

    $this->setLoadedUid($uid);

    if ($user->hasPermission('edit any ' . $nodeType . ' content')) {
      return TRUE;
    }

    if ($this->isNodeOwner($nid, $uid) && $this->canUpdateOwnNode($uid, $nodeType)) {
      return TRUE;
    }

    return FALSE;
  }

  public function canUserBypassNodeAccess($uid) {
    $user = $this->getUserInstance($uid);
    if ($user->hasPermission('bypass node access')) {
      return TRUE;
    }

    return FALSE;
  }

  public function canUserDeleteNode($uid, $nodeType, $nid) {
    $user = $this->getUserInstance($uid);
    if ($user->hasPermission('delete any ' . $nodeType . ' content')) {
      return TRUE;
    }

    if ($this->isNodeOwner($nid, $uid) && $this->canDeleteOwnNode($uid, $nodeType)) {
      return TRUE;
    }

    return FALSE;
  }

  private function getGrantDelete($uid, $nodeType, $nid) {
    if ($this->canUserBypassNodeAccess($uid)) {
      return 1;
    }

    if ($this->canUserDeleteNode($uid, $nodeType, $nid)) {
      return 1;
    }

    return 0;
  }

  private function getGrantUpdate($uid, $nodeType, $nid) {
    if ($this->canUserBypassNodeAccess($uid)) {
      return 1;
    }

    if ($this->canUserUpdateNode($uid, $nodeType, $nid)) {
      return 1;
    }

    return 0;
  }

  public function isNodeOwner($nid, $uid) {
    $node = $this->node->load($nid);
    if (intval($node->getOwnerId()) == intval($uid)) {
      return TRUE;
    }

    return FALSE;
  }

  private function canUpdateOwnNode($uid, $nodeType) {
    $user = $this->getUserInstance($uid);
    if ($user->hasPermission('edit own ' . $nodeType . ' content')) {
      return 1;
    }

    return 0;
  }

  private function canDeleteOwnNode($uid, $nodeType) {
    $user = $this->getUserInstance($uid);
    if ($user->hasPermission('delete own ' . $nodeType . ' content')) {
      return 1;
    }

    return 0;
  }

  public function getGrantsByNid($nid) {
    $grants = [];
    foreach ($this->grants as $grant) {
      if ($grant->nid == $nid) {
        $grants[] = $grant;
      }
    }

    return $grants;
  }

  /**
   * @return int
   */
  public function getLoadedUid() {
    return $this->loadedUid;
  }

  /**
   * @param int $loadedUid
   */
  public function setLoadedUid($loadedUid) {
    $this->loadedUid = $loadedUid;
  }

  /**
   * @return User
   */
  public function getUserInstance($uid) {
    if ($this->getLoadedUid() !== $uid) {
      $user = $this->userEntityStorage->load($uid);
      $this->setUserInstance($user);
      return $user;
    }

    return $this->userInstance;
  }

  /**
   * @param User $userInstance
   */
  public function setUserInstance($userInstance) {
    $this->userInstance = $userInstance;
  }

  public function rebuildByTid($tid, $formState) {
    $nids = $this->accessStorage->getNidsByTid($tid);
    if (!empty($nids)) {
      $this->dropRecordsByNids($nids);
    }

    if (empty($this->accessStorage->getSubmittedUserIds()) && empty($this->accessStorage->getSubmittedRolesGrantedAccess($formState))) {
      return;
    }

    foreach ($nids as $nid) {
      $grants = $this->createGrants($nid);
      foreach ($grants as $grant) {
        $this->database->insert('node_access')
          ->fields(
            ['nid', 'langcode', 'fallback', 'gid', 'realm', 'grant_view', 'grant_update', 'grant_delete'],
            [$nid, $grant->langcode, 1, $grant->gid, $grant->realm, $grant->grant_view, $grant->grant_update, $grant->grant_delete]
          )
          ->execute();
      }
    }

  }

  private function dropRecordsByNids($nids) {
    $this->database->delete('node_access')
      ->condition('nid', $nids, 'IN')
      ->execute();
  }

  private function dropRecordsByUid($uid) {
    $this->database->delete('node_access')
      ->condition('realm', 'permissions_by_term__uid_' . $uid)
      ->execute();
  }

  public function rebuildByUid($uid, $noDrop = FALSE) {
    if ($noDrop === FALSE) {
      $this->dropRecordsByUid($uid);
    }

    $nids = $this->accessStorage->getAllNids();

    foreach ($nids as $nid) {
      $grants = $this->createGrants($nid, $uid);
      foreach ($grants as $grant) {
        $this->database->insert('node_access')
          ->fields(
            ['nid', 'langcode', 'fallback', 'gid', 'realm', 'grant_view', 'grant_update', 'grant_delete'],
            [$nid, $grant->langcode, 1, $grant->gid, $grant->realm, $grant->grant_view, $grant->grant_update, $grant->grant_delete]
          )
          ->execute();
      }
    }

  }

  public function rebuildByNid($nid) {
    $grants = $this->createGrants($nid);
    foreach ($grants as $grant) {
      $this->database->insert('node_access')
        ->fields(
          ['nid', 'langcode', 'fallback', 'gid', 'realm', 'grant_view', 'grant_update', 'grant_delete'],
          [$nid, $grant->langcode, 1, $grant->gid, $grant->realm, $grant->grant_view, $grant->grant_update, $grant->grant_delete]
        )
        ->execute();
    }
  }

}