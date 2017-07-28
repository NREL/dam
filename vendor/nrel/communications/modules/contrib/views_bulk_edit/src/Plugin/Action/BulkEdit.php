<?php

namespace Drupal\views_bulk_edit\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Bulk edit entities.
 *
 * @Action(
 *   id = "views_bulk_edit",
 *   label = @Translation("Edit"),
 *   confirm_form_route_name = "views_bulk_edit.edit_form",
 *   deriver = "Drupal\views_bulk_edit\Plugin\Derivative\BulkEditDeriver"
 * )
 */
class BulkEdit extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * Private store factory.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $privateTempStoreFactory;

  /**
   * BulkDownload constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   The plugin Id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\user\PrivateTempStoreFactory $privateTempStoreFactory
   *   The storage service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PrivateTempStoreFactory $privateTempStoreFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->privateTempStoreFactory = $privateTempStoreFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('user.private_tempstore')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    return $this->executeMultiple([$entity]);
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    // Grab the entity Ids.
    $entities_keyed = [];

    foreach ($entities as $entity) {
      $entities_keyed[$entity->id()] = $entity;
    }

    $this->persistBulkEditEntityIds($entities_keyed);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\Core\Access\AccessResultInterface $access */
    $access = $object->access('update', $account, TRUE);
    $access_result = $access->andIf(AccessResult::allowedIfHasPermission($account, 'use views bulk edit'));
    return $return_as_object ? $access_result : $access_result->isAllowed();
  }

  /**
   * Saves the entity  Ids.
   *
   * @param \Drupal\Core\Entity\EntityInterface[] $entities_keyed
   *   An array of entities.
   */
  protected function persistBulkEditEntityIds(array $entities_keyed) {
    $privateStore = $this->privateTempStoreFactory->get('views_bulk_edit');
    $entity_data = [];
    foreach ($entities_keyed as $entity_id => $entity) {
      $entity_data[$entity->getEntityTypeId()][$entity->bundle()][] = $entity->id();
    }
    $privateStore->set('entity_data', $entity_data);
  }

}
