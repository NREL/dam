<?php

namespace Drupal\linkit_media_file_direct\Plugin\Linkit\Substitution;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\GeneratedUrl;
use Drupal\linkit\Plugin\Linkit\Substitution\Canonical;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A substitution plugin for the canonical URL of an entity.
 *
 * @Substitution(
 *   id = "media_file_direct",
 *   label = @Translation("File media direct URL"),
 * )
 */
class MediaFileDirect extends Canonical {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  protected $entityFieldManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $entity_field_manager, $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_field.manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl(EntityInterface $entity) {
    $file_count = 0;
    $url = new GeneratedUrl();

    // Retrieve the list of media fields.
    $media_fields = $this->entityFieldManager->getFieldMapByFieldType('file')['media'];

    // Determine if the passed entity has any of the media fields.
    foreach ($media_fields as $field_name => $field_info) {
      /** @var \Drupal\media_entity\Entity\Media $entity */
      if ($entity->hasField($field_name)) {

        // Retrieve the list of files for the field in this entity.
        $files = $entity->get($field_name);
        foreach ($files as $index => $file_info) {

          if ($file_info->isDisplayed()) {

            // Retrieve full entity, in order to create the url.
            $file = $this->entityTypeManager->getStorage('file')->load($file_info->target_id);

            /** @var \Drupal\file\FileInterface $file */
            $url->setGeneratedUrl(file_create_url($file->getFileUri()));
            $file_count++;
          }

          // If there is more than one file, abort and link to the entity.
          if ($file_count > 1) {
            /** @var \Drupal\Core\Entity\EntityInterface $entity */
            return $entity->toUrl('canonical')->toString(TRUE);
          }
        }
      }
    }

    // Indicate that this output depends on this entity for caching.
    $url->addCacheableDependency($entity);
    return $url;
  }

}
