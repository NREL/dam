<?php

namespace Drupal\permissions_by_term\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Drupal\Component\Utility\Tags;
use Symfony\Component\HttpFoundation\JsonResponse;
use \Drupal\permissions_by_term\AccessCheckInterface;
use \Drupal\Component\Utility\Html;
use \Drupal\Core\Access\AccessResult;

/**
 * Default controller for the permissions_by_term module.
 */
class PermissionsByTermController extends ControllerBase {

  /**
   * PermissionsByTermController constructor.
   *
   * @param \Drupal\permissions_by_term\AccessCheckInterface
   */
  public function __construct(AccessCheckInterface $access_check_service) {
    $this->oAccessCheckService = $access_check_service;
  }

  /**
   * Handles views in module's logic.
   */
  public function handleViews(&$view) {
    if ($this->oAccessCheckService->viewContainsNode($view) === TRUE) {
      $this->oAccessCheckService->removeForbiddenNodesFromView($view);
    }
  }

  /**
   * Handles nodes in module's logic.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The AccessResult object.
   */
  public function handleNode($node_id) {
    if ($this->oAccessCheckService->canUserAccessByNodeId($node_id) === TRUE) {
      return AccessResult::neutral();
    }
    else {
      return AccessResult::forbidden();
    }
  }

  /**
   * Returns JSON response for user's autocomplete field in permissions form.
   *
   * @return JsonResponse
   *   The response as JSON.
   */
  public function autoCompleteMultiple() {
    // The user enters a comma-separated list of users.
    // We only autocomplete the last user.
    $array = Tags::explode($_REQUEST['q']);

    // Fetch last user.
    $last_string = trim(array_pop($array));

    $matches = [];

    $aUserIds = \Drupal::entityQuery('user')
      ->condition('name', $last_string, 'CONTAINS')
      ->execute();

    $prefix = count($array) ? implode(', ', $array) . ', ' : '';

    foreach ($aUserIds as $iUserId) {
      $oUser = user_load($iUserId);
      $matches[$prefix . $oUser->getUsername()] = Html::escape($oUser->getUsername());
    }

    return new JsonResponse($matches);
  }

}
