<?php

/**
 * @file
 * Definition of Drupal\ipsum\Plugin\ipsum\provider\BaconIpsum.
 */

namespace Drupal\ipsum\Plugin\ipsum\provider;

use Drupal\ipsum\Plugin\ProviderBase;

/**
 * Provides a plugin to generate Bacon-flavored ipsum.
 *
 * @IpsumProvider(
 *   id = "bacon",
 *   label = @Translation("Bacon Ipsum"),
 *   description = @Translation("Delicious Bacon-flavored lorem ipsum text"),
 *   settings = {
 *     "sentence_words_min" = 6,
 *     "sentence_words_max" = 20,
 *     "paragraph_sentences_min" = 2,
 *     "paragraph_sentences_max" = 6
 *   }
 * )
 */
class BaconIpsum extends ProviderBase {

  /**
   * {@inheritdoc}
   */
  public function getVocabulary() {
    return array(
      // @TODO
      'bacon',
    );
  }

}
