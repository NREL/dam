<?php
/**
 * @file
 * Contains \Drupal\exif\Controller\ExifController
 */

namespace Drupal\exif\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Routing\LocalRedirectResponse;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Drupal\node\NodeTypeInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\taxonomy\Entity\Vocabulary;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ExifSettingsController extends ControllerBase {


  /**
   * button to create a vocabulary "photographies'metadata" (exif,iptc and xmp data contains in jpeg file)
   * @return Response
   */
  public function showGuide() {
    return [
      '#message' => "",
      '#taxonomy' => 'http://drupal.org/handbook/modules/taxonomy/',
      '#theme' => 'exif_helper_page',
      '#attached' => array(
        'library' =>  array(
          'exif/exif-admin'
        ),
      ),
    ];
  }

  /**
   * button to create a vocabulary "photographies'metadata" (exif,iptc and xmp data contains in jpeg file)
   * @return Response
   */
  public function createPhotographyVocabulary() {
    $values = array(
      "name" => "photographs metadata",
      "vid" => "photographs_metadata",
      "description" => "information related to photographs"
    );
    $voc = Vocabulary::load("photographs_metadata");
    if (!$voc) {
      Vocabulary::create($values)->save();
      $message = $this->t('The  vocabulary photography has been created');
    } else {
      $message = $this->t('The  vocabulary photography is already created. nothing to do');
    }
    drupal_set_message($message);
    $response = new RedirectResponse('/admin/config/media/exif/helper');
    $response->send();
    exit();
  }

  protected function configureEntityFormDisplay($field_name, $widget_id = NULL) {
    // Make sure the field is displayed in the 'default' form mode (using
    // default widget and settings). It stays hidden for other form modes
    // until it is explicitly configured.
    $options = $widget_id ? ['type' => $widget_id] : [];
    entity_get_form_display($this->entityTypeId, $this->bundle, 'default')
      ->setComponent($field_name, $options)
      ->save();
  }

  protected function configureEntityViewDisplay($field_name, $formatter_id = NULL) {
    // Make sure the field is displayed in the 'default' view mode (using
    // default formatter and settings). It stays hidden for other view
    // modes until it is explicitly configured.
    $options = $formatter_id ? ['type' => $formatter_id] : [];
    entity_get_display($this->entityTypeId, $this->bundle, 'default')
      ->setComponent($field_name, $options)
      ->save();
  }

  protected function addFieldToContentType(NodeTypeInterface $type, $fieldLabel, $fieldName, $fieldType, $fieldWidget, $widgetSettings = array(), $settings = array()) {
    $realFieldName = 'field_' . $fieldName;
    $field_storage = FieldStorageConfig::loadByName('node', $realFieldName);
    if (empty($field_storage)) {
      $field_storage_values = [
        'field_name' => $realFieldName,
        'field_label' => $fieldLabel,
        'entity_type' => 'node',
        'bundle' => $type->id(),
        'type' => $fieldType,
        'translatable' => FALSE,
      ];
      $this->entityTypeManager()
        ->getStorage('field_storage_config')
        ->create($field_storage_values)
        ->save();
    }
    $this->node_add_extra_field($type, $realFieldName, $fieldLabel, $fieldWidget, $widgetSettings);
  }

  protected function addReferenceToContentType(NodeTypeInterface $type, $fieldLabel, $fieldName, $fieldType, $fieldTypeBundle, $fieldWidget, $widgetSettings = array(), $settings = array()) {
    $realFieldName = 'field_' . $fieldName;
    $field_storage = FieldStorageConfig::loadByName('node', $realFieldName);
    if (empty($field_storage)) {
      $field_storage_values = array(
        'field_name' => $realFieldName,
        'field_label' => $fieldLabel,
        'entity_type' => 'node',
        'bundle' => $type->id(),
        'type' => 'entity_reference',
        'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
        'translatable' => FALSE,
        'settings' => array(
          'target_type' => $fieldType,
          'handler_settings' => array('target_bundles' => array($fieldTypeBundle)),
        )
      );
      $this->entityTypeManager()
        ->getStorage('field_storage_config')
        ->create($field_storage_values)
        ->save();
    }
    $this->node_add_extra_field($type, $realFieldName, $fieldLabel, $fieldWidget, $widgetSettings);
  }


