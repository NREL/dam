<?php

namespace Drupal\textimage;

use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\image\ImageEffectManager;
use Drupal\image\ImageStyleInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides a Textimage.
 */
class Textimage implements TextimageInterface {

  use StringTranslationTrait;

  /**
   * The Textimage factory service.
   *
   * @var \Drupal\textimage\TextimageFactory
   */
  protected $factory;

  /**
   * The lock service.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * The image factory service.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The textimage cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The Textimage logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The image effect manager service.
   *
   * @var \Drupal\image\ImageEffectManager
   */
  protected $imageEffectManager;

  /**
   * Textimage id.
   *
   * It is a SHA256 hash of the textimage effects and data.
   *
   * @var string
   *
   * @see \Drupal\textimage\Textimage::process()
   */
  protected $id = NULL;

  /**
   * If data for this Textimage has been processed.
   *
   * @var bool
   */
  protected $processed = FALSE;

  /**
   * If this Textimage has been built.
   *
   * @var bool
   */
  protected $built = FALSE;

  /**
   * Textimage metadata.
   *
   * @var array
   */
  protected $imageData = [];

  /**
   * Textimage URI.
   *
   * @var string
   */
  protected $uri = NULL;

  /**
   * Textimage width.
   *
   * @var int
   */
  protected $width = NULL;

  /**
   * Textimage height.
   *
   * @var int
   */
  protected $height = NULL;

  /**
   * Image style used for this Textimage.
   *
   * @var \Drupal\image\ImageStyleInterface
   */
  protected $style = NULL;

  /**
   * The array of image effects for this Textimage.
   *
   * @var array
   */
  protected $effects = [];

  /**
   * The array of text elements for this Textimage.
   *
   * @var array
   */
  protected $text = [];

  /**
   * The file extension for this Textimage.
   *
   * @var string
   */
  protected $extension = NULL;

  /**
   * RGB hex color to be used for GIF images.
   *
   * Image effects may override this setting, this is here in case we build
   * a Textimage from scratch.
   *
   * @var string
   */
  protected $gifTransparentColor = '#FFFFFF';

  /**
   * If this Textimage has to be cached.
   *
   * @var bool
   */
  protected $caching = TRUE;

  /**
   * An image file entity.
   *
   * The source file used to build the image derivative in standard image
   * system context. Also used to track Textimages from image fields formatted
   * through Textimage field display formatter and to resolve file tokens.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $sourceImageFile = NULL;

  /**
   * An array of objects to resolve tokens.
   *
   * @var array
   */
  protected $tokenData = [];

  /**
   * Bubbleable metadata of the Textimage.
   *
   * @var \Drupal\Core\Render\BubbleableMetadata
   */
  protected $bubbleableMetadata = NULL;

