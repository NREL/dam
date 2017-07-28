<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Posterizes an image.
 *
 * @ImageEffect(
 *   id = "image_posterize",
 *   label = @Translation("Posterize"),
 *   description = @Translation("Posterizes an image.")
 * )
 */
class PosterizeImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('posterize', $this->configuration)) {
      $this->logger->error('Image posterize failed using the %toolkit toolkit on %path (%mimetype)', array(
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
      'colors' => '5',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['colors'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Color levels per channel'),
      '#default_value' => $this->configuration['colors'],
      '#required' => TRUE,
      '#size' => 10,
      '#element_validate' => array('image_effect_integer_validate'),
      '#allow_negative' => FALSE,
      '#description' => $this->t('Number of unique values per color channel to reduce this image to. The transparency channel is left unchanged. This effect can be used to reduce file size on png images.'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['colors'] = $form_state->getValue('colors');
  }

}
