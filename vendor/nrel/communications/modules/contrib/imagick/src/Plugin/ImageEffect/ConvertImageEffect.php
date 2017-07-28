<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;
use Drupal\imagick\ImagickConst;

/**
 * Blurs an image resource.
 *
 * @ImageEffect(
 *   id = "image_convert",
 *   label = @Translation("Convert"),
 *   description = @Translation("Converts image's filetype and quality")
 * )
 */
class ConvertImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('convert', $this->configuration)) {
      $this->logger->error('Image convert failed using the %toolkit toolkit on %path (%mimetype)', array(
        '%toolkit' => $image->getToolkitId(),
        '%path' => $image->getSource(),
        '%mimetype' => $image->getMimeType()
      ));
      return FALSE;
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'format' => 'image/jpeg',
      'quality' => \Drupal::config('imagick.config')->get('jpeg_quality'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['format'] = array(
      '#title' => $this->t("File format"),
      '#type' => 'select',
      '#default_value' => $this->configuration['format'],
      '#options' => ImagickConst::imagick_file_formats(),
    );
    $form['quality'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Quality'),
      '#description' => $this->t('Override the default image quality. Works for Imagemagick only. Ranges from 0 to 100. Higher values mean better image quality but bigger files.'),
      '#size' => 3,
      '#maxlength' => 3,
      '#default_value' => $this->configuration['quality'],
      '#field_suffix' => '%',
      '#states' => [
        'visible' => [
          ':input[name="data[format]"]' => ['value' => 'image/jpeg'],
        ],
      ],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['format'] = $form_state->getValue('format');
    $this->configuration['quality'] = $form_state->getValue('quality');
  }

}
