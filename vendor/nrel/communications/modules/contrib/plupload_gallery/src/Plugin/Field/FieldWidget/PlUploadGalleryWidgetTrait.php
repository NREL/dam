<?php

namespace Drupal\plupload_gallery\Plugin\Field\FieldWidget;

//use Drupal\Component\Utility\NestedArray;
//use Drupal\Component\Utility\Bytes;
//use Drupal\Core\Field\FieldDefinitionInterface;
//use Drupal\Core\Field\FieldFilteredMarkup;
//use Drupal\Core\Field\FieldItemListInterface;
//use Drupal\Core\Field\FieldStorageDefinitionInterface;
//use Drupal\Core\Field\WidgetBase;
//use Drupal\Core\Form\FormStateInterface;
//use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
//use Drupal\Core\Render\ElementInfoManagerInterface;
//use Drupal\file\Element\ManagedFile;
//use Drupal\file\Entity\File;
//use Drupal\Component\Utility\Xss;

trait PluploadGalleryWidgetTrait {

  public function widgetDefaults() {
    return array(
      'other_fields' => '',
      'field' => '',
      'manage_form_mode' => '',
      'uploads_form_mode' => '',
      'gallery_view_mode' => ''
    );
  }

  public function widgetSettingsForm($form) {
    $element = array();

    $displayManager = \Drupal::service('entity_display.repository');
    $form_modes = $displayManager->getFormModeOptionsByBundle($form['#entity_type'], $form['#bundle']);
    $form_mode_options = array();
    foreach ($form_modes as $mode => $setting) {
      $form_mode_options[$mode] = $mode;
    }

    $element['uploads_form_mode'] = array(
      '#type' => 'select',
      '#title' => t('Uploads tab form mode'),
      '#default_value' => $this->getSetting('uploads_form_mode'),
      '#options' => $form_mode_options,
      '#required' => TRUE,
    );

    $element['manage_form_mode'] = array(
      '#type' => 'select',
      '#title' => t('Manage tab form mode'),
      '#default_value' => $this->getSetting('manage_form_mode'),
      '#options' => $form_mode_options,
      '#required' => TRUE,
    );

    $form_modes = $displayManager->getViewModeOptionsByBundle($form['#entity_type'], $form['#bundle']);
    $view_mode_options = array();
    foreach ($form_modes as $mode => $setting) {
      $view_mode_options[$mode] = $mode;
    }
    $element['gallery_view_mode'] = array(
      '#type' => 'select',
      '#title' => t('Gallery tab view mode'),
      '#default_value' => $this->getSetting('gallery_view_mode'),
      '#options' => $view_mode_options,
      '#required' => TRUE,
    );
    return $element;

  }

  public function widgetSettingSummary() {
    $summary = array();
    $summary[] = t('Upload tab form mode: @uploads', array('@uploads' =>
      $this->getSetting('uploads_form_mode')));
    $summary[] = t('Manage tab form mode: @manage', array('@manage' =>
      $this->getSetting('manage_form_mode')));
    $summary[] = t('Gallery tab view mode: @manage', array('@manage' =>
      $this->getSetting('gallery_view_mode')));
    return $summary;
  }

  public function getBundle() {
    return array_shift($this->getFieldSetting('handler_settings')['target_bundles']);
  }

}
