<?php

namespace Drupal\permissions_by_term;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\user\Entity\User;

/**
 * AccessCheckService class.
 */
class AccessCheck implements AccessCheckInterface{

  /**
   * AccessCheckService constructor.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function canUserAccessByNodeId($iNid, $uid = FALSE) {
    $node = $this->entityManager->getStorage('node')->load($iNid);

    $access_allowed = TRUE;

    foreach ($node->getFields() as $field) {
      if ($field->getFieldDefinition()->getType() == 'entity_reference' && $field->getFieldDefinition()->getSetting('target_type') == 'taxonomy_term') {
        $aReferencedTaxonomyTerms = $field->getValue();
        if (!empty($aReferencedTaxonomyTerms)) {
          foreach ($aReferencedTaxonomyTerms as $aReferencedTerm) {
            if (isset($aReferencedTerm['target_id']) && !$this->isAccessAllowedByDatabase($aReferencedTerm['target_id'], $uid)) {
              $access_allowed = FALSE;
            }
          }
        }
      }
    }

    return $access_allowed;
  }

  /**
   * {@inheritdoc}
   */
  public function viewContainsNode($view) {
    $bViewContainsNodes = FALSE;

    foreach ($view->result as $view_result) {
      if (array_key_exists('nid', $view_result) === TRUE) {
        $bViewContainsNodes = TRUE;
        break;
      }
    }
    return $bViewContainsNodes;
  }

  /**
   * {@inheritdoc}
   */
  public function removeForbiddenNodesFromView(&$view) {
    $aNodesToHideInView = array();

    // Iterate over all nodes in view.
    foreach ($view->result as $v) {

      if ($this->canUserAccessByNodeId($v->nid) === FALSE) {
        $aNodesToHideInView[] = $v->nid;
      }

    }

    $counter = 0;

    foreach ($view->result as $v) {
      if (in_array($v->nid, $aNodesToHideInView)) {
        unset($view->result[$counter]);
      }
      $counter++;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isAccessAllowedByDatabase($tid, $uid = FALSE) {

    if ($uid === FALSE) {
      $user = \Drupal::currentUser();
    } elseif (is_numeric($uid)) {
      $user = User::load($uid);
    }

    // Admin can access everything (user id "1").
    if ($user->id() == 1) {
      return TRUE;
    }

    $tid = intval($tid);

    if (!$this->isAnyPermissionSetForTerm($tid)) {
      return TRUE;
    }

    /* At this point permissions are enabled, check to see if this user or one
     * of their roles is allowed.
     */
    $aUserRoles = $user->getRoles();

    foreach ($aUserRoles as $sUserRole) {

      if ($this->isTermAllowedByUserRole($tid, $sUserRole)) {
        return TRUE;
      }

    }

    $iUid = intval($user->id());

    if ($this->isTermAllowedByUserId($tid, $iUid)) {
      return TRUE;
    }

    return FALSE;

  }

  /**
   * {@inheritdoc}
   */
  public function isTermAllowedByUserId($tid, $iUid) {

    $query_result = db_query("SELECT uid FROM {permissions_by_term_user} WHERE tid = :tid AND uid = :uid",
      array(':tid' => $tid, ':uid' => $iUid))->fetchField();

    if (!empty($query_result)) {
      return TRUE;
    }
    else {
      return FALSE;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function isTermAllowedByUserRole($tid, $sUserRole) {
    $query_result = db_query("SELECT rid FROM {permissions_by_term_role} WHERE tid = :tid AND rid IN (:user_roles)",
      array(':tid' => $tid, ':user_roles' => $sUserRole))->fetchField();

    if (!empty($query_result)) {
      return TRUE;
    }
    else {
      return FALSE;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function isAnyPermissionSetForTerm($tid) {

    $iUserTableResults = intval(db_query("SELECT COUNT(1) FROM {permissions_by_term_user} WHERE tid = :tid",
      array(':tid' => $tid))->fetchField());

    $iRoleTableResults = intval(db_query("SELECT COUNT(1) FROM {permissions_by_term_role} WHERE tid = :tid",
      array(':tid' => $tid))->fetchField());

    if ($iUserTableResults > 0 ||
      $iRoleTableResults > 0) {
      return TRUE;
    }

  }

}
