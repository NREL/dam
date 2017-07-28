<?php

namespace Drupal\textimage\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\image_effects\Plugin\ImageEffectsPluginManager;
use Drupal\textimage\TextimageFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Main Textimage settings admin form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The Textimage factory.
   *
   * @var \Drupal\textimage\TextimageFactory
   */
  protected $textimageFactory;

  /**
   * The font selector plugin manager.
   *
   * @var \Drupal\image_effects\Plugin\ImageEffectsPluginManager
   */
  protected $fontManager;

  /**
   * The Image factory.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * Constructs the class for Textimage settings form.
   *
   * @param \Drupal\textimage\TextimageFactory $textimage_factory
   *   The Textimage factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\image_effects\Plugin\ImageEffectsPluginManager $font_plugin_manager
   *   The font selector plugin manager.
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   The Image factory.
   */
  public function __construct(TextimageFactory $textimage_factory, ConfigFactoryInterface $config_factory, ImageEffectsPluginManager $font_plugin_manager, ImageFactory $image_factory) {
    parent::__construct($config_factory);
    $this->textimageFactory = $textimage_factory;
    $this->fontManager = $font_plugin_manager;
    $this->imageFactory = $image_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('textimage.factory'),
      $container->get('config.factory'),
      $container->get('plugin.manager.image_effects.font_selector'),
      $container->get('image.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'textimage_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['textimage.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('textimage.settings');

    $form['settings'] = [
      '#type' => 'container',
      '#tree' => TRUE,
      '#attributes' => [
        'id' => 'textimage-settings-main',
      ],
    ];

    // Main part of settings form.
    $form['settings']['main'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Main settings'),
    ];
    // Default image file format/extension.
    $extensions = $this->imageFactory->getSupportedExtensions();
    $options = array_combine($extensions, $extensions);
    $form['settings']['main']['default_extension'] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => $this->t('Default image file extension'),
      '#default_value' => $config->get('default_extension'),
      '#required' => TRUE,
      '#description' => $this->t('Select the default extension of the image files produced by Textimage. This can be overridden by image style effects that specify a format conversion like e.g. <em>Convert</em>. This setting does not affect image derivatives created by the Image module.'),
    ];
    // Default font.
    $font_plugin = $this->fontManager->getPlugin($this->config('image_effects.settings')->get('font_selector.plugin_id'));
    $form['settings']['main']['default_font_uri'] = $font_plugin->selectionElement([
      '#title' => $this->t('Default font'),
      '#description' => $this->t('Select the default font to be used by Textimage.'),
      '#default_value' => $config->get('default_font.uri'),
    ]);

    // URL generation.
    $form['settings']['url_generation'] = [
      '#type' => 'details',
      '#title' => $this->t('URL generation'),
      '#open' => TRUE,
    ];
    $form['settings']['url_generation']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#description' => $this->t("When selected, direct generation of Textimage images is enabled for users having the 'Generate Textimage URL derivatives' permission."),
      '#default_value' => $config->get('url_generation.enabled'),
    ];
    $form['settings']['url_generation']['text_separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text separator'),
      '#maxlength' => 5,
      '#required' => TRUE,
      '#description' => $this->t("Indicate the sequence of characters to be used to split the URL text string in separate strings. Each string will be consumed by a 'Text overlay' effect in the sequence specified within the image style. Note that slashes '/' and plus '+' characters are not allowed."),
      '#default_value' => $config->get('url_generation.text_separator'),
    ];

    // Maintenance.
    $form['settings']['maintenance'] = [
      '#type' => 'details',
      '#title' => $this->t('Maintenance'),
    ];
    $form['settings']['maintenance']['debug'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display debugging information'),
      '#default_value' => $config->get('debug'),
      '#description' => $this->t("Logs Textimage debug messages and shows them to users with the '%permission' permissions.", [
        '%permission' => implode(', ', [
          $this->t('Administer site configuration'),
          $this->t('Administer image styles'),
        ]),
      ]),
    ];
    $form['settings']['maintenance']['flush_all_label'] = [
      '#markup' => $this->t('Remove all image files generated via Textimage, flush all the image styles, and clear the Textimage cache.') . '<br/>',
    ];
    $form['settings']['maintenance']['flush_all'] = [
      '#type' => 'submit',
      '#name' => 'flush_all',
      '#value' => $this->t('Cleanup Textimage'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (preg_match('/[+\/]/', $form_state->getValue([
      'settings', 'url_generation', 'text_separator',
    ]))) {
      $form_state->setErrorByName('settings][url_generation][text_separator', $this->t('Invalid characters specified for the text separator.'));
    };
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('textimage.settings');

    // Redirect to cleanup if required.
    if ($form_state->getTriggeringElement()['#name'] == 'flush_all') {
      $form_state->setRedirect('textimage.flush_all');
      return;
    }

    // Main settings.
    $font_plugin = $this->fontManager->getPlugin($this->config('image_effects.settings')->get('font_selector.plugin_id'));
    $config
      ->set('default_extension', $form_state->getValue([
        'settings', 'main', 'default_extension',
      ]))
      ->set('default_font.name', $font_plugin->getDescription($form_state->getValue([
        'settings', 'main', 'default_font_uri',
      ])))
      ->set('default_font.uri', $form_state->getValue([
        'settings', 'main', 'default_font_uri',
      ]));

    // URL generation.
    $config
      ->set('url_generation.enabled', $form_state->getValue([
        'settings', 'url_generation', 'enabled',
      ]))
      ->set('url_generation.text_separator', $form_state->getValue([
        'settings', 'url_generation', 'text_separator',
      ]));

    // Maintenance.
    $config
      ->set('debug', $form_state->getValue([
        'settings', 'maintenance', 'debug',
      ]));

    $config->save();
    parent::submitForm($form, $form_state);
  }

}
