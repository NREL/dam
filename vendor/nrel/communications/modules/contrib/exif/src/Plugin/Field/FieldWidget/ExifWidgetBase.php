<?php
/**
 * @file
 * Contains \Drupal\exif\Plugin\Field\FieldWidget\ExifReadonlytWidget.
 */

namespace Drupal\exif\Plugin\Field\FieldWidget;

use Drupal;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exif\ExifFactory;
use Drupal\field\Entity\FieldConfig;


/**
 * Base class for 'Exif Field widget' plugin implementations.
 *
 * @ingroup field_widget
 */
abstract class ExifWidgetBase extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    if ($form['#entity_type'] == "node" || $form['#entity_type'] == "media") {
      $image_fields = $this->retrieveImageFieldFromBundle($form['#entity_type'], $form['#bundle']);
      $default_image_value = $this->retrieveImageFieldDefaultValue($element, $image_fields);
      $element['image_field'] = array(
        '#type' => 'radios',
        '#title' => t('image field to use to retrieve data'),
        '#description' => t('determine the image used to look for exif and iptc metadata'),
        '#options' => $image_fields,
        '#default_value' => $default_image_value,
        '#element_validate' => array(
          array(
            get_class($this),
            'validateImageField'
          )
        )
      );
    }
    if ($form['#entity_type'] == "file") {
      $element['image_field'] = array(
        '#type' => 'hidden',
        '#default_value' => "file",
        '#value' => "file",
      );
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $image_field = $this->getSetting('image_field');
    if (isset($image_field)) {
      $bundle_name = $this->fieldDefinition->getTargetBundle();
      $entity_type = $this->fieldDefinition->getTargetEntityTypeId();
      $image_field_config = Drupal::getContainer()
        ->get('entity_field.manager')
        ->getFieldDefinitions($entity_type, $bundle_name)[$image_field];
      if ($image_field_config instanceof FieldConfig) {
        if ($image_field_config->getType() == "image" || $image_field_config->getType() == "media") {
          $label = t("'@image_linked_label' (id: @image_linked_id)",array('@image_linked_label' => $image_field_config->getLabel(), '@image_linked_id' => $image_field));
        } else {
          $label = $image_field;
        }
      }
      $image_field_msg = t("exif will be extracted from image field @image", array('@image' => $label));
    }
    else {
      $image_field_msg = t('No image chosen. field will stay empty.');
    }
    array_unshift($summary, $image_field_msg);


    return $summary;
  }
  

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'image_field' => NULL
    ) + parent::defaultSettings();
  }




  public static function validateImageField($element, FormStateInterface $form_state, $form) {
    $elementSettings = $form_state->getValue($element['#parents']);
    if (!$elementSettings) {
      //$form_state->setErrorByName('image_field', t('you must choose at least one image field to retrieve metadata.'));
      $field_storage_definitions = Drupal::getContainer()
        ->get('entity_field.manager')
        ->getFieldStorageDefinitions($form['#entity_type']);
      $field_storage = $field_storage_definitions[$element['#field_name']];
      if ($field_storage) {
        $args = array('%field' => $field_storage->getName());
        $message = t('Field %field must be link to an image field.', $args);
      } else {
        $message = t('Field must be link to an image field.');
      }
      $form_state->setErrorByName('image_field', $message);
    }
  }
  

  /**
   * calculate default value for settings form (more precisely image_field setting) of widget.
   * Look for the first image field found.
   * @param $widget
   * @param $image_fields
   */
  function retrieveImageFieldDefaultValue($widget, $image_fields) {
    if (array_key_exists('settings',$widget) && array_key_exists('image_field',$widget['settings'])) {
      $result = $widget['settings']['image_field'];
    } else {
      $result = NULL;
    }
    if (empty($result)) {
      //Look for the first image field found.
      $temp = array_keys($image_fields);
      if (!empty($temp) && is_array($temp)) {
        $result = $temp[0];
      }
    }
    return $result;
  }

  function retrieveImageFieldFromBundle($entity_type, $bundle_name) {
    $fields_of_bundle = Drupal::getContainer()
      ->get('entity_field.manager')
      ->getFieldDefinitions($entity_type, $bundle_name);
    $result = array();
    foreach ($fields_of_bundle as $key => $value) {
      if ($value instanceof FieldConfig) {
        if ($value->getType() == "image" || $value->getType() == "media") {
          $result[$key] = $value->getLabel() . " (" . $key . ")";
        }
      }
    }
    return $result;
  }

}

?>
