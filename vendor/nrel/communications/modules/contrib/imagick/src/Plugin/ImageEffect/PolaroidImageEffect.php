<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Adds a polaroid effect to the image.
 *
 * @ImageEffect(
 *   id = "image_polaroid",
 *   label = @Translation("Polaroid"),
 *   description = @Translation("Adds a polaroid effect to the image.")
 * )
 */
class PolaroidImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('polaroid', $this->configuration)) {
      $this->logger->error('Image polaroid failed using the %toolkit toolkit on %path (%mimetype)', array(
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
      'angle' => '10',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['angle'] = array(
      '#type' => 'number',
      '#title' => $this->t('angle'),
      '#description' => $this->t('The angle of the polaroid image. Leave this field empty to generate a random angle between -30 and 30 degrees.'),
      '#default_value' => $this->configuration['angle'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['angle'] = $form_state->getValue('angle');
  }

}
