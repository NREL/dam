<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Adds vignette to an image.
 *
 * @ImageEffect(
 *   id = "image_vignette",
 *   label = @Translation("Vignette"),
 *   description = @Translation("Adds vignette to an image.")
 * )
 */
class VignetteImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('vignette', $this->configuration)) {
      $this->logger->error('Image vignette failed using the %toolkit toolkit on %path (%mimetype)', array(
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
      'blackpoint' => '0.5',
      'whitepoint' => '0.5',
      'x' => '5',
      'y' => '5',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['blackpoint'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Blackpoint'),
      '#description' => $this->t('The black point'),
      '#default_value' => $this->configuration['blackpoint'],
    );
    $form['whitepoint'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Whitepoint'),
      '#description' => $this->t('The white point'),
      '#default_value' => $this->configuration['whitepoint'],
    );
    $form['x'] = array(
      '#type' => 'number',
      '#title' => $this->t('X'),
      '#description' => $this->t('The X offset of the ellipse'),
      '#default_value' => $this->configuration['x'],
    );
    $form['y'] = array(
      '#type' => 'number',
      '#title' => $this->t('Y'),
      '#description' => $this->t('The Y offset of the ellipse'),
      '#default_value' => $this->configuration['y'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['blackpoint'] = $form_state->getValue('blackpoint');
    $this->configuration['whitepoint'] = $form_state->getValue('whitepoint');
    $this->configuration['x'] = $form_state->getValue('x');
    $this->configuration['y'] = $form_state->getValue('y');
  }

}
