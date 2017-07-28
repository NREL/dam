<?php

namespace Drupal\realistic_dummy_content_api\Framework;

/**
 * Drupal 7-specific code.
 */
class Drupal7 extends Framework implements FrameworkInterface {

  /**
   * {@inheritdoc}
   */
  public function hookEntityPresave($entity, $type) {
    if ($type != 'user') {
      $this->genericEntityPresave($entity, $type);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fieldTypeMachineName($info) {
    return $info['machine_name'];
  }

  /**
   * {@inheritdoc}
   */
  public function entityProperties($entity) {
    return (array) $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function userPictureFilename($user) {
    return $user->picture->filename;
  }

  /**
   * {@inheritdoc}
   */
  public function develGenerate($info) {
    module_load_include('inc', 'devel_generate');

    if ($info['entity_type'] == 'node') {
      // See https://www.drupal.org/node/2324027
      $info['users'] = array(1);
      $info['title_length'] = 3;
      if ($info['kill']) {
        devel_generate_content_kill($info);
      }
      for ($i = 0; $i < $info['num']; $i++) {
        devel_generate_content_add_node($info);
      }
    }
    elseif ($info['entity_type'] == 'user') {
      devel_create_users($info['num'], $info['kill']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function hookUserInsert(&$edit, $account, $category) {
    // This hook is invoked only once when the user is first created, whether
    // by the administrator or by devel_generate. The hook is not invoked
    // thereafter.
    $filter = array(
      'exclude' => array(
        'picture',
      ),
    );
    $this->genericEntityPresave($account, 'user', $filter);
  }

  /**
   * {@inheritdoc}
   */
  public function frameworkSpecificTests(&$errors, &$tests) {
  }

  /**
   * {@inheritdoc}
   */
  public function hookUserPresave(&$edit, $account, $category) {
    // This hook is called when content is updated, in which case we don't want
    // to tamper with it. When content is first created, the $account's is_new
    // property is set to FALSE, se we can't depend on that to determine whether
    // the user is new or not. However, $edit['picture_delete'] is _only_ set
    // when users are updated, so we can check for that to determine whether or
    // not to continue modifying the account.
    if (isset($edit['picture_delete'])) {
      // Not a new account, don't mess with it.
      return;
    }
    // At this point we know we are dealing with a new user.
    // $edit['uid'] can have several values:
    // This hook is invoked twice when content is created via devel_generate,
    // once with $edit['uid'] set to NULL (which causes us to do nothing) and
    // once with $edit['uid'] set to the UID of newly-created user object.
    // When the user is changed via the admin interface, this hook is invoked
    // but $edit['uid'] is not set. $edit['uid'] is never set during testing,
    // so we use $account->uid instead. $account->uid is set whether we are
    // creating the user in our test code or it's created via devel_generate.
    if (isset($account->uid) && $account->uid) {
      $filter = array(
        'include' => array(
          'picture',
        ),
      );
      $user = (object) $edit;
      $this->genericEntityPresave($user, 'user', $filter);
      $edit = (array) $user;
    }
  }

  /**
   * Generic function called by various hooks in Drupal.
   *
   * Hook_entity_insert(), hook_user_insert() and hook_user_presave() have
   * subtle differences. This function aims to be more abstract and uses the
   * concept of a filter, see below.
   *
   * @param object $entity
   *   The object for a given type, for example this can be a user object
   *   or a node object.
   * @param string $type
   *   The entity type of the information to change, for example 'user' or
   *   'node'.
   * @param array $filter
   *   If set, only certain fields will be considered when manipulating
   *   the object. This can be useful, for example for users, because
   *   two separate manipulations need to be performed, depending on whether
   *   hook_user_insert() or hook_user_presave(). Both hooks need to modify
   *   only certain properties and fields, but taken together the entire
   *   object can be manipulated.
   *   The filter is an associative array which can contain no key (all
   *   fields and properties should be manipulated), the include key (fields
   *   included are the only ones to be manipulated, or the exclude key (all
   *   fields except those included are the ones to be manipulated).
   *
   *   realistic_dummy_content_api_user_insert() defines the array
   *   ('exclude' => array(picture)) whereas
   *   realistic_dummy_content_api_user_presave() defines the array
   *   ('include' => array(picture)). Therefore taken together these two
   *   hooks manipulate the entire user object, but in two phases.
   *
   *   This allows hook implementations to return a different class based on
   *   the type of filter.
   */
  public function genericEntityPresave($entity, $type, $filter = array()) {
    try {
      if (realistic_dummy_content_api_is_dummy($entity, $type)) {
        $candidate = $entity;
        realistic_dummy_content_api_improve_dummy_content($candidate, $type, $filter);
        realistic_dummy_content_api_validate($candidate, $type);
        // $entity = $candidate;.
      }
    }
    catch (\Exception $e) {
      drupal_set_message(t('realistic_dummy_content_api failed to modify dummy objects: message: @m', array('@m' => $e->getMessage())), 'error', FALSE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function alter($type, &$data, &$context1 = NULL, &$context2 = NULL, &$context3 = NULL) {
    return drupal_alter($type, $data, $context1, $context2, $context3);
  }

  /**
   * {@inheritdoc}
   */
  public function fieldInfoFields() {
    return field_info_fields();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldInfoField($field) {
    return field_info_field($field);
  }

  /**
   * {@inheritdoc}
   */
  public function moduleList() {
    return module_list();
  }

  /**
   * {@inheritdoc}
   */
  public function moduleInvokeAll($hook) {
    $args = func_get_args();
    return call_user_func_array('module_invoke_all', $args);
  }

  /**
   * {@inheritdoc}
   */
  public function entityIsDummy($entity, $type) {
    $return = FALSE;
    // Any entity with the devel_generate property set should be considered
    // dummy content. although not all dummy content has this flag set.
    // See https://drupal.org/node/2252965
    // See https://drupal.org/node/2257271
    if (isset($entity->devel_generate)) {
      return TRUE;
    }
    switch ($type) {
      case 'user':
        // devel_generate puts .invalid at the end of the generated user's
        // email address. This module should not be activated on a production
        // site, or else anyone can put ".invalid" at the end of their email
        // address and their profile's content will be overridden.
        $suffix = '.invalid';
        if (isset($entity->mail) && $this->drupalSubstr($entity->mail, strlen($entity->mail) - strlen($suffix)) == $suffix) {
          return TRUE;
        }
        break;

      default:
        break;
    }
    return $return;
  }

  /**
   * Mockable wrapper around drupal_substr().
   *
   * See that function for details.
   */
  public function drupalSubstr($text, $start) {
    return drupal_substr($text, $start);
  }

  /**
   * {@inheritdoc}
   */
  public function getBundleName($entity) {
    return $entity->type;
  }

  /**
   * {@inheritdoc}
   */
  public function configGet($name, $default = NULL) {
    return variable_get($name, $default);
  }

  /**
   * {@inheritdoc}
   */
  public function stateGet($name, $default = NULL) {
    return variable_get($name, $default);
  }

  /**
   * {@inheritdoc}
   */
  public function setEntityProperty(&$entity, $property, $value) {
    $entity->{$property} = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function formatProperty($type, $value, $options = array()) {
    switch ($type) {
      case 'file':
        return array(
          LANGUAGE_NONE => array(
            (array) $value,
          ),
        );

      case 'value':
      case 'tid':
        return array(
          LANGUAGE_NONE => array(
            array_merge($options, array(
              $type => $value,
            )),
          ),
        );

      case 'text_with_summary':
        return array(
          LANGUAGE_NONE => array(
            array_merge($options, array(
              'value' => $value,
            )),
          ),
        );

      default:
        throw new \Exception('Unknown property type ' . $type);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityProperty(&$entity, $property) {
    return $entity->{$property};
  }

  /**
   * {@inheritdoc}
   */
  public function createEntity($info = array()) {
    if (isset($info['entity_type'])) {
      $entity_type = $info['entity_type'];
    }
    else {
      $entity_type = 'node';
    }
    $entity = new \stdClass();
    switch ($entity_type) {
      case 'node':
        $entity->title = rand(100000, 999999);
        $entity->type = $this->getDefaultNodeType();
        node_save($entity);
        break;

      case 'user':
        $options = array(
          'name' => rand(1000000, 9999999),
        );
        if (isset($info['dummy']) && $info['dummy']) {
          $options['mail'] = $options['name'] . '@example.invalid';
        }
        $entity = user_save(drupal_anonymous_user(), $options);
        break;

      default:
        throw new \Exception('Unknown entity type ' . $entity_type);
    }
    return $entity;
  }

  /**
   * Retrieves the default node type for this framework.
   */
  public function getDefaultNodeType() {
    return 'article';
  }

  /**
   * Tests $this->getDefaultNodeType().
   */
  public function testsGetDefaultNodeType() {
    return !is_string($this->getDefaultNodeType());
  }

  /**
   * {@inheritdoc}
   */
  public function debug($message, $info = NULL) {
    if ($this->moduleExists('devel')) {
      // @codingStandardsIgnoreStart
      dpm($message, $info);
      // @codingStandardsIgnoreEnd
    }
    $this->watchdog('<pre>' . print_r(array($info => $message), TRUE) . '</pre>');
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
  public function moduleExists($module) {
    return module_exists($module);
  }

  /**
   * {@inheritdoc}
   */
  public function watchdog($message, $severity = 5) {
    watchdog('realistic_dummy_content_api', $message, $severity);
  }

  /**
   * {@inheritdoc}
   */
  public function getAllVocabularies() {
    $return = taxonomy_get_vocabularies();
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function fileSave($drupal_file) {
    $return = file_save($drupal_file);
    return $return;
  }

  /**
   * Tests $this->hookUserInsert().
   */
  public function testHookUserInsert() {
    self::clearTestFlag('hookUserInsert called');
    $this->createEntity(array('entity_type' => 'user'));
    return !static::getTestFlag('hookUserInsert called');
  }

  /**
   * Tests $this->hookUserPresave().
   */
  public function testHookUserPresave() {
    self::clearTestFlag('hookUserPresave called');
    $this->createEntity(array('entity_type' => 'user'));
    return !static::getTestFlag('hookUserPresave called');
  }

  /**
   * {@inheritdoc}
   */
  public function vocabularyIdentifier($vocabulary) {
    return $vocabulary->vid;
  }

  /**
   * {@inheritdoc}
   */
  public function newVocabularyTerm($vocabulary, $name) {
    $term = new \stdClass();
    $term->name = $name;
    $term->vid = $vocabulary->vid;
    taxonomy_term_save($term);
    return $term;
  }

  /**
   * {@inheritdoc}
   */
  public function timerStart($id) {
    return timer_start($id);
  }

  /**
   * {@inheritdoc}
   */
  public function timerStop($id) {
    return timer_stop($id);
  }

  /**
   * {@inheritdoc}
   */
  public function filteredHtml() {
    return 'filtered_html';
  }

  /**
   * {@inheritdoc}
   */
  public function variableDel($variable) {
    variable_del($variable);
  }

}
