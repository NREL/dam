<?php

namespace Drupal\textimage\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\textimage\TextimageFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the Textimage text field formatter.
 *
 * @FieldFormatter(
 *   id = "textimage_text_field_formatter",
 *   label = @Translation("Textimage"),
 *   field_types = {
 *     "string",
 *     "string_long",
 *     "text",
 *     "text_with_summary",
 *     "text_long"
 *   }
 * )
 */
class TextimageTextFieldFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The Textimage factory service.
   *
   * @var \Drupal\textimage\TextimageFactory
   */
  protected $textimageFactory;

  /**
   * The image style entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $imageStyleStorage;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a TextimageTextFieldFormatter object.
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
   * @param \Drupal\textimage\TextimageFactory $textimage_factory
   *   The Textimage factory service.
   * @param \Drupal\Core\Entity\EntityStorageInterface $image_style_storage
   *   The image style entity storage.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, AccountInterface $current_user, TextimageFactory $textimage_factory, EntityStorageInterface $image_style_storage, LoggerInterface $logger) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->currentUser = $current_user;
    $this->textimageFactory = $textimage_factory;
    $this->imageStyleStorage = $image_style_storage;
    $this->logger = $logger;
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
      $container->get('textimage.factory'),
      $container->get('entity_type.manager')->getStorage('image_style'),
      $container->get('textimage.logger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'image_style' => '',
      'image_text_values' => 'merge',
      'image_link' => '',
      'image_alt' => '',
      'image_title' => '',
      'image_build_deferred' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    // Image style setting.
    $image_styles = $this->textimageFactory->getTextimageStyleOptions(TRUE);
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

    // Multi-value text field image generation settings.
    if ($this->fieldDefinition->getFieldStorageDefinition()->getCardinality() != 1) {
      $options = [
        'merge' => $this->t("Build one single image, styling together text values."),
        'itemize' => $this->t("Build multiple images, styling each text value in a separate image."),
      ];
      $element['image_text_values'] = [
        '#title' => $this->t('Multiple values text field'),
        '#type' => 'radios',
        '#default_value' => $this->getSetting('image_text_values'),
        '#options' => $options,
        '#required' => TRUE,
        '#description' => $this->t("Text values are styled following the sequence of 'Text overlay' effects in the image style."),
      ];
    }

    // Link setting.
    $link_types = [
      'content' => $this->t('Content'),
      'file' => $this->t('Styled image'),
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

    // Multi-value text field image generation settings.
    if ($this->fieldDefinition->getFieldStorageDefinition()->getCardinality() != 1) {
      $options = [
        'merge' => $this->t("Build one image"),
        'itemize' => $this->t("Build multiple images"),
      ];
      $summary[] = $this->t('Multiple text values: @option', ['@option' => $options[$this->getSetting('image_text_values')]]);
    }

    // Display link setting only if image is linked.
    $link_types = [
      'content' => $this->t('Linked to content'),
      'file' => $this->t('Linked to styled image'),
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
    // Get image style.
    $image_style = $this->imageStyleStorage->load($this->getSetting('image_style'));

    // Collect bubbleable metadata.
    $bubbleable_metadata = new BubbleableMetadata();

    // Provide token data for the displayed entity.
    $instance = $items->getFieldDefinition();
    $field = $instance->getFieldStorageDefinition();
    $token_data = [$instance->getTargetEntityTypeId() => $items->getEntity()];

    // Get text strings from the text field.
    $text = $this->textimageFactory->getTextFieldText($items);

    // Get alt and title text from the formatter settings, and resolve tokens.
    if ($image_alt = $this->getSetting('image_alt')) {
      $image_alt = $this->textimageFactory->processTextString($image_alt, NULL, $token_data, $bubbleable_metadata);
    }
    if ($image_title = $this->getSetting('image_title')) {
      $image_title = $this->textimageFactory->processTextString($image_title, NULL, $token_data, $bubbleable_metadata);
    }

    // Check if the formatter involves a link to the parent entity.
    $entity_url = $this->getSetting('image_link') == 'content' ? $items->getEntity()->urlInfo() : NULL;

    $elements = [];
    if ($field->getCardinality() != 1 && $this->getSetting('image_text_values') == 'itemize') {
      // Build separate image for each text value.
      foreach ($text as $text_value) {
        $textimage = $this->textimageFactory->get($bubbleable_metadata)
          ->setStyle($image_style)
          ->setTokenData($token_data)
          ->process($text_value);
        if (!$this->getSetting('image_build_deferred')) {
          $textimage->buildImage();
        }

        // Check if the formatter involves a link to the derived image.
        if (!$entity_url && $this->getSetting('image_link') == 'file') {
          $url = $textimage->getUrl();
        }
        else {
          $url = NULL;
        }

        $element = [
          '#theme' => 'textimage_formatter',
          '#uri' => $textimage->getUri(),
          '#width' => $textimage->getWidth(),
          '#height' => $textimage->getHeight(),
          '#alt' => $image_alt,
          '#title' => $image_title,
          '#anchor_url' => $entity_url ?: $url,
        ];
        $bubbleable_metadata->applyTo($element);
        $elements[] = $element;
      }
    }
    else {
      // Build single image with all text values.
      $textimage = $this->textimageFactory->get($bubbleable_metadata)
        ->setStyle($image_style)
        ->setTokenData($token_data)
        ->process($text);
      if (!$this->getSetting('image_build_deferred')) {
        $textimage->buildImage();
      }

      // Check if the formatter involves a link to the derived image.
      if (!$entity_url && $this->getSetting('image_link') == 'file') {
        $url = $textimage->getUrl();
      }
      else {
        $url = NULL;
      }

      $element = [
        '#theme' => 'textimage_formatter',
        '#uri' => $textimage->getUri(),
        '#width' => $textimage->getWidth(),
        '#height' => $textimage->getHeight(),
        '#alt' => $image_alt,
        '#title' => $image_title,
        '#anchor_url' => $entity_url ?: $url,
      ];
      $bubbleable_metadata->applyTo($element);
      $elements[] = $element;
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();
    $style_id = $this->getSetting('image_style');
    /** @var \Drupal\image\ImageStyleInterface $style */
    if ($style_id && $style = ImageStyle::load($style_id)) {
      // If this formatter uses a valid image style to display the image, add
      // the image style configuration entity as dependency of this formatter.
      $dependencies[$style->getConfigDependencyKey()][] = $style->getConfigDependencyName();
    }
    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function onDependencyRemoval(array $dependencies) {
    $changed = parent::onDependencyRemoval($dependencies);
    $style_id = $this->getSetting('image_style');
    /** @var \Drupal\image\ImageStyleInterface $style */
    if ($style_id && $style = ImageStyle::load($style_id)) {
      if (!empty($dependencies[$style->getConfigDependencyKey()][$style->getConfigDependencyName()])) {
        $replacement_id = $this->imageStyleStorage->getReplacementId($style_id);
        // If a valid replacement has been provided in the storage, replace the
        // image style with the replacement and signal that the formatter plugin
        // settings were updated.
        if ($replacement_id && ($image_style = ImageStyle::load($replacement_id))) {
          if ($this->textimageFactory->isTextimage($image_style)) {
            $this->setSetting('image_style', $replacement_id);
            $changed = TRUE;
          }
          else {
            $this->logger->warning('The style %replacement indicated for replacement is not valid for Textimage. The dependent configurations might need manual reconfiguration.', ['%replacement' => $replacement_id]);
          }
        }
      }
    }
    return $changed;
  }

}
