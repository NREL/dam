<?php

namespace Drupal\dam_iptc\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\media_entity\Entity\Media;
use Drupal\media_entity\Entity\MediaBundle;
use Drupal\dam_iptc\IPTC;

/**
 * Class IPTCNodeController.
 */
class IPTCNodeController extends ControllerBase {

  /**
   * Show_extracted_values.
   *
   * @return string
   *   Return Hello string.
   */
  public function show_extracted_values(\Drupal\media_entity\MediaInterface $media) {
    $content = '';
    $image = $media->get('image');
    $file = $image->referencedEntities();
    $uri = $file[0]->getFileUri();
    $file_absolute_path = \Drupal::getContainer()
      ->get('file_system')
      ->realpath($uri);
    $mime_content_type = mime_content_type($file_absolute_path);
    if ($mime_content_type == 'image/png') {
      drupal_set_message("IPTC data is only available for JPEG and TIFF files.");
      return;
    }
    $file_contents = file_get_contents($file_absolute_path);
    getimagesize($file_absolute_path, $info);
    if (isset($info['APP13'])) {
      $iptc_class = new IPTC();
      $iptc = iptcparse($info['APP13']);
      $iptc_fields = $iptc_class->dam_iptc_full_field_list();
      $rows = [];
      foreach ($iptc_fields AS $label => $key) {
        $label_machine = str_replace(' ', '_', $label);
        $machine_field = \Drupal::configFactory()
          ->getEditable('dam_iptc.settings')
          ->get($label_machine);
        $drupal_values = [];
        if ($machine_field <> '') {
          $drupal_values_for_field = $media->get($machine_field)->getValue();
          foreach ($drupal_values_for_field AS $drupal_value_for_field) {
            if (isset($drupal_value_for_field['target_id'])) {
              // If there is a target is is a term id so load the term and it's name
              if ($term = \Drupal\taxonomy\Entity\Term::load($drupal_value_for_field['target_id'])) {
                $drupal_values[] = $term->getName();
              }
            } else {
              $drupal_values[] = $drupal_value_for_field['value'];
            }
          }
        }
        $iptc_value = '';
        if (isset($iptc[$key])) {
          if (count($iptc[$key]) > 1) {
            $iptc_value = implode(', ', $iptc[$key]);
          } else {
            $iptc_value = $iptc[$key][0];
          }
        }
        $rows[] = [
          $label,
          $key,
          $iptc_value,
          (count($drupal_values) > 0 ? implode(', ', $drupal_values) : ''),
          $machine_field,
        ];
        unset($drupal_value_for_field);
      }
      if (count($rows) > 0) {
        $header = [
          'Field Name',
          'IPTC Code',
          'IPTC Extracted Value',
          'Drupal Field Value',
          'Drupal Field Mapping',
        ];
        $markup['table'] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
        ];
        $renderer = \Drupal::service('renderer');
        $content .= render($markup);
      }
    }

    return [
      '#type' => 'markup',
      '#markup' => $content,
    ];
  }

}
