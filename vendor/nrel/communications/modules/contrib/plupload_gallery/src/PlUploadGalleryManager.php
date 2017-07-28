<?php

namespace Drupal\plupload_gallery;

//use Drupal\Component\Utility\Unicode;
//use Drupal\Core\Cache\Cache;
//use Drupal\Core\Entity\EntityManagerInterface;
//use Drupal\Core\Form\FormStateInterface;
//use Drupal\Core\Render\RendererInterface;
//use Drupal\Core\Session\AccountInterface;
//use Drupal\Core\StringTranslation\TranslationInterface;
//use Drupal\Core\StringTranslation\StringTranslationTrait;
//use Drupal\Core\Config\ConfigFactoryInterface;
//use Drupal\Core\Template\Attribute;
use Drupal\node\NodeInterface;
use Drupal\Core\Field\FieldDefinition;
//use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Entity\Entity\FormMode;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Entity\EntityTypeManager;
//use Drupal\Core\Entity\Display\EntityFormDisplayInterface;

/**
 * Defines a book manager.
 */
class PlUploadGalleryManager implements PlUploadGalleryManagerInterface {

  /**
   * Entity manager Service Object.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Config Factory Service Object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $display_manager;

  /**
   * Books Array.
   *
   * @var array
   */
  protected $configManager;
  protected $typeManager;

  /**
   * Constructs a PlUploadGalleryManager object.
   */
  public function __construct(EntityFieldManagerInterface $entity_manager, EntityDisplayRepositoryInterface
  $display_manager, ConfigManagerInterface $config_manager, EntityTypeManager $type_manager) {
    $this->entityManager = $entity_manager;
    $this->displayManager = $display_manager;
    $this->configManager = $config_manager;
    $this->typeManager = $type_manager;
  }

  /**
   * Return true if this entity type / bundle is using either widget
   */
  public function checkEntityHasWidget(NodeInterface $node) {
    $fields = $this->entityManager->getFieldDefinitions('node', $node->getType());
    $plupload_widget = FALSE;
    foreach ($fields as $field_name => $field_definition) {
      if (!empty($field_definition->getTargetBundle())) {
        if ($field_definition->getType() == 'image') {
          $form_modes = $this->displayManager->getFormModeOptionsByBundle('node', $node->getType());
          foreach ($form_modes as $mode => $setting) {
            // Widget settings cannot be obtained via a class.  We need to use entity_get_form_display() instead
            $form_display = entity_get_form_display('node', $node->getType(), $mode);
            $settings = $form_display->getComponents();
            foreach ($settings as $widget) {
              if ($widget['type'] == 'plupload_image_widget') {
                $plupload_widget = TRUE;
              }
            }
         }
        }
        if ($field_definition->getType() == 'entity_reference') {
          $form_modes = $this->displayManager->getFormModeOptionsByBundle('node', $node->getType());
          foreach ($form_modes as $mode => $setting) {
            // Widget settings cannot be obtained via a class.  We need to use entity_get_form_display() instead
            $form_display = entity_get_form_display('node', $node->getType(), $mode);
            $settings = $form_display->getComponents();
            foreach ($settings as $widget) {
              if ($widget['type'] == 'plupload_gallery_entity_reference_widget') {
                $plupload_widget = TRUE;
              }
            }

          }
        }
      }
    }
    return $plupload_widget;
  }


}
