<?php
/**
 * @file
 * Contains \Drupal\exif\Plugin\Field\FieldWidget\ExifReadonlytWidget.
 */

namespace Drupal\exif\Plugin\Field\FieldWidget;

use Drupal;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exif\ExifFactory;
use Drupal\field\Entity\FieldConfig;


/**
 * Plugin implementation of the 'exif_readonly' widget.
 *
 * @FieldWidget(
 *   id = "exif_hidden",
 *   label = @Translation("metadata from image (hidden in forms)"),
 *   description = @Translation("field content is calculated from image field in the same content type (field are hidden from forms)"),
 *   multiple_values = true,
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "entity_reference",
 *     "date",
 *     "datetime",
 *     "datestamp"
 *   }
 * )
 */
class ExifHiddenWidget extends ExifFieldWidgetBase {
  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    //$form['#attached']['css'][] = drupal_get_path('module', 'exif') . '/exif.css';
    $element += array(
      '#type' => '#hidden',
      '#value' => '',
      '#process' => array(array(get_class($this), 'process')),
    );
    return $element;
  }

  function process($element, FormStateInterface $form_state, $form) {

    $element['tid'] = array(
      '#type' => 'hidden',
      '#value' => '',
    );
    $element['value'] = array(
      '#type' => 'hidden',
      '#value' => '',
    );
    $element['timezone'] = array(
      '#type' => 'hidden',
      '#value' => '',
    );
    $element['value2'] = array(
      '#type' => 'hidden',
      '#value' => '',
    );

    $element['display'] = array(
      '#type' => 'hidden',
      '#value' => '',
    );
    return $element;
  }
}
?>
