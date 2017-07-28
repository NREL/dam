<?php
/**
 * Created by IntelliJ IDEA.
 * User: jphautin
 * Date: 02/06/16
 * Time: 01:04
 */

namespace Drupal\exif\Plugin\Field\FieldWidget;


use Drupal\Core\Form\FormStateInterface;
use Drupal\exif\ExifFactory;

abstract class ExifFieldWidgetBase extends ExifWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $exif_fields = $this->retrieveExifFields();
    $default_exif_value = $this->retrieveExifFieldDefaultValue();
    $default_exif_separator_value = $this->retrieveExifFieldDefaultSeparatorValue();
    $element['exif_field'] = array(
      '#type' => 'select',
      '#title' => t('exif field data'),
      '#description' => t('choose to retrieve data from the image field referenced with the selected name or by naming convention.'),
      '#options' => array_merge(array('naming_convention' => 'name of the field is used as the exif field name'), $exif_fields),
      '#default_value' => $default_exif_value,
      '#element_validate' => array(
        array(
          get_class($this),
          'validateExifField'
        )
      )
    );
    $element['exif_field_separator'] = array(
      '#type' => 'textfield',
      '#title' => t('exif field separator'),
      '#description' => t('separator used to split values (if field definition support several values). let it empty if you do not want to split values.'),
      '#default_value' => $default_exif_separator_value,
      '#element_validate' => array(
        array(
          get_class($this),
          'validateExifFieldSeparator'
        )
      )
    );
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $exif_field_separator = $this->getSetting('exif_field_separator');
    if (isset($exif_field_separator) && strlen($exif_field_separator) == 1) {
      $exif_field_msg = t("exif value will be split using character separator '@separator'", array('@separator' => $exif_field_separator));
    }
    else {
      $exif_field_msg = t('exif value will be extracted as one value');
    }
    array_unshift($summary, $exif_field_msg);

    $exif_field = $this->getSetting('exif_field');
    if (isset($exif_field) && $exif_field != 'naming_convention') {
      $exif_field_msg = t("exif data will be extracted from image metadata field '@metadata'", array('@metadata' => $exif_field));
    }
    else {
      $fieldname = $this->fieldDefinition->getName();
      $exif_field = str_replace("field_", "", $fieldname);
      $exif_field_msg = t("Using naming convention. so the exif data will be extracted from image metadata field '@metadata'", array('@metadata' => $exif_field));
    }
    array_unshift($summary, $exif_field_msg);

    return $summary;
  }


  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'exif_field_separator' => '',
      'exif_field' => 'naming_convention',
    ) + parent::defaultSettings();
  }

  public static function validateExifField($element, FormStateInterface $form_state, $form) {
    $elementSettings = $form_state->getValue($element['#parents']);
    if (!$elementSettings) {
      $message = t('you must choose at least one method to retrieve image metadata.');
      $form_state->setErrorByName('exif_field', $message);
    }
  }

  public static function validateExifFieldSeparator($element, &$form_state) {
    $elementSettings = $form_state->getValue($element['#parents']);
    if (!empty($elementSettings) && strlen($elementSettings) > 1) {
      $message = t('the separator is only one character long.');
      $form_state->setErrorByName('exif_field_separator', $message);
    }
  }

  /**
   * @return array of possible exif fields
   */
  private function retrieveExifFields() {
    $exif = ExifFactory::getExifInterface();
    return $exif->getFieldKeys();
  }


  private function retrieveExifFieldDefaultValue() {
    $result = $this->getSetting('exif_field');
    if (empty($result)) {
      $result = 'naming_convention';
    }
    return $result;
  }

  private function retrieveExifFieldDefaultSeparatorValue() {
    $result = $this->getSetting('exif_field_separator');
    if (empty($result)) {
      $result = '';
    }
    return $result;
  }

}

?>
