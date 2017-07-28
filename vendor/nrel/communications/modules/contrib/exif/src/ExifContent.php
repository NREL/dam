<?php
/**
 * Created by IntelliJ IDEA.
 * User: jphautin
 * Date: 18/01/16
 * Time: 22:36
 */

namespace Drupal\exif;


use Drupal;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\UriItem;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeFieldItemList;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\file\Plugin\Field\FieldType\FileFieldItemList;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;

class ExifContent {

  /**
   * Main entrypoint of the module.
   *
   * @param $entity FieldableEntityInterface to update
   */
  function entity_insert_update($entityType, FieldableEntityInterface $entity, $update = TRUE) {
    $bundles_to_check = $this->get_bundle_for_exif_data();
    if (in_array($entity->bundle(), $bundles_to_check)) {
      $exif = ExifFactory::getExifInterface();
      $ar_exif_fields = $this->filter_fields_on_settings($entityType, $entity);
      $ar_exif_fields = $exif->getMetadataFields($ar_exif_fields);
      if (!$update && isset($entity->original)) {
        $original = $entity->original;
        foreach ($ar_exif_fields as $drupal_field => $metadata_field_descriptor) {
          $field_name = $drupal_field;
          $field = $entity->get($field_name);
          $field->offsetSet(0, $original->get($field_name));
        }
      } else {
        $image_fields = $this->get_image_fields($entity);
        $metadata_images_fields = $this->get_image_fields_metadata($entity, $ar_exif_fields, $image_fields);
        foreach ($ar_exif_fields as $drupal_field => $metadata_field_descriptor) {
          $field_name = $drupal_field;
          $field = $entity->get($field_name);
          $key = $metadata_field_descriptor['metadata_field']['tag'];
          $section = $metadata_field_descriptor['metadata_field']['section'];
          if (array_key_exists($metadata_field_descriptor['image_field'], $metadata_images_fields)) {
            if ($key == "all") {
              $j = 0;
              foreach ($metadata_images_fields[$metadata_field_descriptor['image_field']] as $metadata_image_fields) {
                $html = '<table class="metadata-table"><tbody>';
                foreach ($metadata_image_fields as $currentSection => $currentValues) {
                  $html .= '<tr class="metadata-section"><td colspan=2>'.$currentSection.'</td></tr>';
                  foreach ($currentValues as $currentKey => $currentValue) {
                    $exif_value = $this->sanitize_value($currentValue);
                    $html .= '<tr class="metadata-value"><td>'.$currentKey.'</td><td>'.$exif_value.'</td></tr>';
                  }
                }
                $html .= '</tbody><tfoot></tfoot></table>';
                $this->handle_text_field($j, $field, $section, $key, array("value" => $html , 'format' => 'full_html'));
                $j++;
              }
            } else {
              $values = array();
              foreach ($metadata_images_fields[$metadata_field_descriptor['image_field']] as $metadata_image_fields) {
                if (array_key_exists($section, $metadata_image_fields)
                  && array_key_exists($key, $metadata_image_fields[$section])
                ) {
                  $value = $metadata_image_fields[$section][$key];
                  if (is_string($value) && isset($metadata_field_descriptor['metadata_field_separator'])) {
                    $subValues = explode($$this->metadata_field_descriptor['metadata_field_separator'], $value);
                    foreach ($subValues as $index => $subValue) {
                      $values[] = $subValue;
                    }
                  }
                  else {
                    $values[] = $value;
                  }
                }
              }
              $j = 0;
              foreach ($values as $innerkey => $value) {
                $this->handle_field($j, $field, $section, $key, $value);
                $j++;
              }
            }
          }
        }
      }
    }
  }

