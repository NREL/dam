<?php
/**
 * Created by IntelliJ IDEA.
 * User: jphautin
 * Date: 02/06/16
 * Time: 01:04
 */

namespace Drupal\exif\Plugin\Field\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'exif_html' widget.
 *
 * @FieldWidget(
 *   id = "exif_html",
 *   label = @Translation("metadata from image as html table"),
 *   description = @Translation("field content is calculated from image field in the same content type (field are hidden from forms)"),
 *   multiple_values = true,
 *   field_types = {
 *     "text",
 *     "text_long",
 *   }
 * )
 */
class ExifHtmlWidget extends ExifWidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'exif_field_separator' => '',
      'exif_field' => 'all_all',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element += array(
      '#type' => '',
      '#value' => '',
      '#process' => array(array(get_class($this), 'process')),
    );
    return $element;
  }
}