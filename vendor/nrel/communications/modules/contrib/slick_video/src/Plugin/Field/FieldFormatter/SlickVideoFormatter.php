<?php

namespace Drupal\slick_video\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\slick\SlickDefault;
use Drupal\slick\SlickFormatterInterface;
use Drupal\slick\SlickManagerInterface;
use Drupal\slick\Plugin\Field\FieldFormatter\SlickFormatterTrait;
use Drupal\blazy\Dejavu\BlazyVideoBase;
use Drupal\blazy\Dejavu\BlazyVideoTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the slick video field formatter.
 *
 * @FieldFormatter(
 *   id = "slick_video",
 *   label = @Translation("Slick Video"),
 *   field_types = {
 *     "video_embed_field"
 *   }
 * )
 */
class SlickVideoFormatter extends BlazyVideoBase implements ContainerFactoryPluginInterface {

  use SlickFormatterTrait;
  use BlazyVideoTrait;

  /**
   * Constructs a SlickMediaFormatter instance.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, SlickFormatterInterface $formatter, SlickManagerInterface $manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->formatter = $formatter;
    $this->manager   = $manager;
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
      $container->get('slick.formatter'),
      $container->get('slick.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return SlickDefault::extendedSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    // Early opt-out if the field is empty.
    if ($items->isEmpty()) {
      return $element;
    }

    // Collects specific settings to this formatter.
    $settings = $this->getSettings();

    // Overrides slick_image to use blazy template.
    $settings['theme_hook_image'] = $settings['lazy'] = 'blazy';
    $settings['blazy'] = TRUE;
    $build = ['settings' => $settings];

    $this->formatter->buildSettings($build, $items);

    // Fecthes URI from the first item to build dimensions once.
    $this->buildVideo($build['settings'], $items[0]->value);

    // Supports Blazy multi-breakpoint images if provided.
    if (!empty($build['settings']['uri'])) {
      $this->formatter->isBlazy($build['settings']);
    }

    // Build the elements.
    $this->buildElements($build, $items);

    return $this->manager->build($build);
  }

  /**
   * Build the slick carousel elements.
   */
  public function buildElements(array &$build, $items) {
    $settings = $build['settings'];
    $item_id  = $settings['item_id'];

    foreach ($items as $delta => $item) {
      $media_url = $item->value;

      $settings['delta'] = $delta;
      if (empty($media_url)) {
        continue;
      }

      $this->buildVideo($settings, $media_url);

      $element = ['item' => $item, 'settings' => $settings];

      // Image with responsive image, lazyLoad, and lightbox supports.
      $element[$item_id] = $this->formatter->getImage($element);
      $build['items'][$delta] = $element;

      if (!empty($settings['nav'])) {
        // Thumbnail usages: asNavFor pager, dot, arrows, photobox thumbnails.
        $element[$item_id] = empty($settings['thumbnail_style']) ? [] : $this->formatter->getThumbnail($element['settings']);

        $build['thumb']['items'][$delta] = $element;
      }
      unset($element);
    }
  }

  /**
   * Defines the scope for the form elements.
   */
  public function getScopedFormElements() {
    $elements = parent::getScopedFormElements();
    $elements['breakpoints'] = SlickDefault::getConstantBreakpoints();
    return $elements;
  }

}
