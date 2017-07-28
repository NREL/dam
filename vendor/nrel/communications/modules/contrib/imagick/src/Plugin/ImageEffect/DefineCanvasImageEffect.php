<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Applies the define canvas effect on an image resource.
 *
 * @ImageEffect(
 *   id = "image_define_canvas",
 *   label = @Translation("Define canvas"),
 *   description = @Translation("Applies the define canvas effect on an image.")
 * )
 */
class DefineCanvasImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('define_canvas', $this->configuration)) {
      $this->logger->error('Image define canvas failed using the %toolkit toolkit on %path (%mimetype)', array(
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
      'HEX' => '#FFFFFF',
      'under' => TRUE,
      'exact_measurements' => TRUE,
      'exact' => array(
        'width' => '100',
        'height' => '100',
        'anchor' => 'center-center',
      ),
      'relative' => array(
        'leftdiff' => '20',
        'rightdiff' => '20',
        'topdiff' => '20',
        'bottomdiff' => '20',
      ),
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

    $form['HEX'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('HEX'),
      '#default_value' => $this->configuration['HEX'],
      '#attributes' => array(
        'class' => array('colorentry'),
      ),
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
    $form['matte_color']['#attached'] = array(
      'library' => array('imagick/colorpicker'),
    );

    $form['under'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Resize canvas <em>under</em> image (possibly cropping)'),
      '#default_value' => $this->configuration['under'],
      '#description' => $this->t('If <em>not</em> set, this will create a solid flat layer, probably totally obscuring the source image'),
    );

    $form['info'] = array('#value' => $this->t('Enter values in ONLY ONE of the below options. Either exact or relative. Most values are optional - you can adjust only one dimension as needed. If no useful values are set, the current base image size will be used.'));

    $form['exact_measurements'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Exact measurements'),
      '#default_value' => $this->configuration['exact_measurements'],
    );

    $form['exact'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Exact size'),
      'help' => array(
        '#markup' => $this->t('Set the canvas to a precise size, possibly cropping the image. Use to start with a known size.'),
        '#prefix' => '<p>',
        '#suffix' => '</p>',
      ),
      'width' => array(
        '#type' => 'textfield',
        '#title' => $this->t('Width'),
        '#default_value' => $this->configuration['exact']['width'],
        '#description' => $this->t('Enter a value in pixels or percent'),
        '#size' => 5,
      ),
      'height' => array(
        '#type' => 'textfield',
        '#title' => $this->t('Height'),
        '#default_value' => $this->configuration['exact']['height'],
        '#description' => $this->t('Enter a value in pixels or percent'),
        '#size' => 5,
      ),
      'anchor' => array(
        '#type' => 'radios',
        '#title' => $this->t('Anchor'),
        '#options' => array(
          'left-top' => $this->t('Top left'),
          'center-top' => $this->t('Top center'),
          'right-top' => $this->t('Top right'),
          'left-center' => $this->t('Center left'),
          'center-center' => $this->t('Center'),
          'right-center' => $this->t('Center right'),
          'left-bottom' => $this->t('Bottom left'),
          'center-bottom' => $this->t('Bottom center'),
          'right-bottom' => $this->t('Bottom right'),
        ),
        '#theme' => 'image_anchor',
        '#default_value' => $this->configuration['exact']['anchor'],
      ),
      '#states' => array(
        'visible' => array(
          ':input[name="data[exact_measurements]"]' => array('checked' => TRUE),
        ),
      ),
    );
    $form['relative'] = array(
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Relative size'),
      'help' => array(
        '#markup' => $this->t('Set the canvas to a relative size, based on the current image dimensions. Use to add simple borders or expand by a fixed amount. Negative values may crop the image.'),
        '#prefix' => '<p>',
        '#suffix' => '</p>',
      ),
      'leftdiff' => array(
        '#type' => 'textfield',
        '#title' => $this->t('left difference'),
        '#default_value' => $this->configuration['relative']['leftdiff'],
        '#size' => 6,
        '#description' => $this->t('Enter an offset in pixels.'),
      ),
      'rightdiff' => array(
        '#type' => 'textfield',
        '#title' => $this->t('right difference'),
        '#default_value' => $this->configuration['relative']['rightdiff'],
        '#size' => 6,
        '#description' => $this->t('Enter an offset in pixels.'),
      ),
      'topdiff' => array(
        '#type' => 'textfield',
        '#title' => $this->t('top difference'),
        '#default_value' => $this->configuration['relative']['topdiff'],
        '#size' => 6,
        '#description' => $this->t('Enter an offset in pixels.'),
      ),
      'bottomdiff' => array(
        '#type' => 'textfield',
        '#title' => $this->t('bottom difference'),
        '#default_value' => $this->configuration['relative']['bottomdiff'],
        '#size' => 6,
        '#description' => $this->t('Enter an offset in pixels.'),
      ),
      '#states' => array(
        'visible' => array(
          ':input[name="data[exact_measurements]"]' => array('checked' => FALSE),
        ),
      ),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['HEX'] = $form_state->getValue('HEX');
    $this->configuration['under'] = $form_state->getValue('under');
    $this->configuration['exact_measurements'] = $form_state->getValue('exact_measurements');

    $this->configuration['exact']['width'] = $form_state->getValue([
      'exact',
      'width'
    ]);
    $this->configuration['exact']['height'] = $form_state->getValue([
      'exact',
      'height'
    ]);
    $this->configuration['exact']['anchor'] = $form_state->getValue([
      'exact',
      'anchor'
    ]);

    $this->configuration['relative']['leftdiff'] = $form_state->getValue([
      'relative',
      'leftdiff'
    ]);
    $this->configuration['relative']['rightdiff'] = $form_state->getValue([
      'relative',
      'rightdiff'
    ]);
    $this->configuration['relative']['topdiff'] = $form_state->getValue([
      'relative',
      'topdiff'
    ]);
    $this->configuration['relative']['bottomdiff'] = $form_state->getValue([
      'relative',
      'bottomdiff'
    ]);
  }

}
