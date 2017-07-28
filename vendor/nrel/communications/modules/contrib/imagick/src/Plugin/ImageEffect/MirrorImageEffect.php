<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Mirrors an image resource.
 *
 * @ImageEffect(
 *   id = "image_mirror",
 *   label = @Translation("Mirror"),
 *   description = @Translation("Mirrors the image.")
 * )
 */
class MirrorImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('mirror', $this->configuration)) {
      $this->logger->error('Image mirror failed using the %toolkit toolkit on %path (%mimetype)', array(
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
      'flip' => 0,
      'flop' => 0,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['flip'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Mirror image verticaly'),
      '#default_value' => $this->configuration['flip'],
    );
    $form['flop'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Mirror image horizontaly'),
      '#default_value' => $this->configuration['flop'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['flip'] = $form_state->getValue('flip');
    $this->configuration['flop'] = $form_state->getValue('flop');
  }

}
