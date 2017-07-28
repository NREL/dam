<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Adds a wave effect to an image.
 *
 * @ImageEffect(
 *   id = "image_wave",
 *   label = @Translation("Wave"),
 *   description = @Translation("Adds a wave effect to an image.")
 * )
 */
class WaveImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('wave', $this->configuration)) {
      $this->logger->error('Image wave failed using the %toolkit toolkit on %path (%mimetype)', array(
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
      'amplitude' => '8',
      'length' => '64',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['amplitude'] = array(
      '#type' => 'number',
      '#title' => $this->t('Amplitude'),
      '#description' => $this->t('The amplitude of the wave effect.'),
      '#default_value' => $this->configuration['amplitude'],
    );
    $form['length'] = array(
      '#type' => 'number',
      '#title' => $this->t('Length'),
      '#description' => $this->t('The length of the wave effect.'),
      '#default_value' => $this->configuration['length'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['amplitude'] = $form_state->getValue('amplitude');
    $this->configuration['length'] = $form_state->getValue('length');
  }

}