  protected function node_add_extra_field(NodeTypeInterface $type, $name, $fieldLabel, $fieldWidget, $widgetSettings) {
    $machinename = strtolower($name);
    // Add or remove the body field, as needed.
    $field_storage = FieldStorageConfig::loadByName('node', $machinename);
    $field = FieldConfig::loadByName('node', $type->id(), $machinename);
    if (empty($field)) {
      $field = entity_create('field_config', array(
        'field_storage' => $field_storage,
        'bundle' => $type->id(),
        'label' => $fieldLabel,
        'settings' => array('display_summary' => TRUE),
      ));
      $field->save();
    }

    // Assign widget settings for the 'default' form mode.
    $entityFormDisplay = Drupal::entityManager()
      ->getStorage('entity_form_display')
      ->load('node.' . $type->id() . '.' . 'default');
    $entityFormDisplay
      ->setComponent($machinename, array(
        'type' => $fieldWidget,
        'settings' => $widgetSettings
      ))
      ->save();

    // Assign display settings for the 'default' and 'teaser' view modes.
    entity_get_display('node', $type->id(), 'default')
      ->setComponent($machinename, array(
        'label' => 'hidden',
        'type' => 'text_default',
      ))
      ->save();

    // The teaser view mode is created by the Standard profile and therefore
    // might not exist.
    $view_modes = Drupal::entityManager()->getViewModes('node');
    if (isset($view_modes['teaser'])) {
      entity_get_display('node', $type->id(), 'teaser')
        ->setComponent($machinename, array(
          'label' => 'hidden',
          'type' => 'text_summary_or_trimmed',
        ))
        ->save();
    }


    return $field;
  }

  /**
   * Button to create an Photography node type with default exif field (title,model,keywords) and default behavior but 'promoted to front'
   * @return Response
   */
  public function createPhotographyNodeType() {
    $nodeTypeName = 'Photography';
    $machinename = strtolower($nodeTypeName);

    try {

      $node_type = NodeType::load($machinename);
      if (!$node_type) {
        $node_type = NodeType::create(
          [
            'name' => $nodeTypeName,
            'type' => $machinename,
            'description' => 'Use Photography for content where the photo is the main content. You still can have some other information related to the photo itself.',
          ]);
        $node_type->save();
        node_add_body_field($node_type);
      }

      //add default display
      $values = array(
        'targetEntityType' => 'node',
        'bundle' => $machinename,
        'mode' => 'default',
        'status' => TRUE
      );
      $this->entityTypeManager()
        ->getStorage('entity_view_display')
        ->create($values);

      //add default form display
      $values = array(
        'targetEntityType' => 'node',
        'bundle' => $machinename,
        'mode' => 'default',
        'status' => TRUE,
      );
      $this->entityTypeManager()
        ->getStorage('entity_form_display')
        ->create($values);

      //$this->configureEntityFormDisplay($fieldName);
      //$this->configureEntityViewDisplay($fieldName);

      //then add fields
      //add photo field of type image_field
      $this->addFieldToContentType($node_type, 'Photo', 'image', 'image', 'exif_readonly');
      $this->addFieldToContentType($node_type, 'Model', 'exif_model', 'text', 'exif_readonly');
      $widget_settings = [
        'image_field' => 'field_image',
        'exif_field' => 'naming_convention'
      ];
      $this->addFieldToContentType($node_type, 'Creation date', 'exif_filedatetime', 'datetime', 'exif_readonly', $widget_settings);
      $this->addReferenceToContentType($node_type, 'Photographer', 'exif_author', 'taxonomy_term','taxonomy', 'exif_readonly', $widget_settings);

      $message = $this->t('The node type photography has been fully created');

    } catch (FieldException $fe) {
      $message = $this->t('An unexpected error was thrown during creation : ')+$fe->getMessage();
    }
    drupal_set_message($message);
    $response = new RedirectResponse('/admin/config/media/exif/helper');
    $response->send();
    exit();
  }

