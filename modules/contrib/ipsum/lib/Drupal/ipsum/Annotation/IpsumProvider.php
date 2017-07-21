<?php

/**
 * @file
 * Definition of Drupal\ipsum\Annotation\IpsumProvider.
 */

namespace Drupal\ipsum\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an ipsum annotation object.
 *
 * @Annotation
 */
class IpsumProvider extends Plugin {

  /**
   * The resource plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the resource plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * Additional administrative information about the plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation (optional)
   */
  public $description = '';

  /**
   * The default settings for the plugin.
   *
   * @var array (optional)
   */
  public $settings = array();
}
