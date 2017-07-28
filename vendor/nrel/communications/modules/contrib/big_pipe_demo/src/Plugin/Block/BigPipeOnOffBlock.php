<?php

namespace Drupal\big_pipe_demo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a BigPipe on/off block.
 *
 * @Block(
 *   id = "big_pipe_onoff_block",
 *   admin_label = @Translation("BigPipe on/off block"),
 * )
 */
class BigPipeOnOffBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a new BigPipeOnOffBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.query_args:big_pipe']);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    if ($this->requestStack->getCurrentRequest()->query->get('big_pipe') === 'off') {
      $build['#markup'] = Markup::create('<strong style="color:red;background-color:white;font-size:16px">' . $this->t('BigPipe is currently OFF. To turn on again, remove <tt>big_pipe=off</tt> from the URL.') . '</strong>');
    }
    else {
      $build['#markup'] = Markup::create('<strong style="color:green;background-color:white;font-size:16px">' . $this->t('BigPipe is currently ON. To turn off, add <tt>?big_pipe=off</tt> to the URL.') . '</strong>');
    }
    return $build;
  }

}
