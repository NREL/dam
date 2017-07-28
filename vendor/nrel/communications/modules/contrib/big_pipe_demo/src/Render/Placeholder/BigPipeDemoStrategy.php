<?php

namespace Drupal\big_pipe_demo\Render\Placeholder;

use Drupal\big_pipe\Render\Placeholder\BigPipeStrategy;
use Drupal\Core\Render\Placeholder\PlaceholderStrategyInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines the BigPipe demo placeholder strategy.
 *
 * Allows opting out from BigPipe by specifying the 'big_pipe=off" query arg.
 *
 * @see \Drupal\big_pipe\Render\Placeholder\BigPipeStrategy
 */
class BigPipeDemoStrategy extends BigPipeStrategy {

  /**
   * The decorated BigPipe placeholder strategy.
   *
   * @var \Drupal\Core\Render\Placeholder\PlaceholderStrategyInterface
   */
  protected $bigPipeStrategy;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new BigPipeDemoStrategy class.
   *
   * @param \Drupal\Core\Render\Placeholder\PlaceholderStrategyInterface $big_pipe_strategy
   *   The decorated BigPipe placeholder strategy.
   * @param \Drupal\Core\Session\SessionConfigurationInterface $session_configuration
   *   The session configuration.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(PlaceholderStrategyInterface $big_pipe_strategy, $session_configuration, RequestStack $request_stack, RouteMatchInterface $route_match) {
    $this->bigPipeStrategy= $big_pipe_strategy;
    parent::__construct($session_configuration, $request_stack, $route_match);

    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public function processPlaceholders(array $placeholders) {
    if ($this->requestStack->getCurrentRequest()->query->get('big_pipe') == 'off') {
      return [];
    }

    return $this->bigPipeStrategy->processPlaceholders($placeholders);
  }

}
