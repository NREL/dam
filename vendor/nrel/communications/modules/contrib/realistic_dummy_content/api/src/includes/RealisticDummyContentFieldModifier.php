<?php

namespace Drupal\realistic_dummy_content_api\includes;

use Drupal\realistic_dummy_content_api\Framework\Framework;

/**
 * Field modifier class.
 *
 * All manipulation of generated content to make it more realistic
 * passes through modifiers (direct or indirect subclasses of
 * RealisticDummyContentEntityBase).
 *
 * This class (RealisticDummyContentFieldModifier) allows active modules to put
 * files in a specific directory hierarchy resembling
 * realistic_dummy_content/fields/[entity_type]/[bundle]/[field_name], and for
 * these files to define data which will replace the values of the corresponding
 * property or field in any given entity.
 *
 * The difference between a field and a property is that a field is managed by
 * Drupal's Field system, whereas a property is not. Example of fields include
 * field_image, which define images in articles (in a standard installation);
 * examples of properties include the user entity's picture property, and the
 * title of nodes.
 *
 * Drupal stores field values differently depending on the type of field, and
 * third-party modules can define their own schemes for storing values; an
 * extensible system has been defined to allow any module (including this one)
 * to define field formats and interpret data from files. To do so, modules must
 * implement hook_realistic_dummy_content_field_manipular_alter(). Please see
 * the example in this module's .module file, with more documentation in
 * realistic_dummy_content_api.api.php. (Realistic Dummy Content API defines
 * specific manipulators for the fields image, text_with_summary,
 * taxonomy_term_reference...).
 */
class RealisticDummyContentFieldModifier extends RealisticDummyContentEntityBase {

  /**
   * Get properties for the entity, for example user's picture or node's name.
   *
   * @return array
   *   An array of RealisticDummyContentAttribute objects, keyed by attribute
   *   name, e.g. title => [RealisticDummyContentAttribute], field_image =>
   *   [RealisticDummyContentAttribute]
   */
  public function getProperties() {
    $modifiable_properties = array();
    $fields = $this->getFields();
    foreach (Framework::instance()->entityProperties($this->getEntity()) as $property => $info) {
      if (!in_array($property, array_keys($fields)) && $this->filter($property)) {
        $this->addModifier($modifiable_properties, 'property', $property);
      }
    }
    return $modifiable_properties;
  }

  /**
   * Get fields for the entity, for example body or field_image.
   *
   * @return array
   *   An array of RealisticDummyContentAttribute objects, keyed by attribute
   *   name, e.g. title => [RealisticDummyContentAttribute], field_image =>
   *   [RealisticDummyContentAttribute]
   */
  public function getFields() {
    $modifiable_fields = array();
    $type = $this->getType();
    $bundle = $this->getBundle();
    // Get _all_ defined fields. This should return an associative array.
    $fields = Framework::instance()->fieldInfoFields();
    foreach ($fields as $field => $info) {
      if (isset($info['bundles'][$type]) && is_array($info['bundles'][$type]) && in_array($bundle, $info['bundles'][$type]) && $this->filter($field)) {
        $this->addModifier($modifiable_fields, 'field', $field);
      }
    }
    return $modifiable_fields;
  }

  /**
   * Adds a modifier to a list of attribute modifiers.
   *
   * To abstract away the difference between fields and properties, we
   * call them all attributes. Modifiers will modify attributes depending on
   * what they are. For example, a user picture is modified differently than
   * an image in an article. This is managed through an extensible class
   * hierarchy. Modules, including this one, can implement
   * hook_realistic_dummy_content_attribute_manipular_alter() to determine
   * which class should modify which attribute (field or property).
   *
   * By default, we will consider that properties are text properties and that
   * fields' [value] property should be modified. This is not the case, however
   * for user pictures (which should load a file), body fields (which contain
   * a text format), and others. These are all defined in subclasses and can
   * be extended by module developers.
   *
   * @param array &$modifiers
   *   Existing array of subclasses of RealisticDummyContentAttribute, to which
   *   new modifiers will be added.
   * @param string $type
   *   Either 'property' or 'field'.
   * @param string $name
   *   Name of the property or field, for example 'body', 'picture', 'title',
   *   'field_image'.
   */
  public function addModifier(&$modifiers, $type, $name) {
    $class = '';
    switch ($type) {
      case 'property':
        $original_class = '\Drupal\realistic_dummy_content_api\includes\RealisticDummyContentTextProperty';
        $attribute_type = $name;
        break;

      case 'field':
        $original_class = '\Drupal\realistic_dummy_content_api\includes\RealisticDummyContentValueField';
        $field_info = Framework::instance()->fieldInfoField($name);
        $attribute_type = $field_info['type'];
        break;

      default:
        return;
    }
    $class = $original_class;

    $info = array(
      'type' => $type,
      'machine_name' => $attribute_type,
      'entity' => $this->getEntity(),
      'field_name' => $name,
    );
    Framework::instance()->alter('realistic_dummy_content_attribute_manipulator', $class, $info);

    if (!$class) {
      // third-parties might want to signal that certain fields cannot be
      // modified (they can be too complex for the default modifier and do not
      // yet have a custom modifier).
      return;
    }
    // @TODO check if class is abstract
    elseif (class_exists($class)) {
      $modifier = new $class($this, $name);
    }
    else {
      watchdog('realistic_dummy_content_api', 'Class does not exist: @c. This is probably because a third-party module has implemented realistic_dummy_content_api_realistic_dummy_content_attribute_manipular_alter() with a class that cannot be implemented. @original will used instead.', array('@c' => $class, '@original' => $original_class));
      $modifier = new $original_class($this, $name);
    }

    if (isset($modifier)) {
      // It's OK to index by name because attributes and fields can never have
      // the same names.
      $modifiers[$name] = $modifier;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function modify() {
    $attributes = $this->getAttributes();
    foreach ($attributes as $attribute) {
      $attribute->change();
    }
  }

  /**
   * Returns all fields and properties.
   *
   * We implement fields and properties as subclasses of the same parent class,
   * which defines a common interface for dealing with them.
   *
   * @return array
   *   An array of RealisticDummyContentAttribute objects, keyed by attribute
   *   name, e.g. title => [RealisticDummyContentAttribute], field_image =>
   *   [RealisticDummyContentAttribute]
   */
  public function getAttributes() {
    return array_merge($this->getFields(), $this->getProperties());
  }

  /**
   * Generate a random number, or during tests, give the first available number.
   */
  public function rand($start, $end) {
    $return = realistic_dummy_content_api_rand($start, $end, $this->getHash());
    return $return;
  }

  /**
   * Get the uid property of this entity, or 0.
   *
   * @return int
   *   The uid of the associated entity.
   */
  public function getUid() {
    $entity = $this->getEntity();
    if (isset($entity->uid)) {
      return $entity->uid;
    }
    else {
      return 0;
    }
  }

}
