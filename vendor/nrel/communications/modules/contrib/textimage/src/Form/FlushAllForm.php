<?php

namespace Drupal\textimage\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\textimage\TextimageFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates a form to confirm flushing of all Textimage images.
 */
class FlushAllForm extends ConfirmFormBase {

  /**
   * The Textimage factory.
   *
   * @var \Drupal\textimage\TextimageFactory
   */
  protected $textimageFactory;

  /**
   * Constructs a FlushAllForm object.
   *
   * @param \Drupal\textimage\TextimageFactory $textimage_factory
   *   The Textimage factory.
   */
  public function __construct(TextimageFactory $textimage_factory) {
    $this->textimageFactory = $textimage_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('textimage.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'textimage_flush_all_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Cleanup Textimage?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This will remove all image files generated via Textimage, flush all the image styles, and clear the Textimage cache.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Proceed');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('textimage.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->textimageFactory->flushAll();
    $form_state->setRedirect('textimage.settings');
  }

}
