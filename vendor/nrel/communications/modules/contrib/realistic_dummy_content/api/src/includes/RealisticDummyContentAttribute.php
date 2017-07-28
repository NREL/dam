<?php

namespace Drupal\realistic_dummy_content_api\includes;

use Drupal\realistic_dummy_content_api\Framework\Framework;

/**
 * Represents either a field or a property for an entity.
 *
 * Fields are for example field_image, or field_body, and attributes are
 * for example the title of a node and the image of a user.
 *
 * We want to abstract away the differences so we can treat both
 * the same way without using control statements in our code.
 */
abstract class RealisticDummyContentAttribute {
  /**
   * Entity managed by this class.
   *
   * The entity is set on construction and is a subclass of
   * RealisticDummyContentEntityBase. It contains information about the
   * entity to which this field instance is attached.
   *
   * @var object
   */
  private $entity;

  /**
   * The name of this attribuet, for example title, picture, field_image...
   *
   * @var string
   */
  private $name;

  /**
   * Constructor.
   *
   * @param object $entity
   *   Object of a subclass of RealisticDummyContentEntityBase.
   * @param string $name
   *   The name of the field, for example body or picture or field_image.
   */
  public function __construct($entity, $name) {
    $this->entity = $entity;
    $this->name = $name;
  }

  /**
   * Getter for $this->name.
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Getter for $this->entity.
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Returns a pseudo-random number.
   *
   * The number should be the same for the same entity, so we need to know the
   * entity.
   *
   * @return int
   *   A random or sequential number.
   */
  public function rand($start, $end) {
    return $this->getEntity()->rand($start, $end);
  }

  /**
   * Returns the appropriate environment, real or testing.
   */
  public function env() {
    return $this->getEntity()->env();
  }

  /**
   * Gets the bundle of the associated entity.
   *
   * @return string
   *   The bundle name.
   */
  public function getBundle() {
    return $this->getEntity()->getBundle();
  }

  /**
   * Gets the UID of the associated entity.
   *
   * @return int
   *   The UID.
   */
  public function getUid() {
    return $this->getEntity()->getUid();
  }

  /**
   * Get the entity type of the associated entity.
   *
   * @return string
   *   The entity type as a string, 'node' or 'user' for example.
   */
  public function getEntityType() {
    return $this->getEntity()->getType();
  }

  /**
   * Returns the type of this attribute.
   *
   * Drupal uses fields (managed by the field system) and properties to define
   * attributes of entities. Fields include body and field_image; properties
   * include title and the user picture.
   *
   * @return string
   *   'property' or 'field'
   */
  public abstract function getType();

  /**
   * Changes this attribute by looking for data in files.
   *
   * Any module can define a file hierarchy to determine realistic dummy data
   * for this attribute. See the ./realistic_dummy_content/ folder for an
   * example.
   *
   * This function checks the filesystem for compatible files (for example, only
   * image files are acceptable candidate files for field_image), choose one
   * through the selection mechanism (random or sequential), and then procedes
   * to change the data for the associated field for this class.
   */
  public function change() {
    $files = $this->getCandidateFiles();
    $this->changeFromFiles($files);
  }

  /**
   * Given candidate files, change value of this attribute based on one of them.
   *
   * @param array $files
   *   An array of files.
   */
  public function changeFromFiles($files) {
    $value = $this->valueFromFiles($files);
    if ($value === NULL) {
      // NULL indicates we could not find a value with which to replace the
      // current value. The value can still be '', or FALSE, etc.
      return;
    }
    $entity = $this->getEntity()->getEntity();
    Framework::instance()->setEntityProperty($entity, $this->getName(), $value);
    $this->getEntity()->setEntity($entity);
  }

  /**
   * Get acceptable file extensions which contain data for this attribute.
   *
   * For example, title attributes can be replaced by data in txt files, whereas
   * picture and field_image attributes require png, jpg, gif.
   *
   * @return array
   *   An array of acceptable file extensions.
   */
  public function getExtensions() {
    // By default, use only text files. Other manipulators, say, for image
    // fields or file fields, can specify other extension types.
    return array('txt');
  }

