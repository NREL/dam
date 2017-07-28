<?php

namespace Drupal\realistic_dummy_content_api\Framework;

/**
 * Represents a mock framework (experimental).
 *
 * Because all framework-specific code is abstracted out of our module's logic,
 * we are able to implement a mock framework which can be used to test our
 * code's logic outside of the context of a specific framework.
 */
class Mock extends Framework {

  /**
   * {@inheritdoc}
   */
  public function newEntity($entity) {
    $this->hookEntityPresave($entity, $entity->type);
    $id = rand();
    $this->entites[$id] = $entity;
    return $id;
  }

  /**
   * {@inheritdoc}
   */
  public function hookEntityPresave($entity, $type) {
    $this->print('[info] About to save ' . $this->toString($entity) . ' of type ' . $type);
    if (realistic_dummy_content_api_is_dummy($entity, $type)) {
      $this->print('[info] Determined that ' . $this->toString($entity) . ' of type ' . $type . ' is dummy, about to improve it');
      realistic_dummy_content_api_improve_dummy_content($entity, $type);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function moduleInvokeAll($hook) {
    $args = func_get_args();
    $hook = array_shift($args);
    $this->print('[info] About to let all modules apply hook ' . $hook . ' with arguments ' . serialize($args);
    if (function_exists('realistic_dummy_content_api_' . $hook)) {
      call_user_func_array('realistic_dummy_content_api_' . $hook, $args);
    }
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
    return array(
      'some-field-name' => array(
        'node' => array(
          'article',
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function moduleList() {
    return array(
      'realistic_dummy_content',
      'realistic_dummy_content_api',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function configGet($name, $default) {
    return (isset($this->config[$name])) ? $this->config[$name] : $default;
  }

  /**
   * {@inheritdoc}
   */
  public function alter($type, &$data, &$context1 = NULL, &$context2 = NULL, &$context3 = NULL) {
    if ($type == 'realistic_dummy_content_attribute_manipulator') {
      return realistic_dummy_content_api_realistic_dummy_content_attribute_manipulator_alter($data, $context1, $context2, $context3);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getBundleName($entity) {
    return $entity->bundle;
  }

  /**
   * {@inheritdoc}
   */
  public function implelentStateGet($name, $default) {
    return (isset($this->config[$name])) ? $this->config[$name] : $default;
  }

  /**
   * {@inheritdoc}
   */
  public function setEntityProperty(&$entity, $property, $value) {
    $entity->$property = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function debug($message, $info) {
    $this->print('[debug] ' . $message . ' ' . $info);
  }

}
