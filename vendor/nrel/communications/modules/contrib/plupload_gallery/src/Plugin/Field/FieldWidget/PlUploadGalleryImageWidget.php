<?php

namespace Drupal\plupload_gallery\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\plupload_widget\Plugin\Field\FieldWidget\ImageWidget as PlUploadImageWidget;
use Drupal\plupload_gallery\Plugin\Field\FieldWidget\PlUploadGalleryWidgetTrait;


/**
 * @FieldWidget(
 *   id = "plupload_gallery_widget",
 *   label = @Translation("Plupload Gallery"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class PlUploadGalleryImageWidget extends PlUploadImageWidget {

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

    $element += $this->widgetSettingsForm($form);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $summary = array_merge($summary, $this->widgetSettingSummary());

    return $summary;
  }

  /**
   * Override to replace the upload/file HTML control
   * with the PLUPLOAD form element.
   *
   */
  public static function process($element, FormStateInterface $form_state, $form) {

    $entity = $form_state->getFormObject()->getEntity();
    $entity_id= $entity->id();
    $element = parent::process($element, $form_state, $form);

    /** @var UploadConfiguration */
    $configuration = unserialize($form[$element['#parents'][0]]['#upload_configuration']);

    if ($configuration->cardinality != 1) {
      $element['upload']['#plupload_settings']['url'] = \Drupal::url('plupload_gallery.upload_image_handler', array(
        'plupload_token' => \Drupal::csrfToken()->get('plupload-handle-uploads'),
        'field_name' => $element['#field_name'],
        'entity_type' => $element['#entity_type'],
        'entity_id' => $entity_id
      ));
    }
    $element['upload']['#autoupload'] = FALSE;
    $element['upload']['#autosubmit'] = FALSE;
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function form(FieldItemListInterface $items, array &$form, FormStateInterface $form_state, $get_delta = NULL) {

    $element = parent::form($items, $form, $form_state, $get_delta);

    return $element;
  }


}