  /**
   * Button to create an Photography node type with default exif field (title,model,keywords) and default behavior but 'promoted to front'
   * @return Response
   */
  public function createPhotographyMediaType() {
    $typeName = 'Photography';
    $machinename = strtolower($typeName);

    try {

      if (Drupal::moduleHandler()->moduleExists("media_entity")) {
        $media_type = Drupal\media_entity\Entity\MediaBundle::load($machinename);
        if (!$media_type) {
          $media_type = Drupal\media_entity\Entity\MediaBundle::create(
            [
              'name' => $typeName,
              'type' => $machinename,
              'description' => 'Use Photography for content where the photo is the main content. You still can have some other information related to the photo itself.',
            ]);
          $media_type->save();
        }

        //add default display
        $values = array(
          'targetEntityType' => 'media',
          'bundle' => $machinename,
          'mode' => 'default',
          'status' => TRUE
        );
        $this->entityTypeManager()
          ->getStorage('entity_view_display')
          ->create($values);

        //add default form display
        $values = array(
          'targetEntityType' => 'media',
          'bundle' => $machinename,
          'mode' => 'default',
          'status' => TRUE,
        );
        $this->entityTypeManager()
          ->getStorage('entity_form_display')
          ->create($values);

        //$this->configureEntityFormDisplay($fieldName);
        //$this->configureEntityViewDisplay($fieldName);

        //then add fields
        //add photo field of type image_field
        $this->addFieldToContentType($media_type, 'Photo', 'image', 'image', 'exif_readonly');
        $this->addFieldToContentType($media_type, 'Model', 'exif_model', 'text', 'exif_readonly');
        $widget_settings = [
          'image_field' => 'field_image',
          'exif_field' => 'naming_convention'
        ];
        $this->addFieldToContentType($media_type, 'Creation date', 'exif_filedatetime', 'datetime', 'exif_readonly', $widget_settings);
        $this->addReferenceToContentType($media_type, 'Photographer', 'exif_author', 'taxonomy_term','taxonomy', 'exif_readonly', $widget_settings);

        $message = $this->t('The media type photography has been fully created');
      } else {
        $message = 'nothing done. Media modules not present.';
      }
    } catch (FieldException $fe) {
      $message = $this->t('An unexpected error was thrown during creation : ')+$fe->getMessage();
    }
    drupal_set_message($message);
    $response = new RedirectResponse('/admin/config/media/exif/helper');
    $response->send();
    exit();
  }

  /**
   *  var $field = array(
   * 'name' => 'myfield',
   * 'entity_type' => 'myentitytype',
   * 'type' => 'text_long',
   * 'module' => 'text',
   * 'settings' => array(),
   * 'cardinality' => 1,
   * 'locked' => FALSE,
   * 'indexes' => array(),
   * );
   * //entity_create('field_entity', $field)->save();
   * $this->entityTypeManager()->getStorage('field_entity')->create($field)->save();
   *
   * var $instance = array(
   * 'field_name' => 'myfield',
   * 'entity_type' => 'myentitytype',
   * 'bundle' => 'myentitytype',
   * 'label' => 'Field title',
   * 'description' => t('Field description.'),
   * 'required' => TRUE,
   * 'default_value' => array(),
   * 'settings' => array(
   * 'text_processing' => '0'
   * ),
   * );
   * //entity_create('field_instance', $instance)->save();
   * $this->entityTypeManager()->getStorage('field_instance')->create($instance)->save();
   *
   * var $values = array(
   * 'targetEntityType' => 'myentitytype',
   * 'bundle' => 'myentitytype',
   * 'mode' => 'default',
   * 'status' => TRUE,
   * );
   *
   * $this->entityTypeManager()->getStorage('entity_form_display')->create($values)->save();
   *
   * var entity_form_display = getStorage('entity_form_display')->load($entity_type . '.' . $bundle . '.' . $form_mode);
   *
   * entity_form_display->setComponent('myfield', array(
   * 'type' => 'text_long',
   * 'settings' => array(
   * 'text_processing' => '0'
   * ),
   * 'weight' => 5,
   * ))->save();
   */


  function sanitize_value($exif_value) {
    if (!Drupal\Component\Utility\Unicode::validateUtf8($exif_value)) {
      $exif_value = Drupal\Component\Utility\Html::escape(utf8_encode($exif_value));
    }
    return $exif_value;
  }

  public function showSample() {
    $sampleImageFilePath = drupal_get_path('module', 'exif') . '/sample.jpg';
    $exif = Drupal\exif\ExifFactory::getExifInterface();
    $fullmetadata = $exif->readMetadataTags($sampleImageFilePath);
    $html = '<table class="metadata-table"><tbody>';
    foreach ($fullmetadata as $currentSection => $currentValues) {
      $html .= '<tr class="metadata-section"><td colspan="2">'.$currentSection.'</td></tr>';
      foreach ($currentValues as $currentKey => $currentValue) {
        $exif_value = $this->sanitize_value($currentValue);
        $html .= '<tr class="metadata-row '.$currentKey.'"><td class="metadata-key">'.$currentKey.'</td><td class="metadata-value">'.$exif_value.'</td></tr>';
      }
    }
    $html .= '</tbody><tfoot></tfoot></table>';
    return [
      '#metadata' => $html,
      '#image_path' => '/'.$sampleImageFilePath,
      '#taxo' => '',//url('admin/structure/taxonomy'),
      '#permissionLink' => '',//url('admin/config/people/permissions'),
      '#taxonomyFragment' => '', //module - taxonomy
      '#theme' => 'exif_sample',
      '#attached' => array(
        'library' =>  array(
          'exif/exif-sample'
        ),
      ),
    ];



  }

}

?>
