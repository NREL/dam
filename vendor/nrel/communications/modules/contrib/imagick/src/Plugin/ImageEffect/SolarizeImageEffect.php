<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Solarizes an image.
 *
 * @ImageEffect(
 *   id = "image_solarize",
 *   label = @Translation("Solarize"),
 *   description = @Translation("Solarizes an image.")
 * )
 */
class SolarizeImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('solarize', $this->configuration)) {
      $this->logger->error('Image solarize failed using the %toolkit toolkit on %path (%mimetype)', array(
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
      'threshold' => '30000',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['threshold'] = array(
      '#type' => 'number',
      '#title' => $this->t('Threshold'),
      '#description' => $this->t('The number of threshold of the solarize effect.'),
      '#default_value' => $this->configuration['threshold'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['threshold'] = $form_state->getValue('threshold');
  }

}
