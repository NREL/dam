<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Adds a swirl effect to an image.
 *
 * @ImageEffect(
 *   id = "image_swirl",
 *   label = @Translation("Swirl"),
 *   description = @Translation("Adds a swirl effect to an image.")
 * )
 */
class SwirlImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('swirl', $this->configuration)) {
      $this->logger->error('Image swirl failed using the %toolkit toolkit on %path (%mimetype)', array(
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
      'degrees' => '90',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['degrees'] = array(
      '#type' => 'number',
      '#title' => $this->t('Degrees'),
      '#description' => $this->t('The number of degrees of the swirl effect.'),
      '#default_value' => $this->configuration['degrees'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['degrees'] = $form_state->getValue('degrees');
  }

}
