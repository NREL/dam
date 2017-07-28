<?php

namespace Drupal\textimage;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\image\ImageStyleInterface;
use Drupal\file\FileInterface;

/**
 * Provides an interface for Textimage objects.
 */
interface TextimageInterface extends ContainerInjectionInterface {

  /**
   * Set the image style.
   *
   * @param \Drupal\image\ImageStyleInterface $image_style
   *   The image style to be used to derive the Textimage.
   *
   * @return $this
   */
  public function setStyle(ImageStyleInterface $image_style);

  /**
   * Set the image effects.
   *
   * @param array $effects
   *   An array of image effects. Since Textimage manipulates effects before
   *   rendering the image, the style effects are copied here to allow that.
   *
   * @return $this
   */
  public function setEffects(array $effects);

  /**
   * Sets the image file extension.
   *
   * @param string $extension
   *   The file extension to be used (e.g. jpeg/png/gif).
   *
   * @return $this
   */
  public function setTargetExtension($extension);

  /**
   * Set the RGB hex color to be used for GIF images.
   *
   * @param string $color
   *   The color to be used for transparent.
   *
   * @return $this
   */
  public function setGifTransparentColor($color);

  /**
   * Sets the image source file.
   *
   * @param \Drupal\file\FileInterface $source_image_file
   *   A file entity.
   * @param int|null $width
   *   (optional) The source image width if known. Defaults to NULL.
   * @param int|null $height
   *   (optional) The source image height if known. Defaults to NULL.
   *
   * @return $this
   */
  public function setSourceImageFile(FileInterface $source_image_file, $width = NULL, $height = NULL);

  /**
   * Sets the token data to resolve tokens.
   *
   * @param array $token_data
   *   An array of objects to resolve tokens.
   *
   * @return $this
   */
  public function setTokenData(array $token_data);

  /**
   * Set Textimage to be temporary.
   *
   * @param bool $is_temp
   *   FALSE if caching is required for this Textimage.
   *
   * @return $this
   */
  public function setTemporary($is_temp);

  /**
   * Set image destination URI.
   *
   * @param string $uri
   *   A valid URI.
   *
   * @return $this
   */
  public function setTargetUri($uri);

  /**
   * Sets the bubbleable metadata.
   *
   * @param \Drupal\Core\Render\BubbleableMetadata $bubbleable_metadata
   *   A BubbleableMetadata object.
   *
   * @return $this
   *
   * @internal
   */
  public function setBubbleableMetadata(BubbleableMetadata $bubbleable_metadata = NULL);

  /**
   * Return the Textimage id.
   *
   * @return string
   *   A SHA256 hash.
   */
  public function id();

  /**
   * Return the processed text.
   *
   * @return array
   *   An array of fully processed text elements.
   */
  public function getText();

  /**
   * Returns the URI of the Textimage.
   *
   * @return string
   *   An URI.
   */
  public function getUri();

  /**
   * Returns the URL of the Textimage.
   *
   * @return \Drupal\Core\Url
   *   The Url object for the textimage.
   */
  public function getUrl();

  /**
   * Returns the height of the Textimage.
   *
   * @return int|null
   *   The height of the Textimage, or NULL if not available.
   */
  public function getHeight();

  /**
   * Returns the width of the Textimage.
   *
   * @return int|null
   *   The width of the Textimage, or NULL if not available.
   */
  public function getWidth();

  /**
   * Gets the bubbleable metadata of the Textimage.
   *
   * @return \Drupal\Core\Render\BubbleableMetadata
   *   A BubbleableMetadata object.
   */
  public function getBubbleableMetadata();

  /**
   * Load Textimage metadata from cache.
   *
   * @param string $id
   *   The id of the Textimage to load.
   *
   * @return bool
   *   TRUE if cache entry exists, FALSE otherwise.
   */
  public function load($id);

  /**
   * Process the Textimage, with the required raw text.
   *
   * @param array|string $text
   *   An array of text strings, or a single string, with tokens not resolved.
   *
   * @return $this
   */
  public function process($text);

  /**
   * Build the image via core ImageStyle::createDerivative() method.
   *
   * @return $this
   */
  public function buildImage();

}
