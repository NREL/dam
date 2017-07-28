<?php

namespace Drupal\realistic_dummy_content_api\includes;

/**
 * Represents files as groups.
 *
 * For example:
 *
 *     1.txt
 *     2.txt
 *     3.txt
 *
 * will be represented as three files, but
 *
 *     1.txt
 *     2.txt
 *     2.attribute.txt
 *     2.attribute1.txt
 *     3.txt
 *
 * will also be represented as three files, but the second one will have two
 * attributes, attribute and attribute1.
 *
 * This allows us to defined attributes or metadata for certain file types, for
 * example:
 *
 *   realistic_dummy_content/fields/node/article/
 *     - body/
 *       - ipsum.txt
 *       - ipsum.format.txt
 *       - lorem.txt
 *    - field_image/
 *       - 1.jpg
 *       - 2.jpg
 *       - 2.alt.txt
 *
 * In the above example, `realistic_dummy_content` sees two possible body
 * values, _one of which with a specific input format_; and two possible images,
 * _one of which with a specific alt text_. Attributes are never compulsory, and
 * in the case where an attribute is needed, a reasonable fallback value is
 * used, for example Framework::instance()->filteredHtml() will be used if no
 * format is specified for the body.
 */
class RealisticDummyContentFileGroup {

  private $radical;
  private $file;
  private $attributes;

  /**
   * Constructor for a file object.
   *
   * Several actual files can reside in the same file object if their names have
   * the same radical, for example:
   *
   *   a.b.c
   *   a.c
   *
   * have the same radical, a.c.
   *
   * @param string $radical
   *   The radical file name, which may or may not exist on the filesystem.
   *   For example, if the file is called a.b.c, the radical is a.c, even though
   *   a.c does not exist on the filesystem.
   * @param object $file
   *   The radical drupal file object, or NULL if it does not exist on the file
   *   system.
   * @param array $attributes
   *   An array in the format:
   *    array(
   *     'attribute_name' => [file object],
   *   ),
   *   (where attribute_name can be "b" as in the above example).
   *
   * @throws RealisticDummyContentException
   */
  public function __construct($radical, $file, $attributes) {
    if (!is_string($radical)) {
      throw new RealisticDummyContentException('Please use string for radical');
    }
    if ($file && !is_object($file)) {
      throw new RealisticDummyContentException('Please use NULL or object for file');
    }
    if (!is_array($attributes)) {
      throw new RealisticDummyContentException('Please use array for attributes');
    }
    $this->radical = $radical;
    $this->file = $file;
    $this->attributes = $attributes;
  }

  /**
   * Getter for radical.
   */
  public function getRadical() {
    return $this->radical;
  }

  /**
   * Getter for file.
   */
  public function getFile() {
    return $this->file;
  }

  /**
   * Getter for attributes.
   */
  public function getAttributes() {
    return $this->attributes;
  }

  /**
   * Returns the value of the radical file if one exists.
   *
   * @return null|object
   *   NULL if a radical file does not exist, if it does not have contents, or
   *   if an error occurred. Otherwise returns the contents of the file.
   */
  public function value() {
    try {
      $file = $this->getFile();
      if (isset($file->uri)) {
        return trim(RealisticDummyContentEnvironment::get()->fileGetContents($file->uri));
      }
      else {
        return NULL;
      }
    }
    catch (Exception $e) {
      return NULL;
    }
  }

  /**
   * Return the value for an attribute name if possible.
   *
   * @param string $name
   *   The attribute name to fetch.
   * @param mixed $default
   *   The default value.
   *
   * @return mixed
   *   The default value if the attribute does not exist, if it's empty or if an
   *   error occurred, otherwise the contents of the attributes file.
   */
  public function attribute($name, $default = NULL) {
    try {
      $attributes = $this->getAttributes();
      if (isset($attributes[$name]->uri)) {
        $return = trim(RealisticDummyContentEnvironment::get()->fileGetContents($attributes[$name]->uri));
        return $return;
      }
      else {
        return $default;
      }
    }
    catch (Exception $e) {
      return $default;
    }
  }

  /**
   * Returns the extension of the radical filename.
   *
   * @return string
   *   An extension, can be empty.
   *
   * @throws \Exception
   */
  public function getRadicalExtension() {
    $filename = $this->getRadical();
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    if (!$extension) {
      throw new RealisticDummyContentException('Files require extensions.');
    }
    return $extension;
  }

}
