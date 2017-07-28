<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;
use Drupal\imagick\ImagickConst;

/**
 * Blurs an image resource.
 *
 * @ImageEffect(
 *   id = "image_blur",
 *   label = @Translation("Blur"),
 *   description = @Translation("Blurs an image, different methods can be used.")
 * )
 */
class BlurImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('blur', $this->configuration)) {
      $this->logger->error('Image blur failed using the %toolkit toolkit on %path (%mimetype)', array(
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
      'type' => ImagickConst::NORMAL_BLUR,
      'radius' => '16',
      'sigma' => '16',
      'angle' => '0',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['help'] = array('#value' => $this->t('The intensity of the blur effect. For reasonable results, the radius should be larger than sigma.'));
    $form['type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Blur type'),
      '#options' => array(
        ImagickConst::NORMAL_BLUR => $this->t('Normal'),
        ImagickConst::ADAPTIVE_BLUR => $this->t('Adaptive'),
        ImagickConst::GAUSSIAN_BLUR => $this->t('Gaussian'),
        ImagickConst::MOTION_BLUR => $this->t('Motion'),
        ImagickConst::RADIAL_BLUR => $this->t('Radial'),
      ),
      '#default_value' => $this->configuration['type'],
    );
    $form['radius'] = array(
      '#type' => 'number',
      '#title' => $this->t('Radius'),
      '#description' => $this->t('The radius of the Gaussian, in pixels, not counting the center pixel.'),
      '#states' => array(
        'invisible' => array(
          ':input[name="data[type]"]' => array(
            'value' => ImagickConst::RADIAL_BLUR,
          ),
        ),
      ),
      '#default_value' => $this->configuration['radius'],
    );
    $form['sigma'] = array(
      '#type' => 'number',
      '#title' => $this->t('Sigma'),
      '#description' => $this->t('The standard deviation of the Gaussian, in pixels'),
      '#states' => array(
        'invisible' => array(
          ':input[name="data[type]"]' => array(
            'value' => ImagickConst::RADIAL_BLUR,
          ),
        ),
      ),
      '#default_value' => $this->configuration['sigma'],
    );
    $form['angle'] = array(
      '#type' => 'number',
      '#title' => $this->t('Angle'),
      '#description' => $this->t('The angle of the blur'),
      '#states' => array(
        'visible' => array(
          ':input[name="data[type]"]' => array(
            array('value' => ImagickConst::MOTION_BLUR),
            array('value' => ImagickConst::RADIAL_BLUR),
          ),
        ),
      ),
      '#default_value' => $this->configuration['angle'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['type'] = $form_state->getValue('type');
    $this->configuration['radius'] = $form_state->getValue('radius');
    $this->configuration['sigma'] = $form_state->getValue('sigma');
    $this->configuration['angle'] = $form_state->getValue('angle');
  }

}
