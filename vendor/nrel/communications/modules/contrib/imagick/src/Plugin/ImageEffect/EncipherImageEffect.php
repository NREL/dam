<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Applies the encipher effect on an image resource.
 *
 * @ImageEffect(
 *   id = "image_encipher",
 *   label = @Translation("Encipher"),
 *   description = @Translation("Applies the encipher effect on an image.")
 * )
 */
class EncipherImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('encipher', $this->configuration)) {
      $this->logger->error('Image encipher failed using the %toolkit toolkit on %path (%mimetype)', array(
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
      'password' => '',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['password'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Password to encrypt the image'),
      '#default_value' => $this->configuration['password'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['password'] = $form_state->getValue('password');
  }

}
