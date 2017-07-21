<?php

/**
 * @file
 * Definition of Drupal\ipsum\Plugin\ipsum\provider\DrupalIpsum.
 */

namespace Drupal\ipsum\Plugin\ipsum\provider;

use Drupal\ipsum\Plugin\ProviderBase;

/**
 * Provides a plugin to generate Drupal-flavored ipsum.
 *
 * @IpsumProvider(
 *   id = "drupal",
 *   label = @Translation("Drupal Ipsum"),
 *   description = @Translation("Drupal-flavored lorem ipsum text"),
 *   settings = {
 *     "sentence_words_min" = 6,
 *     "sentence_words_max" = 20,
 *     "paragraph_sentences_min" = 2,
 *     "paragraph_sentences_max" = 6
 *   }
 * )
 */
class DrupalIpsum extends ProviderBase {

  /**
   * {@inheritdoc}
   */
  public function getVocabulary() {
    return array(
      'drupal',
      'hook',
      'module',
      'theme',
      'alter',
      'node',
      'entity',
      'comment',
      'user',
      'taxonomy',
      'term',
      'vocabulary',
      'content',
      'permission',
      'hack',
      'core',
      'contrib',
      'template',
      'menu',
      'profile',
      'behaviors',
      'ahah',
      'ajax',
      'javascript',
      'css',
      'html',
      'markup',
      'form',
      'api',
      'FAPI',
      'install',
      'uninstall',
      'enable',
      'disable',
      'block',
      'help',
      'documentation',
      'RTFM',
      'article',
      'webform',
      'captcha',
      'views',
      'panels',
      'context',
      'ctools',
      'zen',
      'cck',
      'features',
      'cron',
      'locale',
      'i18n',
      'community',
      'server',
      'git',
      'commit',
      'push',
      'pull',
      'diff',
      'issue',
      'project',
      'role',
      'field',
      'cache',
      'session',
      'semaphore',
      'bug',
      'major',
      'minor',
      'critical',
      'meta',
      'profile',
      'color',
      'filter',
      'backup',
      'migrate',
      'actions',
      'rules',
      'plugin',
      'book',
      'contact',
      'dashboard',
      'workbench',
      'trigger',
      'simpletest',
      'devel',
      'generate',
      'flag',
      'bundle',
      'token',
      'variable',
      'wysiwyg',
      'jquery',
      'html5',
      'css3',
      'scheduler',
      'date',
      'calendar',
      'overlay',
      'revision',
      'access',
      'registry',
      'router',
      'link',
      'alias',
      'database',
      'query',
      'responsive',
      'mobile',
      'xhtml',
    );
  }

}
