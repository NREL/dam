<?php

namespace Drupal\realistic_dummy_content_api\includes;

use Drupal\realistic_dummy_content_api\Framework\Framework;

/**
 * Represents a term reference field.
 */
class RealisticDummyContentTermReferenceField extends RealisticDummyContentField {

  /**
   * {@inheritdoc}
   */
  public function implementValueFromFile($file) {
    try {
      $termname = $file->value();
      if ($termname) {
        $return = Framework::instance()->formatProperty('tid',
          $this->getTid($termname));
        return $return;
      }
    }
    catch (\Exception $e) {
      Framework::instance()->debug('Problem with taxonomy term: ' . $e->getMessage());
      return NULL;
    }
  }

  /**
   * Returns term id for a term which is either existing or created on the fly.
   *
   * Let's say an entity (node) contains a term reference to the taxonomy
   * vocabulary "location", and in the realistic dummy content file structure,
   * "Australia" is used for the location. If "Australia" exists as a
   * "location", then this function will return its tid. If not, the term will
   * be created, and then the tid will be returned.
   *
   * @param string $name
   *   The string for the taxonomy term.
   *
   * @return int
   *   The associated pre-existing or just-created tid.
   *
   * @throws \Exception
   */
  public function getTid($name) {
    $vocabularies = Framework::instance()->getAllVocabularies();
    $field_info = Framework::instance()->fieldInfoField($this->getName());
    $candidate_existing_terms = array();
    foreach ($field_info['settings']['allowed_values'] as $vocabulary) {
      $vocabulary_name = $vocabulary['vocabulary'];
      foreach ($vocabularies as $vocabulary) {
        if ($vocabulary->machine_name == $vocabulary_name) {
          $candidate_existing_terms = array_merge($candidate_existing_terms, taxonomy_get_tree(Framework::instance()->vocabularyIdentifier($vocabulary)));
        }
      }
    }
    foreach ($candidate_existing_terms as $candidate_existing_term) {
      if ($candidate_existing_term->name == $name) {
        return $candidate_existing_term->tid;
      }
    }

    if (!isset($vocabulary)) {
      throw new \Exception('Expecting the taxonomy term reference to reference at least one vocabulary');
    }

    $term = Framework::instance()->newVocabularyTerm($vocabulary, $name);

    if (isset($term->tid) && $term->tid) {
      return $term->tid;
    }
    else {
      throw new \Exception('tid could not be determined');
    }
  }

}