  /**
   * Constructs a Textimage object.
   *
   * @param \Drupal\textimage\TextimageFactory $textimage_factory
   *   The Textimage factory.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock_service
   *   The lock service.
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   The image factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Psr\Log\LoggerInterface $logger
   *   The Textimage logger.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_service
   *   The Textimage cache service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\image\ImageEffectManager $image_effect_manager
   *   The image effect manager service.
   */
  public function __construct(TextimageFactory $textimage_factory, LockBackendInterface $lock_service, ImageFactory $image_factory, ConfigFactoryInterface $config_factory, LoggerInterface $logger, CacheBackendInterface $cache_service, FileSystemInterface $file_system, ImageEffectManager $image_effect_manager) {
    $this->factory = $textimage_factory;
    $this->lock = $lock_service;
    $this->imageFactory = $image_factory;
    $this->configFactory = $config_factory;
    $this->logger = $logger;
    $this->cache = $cache_service;
    $this->fileSystem = $file_system;
    $this->imageEffectManager = $image_effect_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('textimage.factory'),
      $container->get('lock'),
      $container->get('image.factory'),
      $container->get('config.factory'),
      $container->get('textimage.logger'),
      $container->get('cache.textimage'),
      $container->get('file_system'),
      $container->get('plugin.manager.image.effect')
    );
  }

  /**
   * Set a property to a specified value.
   *
   * A Textimage already processed will not allow changes.
   *
   * @param string $property
   *   The property to set.
   * @param mixed $value
   *   The value to set.
   *
   * @return $this
   */
  protected function set($property, $value) {
    if (!property_exists($this, $property)) {
      throw new TextimageException("Attempted to set non existing property '{$property}'");
    }
    if (!$this->processed) {
      $this->$property = $value;
    }
    else {
      throw new TextimageException("Attempted to set property '{$property}' when image was processed already");
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setStyle(ImageStyleInterface $image_style) {
    if ($this->style) {
      throw new TextimageException("Image style already set");
    }
    $this->set('style', $image_style);
    $effects = @$this->style->getEffects()->getConfiguration();
    $this->setEffects($effects);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setEffects(array $effects) {
    if ($this->effects) {
      throw new TextimageException("Image effects already set");
    }
    return $this->set('effects', $effects);
  }

  /**
   * {@inheritdoc}
   */
  public function setTargetExtension($extension) {
    if ($this->extension) {
      throw new TextimageException("Extension already set");
    }
    $extension = strtolower($extension);
    if (!in_array($extension, $this->imageFactory->getSupportedExtensions())) {
      $this->logger->error("Unsupported image file extension (%extension) requested.", ['%extension' => $extension]);
      throw new TextimageException("Attempted to set an unsupported file image extension ({$extension})");
    }
    return $this->set('extension', $extension);
  }

  /**
   * {@inheritdoc}
   */
  public function setGifTransparentColor($color) {
    return $this->set('gifTransparentColor', $color);
  }

  /**
   * {@inheritdoc}
   */
  public function setSourceImageFile(FileInterface $source_image_file, $width = NULL, $height = NULL) {
    if ($source_image_file) {
      $this->set('sourceImageFile', $source_image_file);
    }
    if ($width && $height) {
      $this->set('width', $width);
      $this->set('height', $height);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setTokenData(array $token_data) {
    if ($this->tokenData) {
      throw new TextimageException("Token data already set");
    }
    return $this->set('tokenData', $token_data);
  }

  /**
   * {@inheritdoc}
   */
  public function setTemporary($is_temp) {
    if ($this->uri) {
      throw new TextimageException("URI already set");
    }
    $this->set('caching', !$is_temp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setTargetUri($uri) {
    if ($this->uri) {
      throw new TextimageException("URI already set");
    }
    if ($uri) {
      if (!file_valid_uri($uri)) {
        throw new TextimageException("Invalid target URI '{$uri}' specified");
      }
      $dir_name = $this->fileSystem->dirname($uri);
      $base_name = $this->fileSystem->basename($uri);
      $valid_uri = $this->createFilename($base_name, $dir_name);
      if ($uri != $valid_uri) {
        throw new TextimageException("Invalid target URI '{$uri}' specified");
      }
      $this->setTargetExtension(pathinfo($uri, PATHINFO_EXTENSION));
      $this->set('uri', $uri);
      $this->set('caching', FALSE);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setBubbleableMetadata(BubbleableMetadata $bubbleable_metadata = NULL) {
    if ($this->bubbleableMetadata) {
      throw new TextimageException("Bubbleable metadata already set");
    }
    $bubbleable_metadata = $bubbleable_metadata ?: new BubbleableMetadata();
    return $this->set('bubbleableMetadata', $bubbleable_metadata);
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->processed ? $this->id : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getText() {
    return $this->processed ? array_values($this->text) : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getUri() {
    return $this->processed ? $this->uri : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl() {
    return $this->processed ? Url::fromUri(file_create_url($this->getUri())) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getHeight() {
    return $this->processed ? $this->height : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidth() {
    return $this->processed ? $this->width : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getBubbleableMetadata() {
    return $this->processed ? $this->bubbleableMetadata : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function load($id) {
    // Do not re-process.
    if ($this->processed) {
      return $this;
    }
    // Load from the cache.
    $this->id = $id;
    if ($cached_data = $this->getCachedData()) {
      $this->set('imageData', $cached_data['imageData']);
      $this->set('uri', $cached_data['uri']);
      $this->set('width', $cached_data['width']);
      $this->set('height', $cached_data['height']);
      $this->set('effects', $cached_data['effects']);
      $this->set('text', $cached_data['imageData']['text']);
      $this->set('extension', $cached_data['imageData']['extension']);
      if ($cached_data['imageData']['sourceImageFileId']) {
        $this->set('sourceImageFile', File::load($cached_data['imageData']['sourceImageFileId']));
      }
      $this->set('gifTransparentColor', $cached_data['imageData']['gifTransparentColor']);
      $this->set('caching', TRUE);
      $this->set('bubbleableMetadata', $cached_data['bubbleableMetadata']);
      $this->processed = TRUE;
    }
    else {
      throw new TextimageException("Missing Textimage cache entry {$this->id}");
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text) {
    // Do not re-process.
    if ($this->processed) {
      throw new TextimageException("Attempted to re-process an already processed Textimage");
    }

    // Effects must be loaded.
    if (empty($this->effects)) {
      $this->logger->error('Textimage had no image effects to process.');
      return $this;
    }

    // Collect bubbleable metadata.
    if ($this->style) {
      $this->bubbleableMetadata = $this->bubbleableMetadata->addCacheableDependency($this->style);
    }
    if ($this->sourceImageFile) {
      $this->bubbleableMetadata = $this->bubbleableMetadata->addCacheableDependency($this->sourceImageFile);
    }

    // Normalise $text to an array.
    if (!$text) {
      $text = [];
    }
    if (!is_array($text)) {
      $text = [$text];
    }

    // Find the default text from effects.
    $default_text = [];
    foreach ($this->effects as $uuid => $effect_configuration) {
      if ($effect_configuration['id'] == 'image_effects_text_overlay') {
        $uuid = isset($effect_configuration['uuid']) ? $effect_configuration['uuid'] : $uuid;
        $default_text[$uuid] = $effect_configuration['data']['text_string'];
      }
    }

    // Process text to resolve tokens and required case conversions.
    $processed_text = [];
    $this->tokenData['file'] = isset($this->tokenData['file']) ? $this->tokenData['file'] : $this->sourceImageFile;
    foreach ($default_text as $uuid => $default_text_item) {
      $text_item = array_shift($text);
      $effect_instance = $this->imageEffectManager->createInstance($this->effects[$uuid]['id']);
      $effect_instance->setConfiguration($this->effects[$uuid]);
      if ($text_item) {
        // Replace any tokens in text with run-time values.
        $text_item = ($text_item == '[textimage:default]') ? $default_text_item : $text_item;
        $processed_text[$uuid] = $this->factory->processTextString($text_item, NULL, $this->tokenData, $this->bubbleableMetadata);
      }
      else {
        $processed_text[$uuid] = $this->factory->processTextString($default_text_item, NULL, $this->tokenData, $this->bubbleableMetadata);
      }
      // Let text be altered by the effect's alter hook.
      $processed_text[$uuid] = $effect_instance->getAlteredText($processed_text[$uuid]);
    }
    $this->text = $processed_text;

    // Set the output image file extension, and find derivative dimensions.
    $runtime_effects = $this->effects;
    foreach ($this->text as $uuid => $text_item) {
      $runtime_effects[$uuid]['data']['text_string'] = $text_item;
    }
    $runtime_style = $this->buildStyleFromEffects($runtime_effects);
    if ($this->sourceImageFile) {
      if ($this->width && $this->height) {
        $dimensions = [
          'width' => $this->width,
          'height' => $this->height,
        ];
      }
      else {
        // @todo (core) we need to take dimensions via image system as they are
        // not available from the file entity, see #1448124.
        $source_image = $this->imageFactory->get($this->sourceImageFile->getFileUri());
        $dimensions = [
          'width' => $source_image->getWidth(),
          'height' => $source_image->getHeight(),
        ];
      }
      $uri = $this->sourceImageFile->getFileUri();
    }
    else {
      $dimensions = [
        'width' => 1,
        'height' => 1,
      ];
      $uri = NULL;
    }
    $runtime_style->transformDimensions($dimensions, $uri);
    $this->set('width', $dimensions['width']);
    $this->set('height', $dimensions['height']);

    // Resolve image file extension.
    if (!$this->extension) {
      if ($this->sourceImageFile) {
        $extension = pathinfo($this->sourceImageFile->getFileUri(), PATHINFO_EXTENSION);
      }
      else {
        $extension = $this->configFactory->get('textimage.settings')->get('default_extension');
      }
      $this->setTargetExtension($runtime_style->getDerivativeExtension($extension));
    }

    // Data for this textimage.
    $this->imageData = [
      'text'                => $this->text,
      'extension'           => $this->extension,
      'sourceImageFileId'   => $this->sourceImageFile ? $this->sourceImageFile->id() : NULL,
      'sourceImageFileUri'  => $this->sourceImageFile ? $this->sourceImageFile->getFileUri() : NULL,
      'gifTransparentColor' => $this->gifTransparentColor,
    ];

    // Remove text from effects outline, as actual runtime text goes
    // separately to the hash.
    foreach ($this->effects as $uuid => &$effect_configuration) {
      if ($effect_configuration['id'] == 'image_effects_text_overlay') {
        unset($effect_configuration['data']['text_string']);
      }
    }

    // Get SHA256 hash, being the Textimage id, for cache checking.
    $hash_input = [
      'effects_outline'     => $this->effects,
      'image_data'          => $this->imageData,
    ];
    $this->id = hash('sha256', serialize($hash_input));

    // Check cache and return if hit.
    if ($this->caching && ($cached_data = $this->getCachedData())) {
      $this->set('uri', $cached_data['uri']);
      $this->processed = TRUE;
      $this->logger->debug('Cached Textimage, @uri', ['@uri' => $this->getUri()]);
      if (is_file($this->getUri())) {
        $this->built = TRUE;
      }
      return $this;
    }
    else {
      // Not found, build the image.
      // Get URI of the to-be image file.
      if (!$this->uri) {
        $this->buildUri();
      }
      $this->processed = TRUE;
      if ($this->caching) {
        $this->setCached();
      }
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function buildImage() {
    // Do not proceed if not processed.
    if (!$this->processed) {
      throw new TextimageException("Attempted to build Textimage before processing data");
    }

    // Do not re-build.
    if ($this->built) {
      return $this;
    }

    // Check file store and return if hit.
    if ($this->caching && is_file($this->getUri())) {
      $this->logger->debug('Stored Textimage, @uri', ['@uri' => $this->getUri()]);
      return $this;
    }

    // If no source image specified, we are processing a pure Textimage
    // request. In that case we create a new 1x1 image to ensure we start
    // with a clean background.
    $source = isset($this->sourceImageFile) ? $this->sourceImageFile->getFileUri() : NULL;
    $image = $this->imageFactory->get($source);
    if (!$source) {
      $image->createNew(1, 1, $this->extension, $this->gifTransparentColor);
    }

    // Reset state.
    $this->factory->setState();
    $this->factory->setState('building_module', 'textimage');

    // Try a lock to the file generation process. If cannot get the lock,
    // return success if the file exists already. Otherwise return failure.
    $lock_name = 'textimage_process:' . Crypt::hashBase64($this->getUri());
    if (!$lock_acquired = $this->lock->acquire($lock_name)) {
      return file_exists($this->getUri()) ? TRUE : FALSE;
    }

    // Inject processed text in the image_effects_text_overlay effects data,
    // and build a runtime-only style.
    $runtime_effects = $this->effects;
    foreach ($this->text as $uuid => $text_item) {
      $runtime_effects[$uuid]['data']['text_string'] = $text_item;
    }
    $runtime_style = $this->buildStyleFromEffects($runtime_effects);

    // Manage change of file extension if needed.
    if ($this->sourceImageFile) {
      $runtime_extension = pathinfo($this->sourceImageFile->getFileUri(), PATHINFO_EXTENSION);
    }
    else {
      $runtime_extension = $this->extension;
    }
    $runtime_extension = $runtime_style->getDerivativeExtension($runtime_extension);
    if ($runtime_extension != $this->extension) {
      // Find the max weight from effects.
      $max_weight = NULL;
      foreach ($runtime_style->getEffects()->getConfiguration() as $effect_configuration) {
        if (!$max_weight || $effect_configuration['weight'] > $max_weight) {
          $max_weight = $effect_configuration['weight'];
        }
      }
      // Add an image_convert effect as last effect.
      $convert = [
        'id' => 'image_convert',
        'weight' => ++$max_weight,
        'data' => [
          'extension' => $this->extension,
        ],
      ];
      $runtime_style->addImageEffect($convert);
    }

    // Generate the image.
    if (!$this->processed = $this->createDerivativeFromImage($runtime_style, $image, $this->getUri())) {
      if (isset($this->style)) {
        throw new TextimageException("Textimage failed to build an image for image style '{$this->style->id()}'");
      }
      else {
        throw new TextimageException("Textimage failed to build an image");
      }
    }
    $this->logger->debug('Built Textimage, @uri', ['@uri' => $this->getUri()]);

    // Release lock.
    if (!empty($lock_acquired)) {
      $this->lock->release($lock_name);
    }

    // Reset state.
    $this->factory->setState();

    $this->built = TRUE;
    return $this;
  }

  /**
   * Builds an image style from an array of effects.
   *
   * The runtime style object does not get saved. It is used to be
   * passed to ImageStyle::createDerivative() to build an image derivative.
   *
   * @param array $effects
   *   An array of image effects.
   *
   * @return \Drupal\image\ImageStyleInterface
   *   An image style object.
   */
  protected function buildStyleFromEffects(array $effects) {
    $style = ImageStyle::create([]);
    foreach ($effects as $effect) {
      $effect_instance = $this->imageEffectManager->createInstance($effect['id']);
      $default_config = $effect_instance->defaultConfiguration();
      $effect['data'] = NestedArray::mergeDeep($default_config, $effect['data']);
      $style->addImageEffect($effect);
    }
    $style->getEffects()->sort();
    return $style;
  }

  /**
   * Creates a full file path from a directory and filename.
   *
   * Copied parts of file_create_filename() to avoid file existence check.
   *
   * @param string $basename
   *   String filename.
   * @param string $directory
   *   String containing the directory or parent URI.
   *
   * @return string
   *   File path consisting of $directory and a unique filename based off
   *   of $basename.
   */
  protected function createFilename($basename, $directory) {
    // Strip control characters (ASCII value < 32). Though these are allowed in
    // some filesystems, not many applications handle them well.
    $basename = preg_replace('/[\x00-\x1F]/u', '_', $basename);
    if (substr(PHP_OS, 0, 3) == 'WIN') {
      // These characters are not allowed in Windows filenames.
      $basename = str_replace([':', '*', '?', '"', '<', '>', '|'], '_', $basename);
    }

    // A URI or path may already have a trailing slash or look like "public://".
    if (substr($directory, -1) == '/') {
      $separator = '';
    }
    else {
      $separator = '/';
    }

    return $directory . $separator . $basename;
  }

  /**
   * Create the derivative image from the Image object.
   *
   * @todo (core) remove if #2359443 gets in
   */
  protected function createDerivativeFromImage($style, $image, $derivative_uri) {
    // Get the folder for the final location of this style.
    $directory = $this->fileSystem->dirname($derivative_uri);

    // Build the destination folder tree if it doesn't already exist.
    if (!file_prepare_directory($directory, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS)) {
      $this->logger->error('Failed to create Textimage directory: %directory', ['%directory' => $directory]);
      return FALSE;
    }

    if (!$image->isValid()) {
      if ($image->getSource()) {
        $this->logger->error("Invalid image at '%image'.", ['%image' => $image->getSource()]);
      }
      else {
        $this->logger->error("Invalid source image.");
      }
      return FALSE;
    }

    foreach ($style->getEffects() as $effect) {
      $effect->applyEffect($image);
    }

    if (!$image->save($derivative_uri)) {
      if (file_exists($derivative_uri)) {
        $this->logger->error('Cached image file %destination already exists. There may be an issue with your rewrite configuration.', ['%destination' => $derivative_uri]);
      }
      return FALSE;
    }

    return TRUE;
  }

  // @codingStandardsIgnoreStart
  /**
   * Set URI to image file.
   *
   * An appropriate directory structure is in place to support styled,
   * unstyled and uncached (temporary) image files:
   *
   * for images with a supporting image style (styled) -
   *   {style_wrapper}://textimage_store/cache/styles/{style}/{substr(file name, 1)}/{substr(file name, 2)}/{file name}.{extension}
   *
   * for images generated via direct theme (unstyled) -
   *   {default_wrapper}://textimage_store/cache/api/{substr(file name, 1)}/{substr(file name, 2)}/{file name}.{extension}
   *
   * for uncached, temporary -
   *   {default_wrapper}://textimage_store/temp/{file name}.{extension}
   *
   * @return $this
   */
  protected function buildUri() {
  // @codingStandardsIgnoreEnd
    // The file name will be the Textimage hash.
    if ($this->caching) {
      $base_name = $this->id . '.' . $this->extension;
      if ($this->style) {
        $scheme = $this->style->getThirdPartySetting('textimage', 'uri_scheme', $this->configFactory->get('system.file')->get('default_scheme'));
        $this->set('uri', $this->factory->getStoreUri('/cache/styles/', $scheme) . $this->style->id() . '/' . substr($base_name, 0, 1) . '/' . substr($base_name, 0, 2) . '/' . $base_name);
      }
      else {
        $this->set('uri', $this->factory->getStoreUri('/cache/api/') . substr($base_name, 0, 1) . '/' . substr($base_name, 0, 2) . '/' . $base_name);
      }
    }
    else {
      $base_name = hash('sha256', session_id() . microtime()) . '.' . $this->extension;
      $this->set('uri', $this->factory->getStoreUri('/temp/') . $base_name);
    }
    return $this;
  }

  /**
   * Get cached Textimage data.
   *
   * @return bool
   *   TRUE if an existing image file can be used, FALSE if no hit
   */
  protected function getCachedData() {
    if ($cached = $this->cache->get('tiid:' . $this->id)) {
      return $cached->data;
    }
    return FALSE;
  }

  /**
   * Cache Textimage data.
   *
   * @return $this
   */
  protected function setCached() {
    $data = [
      'imageData' => $this->imageData,
      'uri' => $this->getUri(),
      'width' => $this->getWidth(),
      'height' => $this->getHeight(),
      'effects' => $this->effects,
      'bubbleableMetadata' => $this->getBubbleableMetadata(),
    ];
    $this->cache->set('tiid:' . $this->id, $data, Cache::PERMANENT, $this->getBubbleableMetadata()->getCacheTags());
    return $this;
  }

}
