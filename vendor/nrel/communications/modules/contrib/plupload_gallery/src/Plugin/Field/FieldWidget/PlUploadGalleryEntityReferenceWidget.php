<?php

namespace Drupal\plupload_gallery\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
//use Drupal\Core\Field\FieldItemInterface;
//use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Form\FormStateInterface;
//use Drupal\user\EntityOwnerInterface;
//use Symfony\Component\Validator\ConstraintViolationInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\Core\Field\FieldDefinition;
use Drupal\plupload_gallery\Plugin\Field\FieldWidget\PlUploadGalleryWidgetTrait;
//use Drupal\plupload_widget\Plugin\Field\FieldWidget\ImageWidget;
//use Drupal\Core\Entity\EntityFieldManager;
//use Drupal\Core\Entity\EntityTypeManagerInterface;
//use Drupal\Core\Field\FieldConfigBase;
//use Drupal\Core\Entity\EntityTypeInterface;
//use Drupal\node\Entity\Node;
//use Drupal\field\FieldConfigInterface;

/**
 * Plugin implementation of the 'entity_reference_autocomplete' widget.
 *
 * @FieldWidget(
 *   id = "plupload_gallery_entity_reference_widget",
 *   label = @Translation("Plupload Gallery Entity Reference"),
 *   description = @Translation("Reference an image field in another content type."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class PlUploadGalleryEntityReferenceWidget extends EntityReferenceAutocompleteWidget {

  use PluploadGalleryWidgetTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'other_fields' => '',
      'field' => '',
      'manage_form_mode' => '',
      'uploads_form_mode' => '',
      'gallery_view_mode' => ''
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $entityManager = \Drupal::service('entity_field.manager');
    $entity_type = $this->getFieldSetting('target_type');
    // This is going to get complicated if more than one bundle is selected

    $fields = $entityManager->getFieldDefinitions($entity_type, $this->getBundle());
    $image_options = [];
    $other_options = [];
    foreach ($fields as $field_name => $field_definition) {
      if (!empty($field_definition->getTargetBundle())) {
        if ($field_definition->getType() == 'image') {
          $image_options[$field_name] = $field_definition->getLabel();
        }
        elseif ($field_definition->getLabel()) {
          $other_options[$field_name] = $field_definition->getLabel();
        }
      }
    }

    $element['field'] = array(
      '#type' => 'select',
      '#title' => t('Field'),
      '#default_value' => $this->getSetting('field'),
      '#options' => $image_options,
      '#required' => TRUE,
    );

    $element['other_fields'] = array(
      '#type' => 'select',
      '#title' => t('Other Fields'),
      '#description' => t('Other fields to include in the upload form.  Any values entered apply to all image entities
      that are created.'),
      '#default_value' => $this->getSetting('other_fields'),
      '#options' => $other_options,
      '#multiple' => TRUE,
      '#required' => TRUE,
    );

    $element += $this->widgetSettingsForm($form);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $summary[] = t('Bundle: @bundle', array('@bundle' =>  $this->getBundle()));
    $summary[] = t('Image field: @field', array('@field' => $this->getSetting('field')));
    $summary[] = t('Other fields: @other_fields', array('@other_fields' => implode(',',
      $this->getSetting('other_fields'))));

    $summary = array_merge($summary, $this->widgetSettingSummary());

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $build = parent::formElement($items, $delta, $element, $form, $form_state);

    // Todo Can we remove the surrounding addmore html?
    if ($delta > 0) return;

    // Change the element description because
    // the PLUPLOAD widget MUST have the
    // extension filters as descripiton.
    // @see \Drupal\plupload\Element\PlUploadFile::preRenderPlUploadFile()
    // @see \Drupal\file\Plugin\Field\FieldWidget\FileWidget::formElement()
    $file_upload_help = array(
      '#theme' => 'file_upload_help',
      '#description' => '',
      '#upload_validators' => '',
      '#cardinality' => 1,
    );
    $element['#description'] = \Drupal::service('renderer')->renderPlain($file_upload_help);

    // Replace the upload HTML element with PLUPLOAD
    $element['upload'] = [
      '#type' => 'plupload',
      '#title' => t('Upload files'),
      //'#description' => t('This multi-upload widget uses Plupload library.'),
      '#autoupload' => TRUE,
      '#autosubmit' => TRUE,
      //'#submit_element' => "[name={$element['upload_button']['#name']}]",
      '#upload_validators' => [
        //'file_validate_extensions' => $configuration->validators['file_validate_extensions'],
      ],
      '#plupload_settings' => [
        'runtimes' => 'html5,flash,silverlight,html4',
       // 'chunk_size' => $configuration->chunk_size . 'b',
       // 'max_file_size' => $configuration->max_size . 'b',
        'max_file_count' => 1,
      ],
      '#event_callbacks' => [
        'FilesAdded' => 'Drupal.plupload_widget.filesAddedCallback',
        'UploadComplete' => 'Drupal.plupload_widget.uploadCompleteCallback',
      ],
      '#attached' => [
        // We need to specify the plupload attachment because it is a default
        // and will be overriden by our value.
        'library' => ['plupload_widget/plupload_widget', 'plupload/plupload'],
      ]
    ];

    $entity = $form_state->getFormObject()->getEntity();
    $entity_id = $entity->id();
    $element['upload']['#plupload_settings']['url'] = \Drupal::url('plupload_gallery.upload_entity_handler', array(
      'plupload_token' => \Drupal::csrfToken()->get('plupload-handle-uploads'),
      'field_name' => $this->fieldDefinition->getName(),
      'entity_type' => $items->getEntity()->getEntityTypeId(),
      'entity_id' => $entity_id,
      'referenced_entity_type' => $this->getFieldSetting('target_type'),
      'referenced_entity_bundle' => $this->getBundle(),
      'referenced_entity_field' => $this->getSetting('field'),
      'referenced_other_fields' => implode(',', $this->getSetting('other_fields'))
    ));

    return $element;
  }


//  /**
//   * {@inheritdoc}
//   */
//  public function errorElement(array $element, ConstraintViolationInterface $error, array $form, FormStateInterface $form_state) {
//    return isset($element['target_id']) ? $element['target_id'] : FALSE;
//  }

  /**
   * {@inheritdoc}
   */
//  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
//    foreach ($values as $key => $value) {
//      // The entity_autocomplete form element returns an array when an entity
//      // was "autocreated", so we need to move it up a level.
//      if (is_array($value['target_id'])) {
//        unset($values[$key]['target_id']);
//        $values[$key] += $value['target_id'];
//      }
//    }
//
//    return $values;
//  }


}
