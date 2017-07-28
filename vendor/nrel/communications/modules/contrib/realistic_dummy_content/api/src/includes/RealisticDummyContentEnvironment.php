<?php

namespace Drupal\realistic_dummy_content_api\includes;

/**
 * The abstract base environment.
 *
 * During normal execution, we want to do things like interact with the file-
 * system and such. However during testing we want to abstract that away. This
 * class defines abstract functions representing what the environment should
 * do.
 */
abstract class RealisticDummyContentEnvironment {
  /**
   * Private variable containing the environment to use.
   *
   * Calls are made directly to RealisticDummyContentEnvironment's static
   * methods, which then forward them to the appropriate environment. The
   * environment can be live, or simulated as during tests. This is a form of
   * mocking. See http://en.wikipedia.org/wiki/Mock_object.
   *
   * @var object
   */
  static private $env;

  /**
   * Get the current environment.
   *
   * See the comment on the private variable $env.
   *
   * default to a live environment if none is set. (During testing, a mock
   * environment will be set here so we can better control it.)
   *
   * @return object
   *   An object of type RealisticDummyContentEnvironment
   */
  public static function get() {
    if (!self::$env) {
      self::$env = new RealisticDummyContentLiveEnvironment();
    }
    return self::$env;
  }

  /**
   * Set the current environment.
   *
   * See the comment on the private variable $env.
   *
   * @param object $environment
   *   An object of type RealisticDummyContentEnvironment.
   */
  public static function set($environment) {
    self::$env = $environment;
  }

  /**
   * Get the contents of a file.
   *
   * @param string $filename
   *   A valid filename, for example /drupal/root/sites/all/modules/
   *   your_module/realistic_dummy_content/fields/node/blog/body/03.txt.
   *
   * @throws \Exception
   */
  public function fileGetContents($filename) {
    if (!$filename) {
      throw new RealisticDummyContentException('Please use valid filename');
    }
    if (strpos($filename, '/') === FALSE) {
      throw new RealisticDummyContentException('Please use an absolute filename including its path, which must always contain at least one slash. You are using ' . $filename);
    }
    $return = $this->implementFileGetContents($filename);
    return $return;
  }

  /**
   * Internal function used to get the contents of a file.
   *
   * Wrapper around PHP's file_get_contents() (or a simulation thereof).
   * This function will not return an exception. Please use
   * RealisticDummyContentEnvironment::file_get_contents(), instead.
   *
   * @param string $filename
   *   A valid filename, for example /drupal/root/sites/all/modules/your_module/
   *   realistic_dummy_content/fields/node/blog/body/03.txt.
   *
   * @return object
   *   Undefined in case the filename is invalid; otherwise returns the contents
   *   of the file.
   */
  public abstract function implementFileGetContents($filename);

  /**
   * Save the file data to the real or test environment.
   *
   * @param string $data
   *   The data.
   * @param string $destination
   *   Where to put it.
   *
   * @return object
   *   The file object.
   *
   * @throws \Exception
   */
  public function fileSaveData($data, $destination = NULL) {
    $return = $this->implementFileSaveData($data, $destination);
    return $return;
  }

  /**
   * Implements $this->fileSaveData().
   */
  public abstract function implementFileSaveData($data, $destination = NULL);

  /**
   * Saves a file.
   */
  public function fileSave(stdClass $file) {
    $return = $this->implementFileSave($file);
    return $return;
  }

  /**
   * Implements $this->fileSave().
   */
  public abstract function implementFileSave(stdClass $file);

  /**
   * Returns all files with a given extension for a given filepath.
   *
   * Files do not always have a one-to-one relationship with the filesystem.
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
   *     2.txt.attribute.txt
   *     2.txt.attribute1.txt
   *     3.txt
   *
   * will also be represented as three files, but the second one will have two
   * attributes, attribute and attribute1.
   *
   * @param string $filepath
   *   An absolute filepath on the system, for example
   *   /path/to/drupal/sites/all/
   *   modules/mymodule/realistic_dummy_content/fields/node/article/body.
   * @param array $extensions
   *   An array of extensions which should be taken into consideration.
   *
   * @return array
   *   An empty array in case of an error, or an array of objects of type
   *   RealisticDummyContentFileGroup.
   */
  public static function getAllFileGroups($filepath, $extensions) {
    try {
      $candidate_files = file_scan_directory($filepath, '/.*$/', array('key' => 'filename'));

      $files = self::sortCandidateFiles($candidate_files, $extensions);

      $return = array();
      foreach ($files as $radical => $attributes) {

        $return[] = new RealisticDummyContentFileGroup($radical, isset($attributes['file']) ? $attributes['file'] : NULL, isset($attributes['attributes']) ? $attributes['attributes'] : array());
      }
      return $return;
    }
    catch (Exception $e) {
      return array();
    }
  }

