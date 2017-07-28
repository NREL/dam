<?php

namespace Drupal\realistic_dummy_content_api\Framework;

// Explicitly load the Drupal 7 class from which we inherit.
module_load_include('php', 'realistic_dummy_content_api', 'src/Framework/Drupal7');

/**
 * Represents the Backdrop framework.
 */
class Backdrop extends Drupal7 {

  /**
   * {@inheritdoc}
   */
  public function fileSave($drupal_file) {
    file_save($drupal_file);
    return $drupal_file;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultNodeType() {
    return 'post';
  }

  /**
   * {@inheritdoc}
   */
  public function vocabularyIdentifier($vocabulary) {
    return $vocabulary->name;
  }

  /**
   * {@inheritdoc}
   */
  public function newVocabularyTerm($vocabulary, $name) {
    $term = new \TaxonomyTerm(array(
      'name' => $name,
      'vocabulary' => $vocabulary->machine_name,
    ));
    $term->save();
    return $term;
  }

}
