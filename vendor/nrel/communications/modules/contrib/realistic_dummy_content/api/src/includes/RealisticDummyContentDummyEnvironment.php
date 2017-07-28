<?php

namespace Drupal\realistic_dummy_content_api\includes;

/**
 * The dummy environment.
 *
 * During normal execution, we want to do things like interact with the file-
 * system and such. However during testing we want to abstract that away. This
 * class represents the dummy environment which can be used during unit tests.
 */
class RealisticDummyContentDummyEnvironment extends RealisticDummyContentEnvironment {
  private $files;

  /**
   * {@inheritdoc}
   */
  public function createFile($path, $data) {
    if (!is_array($this->files)) {
      $this->files = array();
    }
    $this->files[$path] = $data;
  }

  /**
   * {@inheritdoc}
   */
  public function fileGetContents($filename) {
    if (isset($this->files[$filename])) {
      return $this->files[$filename];
    }
    trigger_error('file_get_contents(): failed to open stream');
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function fileSaveData($data, $destination = NULL) {
    if (!$destination) {
      throw new \Exception('the dummy file system is not designed to use null destination');
    }
    $parsed = parse_url($destination);
    $return_array = array(
      'fid' => 1,
      'uri' => $destination,
      'filename' => $parsed['host'],
    );
    return (object) $return_array;
  }

  /**
   * {@inheritdoc}
   */
  public function fileSave(stdClass $file) {
  }

}