  /**
   * Given a list of candidate files, sort them by names and parts.
   *
   * @param array $candidate_files
   *   An array keyed by filename which contains drupal file objects, like this:
   *
   *     'one.txt' => [file object]
   *     'two.txt.attribute.txt' => [file object]
   *     'two.txt.attribute1.txt' => [file object]
   *     'three.txt' => [file object].
   * @param null|array $extensions
   *   (Default is NULL).
   *   If set, only return file groups whose base file is in one of the
   *   extenstions. For example, given an extension jpg,png, and a file
   *   structure with
   *
   *      a.jpg
   *      a.jpg.alt.txt
   *      b.txt
   *
   *   This function will return:
   *
   *      a.jpg =>
   *        file => [a.jpg]
   *        attributes =>
   *          alt => [a.jpg.alt.txt].
   *
   * @return array
   *   A sorted array which looks like:
   *
   *     one.txt => array('file' => [file object]),
   *     two.txt = array(
   *       attributes => array(
   *         'attribute' => [file object],
   *         'attribute1' => [file object],
   *       )
   *     ),
   *     three.txt => array('file' => [file object]),
   *
   * @throws RealisticDummyContentException
   */
  public static function sortCandidateFiles($candidate_files, $extensions = NULL) {
    foreach ($candidate_files as $candidate_filename => $candidate_file) {
      if (!is_string($candidate_filename)) {
        // Explicitly load the Exception class, because during unit tests the
        // registry is not present.
        module_load_include('inc', 'realistic_dummy_content_api', 'includes/RealisticDummyContentException');
        throw new RealisticDummyContentException('array keys should be strings');
      }
      if (!is_object($candidate_file)) {
        // Explicitly load the Exception class, because during unit tests the
        // registry is not present.
        module_load_include('inc', 'realistic_dummy_content_api', 'includes/RealisticDummyContentException');
        throw new RealisticDummyContentException('array values should be file objects');
      }
      if (strpos($candidate_filename, '/') !== FALSE) {
        // Explicitly load the Exception class, because during unit tests the
        // registry is not present.
        module_load_include('inc', 'realistic_dummy_content_api', 'includes/RealisticDummyContentException');
        throw new RealisticDummyContentException('Please do not pass file paths with slashes (/) to ' . __FUNCTION__);
      }
    }
    $return = self::implementSortCandidateFiles($candidate_files, $extensions);
    return $return;
  }

  /**
   * Given a list of candidate files, sort them by names and parts.
   *
   * @param array $candidate_files
   *   An array keyed by filename which contains drupal file objects. See
   *   SortCandidateFiles().
   * @param array $extensions
   *   (Default is NULL).
   *   If set, extensions to filter by. See SortCandidateFiles().
   *
   * @return array
   *   A sorted array. See SortCandidateFiles().
   *
   * @throws \Exception
   */
  public static function implementSortCandidateFiles($candidate_files, $extensions = NULL) {
    $return = array();
    foreach ($candidate_files as $candidate_filename => $candidate_file) {
      if (self::validCandidateFilename($candidate_filename, $extensions)) {
        self::addFileToArray($return, $candidate_filename, $candidate_file);
      }
    }
    // We expect the files to be sorted alphabetically, which is not the case on
    // all systems.
    ksort($return);
    return $return;
  }

  /**
   * Checks if a filename is valid.
   */
  public static function validCandidateFilename($name, $extensions = NULL) {
    if (self::lowercaseRadicalNoExtension($name) == 'readme') {
      return FALSE;
    }
    if (!$extensions) {
      return TRUE;
    }
    $filparts = self::getFileParts($name);
    return in_array($filparts['base_extension'], $extensions);
  }

  /**
   * Retrieves the parts constituting a filename.
   */
  public static function getFileParts($name) {
    $return = array();
    $parts = explode('.', $name);
    if (count($parts) >= 4) {
      $return['attribute_extention'] = array_pop($parts);
      $return['attribute_name'] = array_pop($parts);
    }
    $return['base'] = implode('.', $parts);
    $return['base_extension'] = array_pop($parts);
    return $return;
  }

