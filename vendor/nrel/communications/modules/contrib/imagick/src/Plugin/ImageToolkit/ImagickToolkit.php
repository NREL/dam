<?php

namespace Drupal\imagick\Plugin\ImageToolkit;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\imagick\ImagickException;
use Drupal\system\Plugin\ImageToolkit\GDToolkit;
use Imagick;

/**
 * Defines the Imagick toolkit for image manipulation within Drupal.
 *
 * @ImageToolkit(
 *   id = "imagick",
 *   title = @Translation("Imagick image manipulation toolkit")
 * )
 */
class ImagickToolkit extends GDToolkit {

  /**
   * Destructs a Imagick object.
   */
  public function __destruct() {
    if (is_object($this->resource)) {
      $this->resource->clear();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function load() {
    // Return immediately if the image file is not valid.
    if (!$this->isValid()) {
      return FALSE;
    }

    try {
      $source = $this->getSource();
      $path = \Drupal::service('file_system')->realpath($source);

      $resource = new Imagick($path);
      $this->setResource($resource);

      return TRUE;
    } catch (ImagickException $e) {
      return FALSE;
    }
  }

  /**
   * Sets the Imagick image resource.
   *
   * @param Imagick $resource
   *   The Imagick image resource.
   *
   * @return $this
   */
  public function setResource($resource) {
    $this->preLoadInfo = NULL;
    $this->resource = $resource;

    return $this;
  }


  /** Retrieves the Imagick image resource.
   *
   * @return resource|null
   *   The Imagick image resource, or NULL if not available.
   */
  public function getResource() {
    if (!is_object($this->resource)) {
      $this->load();
    }

    return $this->resource;
  }

  /**
   * {@inheritdoc}
   */
  public function save($destination) {
    /* @var $resource \Imagick */
    $resource = $this->getResource();

    $scheme = file_uri_scheme($destination);
    // Work around lack of stream wrapper support in imagejpeg() and imagepng().
    if ($scheme && \Drupal::service('file_system')->validScheme($scheme)) {
      // If destination is not local, save image to temporary local file.
      $local_wrappers = \Drupal::service('stream_wrapper_manager')
        ->getWrappers(StreamWrapperInterface::LOCAL);
      if (!isset($local_wrappers[$scheme])) {
        $permanent_destination = $destination;
        $destination = \Drupal::service('file_system')
          ->tempnam('temporary://', 'imagick_');
      }
      // Convert stream wrapper URI to normal path.
      $destination = \Drupal::service('file_system')->realpath($destination);
    }

    // If preferred format is set, use it as prefix for writeImage
    // If not this will throw a ImagickException exception
    try {
      $image_format = strtolower($resource->getImageFormat());
      $destination = implode(':', array($image_format, $destination));
    } catch (ImagickException $e) {}

    // Only compress JPEG files because other filetypes will increase in filesize
    if (isset($image_format) && in_array($image_format, array('jpeg', 'jpg'))) {
      // Get image quality from effect or global setting
      $quality = $resource->getImageProperty('quality') ?: $this->configFactory->get('imagick.config')->get('jpeg_quality');
      // Set image compression quality
      $resource->setImageCompressionQuality($quality);
    }

    // Write image to destination
    if (isset($image_format) && in_array($image_format, array('gif'))) {
      if (!$resource->writeImages($destination, TRUE)) {
        return FALSE;
      }
    }
    else {
      if (!$resource->writeImage($destination)) {
        return FALSE;
      }
    }

    // Move temporary local file to remote destination.
    if (isset($permanent_destination)) {
      return (bool) file_unmanaged_move($destination, $permanent_destination, FILE_EXISTS_REPLACE);
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidth() {
    if ($this->preLoadInfo) {
      return $this->preLoadInfo[0];
    }
    elseif ($resource = $this->getResource()) {
      $data = $resource->getImageGeometry();

      return $data['width'];
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getHeight() {
    if ($this->preLoadInfo) {
      return $this->preLoadInfo[1];
    }
    elseif ($resource = $this->getResource()) {
      $data = $resource->getImageGeometry();

      return $data['height'];
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['image_jpeg_quality'] = array(
      '#type' => 'number',
      '#title' => $this->t('JPEG quality'),
      '#description' => $this->t('Define the image quality for JPEG manipulations. Ranges from 0 to 100. Higher values mean better image quality but bigger files.'),
      '#min' => 0,
      '#max' => 100,
      '#default_value' => $this->configFactory->get('imagick.config')
        ->get('jpeg_quality'),
      '#field_suffix' => $this->t('%'),
    );

    $form['image_resize_filter'] = array(
      '#type' => 'select',
      '#title' => t('Imagic resize filter'),
      '#description' => t('Define the resize filter for image manipulations. If you\'re not sure what you should enter here, leave the default settings.'),
      '#options' => array(
        -1 => t('- None -'),
        imagick::FILTER_UNDEFINED => 'FILTER_UNDEFINED',
        imagick::FILTER_POINT => 'FILTER_POINT',
        imagick::FILTER_BOX => 'FILTER_BOX',
        imagick::FILTER_TRIANGLE => 'FILTER_TRIANGLE',
        imagick::FILTER_HERMITE => 'FILTER_HERMITE',
        imagick::FILTER_HANNING => 'FILTER_HANNING',
        imagick::FILTER_HAMMING => 'FILTER_HAMMING',
        imagick::FILTER_BLACKMAN => 'FILTER_BLACKMAN',
        imagick::FILTER_GAUSSIAN => 'FILTER_GAUSSIAN',
        imagick::FILTER_QUADRATIC => 'FILTER_QUADRATIC',
        imagick::FILTER_CUBIC => 'FILTER_CUBIC',
        imagick::FILTER_CATROM => 'FILTER_CATROM',
        imagick::FILTER_MITCHELL => 'FILTER_MITCHELL',
        imagick::FILTER_LANCZOS => 'FILTER_LANCZOS',
        imagick::FILTER_BESSEL => 'FILTER_BESSEL',
        imagick::FILTER_SINC => 'FILTER_SINC',
      ),
      '#default_value' => $this->configFactory->get('imagick.config')
        ->get('resize_filter'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();

    // Check if the quality value has changed
    $jpeg_quality = $this->configFactory->get('imagick.config')
      ->get('jpeg_quality');
    if ($jpeg_quality !== $form_state->getValue(['imagick', 'image_jpeg_quality'])) {
      // Flush image style images
      $styles = ImageStyle::loadMultiple();
      /** @var ImageStyle $style */
      foreach ($styles as $style) {
        $style->flush();
      }
    }

    $this->configFactory->getEditable('imagick.config')
      ->set('jpeg_quality', $form_state->getValue(['imagick', 'image_jpeg_quality']))
      ->set('resize_filter', $form_state->getValue(['imagick', 'image_resize_filter']))
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  public static function isAvailable() {
    return _imagick_is_available();
  }

}
