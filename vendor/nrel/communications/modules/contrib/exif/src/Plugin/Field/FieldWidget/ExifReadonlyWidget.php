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
 *   id = "exif_readonly",
 *   label = @Translation("metadata from image (viewable in forms)"),
 *   description = @Translation("field content is calculated from image field in the same content type (field are viewable but readonly in forms)"),
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
class ExifReadonlyWidget extends ExifFieldWidgetBase {
  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = $items->getValue();
    $entity_type = $items->getFieldDefinition()->getTargetEntityTypeId();
    $access = \Drupal::entityManager()->getAccessControlHandler($entity_type)->fieldAccess('view', $items->getFieldDefinition());
    if (!$access) {
      $element += array(
        '#type' => '#hidden',
        '#value' => ''
      );
    }
    $element +=  $items->view();
    $element += array(
      '#value' => $value,
      '#default_value' => $value
    );
    return $element;
  }
}

?>