  /**
   * Let's check if this node type contains an image field.
   *
   * @return array the list of bundle where the exif data could be updated.
   */
  function get_bundle_for_exif_data() {
    $config = Drupal::config('exif.settings');
    $new_types = array();
    //fill up array with checked nodetypes
    foreach ($config->get('nodetypes', array()) as $type) {
      if ($type != "0") {
        $new_types[] = $type;
      }
    }
    foreach ($config->get('mediatypes', array()) as $type) {
      if ($type != "0") {
        $new_types[] = $type;
      }
    }
    return $new_types;
  }

  /**
   * look for metadata fields in an entity type.
   *
   * @param NodeInterface $entity the entity to look for metadata fields
   * @return array the list of metadata fields found in the entity
   */
  function filter_fields_on_settings($entityType, FieldableEntityInterface $entity) {
    $result = array();
    foreach ($entity->getFieldDefinitions() as $fieldName => $fieldDefinition) {
      if ($fieldDefinition instanceof FieldConfigInterface) {
        $settings = \Drupal::entityTypeManager()
          ->getStorage('entity_form_display')
          ->load($entityType. '.' . $entity->bundle() . '.default')
          ->getComponent($fieldName)['settings'];
        $exifField = NULL;
        if (array_key_exists('exif_field',$settings)) {
          $exifField = $settings['exif_field'];
        }
        $exifFieldSeparator = NULL;
        if (array_key_exists('exif_field_separator',$settings)) {
          $exifFieldSeparator = $settings['exif_field_separator'];
        }
        $imageField = NULL;
        if (array_key_exists('image_field',$settings)) {
          $imageField = $settings['image_field'];
        }
        $mediaField = NULL;
        if (array_key_exists('media_generic',$settings)) {
          $mediaField = $settings['media_generic'];
        }
        if (isset($exifField) && ((isset($imageField)) || (isset($mediaField)))) {
          $element = array();
          if ($exifField == 'naming_convention') {
            $name = substr($fieldName, 6);
          }
          else {
            $name = $exifField;
          }
          $element['metadata_field'] = $name;
          if (isset($exifFieldSeparator) && strlen($exifFieldSeparator) > 0) {
            $element['metadata_field_separator'] = $exifFieldSeparator;
          }
          if (!isset($imageField) && isset($mediaField)) {
            $element['image_field'] = $mediaField;
          }
          else {
            $element['image_field'] = $imageField;
          }
          $result[$fieldName] = $element;
        }
      }
    }
    return $result;
  }

  /**
   * look for image fields in an entity type.
   *
   * @param FieldableEntityInterface $entity the entity to look for image fields
   * @return array the list of image fields found in the entity
   */
  function get_image_fields(FieldableEntityInterface $entity) {
    $result = array();
    if ($entity->getEntityTypeId() == 'node' or $entity->getEntityTypeId() == 'media' ) {
      foreach ($entity->getFieldDefinitions() as $fieldName => $fieldDefinition) {
        if ($fieldDefinition->getType() == 'image') {
          $result[$fieldName] = $fieldDefinition;
        }
      }
    }
    if ($entity->getEntityTypeId() == 'file') {
      $result['file'] = $entity;
    }
    return $result;
  }

  function get_image_fields_metadata(FieldableEntityInterface $entity, &$ar_exif_fields, $image_fields) {
    $result = array();
    if (empty($ar_exif_fields)) {
      return TRUE;
    }
    if (empty($image_fields)) {
      return FALSE;
    }

    foreach ($ar_exif_fields as $drupal_field => $metadata_settings) {
      $field_image_name = $metadata_settings['image_field'];
      if (empty($image_fields[$field_image_name])) {
        $result[$field_image_name] = array();
      }
      else {
        $images_descriptor = $this->get_file_uri_and_language($entity, $field_image_name);
        if ($images_descriptor == FALSE) {
          $fullmetadata = array();
        }
        else {
          foreach ($images_descriptor as $index => $image_descriptor) {
            $fullmetadata[$index] = $this->get_data_from_file_uri($image_descriptor['uri']);
          }
        }
        $result[$field_image_name] = $fullmetadata;
        $ar_exif_fields[$drupal_field]['language'] = $image_descriptor['language'];
      }
    }
    return $result;
  }

