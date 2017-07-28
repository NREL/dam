<?php

namespace Drupal\textimage\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use Drupal\textimage\TextimageFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the Textimage image field formatter.
 *
 * @FieldFormatter(
 *   id = "textimage_image_field_formatter",
 *   label = @Translation("Textimage"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class TextimageImageFieldFormatter extends ImageFormatter {

  /**
   * The Textimage factory service.
   *
   * @var \Drupal\textimage\TextimageFactory
   */
  protected $textimageFactory;

  /**
   * Constructs a TextimageImageFieldFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityStorageInterface $image_style_storage
   *   The image style entity storage.
   * @param \Drupal\textimage\TextimageFactory $textimage_factory
   *   The Textimage factory service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, AccountInterface $current_user, EntityStorageInterface $image_style_storage, TextimageFactory $textimage_factory) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $current_user, $image_style_storage);
    $this->textimageFactory = $textimage_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('current_user'),
      $container->get('entity_type.manager')->getStorage('image_style'),
      $container->get('textimage.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'image_alt' => '',
      'image_title' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    // Image style setting.
    $image_styles = $this->textimageFactory->getTextimageStyleOptions();
    if (empty($image_styles)) {
      $image_styles[''] = $this->t('No Textimage style available');
    }
    $description_link = Link::fromTextAndUrl(
      $this->t('Configure Image Styles'),
      Url::fromRoute('entity.image_style.collection')
    );
    $element['image_style'] = [
      '#title' => $this->t('Image style'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('image_style'),
      '#options' => $image_styles,
      '#required' => TRUE,
      '#description' => $description_link->toRenderable() + [
        '#access' => $this->currentUser->hasPermission('administer image styles'),
      ],
    ];

    // Link setting.
    $link_types = [
      'content' => $this->t('Content'),
      'file' => $this->t('Original image'),
      'derivative' => $this->t('Styled image'),
    ];
    $element['image_link'] = [
      '#title' => $this->t('Link image to'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('image_link'),
      '#empty_option' => $this->t('Nothing'),
      '#options' => $link_types,
    ];

    // Image alt and title attribute settings.
    $description = $this->t('This text will be used by screen readers, search engines, or when the image cannot be loaded.');
    $description .= ' ' . $this->t('Tokens can be used.');
    if ($this->fieldDefinition->getType() == 'image') {
      $description .= ' ' . $this->t('Leave empty to use the alternative text set on content level.');
    }
    $element['image_alt'] = [
      '#title' => $this->t('Alternative text'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('image_alt'),
      '#description' => $description,
      '#maxlength' => 512,
    ];
    $description = $this->t('The title is used as a tool tip when the user hovers the mouse over the image.');
    $description .= ' ' . $this->t('Tokens can be used.');
    if ($this->fieldDefinition->getType() == 'image') {
      $description .= ' ' . $this->t('Leave empty to use the title set on content level.');
    }
    $element['image_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $this->getSetting('image_title'),
      '#description' => $description,
      '#maxlength' => 1024,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $image_styles = $this->textimageFactory->getTextimageStyleOptions();
    // Unset possible 'No defined styles' option.
    unset($image_styles['']);
    // Styles could be lost because of enabled/disabled modules that defines
    // their styles in code.
    $image_style_setting = $this->getSetting('image_style');
    if (isset($image_styles[$image_style_setting])) {
      $summary[] = $this->t('Image style: @style', ['@style' => $image_styles[$image_style_setting]]);
    }
    else {
      $summary[] = $this->t('Image style: undefined');
    }

    // Display link setting only if image is linked.
    $link_types = [
      'content' => $this->t('Linked to content'),
      'file' => $this->t('Linked to original image'),
      'derivative' => $this->t('Linked to styled image'),
    ];
    if (isset($link_types[$this->getSetting('image_link')])) {
      $summary[] = $link_types[$this->getSetting('image_link')];
    }

    // Display this setting only if alt text is specified.
    if ($this->getSetting('image_alt')) {
      $summary[] = $this->t('Alternative text: @image_alt', ['@image_alt' => $this->getSetting('image_alt')]);
    }

    // Display this setting only if title is specified.
    if ($this->getSetting('image_title')) {
      $summary[] = $this->t('Title: @image_title', ['@image_title' => $this->getSetting('image_title')]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $files = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($files)) {
      return $elements;
    }

    // Get image style.
    $image_style = $this->imageStyleStorage->load($this->getSetting('image_style'));

    // Collect bubbleable metadata.
    $bubbleable_metadata = new BubbleableMetadata();

    // Provide token data for the displayed entity.
    $instance = $items->getFieldDefinition();
    $token_data = [$instance->getTargetEntityTypeId() => $items->getEntity()];

    // Get alt and title text from the formatter settings, and resolve tokens.
    if ($image_alt = $this->getSetting('image_alt')) {
      $image_alt = $this->textimageFactory->processTextString($image_alt, NULL, $token_data, $bubbleable_metadata);
    }
    if ($image_title = $this->getSetting('image_title')) {
      $image_title = $this->textimageFactory->processTextString($image_title, NULL, $token_data, $bubbleable_metadata);
    }

    // Check if the formatter involves a link to the parent entity.
    $entity_url = $this->getSetting('image_link') == 'content' ? $items->getEntity()->urlInfo() : NULL;

    foreach ($files as $delta => $file) {
      $textimage = $this->textimageFactory->get($bubbleable_metadata)
        ->setStyle($image_style)
        ->setSourceImageFile($file)
        ->setTokenData($token_data)
        ->process(NULL);

      // Check if the formatter involves a link to the original or derived
      // image.
      if (!$entity_url) {
        switch ($this->getSetting('image_link')) {
          case 'file':
            $url = Url::fromUri(file_create_url($file->getFileUri()));
            break;

          case 'derivative':
            $url = $textimage->getUrl();
            break;

          default:
            $url = NULL;
            break;

        }
      }

      $elements[$delta] = [
        '#theme' => 'textimage_formatter',
        '#item' => $file->_referringItem,
        '#uri' => $textimage->getUri(),
        '#width' => $textimage->getWidth(),
        '#height' => $textimage->getHeight(),
        '#alt' => $image_alt,
        '#title' => $image_title,
        '#anchor_url' => $entity_url ?: $url,
      ];
      $bubbleable_metadata->applyTo($elements[$delta]);
    }

    return $elements;
  }

}
