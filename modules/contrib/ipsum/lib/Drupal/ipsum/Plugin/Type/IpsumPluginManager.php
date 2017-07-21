<?php

/**
 * @file
 * Definition of Drupal\ipsum\Plugin\Type\IpsumPluginManager.
 */

namespace Drupal\ipsum\Plugin\Type;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages discovery and instantiation of ipsum provider plugins.
 */
class IpsumPluginManager extends DefaultPluginManager {

  /**
   * Constructs a new \Drupal\ipsum\Plugin\Type\IpsumPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, LanguageManagerInterface $language_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ipsum/provider', $namespaces, $module_handler, 'Drupal\ipsum\Annotation\IpsumProvider');

    $this->setCacheBackend($cache_backend, $language_manager, 'ipsum_plugins');
    $this->alterInfo('ipsum_provider');
  }
}
