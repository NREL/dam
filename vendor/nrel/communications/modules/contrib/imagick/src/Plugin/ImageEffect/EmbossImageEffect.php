<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Applies the emboss effect on an image resource.
 *
 * @ImageEffect(
 *   id = "image_emboss",
 *   label = @Translation("Emboss"),
 *   description = @Translation("Applies the emboss effect on an image.")
 * )
 */
class EmbossImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('emboss', $this->configuration)) {
      $this->logger->error('Image emboss failed using the %toolkit toolkit on %path (%mimetype)', array(
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
      'sigma' => '8',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['radius'] = array(
      '#type' => 'number',
      '#title' => $this->t('Radius'),
      '#description' => $this->t('The radius of the emboss effect.'),
      '#default_value' => $this->configuration['radius'],
    );
    $form['sigma'] = array(
      '#type' => 'number',
      '#title' => $this->t('Sigma'),
      '#description' => $this->t('The sigma of the emboss effect.'),
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
