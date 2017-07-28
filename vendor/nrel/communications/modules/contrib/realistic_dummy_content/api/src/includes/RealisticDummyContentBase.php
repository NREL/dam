<?php

namespace Drupal\realistic_dummy_content_api\includes;

/**
 * The most generic content manipulator.
 *
 * Because Realistic Dummy Content defines content as any data
 * including but not limited to entities, entities are managed
 * by a subclass of this, but we leave the door open to manipulators
 * of other types of data (including menus, for example, which are
 * not entites), which are not yet supported but which could be
 * in future releases. See the issue queue or open a new issue
 * at https://drupal.org/project/issues/realistic_dummy_content
 * if you would like to help with this!
 */
abstract class RealisticDummyContentBase {

  /**
   * Retrieves the current environment class.
   */
  public function env() {
    return RealisticDummyContentEnvironment::get();
  }

}
