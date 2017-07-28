<?php

namespace Drupal\realistic_dummy_content_api\includes;

use Drupal\realistic_dummy_content_api\Framework\Framework;

/**
 * Generic entity manipulator.
 *
 * Class with an abstract Modify() method. Subclasses can have
 * access to entities in order to override demo content in them.
 */
abstract class RealisticDummyContentEntityBase extends RealisticDummyContentBase {
  private $hash;
  private $entity;
  private $type;
  private $filter;

  /**
   * Constructor.
   *
   * @param object $entity
   *   The entity object.
   * @param string $type
   *   The entity type of the object, for example user or node.
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
   * @throws \Exception
   */
  public function __construct($entity, $type, $filter = array()) {
    realistic_dummy_content_api_argcheck(array('is_object', 'realistic_dummy_content_api_argcheck_entitytype'));
    $this->entity = $entity;
    $this->hash = md5(serialize($entity));
    $this->type = $type;
    $this->filter = $filter;
  }

  /**
   * Getter for the entity.
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Getter for the hash which uniquely identifies this entity.
   */
  public function getHash() {
    return $this->hash;
  }

  /**
   * Getter for the filter.
   */
  public function getFilter() {
    return $this->filter;
  }

  /**
   * Updates the entity object.
   *
   * Used by functions which manipulate fields and properties. Once they
   * are done with the manipulations, they update the entity using this
   * function.
   */
  public function setEntity($entity) {
    $this->entity = $entity;
  }

  /**
   * Get the entity type of the entity being manipulated.
   *
   * All entities must have a type and a bundle. The type can be node,
   * user, etc. and the bundle can be article, page. In case of a user,
   * there must be a bundle even if there is only one: it is called user,
   * like the entity type.
   *
   * @return string
   *   The entity type, for example "node" or "user".
   */
  public function getType() {
    $return = $this->type;
    return $return;
  }

  /**
   * Get the bundle of the entity being manipulated.
   *
   * All entities must have a type and a bundle. The type can be node,
   * user, etc. and the bundle can be article, page. In case of a user,
   * there must be a bundle even if there is only one: it is called user,
   * like the entity type.
   *
   * @return string
   *   The bundle, for example "article" or "user". Is a bundle is not
   *   readily available, return the entity type.
   */
  public function getBundle() {
    $entity = $this->getEntity();
    if (isset($entity->type)) {
      return Framework::instance()->getBundleName($entity);
    }
    else {
      return $this->getType();
    }
  }

  /**
   * Modify the entity.
   *
   * Subclasses of RealisticDummyContentEntityBase need to override
   * this function to perform modifications on the entity.
   */
  public abstract function modify();

  /**
   * Check if a field should or shouldn't be manipulated.
   *
   * This concept is used especially because of a quirk in the user
   * insertion hooks: hook_user_insert() can't modify the user picture
   * whereas hook_user_presave() can modify only the picture.
   *
   * To get around this, the manipulator objects are called twice, but
   * each time filtered to change only certain parts of the user entity.
   */
  public function filter($field) {
    $return = TRUE;
    $filter = $this->getFilter();
    if (isset($filter['include'])) {
      if (!in_array($field, $filter['include'])) {
        $return = FALSE;
      }
    }
    elseif (isset($filter['exclude'])) {
      if (in_array($field, $filter['exclude'])) {
        $return = FALSE;
      }
    }
    return $return;
  }

}
