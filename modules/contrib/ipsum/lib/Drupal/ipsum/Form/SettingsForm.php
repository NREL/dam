<?php
/**
 * @file
 * Contains \Drupal\ipsum\Form\SettingsForm.
 */

namespace Drupal\ipsum\Form;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\ipsum\Plugin\Type\IpsumPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure statistics settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The ipsum plugin manager.
   *
   * @var \Drupal\ipsum\Plugin\Type\IpsumPluginManager
   */
  protected $ipsumManager;

  /**
   * Constructs a \Drupal\user\StatisticsSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\ipsum\Plugin\Type\IpsumPluginManager
   *   The ipsum plugin manager.
   */
  public function __construct(ConfigFactory $config_factory, ModuleHandlerInterface $module_handler, IpsumPluginManager $ipsum_manager) {
    parent::__construct($config_factory);

    $this->moduleHandler = $module_handler;
    $this->ipsumManager = $ipsum_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('plugin.manager.ipsum')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ipsum_settings_form';
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, array &$form_state) {
    $config = $this->configFactory->get('ipsum.settings');

    // Ipsum provider settings.
    $form['provider'] = array(
      '#type' => 'details',
      '#title' => t('Providers'),
      '#open' => TRUE,
    );

    // Build available provider options.
    $options = array();

    foreach ($this->ipsumManager->getDefinitions() as $definition) {
      $options[$definition['id']] = $definition['label'];
    }

    $form['provider']['default_provider'] = array(
      '#type' => 'select',
      '#title' => t('Default provider'),
      '#options' => $options,
      '#default_value' => $config->get('default_provider'),
      '#description' => t('Select the default lorem ipsum provider.'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, array &$form_state) {
    $this->configFactory->get('ipsum.settings')
      ->set('default_provider', $form_state['values']['default_provider'])
      ->save();

    parent::submitForm($form, $form_state);
  }
}
