<?php

namespace Drupal\realistic_dummy_content_api\includes;

/**
 * The live environment.
 *
 * During normal execution, we want to do things like interact with the file-
 * system and such. However during testing we want to abstract that away. This
 * class represents the live environment.
 */
class RealisticDummyContentLiveEnvironment extends RealisticDummyContentEnvironment {

  /**
   * {@inheritdoc}
   */
  public function implementFileGetContents($filename) {
    return file_get_contents($filename);
  }

  /**
   * {@inheritdoc}
   */
  public function implementFileSaveData($data, $destination = NULL) {
    return file_save_data($data, $destination);
  }

  /**
   * {@inheritdoc}
   */
  public function implementFileSave(stdClass $file) {
    return file_save($file);
  }

}
