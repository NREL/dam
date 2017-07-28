<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Adds spread to an image.
 *
 * @ImageEffect(
 *   id = "image_spread",
 *   label = @Translation("Spread"),
 *   description = @Translation("Adds spread to an image.")
 * )
 */
class SpreadImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('spread', $this->configuration)) {
      $this->logger->error('Image spread failed using the %toolkit toolkit on %path (%mimetype)', array(
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
      'radius' => 10,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['help'] = array(
      '#value' => $this->t('Special effects method that randomly displaces each pixel in a block defined by the radius parameter.')
    );
    $form['radius'] = array(
      '#type' => 'number',
      '#title' => $this->t('Radius'),
      '#description' => $this->t('The spread radius, in pixels.'),
      '#default_value' => $this->configuration['radius'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['radius'] = $form_state->getValue('radius');
  }

}
