<?php

namespace Drupal\slick_media\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\slick\SlickDefault;
use Drupal\slick\Plugin\Field\FieldFormatter\SlickEntityReferenceFormatterBase;
use Drupal\blazy\Dejavu\BlazyVideoTrait;

/**
 * Plugin implementation of the 'slick media entity' formatter.
 *
 * @FieldFormatter(
 *   id = "slick_media",
 *   label = @Translation("Slick Media"),
 *   description = @Translation("Display the referenced entities as a Slick carousel."),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   quickedit = {
 *     "editor" = "disabled"
 *   }
 * )
 */
class SlickMediaFormatter extends SlickEntityReferenceFormatterBase {

  use BlazyVideoTrait;

  /**
   * Returns the blazy manager.
   */
  public function blazyManager() {
    return $this->formatter;
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
    $entities = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($entities)) {
      return [];
    }

    // Collects specific settings to this formatter.
    $settings = $this->getSettings();

    // Asks for Blazy to deal with iFrames, and mobile-optimized lazy loading.
    $settings['blazy']     = TRUE;
    $settings['plugin_id'] = $this->getPluginId();

    // Sets dimensions once to reduce method ::transformDimensions() calls.
    // @todo: A more flexible way to also support paragraphs at one go.
    if (!empty($settings['image_style']) && ($entities[0]->getEntityTypeId() == 'media')) {
      $fields = $entities[0]->getFields();

      if (isset($fields['thumbnail'])) {
        $item = $fields['thumbnail']->get(0);

        $settings['item'] = $item;
        $settings['uri']  = $item->entity->getFileUri();
      }
    }

    $build = ['settings' => $settings];

    $this->formatter->buildSettings($build, $items);

    // Build the elements.
    $this->buildElements($build, $entities, $langcode);

    return $this->manager()->build($build);
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $storage = $field_definition->getFieldStorageDefinition();
    return $storage->isMultiple() && $storage->getSetting('target_type') === 'media';
  }

}