  /**
   * retrieve the URI and Language of an image.
   *
   * @param FieldableEntityInterface $entity the netity to look for
   * @param $field_image_name string the field name containing the image
   * @return array|bool a simple array with uri and language for each images in the field of FALSE if the entity type is not known.
   */
  function get_file_uri_and_language(FieldableEntityInterface $entity, $field_image_name) {
    $result = FALSE;
    if ($entity->getEntityTypeId() == 'node' || $entity->getEntityTypeId() == 'media') {
      $image_field_instance = $entity->get($field_image_name);
      if ($image_field_instance instanceof FileFieldItemList) {
        $nbImages = count($image_field_instance->getValue());
        $result = array();
        for ($i=0; $i<$nbImages; $i++) {
          $result[$i] = array();
          $tmp = $image_field_instance->get($i)->entity;
          $result[$i]['uri'] = $tmp->uri[0];
          $result[$i]['language'] = $tmp->language();
        }
      }
    }
    else {
      if ($entity->getEntityTypeId() == 'file') {
        $result = array();
        $result[0] = array();
        $result[0]['uri'] = $entity->uri;
        $result[0]['language'] = $entity->language();
      }
    }
    return $result;
  }

  /**
   * retrieve all metadata values from an image.
   *
   * @param UriItem $file_uri the File URI to look at.
   * @return mixed
   */
  function get_data_from_file_uri(UriItem $file_uri) {
    //common to media
    $uri = $file_uri->getValue()['value'];
    $absoluteFilePath = Drupal::getContainer()
      ->get('file_system')
      ->realpath($uri);
    $exif = ExifFactory::getExifInterface();
    $fullmetadata = $exif->readMetadataTags($absoluteFilePath);
    return $fullmetadata;
  }


  /**
   * handle field by delegating to specific type handler.
   * @param $index number the index to set the new value
   * @param $field FieldItemListInterface the field to update
   * @param $exif_section string the exif section where value has been retrieved
   * @param $exif_name  string the exif label where value has been retrieved
   * @param $exif_value string the exif value to update
   */
  function handle_field($index, FieldItemListInterface &$field, $exif_section, $exif_name, $exif_value) {
    $value = $this->sanitize_value($exif_value);
    $field_typename = $field->getFieldDefinition()->getType();
    if ($field_typename == 'text') {
      $this->handle_text_field($index, $field, $exif_section, $exif_name, $value);
    }
    else {
      if ($field_typename == 'entity_reference' && $field->getFieldDefinition()
          ->getFieldStorageDefinition()
          ->getSetting('target_type') == 'taxonomy_term'
      ) {
        $this->handle_taxonomy_field($index, $field, $exif_section, $exif_name, $value);
      }
      else {
        if ($field_typename == 'datetime' || $field_typename == 'date') {
          $this->handle_date_field($index, $field, $exif_section, $exif_name, $value);
        }
      }
    }
  }

  /**
   * ensure no HTML or Javascript will be interpreted in the rendering process.
   *
   * @param $exif_value string the value retrieve from the image
   * @return string the value sanitized.
   */
  function sanitize_value($exif_value) {
    if (!Unicode::validateUtf8($exif_value)) {
      $exif_value = Html::escape(utf8_encode($exif_value));
    }
    return $exif_value;
  }


  /**
   * handle text field.
   *
   * @param $index number the index to set the new value
   * @param $field FieldItemListInterface the field to update
   * @param $exif_section string the exif section where value has been retrieved
   * @param $exif_name  string the exif label where value has been retrieved
   * @param $exif_value string the exif value to update
   */
  function handle_text_field($index, FieldItemListInterface &$field, $exif_section, $exif_name, $exif_sanitized_value) {
    $field->offsetSet($index, $exif_sanitized_value);
  }


