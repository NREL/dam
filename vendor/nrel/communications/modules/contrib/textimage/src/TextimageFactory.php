<?php

namespace Drupal\textimage;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\Utility\Token;
use Drupal\image\Entity\ImageStyle;
use Drupal\image\ImageStyleInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides a factory for Textimage.
 */
class TextimageFactory implements TextimageFactoryInterface {

  /**
   * The token resolution service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The Textimage logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The stream wrapper manager service.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManager
   */
  protected $streamWrapperManager;

  /**
   * The Textimage cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The User entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

  /**
   * Constructs a new TextimageFactory object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Utility\Token $token_service
   *   The token resolution service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The Textimage logger.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_service
   *   The Textimage cache service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManager $stream_wrapper_manager
   *   The stream wrapper manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The image style entity storage.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Token $token_service, LoggerInterface $logger, CacheBackendInterface $cache_service, AccountInterface $current_user, StreamWrapperManager $stream_wrapper_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->token = $token_service;
    $this->logger = $logger;
    $this->cache = $cache_service;
    $this->currentUser = $current_user;
    $this->streamWrapperManager = $stream_wrapper_manager;
    $this->userStorage = $entity_type_manager->getStorage('user');
  }

  /**
   * {@inheritdoc}
   */
  public function get(BubbleableMetadata $bubbleable_metadata = NULL) {
    // @todo remove the \Drupal::getContainer() call in a future new major
    // @codingStandardsIgnoreLine
    $textimage = Textimage::create(\Drupal::getContainer());
    $textimage->setBubbleableMetadata($bubbleable_metadata);
    return $textimage;
  }

  /**
   * {@inheritdoc}
   */
  public function load($tiid) {
    $textimage = $this->get();
    $textimage->load($tiid);
    return $textimage;
  }

  /**
   * {@inheritdoc}
   */
  public function processTextString($text, $case_format, array $token_data = [], BubbleableMetadata $bubbleable_metadata = NULL) {
    // Replace any tokens in text with run-time values.
    $token_data['user'] = !empty($token_data['user']) ? $token_data['user'] : $this->userStorage->load($this->currentUser->id());
    return $this->token->replace($text, $token_data, [], $bubbleable_metadata);
  }

  /**
   * Gets a Textimage state variable.
   *
   * @todo (core) remove when #1826362 (ImageStyle to be accessible from
   * ImageEffect plugins) is committed.
   *
   * @param string $variable
   *   State variable.
   *
   * @return mixed
   *   Returned variable, NULL if undefined.
   */
  public function getState($variable = NULL) {
    if ($variable) {
      return $this->setState($variable);
    }
    return NULL;
  }

