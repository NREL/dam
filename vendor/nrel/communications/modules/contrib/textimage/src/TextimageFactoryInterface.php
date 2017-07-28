<?php

namespace Drupal\textimage;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\image\ImageStyleInterface;

/**
 * Provides an interface for TextimageFactory.
 */
interface TextimageFactoryInterface {

  /**
   * Gets a Textimage object.
   *
   * @param \Drupal\Core\Render\BubbleableMetadata $bubbleable_metadata
   *   A BubbleableMetadata object.
   *
   * @return \Drupal\textimage\Textimage
   *   A new Textimage object.
   */
  public function get(BubbleableMetadata $bubbleable_metadata = NULL);

  /**
   * Loads a cached Textimage object.
   *
   * @param string $tiid
   *   The Textimage ID.
   *
   * @return \Drupal\textimage\Textimage
   *   A Textimage object with properties loaded from cache.
   */
  public function load($tiid);

  /**
   * Processes text string, detokenises and applies case conversion.
   *
   * @param string $text
   *   The input string containing unresolved tokens.
   * @param string $case_format
   *   A text conversion option.
   * @param array $token_data
   *   (optional) Token data to be passed to Token::replace.
   * @param \Drupal\Core\Render\BubbleableMetadata $bubbleable_metadata
   *   (optional) A BubbleableMetadata object to collect cacheability metadata
   *   from the token replacement process.
   *
   * @return string
   *   The processed text string.
   *
   * @todo The $case_format parameter is deprecated, remove in a future major.
   */
  public function processTextString($text, $case_format, array $token_data = [], BubbleableMetadata $bubbleable_metadata = NULL);

  /**
   * Checks if an image style is Textimage relevant.
   *
   * @param \Drupal\image\ImageStyleInterface $image_style
   *   The image style to check.
   *
   * @return bool
   *   TRUE if style is Textimage relevant, otherwise FALSE
   */
  public function isTextimage(ImageStyleInterface $image_style);

  /**
   * Gets an array of Textimage image styles suitable for select list options.
   *
   * @param bool $limit_to_textimage
   *   (optional) TRUE to limit styles to only those with Textimage effects.
   *
   * @return string[]
   *   Array of image styles, where both key and value are set to style name.
   */
  public function getTextimageStyleOptions($limit_to_textimage = FALSE);

  /**
   * Flushes Textimage style data.
   *
   * Clears immediate cache and all the image files associated.
   *
   * @param \Drupal\image\ImageStyleInterface $style
   *   The style being flushed.
   */
  public function flushStyle(ImageStyleInterface $style);

  /**
   * Cleans up Textimage.
   *
   * This will remove all image files generated via Textimage, flush all
   * the image styles, clear all cache and all store entries on the db.
   */
  public function flushAll();

  /**
   * Returns a URI within the textimage_store structure.
   *
   * @param string $path
   *   The relative path.
   * @param string $scheme
   *   (optional) The URI scheme of the textimage_store. If NULL, the scheme
   *   set as site default will be used.
   *
   * @return string
   *   The full URI for the specified scheme and relative path.
   */
  public function getStoreUri($path, $scheme = NULL);

  /**
   * Textimage tokens replacement.
   *
   * @param string $key
   *   The Textimage token key within the main token [textimage:key:...].
   *   Key can take 'uri' or 'url' values.
   * @param array $tokens
   *   The tokens to resolve.
   * @param array $data
   *   Token data array.
   * @param \Drupal\Core\Render\BubbleableMetadata $bubbleable_metadata
   *   The bubbleable metadata.
   *
   * @return array
   *   An array of token replacements.
   */
  public function processTokens($key, array $tokens, array $data, BubbleableMetadata $bubbleable_metadata);

  /**
   * Retrieves text from a Text field.
   *
   * @param Drupal\Core\Field\FieldItemListInterface $items
   *   Field items.
   *
   * @return array
   *   An array of sanitized text items.
   */
  public function getTextFieldText(FieldItemListInterface $items);

}
