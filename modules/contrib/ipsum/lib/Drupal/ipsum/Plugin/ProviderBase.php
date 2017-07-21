<?php

/**
 * @file
 * Definition of Drupal\ipsum\Plugin\ipsum\ProviderBase.
 */

namespace Drupal\ipsum\Plugin;

use Drupal\Core\Plugin\PluginBase;

/**
 * Provides a base class for Ipsum plugins.
 */
abstract class ProviderBase extends PluginBase implements ProviderInterface {

  /**
   * The plugin ID of this provider.
   *
   * @var string
   */
  protected $plugin_id;

  /**
   * The name of the module that owns this provider.
   *
   * @var string
   */
  public $module;

  /**
   * An associative array containing the configured settings of this provider.
   *
   * @var array
   */
  public $settings = array(
    'sentence_words_min' => 6,
    'sentence_words_max' => 20,
    'paragraph_sentences_min' => 2,
    'paragraph_sentences_max' => 6,
  );

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->module = $this->pluginDefinition['module'];
    $this->cache = $this->pluginDefinition['cache'];

    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    if (isset($configuration['settings'])) {
      $this->settings = (array) $configuration['settings'];
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return array(
      'id' => $this->getPluginId(),
      'module' => $this->pluginDefinition['module'],
      'settings' => $this->settings,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['title'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->pluginDefinition['description'];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, array &$form_state) {
    // Sentences.
    $form['sentence'] = array(
      '#type' => 'fieldset',
      '#title' => t('Sentences'),
    );

    // @TODO investigate whether we can nest settings, ie: settings[min].
    $form['sentence']['sentence_words_min'] = array(
      '#type' => 'number',
      '#title' => t('Min words'),
      '#description' => t('The minimum number of words that can make up a sentence.'),
      '#default_value' => $this->settings['sentence_words_min'],
    );

    $form['sentence']['sentence_words_max'] = array(
      '#type' => 'number',
      '#title' => t('Max words'),
      '#description' => t('The maximum number of words that can make up a sentence.'),
      '#default_value' => $this->settings['sentence_words_max'],
    );

    // Paragrahs.
    $form['paragraph'] = array(
      '#type' => 'fieldset',
      '#title' => t('Paragraphs'),
    );

    $form['paragraph']['paragraph_sentences_min'] = array(
      '#type' => 'number',
      '#title' => t('Min sentences'),
      '#description' => t('The minimum number of sentences that can make up a paragraph.'),
      '#default_value' => $this->settings['paragraph_sentences_min'],
    );

    $form['paragraph']['paragraph_sentences_max'] = array(
      '#type' => 'number',
      '#title' => t('Max sentences'),
      '#description' => t('The maximum number of sentences that can make up a paragraph.'),
      '#default_value' => $this->settings['paragraph_sentences_max'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getVocabulary() {
    // Provider implementations should override this.
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function generate($type = NULL, $number = NULL, $startsWith = NULL, $html = TRUE) {
    // Default type.
    if (!isset($type)) {
      $type = ProviderInterface::TYPE_PARAGRAPH;
    }

    // Default number.
    if (!isset($number)) {
      $number = 1;
    }

    switch ($type) {
      case ProviderInterface::TYPE_PARAGRAPH:
        return $this->paragraphs($number, $startsWith, $html);
      case ProviderInterface::TYPE_SENTENCE:
        return $this->sentences($number, $startsWith);
      case ProviderInterface::TYPE_WORD:
        return $this->words($number, $startsWith);
      default:
        // @TODO return false?  throw exception?
    }
  }

  /**
   * Return a given number of words.
   *
   * @param integer
   *   The number of words.
   * @param string
   *   Optional. Specify a specific word or words to start with.
   *
   * @return string
   *   The generated words.
   */
  protected function words($number, $startsWith = NULL) {
    $return = array();
    $vocab = $this->getVocabulary();
    $vocab_length = count($vocab);

    // The upper bound of the array. This ie necessary because array_rand()
    // is kinda problematic, so we pick the random key ourselves.
    // @link http://www.php.net/manual/en/function.array-rand.php
    $lim = $vocab_length > 0 ? $vocab_length - 1 : 0;

    // Start with specific words.
    if (isset($startsWith)) {
      $explode = explode(' ', $startsWith);
      $starts_length = count($explode);

      // Make sure we don't exceed the given number of words, even if
      // our "starts with" text is longer.
      if ($starts_length > $number) {
        while (count($explode) > $number) {
          array_pop($explode);
        }

        $starts_length = count($explode);
      }

      $number -= $starts_length;
      $return = $explode;

      if ($number <= 0) {
        return implode(' ', $return);
      }
    }

    // Generate the remaining words.
    for ($i = 0; $i < $number; ++$i) {
      $new_word = $vocab[mt_rand(0, $lim)];
      // Prevent duplicate consecutive words.
      $previous_word = $i > 0 ? $return[$i - 1] : NULL;

      if ($new_word !== $previous_word) {
        $return[] = $new_word;
      }
      else {
        // Force an extra iteration.
        --$i;
      }
    }

    // Capitalize first word if we're generating more than one.
    if ($number > 1 && isset($return[0])) {
      $return[0] = ucwords($return[0]);
    }

    return implode(' ', $return);
  }

  /**
   * Return a given number of sentences.
   *
   * @param integer
   *   The number of sentences.
   * @param string
   *   Optional. Specify a specific word or words to start with.
   *
   * @return string
   *   The generated sentences.
   */
  protected function sentences($number, $startsWith = NULL) {
    $return = '';
    $start = 0;

    // Start with specific words.
    if (isset($startsWith)) {
      $return .= "{$startsWith} ";
      $start = 1;
      $wordsPerSentence = mt_rand($this->settings['sentence_words_min'], $this->settings['sentence_words_max']);
      $explode = explode(' ', $startsWith);
      $starts_length = count($explode);
      $wordsPerSentence -= $starts_length;
      $return .= strtolower($this->words($wordsPerSentence) . '. ');
    }

    // Generate remaining words.
    for ($i = $start; $i < $number; ++$i) {
      $wordsPerSentence = mt_rand($this->settings['sentence_words_min'], $this->settings['sentence_words_max']);
      $return .= $this->words($wordsPerSentence) . '. ';
    }

    return trim($return);
  }

  /**
   * Return a given number paragraphs.
   *
   * @param integer
   *   The number of paragraphs.
   * @param string
   *   Optional. Specify a specific word or words to start with.

   * @param boolean
   *   Whether to wrap the paragraphs in <p> tags.
   *
   * @return string
   *   The generated paragraphs.
   */
  protected function paragraphs($number, $startsWith = NULL, $html = TRUE) {
    $return = '';
    $start = 0;

    if (isset($startsWith)) {
      if ($html) {
        $return .= '<p>';
      }

      $return .= "{$startsWith} ";
      $start = 1;
      $wordsPerSentence = mt_rand($this->settings['sentence_words_min'], $this->settings['sentence_words_max']);
      $explode = explode(' ', $startsWith);
      $starts_length = count($explode);
      $wordsPerSentence -= $starts_length;
      $return .= strtolower($this->words($wordsPerSentence) . '. ');
      $sentencesPerParagraph = mt_rand($this->settings['paragraph_sentences_min'], $this->settings['paragraph_sentences_max']);

      // We already have our first sentence.
      --$sentencesPerParagraph;
      $return .= $this->sentences($sentencesPerParagraph);

      if ($html) {
        $return .= '</p>';
      }
    }

    for ($i = $start; $i < $number; ++$i) {
      $sentencesPerParagraph = mt_rand($this->settings['paragraph_sentences_min'], $this->settings['paragraph_sentences_max']);

      if ($html) {
        $return .= '<p>';
      }

      $return .= $this->sentences($sentencesPerParagraph);

      if ($html) {
        $return .= '</p>';
      }
    }

    return $return;
  }
}