  /**
   * handle date field.
   *
   * @param $index number the index to set the new value
   * @param $field FieldItemListInterface the field to update
   * @param $exif_section string the exif section where value has been retrieved
   * @param $exif_name  string the exif label where value has been retrieved
   * @param $exif_value string the exif value to update
   */
  function handle_date_field($index, FieldItemListInterface &$field, $exif_section, $exif_name, $exif_sanitized_value) {

    if ($exif_name == 'filedatetime') {
      $format = 'atom';
    }
    else {
      $format = 'exif';
    }
    $dateFormatStorage = Drupal::getContainer()
      ->get('entity.manager')
      ->getStorage('date_format');
    if ($dateFormatStorage instanceof EntityStorageInterface) {
      //load format for parsing information from image
      $dateFormat = $dateFormatStorage->load($format);
      if ($dateFormat instanceof DateFormat) {
        //exif internal format do not handle timezone :(
        //Using website timezone instead or default storage if none is defined.
        //TODO : drupal_get_user_timezone();
        //parse string to date following chosen format
        $date_datetime = DrupalDateTime::createFromFormat($dateFormat->getPattern(), $exif_sanitized_value);
        //load storage format
        $storage_format = $field->getFieldDefinition()
          ->getSetting('datetime_type') == DateTimeItem::DATETIME_TYPE_DATE ? DATETIME_DATE_STORAGE_FORMAT : DATETIME_DATETIME_STORAGE_FORMAT;
        //format date to string for storage
        $value = $date_datetime->format($storage_format);
        //store value
        $field->offsetSet($index, $value);
      }
    }
  }

  /**
   * handle taxonomy field.
   *
   * @param $index number the index to set the new value
   * @param $field FieldItemListInterface the field to update
   * @param $exif_section string the exif section where value has been retrieved
   * @param $exif_name  string the exif label where value has been retrieved
   * @param $exif_value string the exif value to update
   */
  function handle_taxonomy_field($index, FieldItemListInterface &$field, $exif_section, $exif_name, $exif_value) {
    //TODO : check if the vocabulary is the same as the field
    //look for the term
    if (!Unicode::validateUtf8($exif_value)) {
      $exif_value = Html::escape(utf8_encode($exif_value));
    }
    $config = Drupal::config('exif.settings');
    //$chosen_vocabulary = $config->get('vocabulary');
    $chosen_vocabulary = array_keys($field->getSettings('vocabulary')['handler_settings']['target_bundles'])[0];
    if (isset($chosen_vocabulary)) {
      //$vocabulary = Vocabulary::load($chosen_vocabulary);
      $terms = taxonomy_term_load_multiple_by_name($exif_value, $chosen_vocabulary);
      if (is_array($terms) && count($terms) > 0) {
        $term = array_shift($terms);
      }
      else {
        // if not exist, create it and also parents if needed.
        $terms = taxonomy_term_load_multiple_by_name($exif_name, $chosen_vocabulary);
        if (is_array($terms) && count($terms) > 0) {
          $parent_term = array_shift($terms);
        }
        else {
          $terms = taxonomy_term_load_multiple_by_name($exif_section, $chosen_vocabulary);
          if (is_array($terms) && count($terms) > 0) {
            $section_term = array_shift($terms);
          }
          else {
            $section_term = $this->create_term($chosen_vocabulary, $exif_section);
          }
          $parent_term = $this->create_term($chosen_vocabulary, $exif_name, $section_term->id());
        }
        $term = $this->create_term($chosen_vocabulary, $exif_value, $parent_term->id());
      }
      $field->offsetSet($index, $term->id());
    }
  }

  /**
   *
   * @param $vid
   * @param $name
   * @param $parent_term
   * @return unknown_type
   */
  function create_term($vid, $name, $parent_term_id = 0) {
    $values = [
      'vid' => $vid,
      'name' => $name,
      'parent' => $parent_term_id
    ];
    $term = Term::create($values);
    $term->save();
    return $term;
  }

}
