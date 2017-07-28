<?php

/**
 * @file
 * Contains \Drupal\plupload_gallery\Form\plupload_gallerySettingsForm.
 */

namespace Drupal\plupload_gallery\Form;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;

/**
 * Configure file system settings for this site.
 */
class PlUploadGalleryAdminForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'plupload_gallery_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['plupload_gallery.admin'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('plupload_gallery.admin');

    $form = array();

    // We select the content types here that cases in all places can be added to
    $types = NodeType::loadMultiple();
    //$types = node_type_get_types();
    $options = array();
    foreach($types as $type) {
      $name = $type->id();
      $options[$name] = $name;
    }
    $form['plupload_gallery_ctypes'] = array(
      '#type'          => 'select',
      '#title'         => t('Content Types That Cases can be added to'),
      '#multiple' 	 => TRUE,
      '#options' 		 => $options,
      '#default_value' => $config->get('plupload_gallery_ctypes'),
      '#description'   => t("Select the content types that cases can be added to."),
    );
    $form['plupload_gallery_anonymous_mouseover'] = array(
      '#type'          => 'textfield',
      '#title'         => t('Image Mouseover visible to anonymous users.'),
      '#default_value' => $config->get('plupload_gallery_anonymous_mouseover'),
      '#description'   => t('Anonymous users see this message on image mouseover whether diagnosis is known or unknown.')
    );
    $form['plupload_gallery_unknown_diagnosis'] = array(
      '#type'          => 'textfield',
      '#title'         => t('Unknown diagnosis'),
      '#default_value' => $config->get('plupload_gallery_unknown_diagnosis'),
      '#description'   => t('This text appears when a case is marked as unknown.')
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('plupload_gallery.admin');

    $form_state->cleanValues();
    foreach ($form_state->getValues() as $key => $value) {
      $config->set($key, $value);
    }

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