  /**
   * Get all candidate files for a given field for this entity.
   */
  public function getCandidateFiles() {
    $files = array();
    foreach (Framework::instance()->moduleList() as $module) {
      $filepath = DRUPAL_ROOT . '/' . drupal_get_path('module', $module) . '/realistic_dummy_content/fields/' . $this->getEntityType() . '/' . $this->getBundle() . '/' . $this->getName();
      $files = array_merge($files, RealisticDummyContentEnvironment::getAllFileGroups($filepath, $this->getExtensions()));
    }
    return $files;
  }

  /**
   * Given a RealisticDummyContentFileGroup object, get structured property.
   *
   * The structured property can then be added to the entity.
   *
   * For example, sometimes the appropriate property is array('value' => 'abc',
   * 'text_format' => Framework::instance()->filteredHtml()); other times is it
   * just a string. Subclasses will determine what to do with the contents from
   * the file.
   *
   * @param object $file
   *   The actual file object.
   *
   * @return null|array
   *   In case of an error or if the value does not apply or is empty, return
   *   NULL; otherwise returns structured data to be added to the entity object.
   */
  public function valueFromFile($file) {
    try {
      if (in_array($file->getRadicalExtension(), $this->getExtensions())) {
        return $this->implementValueFromFile($file);
      }
    }
    catch (Exception $e) {
      return NULL;
    }
  }

  /**
   * Given a RealisticDummyContentFileGroup object, get a structured property.
   *
   * This function is not meant to called directly; rather, call
   * ValueFromFile(). This function must be overriden by subclasses.
   *
   * @param object $file
   *   An object of type RealisticDummyContentFileGroup.
   *
   * @return null|array
   *   Returns structured data to be added to the entity object, or NULL if such
   *   data can't be creatd.
   *
   * @throws \Exception.
   */
  protected abstract function implementValueFromFile($file);

  /**
   * Given a list of files, return a value from one of them.
   *
   * @param array $files
   *   An array of file objects.
   *
   * @return mixed
   *   A file object or array, or an associative array with the keys "value" and
   *   "format", or NULL if there are no files to choose from or the files have
   *   the wrong extension.
   */
  public function valueFromFiles($files) {
    try {
      if (count($files)) {
        $rand_index = $this->rand(0, count($files) - 1);
        $file = $files[$rand_index];
        return $this->valueFromFile($file);
      }
    }
    catch (Exception $e) {
      return NULL;
    }
  }

  /**
   * Return acceptable image file extensions.
   *
   * @return array
   *   An array of extension for image files.
   */
  public function getImageExtensions() {
    return array('gif', 'png', 'jpg');
  }

  /**
   * Return acceptable text file extensions.
   *
   * @return array
   *   An array of extension for text files.
   */
  public function getTextExtensions() {
    return array('txt');
  }

  /**
   * Return an image file object if possible.
   *
   * @param object $file
   *   The RealisticDummyContentFileGroup object.
   *
   * @return null|object
   *   NULL if the file is not an image, or if an error occurred; otherwise a
   *   Drupal file object.
   */
  public function imageSave($file) {
    try {
      $exists = $file->value();
      if (!$exists) {
        throw new RealisticDummyContentException('Please check if the file exists before attempting to save it');
      }
      $return = NULL;
      if (in_array($file->getRadicalExtension(), $this->getImageExtensions())) {
        $return = $this->fileSave($file);
        $alt = $file->attribute('alt');
        if ($alt) {
          $return->alt = $alt;
        }
      }
      return $return;
    }
    catch (Exception $e) {
      return NULL;
    }
  }

  /**
   * Return a file object.
   *
   * @param object $file
   *   The original file, a RealisticDummyContentFileGroup object.
   *
   * @return object
   *   A file object.
   *
   * @throws \Exception.
   */
  public function fileSave($file) {
    $drupal_file = $file->getFile();
    if (!$drupal_file) {
      throw new RealisticDummyContentException('Please check if the file exists before attempting to save it');
    }
    $random = $file->getRadical();
    $drupal_file = $this->env()->fileSaveData($file->value(), 'public://dummyfile' . $random . '.' . $file->getRadicalExtension());
    $drupal_file->uid = $this->getUid();
    $return = Framework::instance()->fileSave($drupal_file);

    if (!is_object($return)) {
      throw new \Exception('Internal error, ' . __FUNCTION__ . ' expecting to return an object');
    }

    return $return;
  }

}
