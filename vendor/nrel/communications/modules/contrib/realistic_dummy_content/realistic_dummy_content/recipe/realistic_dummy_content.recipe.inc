<?php

/**
 * @file
 * Contains a recipe for generating realistic dummy content.
 */

// Required as of the 2.x branch.
use Drupal\realistic_dummy_content_api\includes\RealisticDummyContentRecipe;

/**
 * An example recipe. This one creates four pages, and then 10 articles.
 */
// @codingStandardsIgnoreStart
// We cannot Make this file conform to coding standards because existing
// projects might have used this as an example and we need to continue
// supporting this technique for defining recipes.
class realistic_dummy_content_realistic_dummy_content_recipe extends RealisticDummyContentRecipe {

  /**
   * {@inheritdoc}
   */
  public function _Run_() {
    // If you want sequential numbering rather than random numbering, use
    // variable_set('realistic_dummy_content_api_rand',
    // REALISTIC_DUMMY_CONTENT_API_SEQUENTIAL);.
    $this->NewEntities('node', 'page', 4, array('kill' => TRUE));
    $this->NewEntities('node', 'article', 10, array('kill' => TRUE));
  }
// @codingStandardsIgnoreEnd

}
