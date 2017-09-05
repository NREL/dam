<?php

namespace Drupal\serial\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'dam_media_serial_default_formatter'.
 *
 * @FieldFormatter(
 *   id = "dam_media_serial_default_formatter",
 *   label = @Translation("Dam_Media_Serial default"),
 *   field_types = {
 *     "dam_media_serial",
 *   },
 * )
 */
class Dam_Media_SerialDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      // Render output using dam_media_serial_default theme.
      $source = [
        '#theme' => 'dam_media_serial_default',
        '#dam_media_serial_id' => $item->value,
      ];
      $elements[$delta] = [
        '#markup' => \Drupal::service('renderer')->render($source),
      ];
    }
    return $elements;
  }

}