  /**
   * Sets a Textimage state variable.
   *
   * @todo (core) remove when #1826362 (ImageStyle to be accessible from
   * ImageEffect plugins) is committed.
   *
   * @param string $variable
   *   State variable.
   * @param mixed $value
   *   Value to set, or NULL to return current value.
   *
   * @return mixed
   *   Property value.
   */
  public function setState($variable = NULL, $value = NULL) {
    static $keys;

    if (!isset($keys) or !$variable) {
      $keys = [];
    }

    if ($variable) {
      if ($value) {
        $keys[$variable] = $value;
        return $value;
      }
      else {
        return isset($keys[$variable]) ? $keys[$variable] : NULL;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isTextimage(ImageStyleInterface $image_style) {
    foreach ($image_style->getEffects() as $effect) {
      $definition = $effect->getPluginDefinition();
      if ($definition['id'] == 'image_effects_text_overlay') {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getTextimageStyleOptions($limit_to_textimage = FALSE) {
    $image_styles = ImageStyle::loadMultiple();
    $options = [];
    foreach ($image_styles as $name => $image_style) {
      if ($limit_to_textimage) {
        if ($this->isTextimage($image_style)) {
          $options[$name] = $image_style->label();
        }
      }
      else {
        $options[$name] = $image_style->label();
      }
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function flushStyle(ImageStyleInterface $style) {
    // Clear hashed filename images.
    $wrappers = $this->streamWrapperManager->getWrappers(StreamWrapperInterface::WRITE_VISIBLE);
    foreach ($wrappers as $wrapper => $wrapper_data) {
      if (file_exists($directory = $this->getStoreUri('/cache/styles/', $wrapper) . $style->id())) {
        file_unmanaged_delete_recursive($directory);
      }
    }
    // Clear public textimage directory.
    if (file_exists($directory = 'public://textimage/' . $style->id())) {
      file_unmanaged_delete_recursive($directory);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function flushAll() {
    // Flush Textimage relevant styles so to invalidate the image styles cache
    // tags.
    $styles = ImageStyle::loadMultiple();
    foreach ($styles as $style) {
      $style->flush();
    }
    // Clear whatever directory structure remains, checking in all available
    // schemes.
    $wrappers = $this->streamWrapperManager->getWrappers(StreamWrapperInterface::WRITE_VISIBLE);
    foreach ($wrappers as $wrapper => $wrapper_data) {
      if (file_exists($directory = $this->getStoreUri(NULL, $wrapper))) {
        file_unmanaged_delete_recursive($directory);
      }
    }
    // Remove the URL generation directory.
    if (file_exists($directory = 'public://textimage')) {
      file_unmanaged_delete_recursive($directory);
    }
    // Wipe Textimage cache.
    $this->cache->deleteAll();
    $this->logger->notice('All Textimage images were removed.');
  }

  /**
   * {@inheritdoc}
   */
  public function getStoreUri($path, $scheme = NULL) {
    if (!$scheme) {
      $scheme = $this->configFactory->get('system.file')->get('default_scheme');
    }
    return $scheme . '://textimage_store' . $path;
  }

  /**
   * {@inheritdoc}
   */
  public function processTokens($key, array $tokens, array $data, BubbleableMetadata $bubbleable_metadata) {

    // @todo Not only node?
    $node = isset($data['node']) ? $data['node'] : NULL;

    // Need to avoid endless loops, that would occur if there are
    // circular references in the tokens. Set static variables for
    // the nesting level and the stack of fields accessed so far.
    static $nesting_level;
    static $field_stack;
    if (!isset($nesting_level)) {
      $nesting_level = 0;
      $field_stack = [];
    }
    else {
      $nesting_level++;
    }

    // Get tokens specific for the required key.
    $sub_tokens = $this->token->findWithPrefix($tokens, $key);

    // Return immediately if none, or no node.
    if (empty($sub_tokens) || !$node) {
      $this->rollbackStack($nesting_level, $field_stack);
      return [];
    }

    // Determine the callback function.
    switch ($key) {
      case 'uri':
        $callback_method = 'getUri';
        break;

      case 'url':
        $callback_method = 'getUrl';
        break;

    }

    // Loops through the tokens to resolve.
    $replacements = [];
    foreach ($sub_tokens as $sub_token => $original) {

      // Clear current nesting level field stack.
      unset($field_stack[$nesting_level]);

      // Get token elements.
      $sub_token_array = explode(':', $sub_token);

      // Get requested field name, continue if missing.
      $field_name = isset($sub_token_array[0]) ? $sub_token_array[0] : NULL;
      if (!$field_name) {
        continue;
      }

      // Check for recursion, i.e. the field is already engaged in a
      // token resolution. Throw a TextimageTokenException in case.
      if (in_array($field_name, $field_stack)) {
        $this->rollbackStack($nesting_level, $field_stack);
        throw new TextimageTokenException($original);
      }

      // Set current requested field in the field stack.
      $field_stack[$nesting_level] = $field_name;

      // Get requested display mode, default to 'default'.
      $display_mode = isset($sub_token_array[1]) ? ($sub_token_array[1] ?: 'default') : 'default';

      // Get requested sequence, default to NULL.
      $index = isset($sub_token_array[2]) ? $sub_token_array[2] : NULL;

      // Get field info, continue if missing.
      if (!$field_info = $node->getFieldDefinition($field_name)) {
        continue;
      }

      // Get info on component providing formatting, continue if missing.
      $entity_display = entity_get_display('node', $node->getType(), $display_mode);
      if (!$entity_display) {
        continue;
      }
      $entity_display_component = $entity_display->getComponent($field_name);
      if (empty($entity_display_component['type'])) {
        continue;
      }

      // At this point, if Textimage is providing field formatting for the
      // current field, we can proceed accessing the data needed to resolve
      // the token.
      if (in_array($entity_display_component['type'], ['textimage_text_field_formatter', 'textimage_image_field_formatter'])) {

        // Get the image style used for the field formatting.
        $image_style_name = isset($entity_display_component['settings']['image_style']) ? $entity_display_component['settings']['image_style'] : NULL;
        if (!$image_style_name) {
          continue;
        }
        $image_style = ImageStyle::load($image_style_name);

        // Get the field items.
        $items = $node->get($field_name);

        // Invoke Textimage API functions to return the token value requested.
        if (in_array($field_info->getFieldStorageDefinition()->getTypeProvider(), ['text', 'core'])) {
          $text = $this->getTextFieldText($items);
          if ($field_info->getFieldStorageDefinition()->getCardinality() != 1 && $entity_display_component['settings']['image_text_values'] == 'itemize') {
            // Build separate image for each text value.
            try {
              $ret = [];
              foreach ($text as $text_value) {
                $textimage = $this->get($bubbleable_metadata)
                  ->setStyle($image_style)
                  ->setTokenData($data)
                  ->process($text_value);
                $ret[] = $textimage->$callback_method();
              }
              // Return a single URI/URL if requested, or a comma separated
              // list of all the URIs/URLs generated.
              if (!is_null($index) && isset($ret[$index])) {
                $replacements[$original] = $ret[$index];
              }
              else {
                $replacements[$original] = implode(',', $ret);
              }
            }
            catch (TextimageTokenException $e) {
              // Callback ended up in circular loop, mark the failing token.
              $replacements[$original] = str_replace('textimage', 'void-textimage', $original);
              if ($nesting_level > 0) {
                // Returns up in the nesting of iteration with the failing
                // token.
                $this->rollbackStack($nesting_level, $field_stack);
                throw new TextimageTokenException($e->getToken());
              }
              else {
                // Inform about the token failure.
                $this->logger->warning(
                  'Textimage token @token in node \'@node_title\' can not be resolved (circular reference). Remove the token to avoid this message.',
                  [
                    '@token' => $original,
                    '@node_title' => $node->getTitle(),
                  ]
                );
              }
            }
          }
          else {
            // Build single image with all text values.
            try {
              $textimage = $this->get($bubbleable_metadata)
                ->setStyle($image_style)
                ->setTokenData($data)
                ->process($text);
              $replacements[$original] = $textimage->$callback_method();
            }
            catch (TextimageTokenException $e) {
              // Callback ended up in circular loop, mark the failing token.
              $replacements[$original] = str_replace('textimage', 'void-textimage', $original);
              if ($nesting_level > 0) {
                // Returns up in the nesting of iteration with the failing
                // token.
                $this->rollbackStack($nesting_level, $field_stack);
                throw new TextimageTokenException($e->getToken());
              }
              else {
                // Inform about the token failure.
                $this->logger->warning(
                  'Textimage token @token in node \'@node_title\' can not be resolved (circular reference). Remove the token to avoid this message.',
                  [
                    '@token' => $original,
                    '@node_title' => $node->getTitle(),
                  ]
                );
              }
            }
          }
        }
        elseif ($field_info->getFieldStorageDefinition()->getTypeProvider() == 'image') {
          // Image field. Get a separate Textimage from each of the images
          // in the field.
          try {
            $ret = [];
            foreach ($items as $item) {
              // Get source image from the image field item.
              $item_value = $item->getValue();
              $textimage = $this->get($bubbleable_metadata)
                ->setStyle($image_style)
                ->setTokenData($data)
                ->setSourceImageFile($item->entity, $item_value['width'], $item_value['height'])
                ->process(NULL);
              $ret[] = $textimage->$callback_method();
            }
            // Return a single URI/URL if requested, or a comma separated
            // list of all the URIs/URLs generated.
            if (!is_null($index) && isset($ret[$index])) {
              $replacements[$original] = $ret[$index];
            }
            else {
              $replacements[$original] = implode(',', $ret);
            }
          }
          catch (TextimageTokenException $e) {
            // Callback ended up in circular loop, mark the failing token.
            $replacements[$original] = str_replace('textimage', 'void-textimage', $original);
            if ($nesting_level > 0) {
              // Returns up in the nesting of iteration with the failing token.
              $this->rollbackStack($nesting_level, $field_stack);
              throw new TextimageTokenException($e->getToken());
            }
            else {
              // Inform about the token failure.
              $this->logger->warning(
                'Textimage token @token in node \'@node_title\' can not be resolved (circular reference). Remove the token to avoid this message.',
                [
                  '@token' => $original,
                  '@node_title' => $node->getTitle(),
                ]
              );
            }
          }
        }
      }
    }

    // Return to previous iteration.
    $this->rollbackStack($nesting_level, $field_stack);
    return $replacements;
  }

  /**
   * Helper method to rollback nesting static variables in processTokens.
   */
  protected function rollbackStack(&$nesting_level, &$field_stack) {
    if ($nesting_level) {
      unset($field_stack[$nesting_level]);
      $nesting_level--;
    }
    else {
      $nesting_level = NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTextFieldText(FieldItemListInterface $items) {
    $text = [];
    foreach ($items as $item) {
      $value = $item->getValue();
      $text[] = !empty($value['value']) ? $value['value'] : '';
    }
    return $text;
  }

}
