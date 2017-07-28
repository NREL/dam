<?php

namespace Drupal\big_pipe_demo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a BigPipe anonymous session block.
 *
 * @Block(
 *   id = "big_pipe_anon_session_block",
 *   admin_label = @Translation("BigPipe anonymous session block"),
 * )
 */
class AnonSessionBlock extends BlockBase implements ContainerFactoryPluginInterface, FormInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The session manager.
   *
   * @var \Drupal\Core\Session\SessionManagerInterface
   */
  protected $sessionManager;

  /**
   * Constructs a new AnonSessionBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   *   The session manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder, SessionManagerInterface $session_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
    $this->sessionManager = $session_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
      $container->get('session_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['description']['#markup'] = '<p>' . $this->t('BigPipe only works for users with a session. All authenticated users have a session. For anonymous users, you can explicitly start and stop a session in this block.') . '</p>';
    if (\Drupal::currentUser()->isAnonymous()) {
      $build['form'] = $this->formBuilder->getForm($this);
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return [
      'session.exists',
      'user.roles:anonymous',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'big_pipe_anon_session_block';
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['label_display' => FALSE];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $session_started = $this->sessionManager->isStarted();

    $form['start'] = [
      '#type' => 'submit',
      '#access' => !$session_started,
      '#value' => $this->t('Start session'),
      '#submit' => ['::start'],
    ];
    $form['stop'] = [
      '#type' => 'submit',
      '#access' => $session_started,
      '#value' => $this->t('Stop session'),
      '#submit' => ['::stop'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Starts session.
   */
  public function start(array &$form, FormStateInterface $form_state) {
    $_SESSION['big_pipe_demo'] = TRUE;
  }

  /**
   * Stops session.
   */
  public function stop(array &$form, FormStateInterface $form_state) {
    $this->sessionManager->destroy();
  }

}
