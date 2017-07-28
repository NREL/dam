<?php

namespace Drupal\views_bulk_edit\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;

/**
 * A action deriver.
 */
class BulkEditDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    foreach ($this->getEnabledEntityTypeIds() as $entity_type_id) {
      $this->derivatives[$entity_type_id] = $base_plugin_definition;
      $this->derivatives[$entity_type_id]['type'] = $entity_type_id;
    }

    return $this->derivatives;
  }

  /**
   * Getes the enabled entity type Ids.
   *
   * @return array
   *   An array of enabled entity type Ids.
   */
  protected function getEnabledEntityTypeIds() {
    return \Drupal::config('views_bulk_edit.settings')->get('enabled_entity_type_ids') ?: ['node'];
  }

}
