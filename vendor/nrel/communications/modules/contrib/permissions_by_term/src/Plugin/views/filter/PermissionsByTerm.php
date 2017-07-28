<?php

namespace Drupal\d8views\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\InOperator;
use Drupal\views\ViewExecutable;

/**
 * Filters by given list of node title options.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("d8views_node_titles")
 */
class PermissionsByTerm extends InOperator {

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = t('Allowed node titles');
    $this->definition['options callback'] = array($this, 'generateOptions');
  }

  /**
   * Override the query.
   *
   * So that no filtering takes place if the user doesn't
   * select any options.
   */
  public function query() {
    if (!empty($this->value)) {
      parent::query();
    }
  }

  /**
   * Skip validation.
   *
   * If no options have been chosen so we can use it as a non-filter.
   */
  public function validate() {
    if (!empty($this->value)) {
      parent::validate();
    }
  }

  /**
   * Helper function that generates the options.
   *
   * @return array
   *   Array keys are used to compare with the table field values.
   */
  public function generateOptions() {
    return array(
      'my title' => 'my title',
      'another title' => 'another title',
    );
  }

}
