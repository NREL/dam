<?php

/**
 * @file
 * Definition of Drupal\ipsum\Plugin\ipsum\ProviderInterface.
 */

namespace Drupal\ipsum\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;

/**
 * Defines the interface for text generating ipsum plugins.
 *
 * Plugins are discovered through annotations, which may contain the following
 * definition properties:
 * - title: (required) An administrative summary of what the ipsum does.
 * - description: Optional additional administrative information about the
 *   provider's behavior.
 *
 * Most implementations want to extend the generic basic implementation for
 * ipsum provider plugins.
 *
 * @see \Drupal\ipsum\Plugin\ipsum\ProviderBase
 */
interface ProviderInterface extends ConfigurablePluginInterface, PluginInspectionInterface {

  /**
   * Ipsum type constants.
   */
  const TYPE_WORD = 0;
  const TYPE_SENTENCE = 1;
  const TYPE_PARAGRAPH = 2;

  /**
   * Returns the administrative label for this provider plugin.
   *
   * @return string
   */
  public function getLabel();

  /**
   * Returns the administrative description for this provider plugin.
   *
   * @return string
   */
  public function getDescription();

  /**
   * Generates a plugin's settings form.
   *
   * @param array $form
   *   A minimally prepopulated form array.
   * @param array $form_state
   *   The state of the (entire) configuration form.
   *
   * @return array
   *   The $form array with additional form elements for the settings of this
   *   filter. The submitted form values should match $this->settings.
   */
  public function settingsForm(array $form, array &$form_state);

  /**
   * Returns an array of words that make up this provider's vocabulary.
   *
   * @return array
   */
  public function getVocabulary();

  /**
   * Returns a string of generated dummy filler content.
   *
   * @TODO add configuration
   *
   * @return string
   */
  public function generate();

}
