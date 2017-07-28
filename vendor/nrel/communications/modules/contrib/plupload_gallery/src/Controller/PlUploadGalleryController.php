<?php

/**
 * @file
 * Contains \Drupal\plupload_gallery\Controller\PlUploadGalleryController.
 */

namespace Drupal\plupload_gallery\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Drupal\plupload\UploadController;
use Drupal\node\Entity\EntityInterface;
use Drupal\Core\Entity;
use Drupal\node\Entity\Node;

/**
 * Plupload upload handling route.
 */
class PlUploadGalleryController extends UploadController implements ContainerInjectionInterface {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request object.
   */
  protected $request;

  /**
   * Stores temporary folder URI.
   *
   * This is configurable via the configuration variable. It was added for HA
   * environments where temporary location may need to be a shared across all
   * servers.
   *
   * @var string
   */
  protected $temporaryUploadLocation;

  /**
   * Filename of a file that is being uploaded.
   *
   * @var string
   */
  protected $filename;

  protected $upload_type;

  protected $field_name;

  protected $entity_type;

  protected $entity_id;

  protected $referenced_entity_type;

  protected $referenced_entity_bundle;

  protected $referenced_entity_field;

  protected $referenced_other_fields;

  protected $referenced_other_fields_values;

  protected $response;

  /**
   * Constructs plupload upload controller route controller.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   */
  public function __construct(Request $request) {
    $this->request = $request;
    $this->field_name = $request->query->get('field_name');
    $this->entity_type = $request->query->get('entity_type');
    $this->entity_id = $request->query->get('entity_id');
    $this->referenced_entity_type = $request->query->get('referenced_entity_type');
    $this->referenced_entity_bundle = $request->query->get('referenced_entity_bundle');
    $this->referenced_entity_field = $request->query->get('referenced_entity_field');
    $this->referenced_other_fields = explode(',', $request->query->get('referenced_other_fields'));
    $this->referenced_other_fields_values = unserialize($request->query->get('referenced_other_fields_values'));
    $this->response = new JsonResponse(
      array(
        'jsonrpc' => '2.0',
        'result' => 'finished',
        'id' => 'id',
      ),
      200
    );

  }

  /**
   * {@inheritdoc}
   */
//  public static function create(ContainerInterface $container) {
//    return parent::create($container->get('request_stack')->getCurrentRequest());
//  }

  public function handleImageUploads() {
    $this->upload_type = 'file';
    $this->handleUploads();
    return $this->response;
  }

  public function handleEntityUploads() {
    $this->upload_type = 'entity';
    $this->handleUploads();
    return $this->response;
  }

  /**
   * Handles Plupload uploads. Must conform to UploadController::handleUploads().
   */
  public function handleUploads() {
    // @todo: Implement file_validate_size();
//    \Drupal::logger('plupload_gallery')->notice('handle upload <pre>' . print_r($this->request,1));
    try {
      //$this->prepareTemporaryUploadDestination();
      $this->handleUpload();
    }
    catch (UploadException $e) {
      return $e->getErrorResponse();
    }

  }

  public function showWidget() {
    return array(
      '#markup' => 'widget'
    );
  }

  /**
   * Reads, checks and return filename of a file being uploaded.
   *
   * @throws \Drupal\plupload\UploadException
   */
  protected function getFilename() {
    return parent::getFilename();

  }

  /**
   * Handles multipart uploads.
   *
   * @throws \Drupal\plupload\UploadException
   */
  protected function handleUpload() {
    /* @var $multipart_file \Symfony\Component\HttpFoundation\File\UploadedFile */
    $is_multipart = strpos($this->request->headers->get('Content-Type'), 'multipart') !== FALSE;

    // If this is a multipart upload there needs to be a file on the server.
    if ($is_multipart) {
      $multipart_file = $this->request->files->get('file', array());
      // TODO: Not sure if this is the best check now.
      // Originally it was:
      // if (empty($multipart_file['tmp_name']) || !is_uploaded_file($multipart_file['tmp_name'])) {
      if (!$multipart_file->getPathname() || !is_uploaded_file($multipart_file->getPathname())) {
        throw new UploadException(UploadException::MOVE_ERROR);
      }
    }
    $file = $this->request->files->get('file', array());
    // Get node nid from url
    \Drupal::logger('plupload_gallery')->notice('file <pre>' . print_r($file,1));

    $entity_id = FALSE;
    if ($this->entity_type != '' && is_numeric($this->entity_id)) {
      $entity_id = $this->entity_id;
    }
    //\Drupal::logger('plupload_gallery')->notice(' entity id <pre>' . print_r($entity_id,1));
    if ($entity_id) {
      //\Drupal::logger('plupload_gallery')->notice('field name <pre>' . print_r($this->field_name,1));
      $entity = Node::load($entity_id);
      //\Drupal::logger('plupload_gallery')->notice('entity <pre>' . print_r($entity,1));
      $field_name = $this->field_name;
      if ($field_name) {

        // Add these files

        switch ($this->upload_type) {
          case 'file':
            $this->uploadFileToField($entity, $field_name, $file);
            break;
          case 'entity':
            $this->uploadFileToEntity($entity, $field_name, $file);
            break;
        }
      }

    }

  }

  /**
   * @param $entity
   * @param $field_name
   * @param $file
   */
  protected function uploadFileToField($entity, $field_name, $file) {
    $values = $entity->{$field_name}->getValue();
//        \Drupal::logger('plupload_gallery')->notice('values <pre>' . print_r($values,1));
    // Prepare for file_save_upload which requires this
    \Drupal::request()->files->set('files', array($field_name => $file));
    // Need to find location of fields files here
    $files = file_save_upload($field_name, array(), 'public://');
    // \Drupal::logger('plupload_gallery')->notice('files <pre>' . print_r($files,1));

    $values[] = array_shift($files);
    $entity->set($field_name, $values);
    $entity->save();

  }

  /**
   * @param $entity
   * @param $field_name
   * @param $file
   */
  protected function uploadFileToEntity($entity, $field_name, $file) {
    // Prepare for file_save_upload which requires this
//    \Drupal::logger('plupload_gallery')->notice('file <pre>' . print_r($file,1));
    \Drupal::request()->files->set('files', array($field_name => $file));
    // Need to find location of fields files here
    $files = file_save_upload($field_name, array(), 'public://');
    $file = array_shift($files);
    \Drupal::logger('plupload_gallery')->notice('file name <pre>' . print_r($file->getFilename(),1));
    // First we create a new entity of the target type
    $values = [];
    foreach ($this->referenced_other_fields as $name) {
      $values[$name] = $this->referenced_other_fields_values[$name];
    }
    $values += [
      'type' => $this->referenced_entity_bundle,
      'title' => $file->getFilename(),
      $this->referenced_entity_field => $file,
      'uid' => ($entity instanceof EntityOwnerInterface) ? $entity->getOwnerId() : \Drupal::currentUser()->id(),
      'status' => 1,
    ];

    $target_entity = entity_create($this->referenced_entity_type, $values);
    $target_entity->save();
    \Drupal::logger('plupload_gallery')->notice('target entity id <pre>' . print_r($target_entity->id(),1));

    $values = $entity->{$field_name}->getValue();
//    // The new entity is added as an entity reference field to the entity
////        \Drupal::logger('plupload_gallery')->notice('values <pre>' . print_r($values,1));
//    // \Drupal::logger('plupload_gallery')->notice('files <pre>' . print_r($files,1));
//
    $values[] = ['target_id' => $target_entity->id()];
    $entity->set($field_name, $values);
    $entity->save();

  }

}
