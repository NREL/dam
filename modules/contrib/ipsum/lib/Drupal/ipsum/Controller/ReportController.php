<?php

/**
 * @file
 * Contains \Drupal\ipsum\Controller\ReportController.
 */

namespace Drupal\ipsum\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\ipsum\Plugin\Type\IpsumPluginManager;
use Drupal\Core\Config\ConfigFactory;

/**
 * Ipsum report page controller.
 */
class ReportController extends ControllerBase {

  /**
   * The ipsum plugin manager.
   *
   * @var \Drupal\ipsum\Plugin\Type\IpsumPluginManager
   */
  protected $ipsumManager;

  /**
   * Constructs a ReportController object.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   A module handler.
   * @param \Drupal\ipsum\Plugin\Type\IpsumPluginManager $ipsum_manager
   *   The ipsum plugin manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation_manager
   *   The translation manager service.
   */
  public function __construct(ConfigFactory $config_factory, ModuleHandlerInterface $module_handler, IpsumPluginManager $ipsum_manager, TranslationInterface $translation_manager) {
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->ipsumManager = $ipsum_manager;
    $this->translationManager = $translation_manager;
  }


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('plugin.manager.ipsum'),
      $container->get('string_translation')
    );
  }

  /**
   * Translates a string to the current language or to a given language.
   *
   * See the t() documentation for details.
   */
  protected function t($string, array $args = array(), array $options = array()) {
    return $this->translationManager->translate($string, $args, $options);
  }

  /**
   * Generates an example page.
   */
  public function content() {
    $rows = array();

    $header = array(
      array(
        'data' => $this->t('Name'),
        'class' => array(RESPONSIVE_PRIORITY_MEDIUM),
      ),
      array(
        'data' => $this->t('Description'),
        'class' => array(RESPONSIVE_PRIORITY_MEDIUM),
      ),
    );

    // Find default provider.
    $config = $this->configFactory->get('ipsum.settings');

    // Build rows.
    foreach ($this->ipsumManager->getDefinitions() as $plugin) {
      $label = $plugin['label'];

      if ($plugin['id'] === $config->get('default_provider')) {
        $label .= ' (' . $this->t('default') . ')';
      }

      $rows[] = array(
        'data' => array(
          $label,
          $plugin['description'],
        ),
      );
    }

    $build = array(
      'provider_table' => array(
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#attributes' => array('id' => 'admin-ipsum', 'class' => array('admin-ipsum')),
        '#empty' => $this->t('No providers available.'),
      ),
    );

    return $build;
  }
}