  /**
   * Adds a file to an array of file group parts.
   */
  public static function addFileToArray(&$array, $name, $file) {
    $fileinfo = self::getFileParts($name);
    if (isset($fileinfo['attribute_name'])) {
      $array[$fileinfo['base']]['attributes'][$fileinfo['attribute_name']] = $file;
    }
    else {
      $array[$fileinfo['base']]['file'] = $file;
    }
  }

  /**
   * Returns the attribute of a filename if one exists.
   *
   * If >2 periods are present in the file name, then what is between the
   * last and next to last period is kept, for example:
   *
   *     a.b.c => b
   *     a.b.c.d => c
   *     a.b => NULL
   *     a => NULL
   *
   * @param string $filename
   *   A filename string, for example 'a.b.txt'.
   *
   * @return null|string
   *   Null if there is attribute to extract; otherwise the attribute name, for
   *   example "b".
   *
   * @throws \Exception
   */
  public static function attributeName($filename) {
    $replaced = self::replace($filename, '\2');
    if ($replaced != $filename) {
      return $replaced;
    }
    else {
      return NULL;
    }
  }

  /**
   * Returns the name radical of a filename.
   *
   * The following examples will all return "two.txt"
   *
   *     two.txt
   *     two.txt.attribute.txt
   *     two.txt.attribute1.txt
   *
   * If >2 periods are present in the file name, then what is between the
   * last and next to last period is removed, for example:
   *
   *     a.b.c => a.c
   *     a.b.c.d => a.b.d
   *     a.b => a.b
   *     a => a
   *
   * @param string $filename
   *   A filename string, for example 'a.b.txt'.
   *
   * @return string
   *   The name radical of this file, for example a.txt.
   *
   * @throws RealisticDummyContentException
   */
  public static function filenameRadical($filename) {
    if (!is_string($filename)) {
      throw new RealisticDummyContentException('Please pass ' . __FUNCTION__ . ' a string as a filename, not a ' . gettype($filename));
    }
    return self::replace($filename, '\1\3');
  }

  /**
   * Returns the part of a string before the extension, in lowercase.
   *
   * @param string $filename
   *   A filename string, e.g. rEadmE.txt.
   *
   * @return string
   *   The lowercase radical without the extension, e.g. readme
   */
  public static function lowercaseRadicalNoExtension($filename) {
    return self::strToLower(trim(preg_replace('/\.[^\.]*$/', '', $filename)));
  }

  /**
   * Wrapper around drupal_strtolower(if it exists) or strtolower.
   *
   * See those functions for details.
   */
  public static function strToLower($string) {
    if (function_exists('drupal_strtolower')) {
      return drupal_strtolower($string);
    }
    else {
      return strtolower($string);
    }
  }

  /**
   * Returns part of a filename.
   *
   * Helper function which runs a preg replace function on a filename and
   * returns the result.
   *
   * @param string $filename
   *   A filename, for example a, a.b, a.b.c, a.b.c.d.
   * @param string $replace
   *   A replacement pattern meant to be passed to preg_replace, where:
   *   \1 = everything before the next-to-last period
   *   \2 = everything between the next-to-last and last periods.
   *   \3 = everything after and including the last period.
   *
   * @return string
   *   The replaced filename, or the same filename in case of an error or if the
   *   pattern is not found.
   *
   * @throws RealisticDummyContentException
   */
  public static function replace($filename, $replace) {
    if (!is_string($filename)) {
      throw new RealisticDummyContentException('Please pass ' . __FUNCTION__ . ' a string as a filename, not a ' . gettype($filename));
    }
    return preg_replace('/(^.*)\.([^\.]*)(\.[^\.]*$)/', $replace, $filename);
  }

  /**
   * Returns the trimmed contents of a Drpual file object, or NULL if empty.
   *
   * @param object $file
   *   A drupal file object.
   *
   * @return null|string
   *   NULL if no contents in file, or if an error occurred; otherwise a string
   *   with the trimmed contents of the file.
   */
  public static function getFileContents($file) {
    try {
      if (!is_object($file)) {
        throw new RealisticDummyContentException('Please use a file object');
      }
      return trim(self::get()->fileGetContents($file->uri));
    }
    catch (Exception $e) {
      return NULL;
    }
  }

}
