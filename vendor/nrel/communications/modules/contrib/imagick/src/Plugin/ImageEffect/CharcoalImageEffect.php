<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Applies the charcoal effect on an image resource.
 *
 * @ImageEffect(
 *   id = "image_charcoal",
 *   label = @Translation("Charcoal"),
 *   description = @Translation("Applies the charcoal effect on an image.")
 * )
 */
class CharcoalImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('charcoal', $this->configuration)) {
      $this->logger->error('Image charcoal failed using the %toolkit toolkit on %path (%mimetype)', array(
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
      'radius' => '16',
      'sigma' => '16',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['radius'] = array(
      '#type' => 'number',
      '#title' => $this->t('Radius'),
      '#description' => $this->t('The radius of the Gaussian, in pixels, not counting the center pixel.'),
      '#default_value' => $this->configuration['radius'],
    );
    $form['sigma'] = array(
      '#type' => 'number',
      '#title' => $this->t('Sigma'),
      '#description' => $this->t('The standard deviation of the Gaussian, in pixels'),
      '#default_value' => $this->configuration['sigma'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['radius'] = $form_state->getValue('radius');
    $this->configuration['sigma'] = $form_state->getValue('sigma');
  }

}
