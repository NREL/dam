<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Adds a trim effect to an image.
 *
 * @ImageEffect(
 *   id = "image_trim",
 *   label = @Translation("Trim"),
 *   description = @Translation("Remove edges that are the background color from the image.")
 * )
 */
class TrimImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('trim', $this->configuration)) {
      $this->logger->error('Image trim failed using the %toolkit toolkit on %path (%mimetype)', array(
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
  public function transformDimensions(array &$dimensions, $uri) {
    $dimensions['width'] = $dimensions['height'] = NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'fuzz' => '0',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['fuzz'] = array(
      '#type' => 'number',
      '#title' => $this->t('Fuzz'),
      '#description' => $this->t('The fuzz tolerance.'),
      '#default_value' => $this->configuration['fuzz'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['fuzz'] = $form_state->getValue('fuzz');
  }

}
