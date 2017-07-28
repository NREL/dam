<?php

namespace Drupal\textimage\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a path processor to rewrite Textimage URLs.
 *
 * Supports deferred Textimage generation from textimage formatter themes for
 * both public and private stream wrappers, and direct URL generation of
 * derivatives.
 */
class TextimagePathProcessor implements InboundPathProcessorInterface {

  /**
   * The stream wrapper manager service.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface
   */
  protected $streamWrapperManager;

  /**
   * Constructs a new TextimagePathProcessor object.
   *
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface $stream_wrapper_manager
   *   The stream wrapper manager service.
   */
  public function __construct(StreamWrapperManagerInterface $stream_wrapper_manager) {
    $this->streamWrapperManager = $stream_wrapper_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    $public_directory_path = $this->streamWrapperManager->getViaScheme('public')->getDirectoryPath();
    if (strpos($path, '/' . $public_directory_path . '/textimage_store/') === 0) {
      // Path is for deferred Textimage generation from public scheme.
      $path_prefix = '/' . $public_directory_path . '/textimage_store';

      // Strip out path prefix.
      $rest = preg_replace('|^' . preg_quote($path_prefix . '/', '|') . '|', '', $path);

      // Set the file as query parameter.
      $request->query->set('file', $rest);
      return $path_prefix;
    }
    elseif (strpos($path, '/system/files/textimage_store/') === 0) {
      // Path is for deferred Textimage generation from private scheme.
      $path_prefix = '/system/files/textimage_store';

      // Strip out path prefix.
      $rest = preg_replace('|^' . preg_quote($path_prefix . '/', '|') . '|', '', $path);

      // Set the file as query parameter.
      $request->query->set('file', $rest);
      return $path_prefix;
    }
    elseif (strpos($path, '/' . $public_directory_path . '/textimage/') === 0) {
      // Path is for direct URL Textimage generation.
      $path_prefix = '/' . $public_directory_path . '/textimage';

      // Strip out path prefix.
      $rest = preg_replace('|^' . preg_quote($path_prefix . '/', '|') . '|', '', $path);

      // Get the image style and text.
      if (substr_count($rest, '/') >= 1) {
        list($image_style, $text) = explode('/', $rest, 2);
        // Set the text as query parameter.
        $request->query->set('text', $text);
        return $path_prefix . '/' . $image_style;
      }
      else {
        return $path;
      }
    }
    else {
      return $path;
    }
  }

}
