<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Adds rounded corners to the image.
 *
 * @ImageEffect(
 *   id = "image_rounded_corners",
 *   label = @Translation("Rounded corners"),
 *   description = @Translation("Adds rounded corners to the image.")
 * )
 */
class RoundedCornersImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('rounded_corners', $this->configuration)) {
      $this->logger->error('Image rounded corners failed using the %toolkit toolkit on %path (%mimetype)', array(
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
      'x_rounding' => '50',
      'y_rounding' => '50',
      'stroke_width' => '10',
      'displace' => '5',
      'size_correction' => '-6',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['x_rounding'] = array(
      '#type' => 'number',
      '#title' => $this->t('X rounding'),
      '#description' => $this->t('The x rounding of the rounded corners'),
      '#default_value' => $this->configuration['x_rounding'],
    );
    $form['y_rounding'] = array(
      '#type' => 'number',
      '#title' => $this->t('Y rounding'),
      '#description' => $this->t('The y rounding of the rounded corners'),
      '#default_value' => $this->configuration['y_rounding'],
    );
    $form['stroke_width'] = array(
      '#type' => 'number',
      '#title' => $this->t('Stroke width'),
      '#description' => $this->t('The stroke width of the rounded corners (used to fine-tune the process)'),
      '#default_value' => $this->configuration['stroke_width'],
    );
    $form['displace'] = array(
      '#type' => 'number',
      '#title' => $this->t('Displace'),
      '#description' => $this->t('The displace of the rounded corners (used to fine-tune the process)'),
      '#default_value' => $this->configuration['displace'],
    );
    $form['size_correction'] = array(
      '#type' => 'number',
      '#title' => $this->t('Size correction'),
      '#description' => $this->t('The size correction of the rounded corners (used to fine-tune the process)'),
      '#default_value' => $this->configuration['size_correction'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['x_rounding'] = $form_state->getValue('x_rounding');
    $this->configuration['y_rounding'] = $form_state->getValue('y_rounding');
    $this->configuration['stroke_width'] = $form_state->getValue('stroke_width');
    $this->configuration['displace'] = $form_state->getValue('displace');
    $this->configuration['size_correction'] = $form_state->getValue('size_correction');
  }

}
