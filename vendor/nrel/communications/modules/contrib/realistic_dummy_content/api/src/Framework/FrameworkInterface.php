<?php

namespace Drupal\realistic_dummy_content_api\Framework;

/**
 * Defines and abstracts all functions which are used by our module.
 */
interface FrameworkInterface {

  /**
   * Allows third-party modules to alter data.
   */
  public function alter($type, &$data, &$context1 = NULL, &$context2 = NULL, &$context3 = NULL);

  /**
   * Gets configuration value.
   */
  public function configGet($name, $default = NULL);

  /**
   * Create an entity.
   *
   * @param array $info
   *   Associative array which can contain (defaults are the first
   *   value):
   *     entity_type => node|user|...
   *     dummy => FALSE|TRUE.
   */
  public function createEntity($info = array());

  /**
   * Use devel generate to generate dummy content.
   *
   * @param array $info
   *   Can contain:
   *     entity_type: user|node
   *     node_types: array
   *     users: array of users who can own a node
   *     title_legth
   *     num: int
   *     kill: bool.
   */
  public function develGenerate($info);

  /**
   * Prints debugging information.
   */
  public function debug($message, $info = NULL);

  /**
   * Checks whether an entity should be considered dummy content.
   */
  public function entityIsDummy($entity, $type);

  /**
   * Retrieve properties of an entity.
   *
   * Properties are different from fields. In Drupal 7, node titles are
   * properties, but field_image is a field. In Drupal 8, (almost?)
   * everything is a field.
   *
   * @param object $entity
   *   A Drupal entity object.
   *
   * @return array
   *   Array of properties.
   */
  public function entityProperties($entity);

  /**
   * Retrieve information about one field.
   *
   * @param string $name
   *   A field name.
   *
   * @return array
   *   Information about a field, corresponds to the return of
   *   field_info_field() in Drupal 7.
   */
  public function fieldInfoField($name);

  /**
   * Get information about fields.
   */
  public function fieldInfoFields();

  /**
   * Return a Drupal 7-style field name if possible for a given entity.
   *
   * For example if the field type is entity_reference, we can transform that
   * to taxonomy_term_reference for a given field in a given entity.
   *
   * @param array $info
   *   An associative array which can contain "entity" and "field_name" and
   *   "machine_name".
   *
   * @return string
   *   A Drupal 7-style field type machine name.
   */
  public function fieldTypeMachineName($info);

  /**
   * Saves a file to disk.
   */
  public function fileSave($drupal_file);

  /**
   * Return the default text filter.
   *
   * @return string
   *   filtered_html or basic_html... depending on the framework.
   */
  public function filteredHtml();

  /**
   * Formats a property to add it to an entity.
   *
   * In Drupal 7, this might be array(LANGUAGE_NONE => ...); in Drupal8 it is
   * just the file id, or value.
   *
   * @param string $type
   *   Can be 'file', 'value', ...
   * @param mixed $value
   *   A file, or string...
   * @param array $options
   *   Extra options such as the format.
   *
   * @return mixed
   *   The file data formatted for placement in an entity.
   */
  public function formatProperty($type, $value, $options = array());

  /**
   * Return the root path of the framework.
   */
  public function frameworkRoot();

  /**
   * Retrieves all available vocabularies.
   */
  public function getAllVocabularies();

  /**
   * Gets the bundle name of an entity.
   */
  public function getBundleName($entity);

  /**
   * Retrives the property value for an entity.
   */
  public function getEntityProperty(&$entity, $property);

  /**
   * Gets the path to a module or theme.
   */
  public function getPath($type, $name);

  /**
   * React to an entity just before it is saved.
   */
  public function hookEntityPresave($entity, $type);

  /**
   * Hook called in D7 when a user is inserted.
   */
  public function hookUserInsert(&$edit, $account, $category);

  /**
   * Hook called in D7 when a user is saved.
   */
  public function hookUserPresave(&$edit, $account, $category);

  /**
   * Check if a module exists.
   */
  public function moduleExists($module);

  /**
   * Invokes all hooks.
   */
  public function moduleInvokeAll($hook);

  /**
   * Gets a list of modules.
   */
  public function moduleList();

  /**
   * Creates a new vocabulary term.
   *
   * @param object $vocabulary
   *   The vocabulary object.
   * @param string $name
   *   The name of the new taxonomy term.
   *
   * @return object
   *   The taxonomy term object.
   */
  public function newVocabularyTerm($vocabulary, $name);

  /**
   * Sets the property of an entity.
   */
  public function setEntityProperty(&$entity, $property, $value);

  /**
   * Gets state information.
   */
  public function stateGet($name, $default = NULL);

  /**
   * Starts a timer, see timer_start() in Drupal 7.
   */
  public function timerStart($id);

  /**
   * Stops a timer and returns data, see timer_stop() in Drupal 7.
   */
  public function timerStop($id);

  /**
   * Return the filename of a user picture.
   *
   * @param object $user
   *   A Drupal user object.
   *
   * @return string
   *   A string representing the filename of the user picture if possible.
   */
  public function userPictureFilename($user);

  /**
   * Delete a variable.
   */
  public function variableDel($variable);

  /**
   * Returns the unique identifier for the vocabulary.
   *
   * @param object $vocabulary
   *   The vocabulary object.
   *
   * @return mixed
   *   A unique identifier for this framework.
   */
  public function vocabularyIdentifier($vocabulary);

  /**
   * Logs something to the watchdog.
   *
   * See watchdog() for more details on this function.
   *
   * @param int $severity
   *   The litteral severity as defined in:
   *   https://api.drupal.org/api/drupal/includes%21bootstrap.inc/function/watchdog/7.x,
   *   The default being WATCHDOG_NOTICE or 5. We cannot use the constants here
   *   because PHPUnit and Drupal 8 do not know about them.
   */
  public function watchdog($message, $severity = 5);

}
