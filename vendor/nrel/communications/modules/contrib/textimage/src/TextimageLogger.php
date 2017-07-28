<?php

namespace Drupal\textimage;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Psr\Log\LoggerInterface;

/**
 * Defines a Textimage logger.
 */
class TextimageLogger extends LoggerChannel {
  use StringTranslationTrait;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Textimage logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $loggerChannel;

  /**
   * Constructs a TextimageLogger object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Psr\Log\LoggerInterface $logger_channel
   *   The Textimage logger channel.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerInterface $logger_channel, AccountInterface $current_user) {
    $this->configFactory = $config_factory;
    $this->loggerChannel = $logger_channel;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) {
    // Convert to integer equivalent for consistency with RFC 5424.
    $level_code = is_string($level) ? $this->levelTranslation[$level] : $level;

    // Process debug entries only if required.
    if ($level_code == RfcLogLevel::DEBUG && !$this->configFactory->get('textimage.settings')->get('debug')) {
      return NULL;
    }

    // Logs through the logger channel.
    $this->loggerChannel->log($level_code, $message, $context);

    // Display the message to qualified users.
    if ($this->currentUser->hasPermission('administer site configuration') ||
        $this->currentUser->hasPermission('administer image styles')) {
      switch ($level_code) {
        case RfcLogLevel::DEBUG:
        case RfcLogLevel::INFO:
        case RfcLogLevel::NOTICE:
          $type = 'status';
          break;

        case RfcLogLevel::WARNING:
          $type = 'warning';
          break;

        default:
          $type = 'error';
      }
      // @todo replace call to $this->t
      // @codingStandardsIgnoreLine
      drupal_set_message($this->t($message, $context), $type, FALSE);
    }
  }

}
