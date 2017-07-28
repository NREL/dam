<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Generates a shadow around an image.
 *
 * @ImageEffect(
 *   id = "image_shadow",
 *   label = @Translation("Shadow"),
 *   description = @Translation("Generates a shadow around an image.")
 * )
 */
class ShadowImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('shadow', $this->configuration)) {
      $this->logger->error('Image shadow failed using the %toolkit toolkit on %path (%mimetype)', array(
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
      'color' => '#454545',
      'opacity' => '100',
      'sigma' => '25',
      'x' => '0',
      'y' => '0',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('colorform'),
      ),
    );
    $form['color'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Color of the shadow'),
      '#default_value' => $this->configuration['color'],
      '#attributes' => array('class' => array('colorentry')),
    );
    $form['colorpicker'] = array(
      '#weight' => -1,
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('colorpicker'),
        'style' => array('float:right'),
      ),
    );
    // Add Farbtastic color picker.
    $form['color']['#attached'] = array(
      'library' => array('imagick/colorpicker'),
    );
    $form['opacity'] = array(
      '#type' => 'number',
      '#title' => $this->t('Opacity'),
      '#description' => $this->t('The opacity of the shadow'),
      '#default_value' => $this->configuration['opacity'],
    );
    $form['sigma'] = array(
      '#type' => 'number',
      '#title' => $this->t('Sigma'),
      '#description' => $this->t('The sigma of the shadow'),
      '#default_value' => $this->configuration['sigma'],
    );
    $form['x'] = array(
      '#type' => 'number',
      '#title' => $this->t('X'),
      '#description' => $this->t('The X value of the shadow'),
      '#default_value' => $this->configuration['x'],
    );
    $form['y'] = array(
      '#type' => 'number',
      '#title' => $this->t('Y'),
      '#description' => $this->t('The Y value of the shadow'),
      '#default_value' => $this->configuration['y'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['color'] = $form_state->getValue('color');
    $this->configuration['opacity'] = $form_state->getValue('opacity');
    $this->configuration['sigma'] = $form_state->getValue('sigma');
    $this->configuration['x'] = $form_state->getValue('x');
    $this->configuration['y'] = $form_state->getValue('y');
  }

}
