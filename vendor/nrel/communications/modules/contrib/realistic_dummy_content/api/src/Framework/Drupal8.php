<?php

namespace Drupal\realistic_dummy_content_api\Framework;

use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Entity\Term;
use Drupal\Component\Utility\Timer;
use Drupal\field\Entity\FieldConfig;

if (!defined('WATCHDOG_ERROR')) {
  define('WATCHDOG_ERROR', 3);
}

/**
 * Drupal 8-specific code.
 */
class Drupal8 extends Framework implements FrameworkInterface {

  /**
   * {@inheritdoc}
   */
  public function userPictureFilename($user) {
    $value = $user->get('user_picture')->getValue();
    $id = $value[0]['target_id'];
    $file = file_load($id);
    return $file->getFileUri();
  }

  /**
   * {@inheritdoc}
   */
  public function hookEntityPresave($entity, $type) {
    try {
      // $type is NULL in Drupal8; we'll compute it here.
      $type = $this->getEntityType($entity);
      if (realistic_dummy_content_api_is_dummy($entity, $type)) {
        $candidate = $entity;
        realistic_dummy_content_api_improve_dummy_content($candidate, $type);
        realistic_dummy_content_api_validate($candidate, $type);
        $entity = $candidate;
      }
    }
    catch (Exception $e) {
      $this->setMessage(t('realistic_dummy_content_api failed to modify dummy objects: message: @m', array('@m' => $e->getMessage())), 'error', FALSE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fieldInfoField($field) {
    $fields = $this->fieldInfoFields();
    return $fields[$field];
  }

  /**
   * {@inheritdoc}
   */
  public function develGenerate($info) {
    if ($info['entity_type'] == 'node') {
      $info['entity_type'] = 'content';
    }
    $info = array_merge(array(
      'pass' => 'some-password',
      'time_range' => 0,
      'roles' => array(),
    ), $info);
    $plugin_manager = \Drupal::service('plugin.manager.develgenerate');
    $instance = $plugin_manager->createInstance($info['entity_type'], array());
    $instance->generate($info);
  }

  /**
   * Returns the entity type (e.g. node, user) as a string.
   *
   * @param object $entity
   *   A Drupal entity.
   *
   * @return string
   *   An entity type machine name (id).
   *
   * @throws Exception
   */
  public function getEntityType($entity) {
    return $entity->getEntityType()->id();
  }

  /**
   * Perform framework-specific tests, if any.
   */
  public function frameworkSpecificTests(&$errors, &$tests) {
    $node = $this->createDummyNode();

    $result = $this->getEntityType($node);
    if ($result == 'node') {
      $tests[] = 'Drupal8::getEntityType() works as expected.';
    }
    else {
      $errors[] = 'Drupal8::getEntityType() returned ' . $result;
    }
  }

  /**
   * Create a dummy node.
   *
   * @return object
   *   A Drupal node object.
   *
   * @throws Exception
   */
  public function createDummyNode() {
    $entity_type = 'node';
    $bundle = 'article';

    // Get definition of target entity type.
    $entity_def = \Drupal::entityManager()->getDefinition($entity_type);

    // Load up an array for creation.
    $new_node = array(
      // Set title.
      'title' => 'test node',
      $entity_def->get('entity_keys')['bundle'] => $bundle,
    );

    $new_post = \Drupal::entityManager()->getStorage($entity_type)->create($new_node);
    $new_post->save();

    return node_load($this->latestId());
  }

  /**
   * {@inheritdoc}
   */
  public function moduleInvokeAll($hook) {
    $args = func_get_args();
    $hook = array_shift($args);
    return \Drupal::moduleHandler()->invokeAll($hook, $args);
  }

  /**
   * {@inheritdoc}
   */
  public function entityIsDummy($entity, $type) {
    $return = isset($entity->devel_generate);
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldInfoFields() {
    $return = array();
    $field_map = \Drupal::entityManager()->getFieldMap();
    // Field map returns:
    // entitytype/name(type, bundles(article => article))
    // we must change that into:
    // name(entity_types=>(node), type=>type, bundles=>node(page, article))
    foreach ($field_map as $entity_type => $fields) {
      foreach ($fields as $field => $field_info) {
        $return[$field]['entity_types'][$entity_type] = $entity_type;
        $return[$field]['field_name'] = $field;
        $return[$field]['type'] = $field_info['type'];
        $return[$field]['bundles'][$entity_type] = $field_info['bundles'];

        $this->addFieldSettings($return, $field, $field_info);
      }
    }
    return $return;
  }

  /**
   * Adds field settings if possible.
   *
   * @param array $return
   *   An array of fields to modify.
   * @param string $field
   *   A field name.
   * @param array $field_info
   *   Information about the field.
   */
  public function addFieldSettings(&$return, $field, $field_info) {
    if ($field_info['type'] == 'entity_reference') {
      if (isset($field_info['bundles']) && count($field_info['bundles'])) {
        $bundle = array_pop($field_info['bundles']);
        $config = FieldConfig::loadByName('node', $bundle, $field);
        if ($config) {
          $settings = $config->getSettings();

          if (isset($settings['handler_settings']['target_bundles'])) {
            foreach ($settings['handler_settings']['target_bundles'] as
            $target) {
              $return[$field]['settings']['allowed_values'][] = array(
                'vocabulary' => $target,
              );
            }
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function moduleList() {
    $full_list = \Drupal::moduleHandler()->getModuleList();
    return array_keys($full_list);
  }

  /**
   * {@inheritdoc}
   */
  public function configGet($name, $default = NULL) {
    $candidate = \Drupal::config('realistic_dummy_content_api')->get($name);
    return $candidate ? $candidate : $default;
  }

  /**
   * {@inheritdoc}
   */
  public function alter($type, &$data, &$context1 = NULL, &$context2 = NULL, &$context3 = NULL) {
    return \Drupal::moduleHandler()->alter($type, $data, $context1, $context2);
  }

  /**
   * {@inheritdoc}
   */
  public function getBundleName($entity) {
    return $entity->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function stateGet($name, $default = NULL) {
    return \Drupal::state()->get($name, $default);
  }

  /**
   * {@inheritdoc}
   */
  public function setEntityProperty(&$entity, $property, $value) {
    if (!is_array($value)) {
      $value = array('set' => $value);
    }
    $entity->set($property, $value['set']);
    if (isset($value['options']['format'])) {
      $entity->{$property}->format = $value['options']['format'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formatProperty($type, $value, $options = array()) {
    switch ($type) {
      case 'file':
        return ['set' => $value->id(), 'options' => $options];

      case 'value':
      case 'tid':
      case 'text_with_summary':
        return ['set' => $value, 'options' => $options];

      default:
        throw new \Exception('Unknown property type ' . $type);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function moduleExists($module) {
    return \Drupal::moduleHandler()->moduleExists($module);
  }

  /**
   * {@inheritdoc}
   */
  public function watchdog($message, $severity = 5) {
    if ($severity == WATCHDOG_ERROR) {
      \Drupal::logger('realistic_dummy_content_api')->error($message);
    }
    else {
      \Drupal::logger('realistic_dummy_content_api')->notice($message);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fieldTypeMachineName($info) {
    $machine_name = isset($info['machine_name']) ? $info['machine_name'] : NULL;
    $entity = isset($info['entity']) ? $info['entity'] : NULL;
    $field_name = isset($info['field_name']) ? $info['field_name'] : NULL;

    if ($machine_name == 'entity_reference' && $entity && $field_name) {
      $settings = $entity->getFieldDefinition($field_name)->getSettings();
      if (isset($settings['target_type']) && $settings['target_type'] == 'taxonomy_term') {
        return 'taxonomy_term_reference';
      }
    }
    return $info['machine_name'];
  }

  /**
   * {@inheritdoc}
   */
  public function debug($message, $info = NULL) {
    if ($this->moduleExists('devel')) {
      if (is_string($message)) {
        // @codingStandardsIgnoreStart
        dpm($message, $info);
        // @codingStandardsIgnoreEnd
      }
      else {
        // @codingStandardsIgnoreStart
        dpm($info . ' ==>');
        // @codingStandardsIgnoreEnd
        if (function_exists('ksm')) {
          ksm($message);
        }
        else {
          // @codingStandardsIgnoreStart
          dpm($message);
          // @codingStandardsIgnoreEnd
        }
      }
    }
    $this->watchdog('<pre>' . print_r(array($info => $message), TRUE) . '</pre>');
  }

  /**
   * {@inheritdoc}
   */
  public function createEntity($info = array()) {
    if (isset($info['entity_type']) && $info['entity_type'] != 'node') {
      throw new \Exception(__FUNCTION__ . ' unknown entity type.');
    }
    else {
      return $this->createDummyNode();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function entityProperties($entity) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityProperty(&$entity, $property) {
    // Drupal8 does not have properties.
    if ($property == 'title') {
      return $entity->getTitle();
    }
    throw new \Exception(__FUNCTION__ . ' should not be called as Drupal8 does not use properties. ' . $property);
  }

  /**
   * {@inheritdoc}
   */
  public function getPath($type, $name) {
    return drupal_get_path($type, $name);
  }

  /**
   * {@inheritdoc}
   */
  public function frameworkRoot() {
    return DRUPAL_ROOT;
  }

  /**
   * {@inheritdoc}
   */
  public function fileSave($drupal_file) {
    $drupal_file->save();
    return $drupal_file;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllVocabularies() {
    return Vocabulary::loadMultiple();
  }

  /**
   * {@inheritdoc}
   */
  public function hookUserInsert(&$edit, $account, $category) {
    // Do nothing in D8.
  }

  /**
   * {@inheritdoc}
   */
  public function hookUserPresave(&$edit, $account, $category) {
    // Do nothing in D8.
  }

  /**
   * {@inheritdoc}
   */
  public function vocabularyIdentifier($vocabulary) {
    return $vocabulary->id();
  }

  /**
   * {@inheritdoc}
   */
  public function newVocabularyTerm($vocabulary, $name) {
    $term = Term::create([
      'name' => $name,
      'vid' => $vocabulary->id(),
    ]);

    $term->save();

    return $term;
  }

  /**
   * {@inheritdoc}
   */
  public function timerStart($id) {
    return Timer::start($id);
  }

  /**
   * {@inheritdoc}
   */
  public function timerStop($id) {
    return Timer::stop($id);
  }

  /**
   * {@inheritdoc}
   */
  public function filteredHtml() {
    return 'basic_html';
  }

  /**
   * {@inheritdoc}
   */
  public function variableDel($variable) {
    // Do nothing in Drupal8 to delete a variable.
  }

}
