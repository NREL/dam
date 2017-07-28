<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Mirrors an image resource.
 *
 * @ImageEffect(
 *   id = "image_modulate",
 *   label = @Translation("Modulate"),
 *   description = @Translation("Modulates the image.")
 * )
 */
class ModulateImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('modulate', $this->configuration)) {
      $this->logger->error('Image modulate failed using the %toolkit toolkit on %path (%mimetype)', array(
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
      'brightness' => 100,
      'saturation' => 100,
      'hue' => 100,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['brightness'] = array(
      '#type' => 'number',
      '#title' => $this->t('Brightness in percentage'),
      '#default_value' => $this->configuration['brightness'],
    );
    $form['saturation'] = array(
      '#type' => 'number',
      '#title' => $this->t('Saturation in percentage'),
      '#default_value' => $this->configuration['saturation'],
    );
    $form['hue'] = array(
      '#type' => 'number',
      '#title' => $this->t('Hue in percentage'),
      '#default_value' => $this->configuration['hue'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['brightness'] = $form_state->getValue('brightness');
    $this->configuration['saturation'] = $form_state->getValue('saturation');
    $this->configuration['hue'] = $form_state->getValue('hue');
  }

}
