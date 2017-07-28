<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Generates a sketch from an image.
 *
 * @ImageEffect(
 *   id = "image_sketch",
 *   label = @Translation("Sketch"),
 *   description = @Translation("Generates a sketch from an image.")
 * )
 */
class SketchImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('sketch', $this->configuration)) {
      $this->logger->error('Image sketch failed using the %toolkit toolkit on %path (%mimetype)', array(
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
      'radius' => '8',
      'sigma' => '8',
      'angle' => '0',
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
    $form['angle'] = array(
      '#type' => 'number',
      '#title' => $this->t('Angle'),
      '#description' => $this->t('Apply the effect along this angle.'),
      '#default_value' => $this->configuration['angle'],
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
    $this->configuration['angle'] = $form_state->getValue('angle');
  }

}
