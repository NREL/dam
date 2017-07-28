<?php

namespace Drupal\textimage\Tests;

use Drupal\image\Entity\ImageStyle;
use Drupal\simpletest\WebTestBase;

/**
 * Base test class for Textimage tests.
 */
abstract class TextimageTestBase extends WebTestBase {

  protected $textimageAdmin = 'admin/config/media/textimage';
  protected $textimageFactory;
  protected $renderer;

  public static $modules = ['textimage', 'node', 'image_effects'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->textimageFactory = $this->container->get('textimage.factory');
    $this->renderer = $this->container->get('renderer');

    // Create Basic page and Article node types.
    if ($this->profile != 'standard') {
      $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);
      $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    }

    // Create a user and log it in.
    $this->adminUser = $this->drupalCreateUser([
      'access content',
      'create article content',
      'edit any article content',
      'delete any article content',
      'administer site configuration',
      'administer image styles',
      'generate textimage url derivatives',
    ]);
    $this->drupalLogin($this->adminUser);

    // Change Image Effects settings.
    $config = \Drupal::configFactory()->getEditable('image_effects.settings');
    $config
      ->set('image_selector.plugin_id', 'dropdown')
      ->set('image_selector.plugin_settings.dropdown.path', drupal_get_path('module', 'image_effects') . '/tests/images')
      ->set('font_selector.plugin_id', 'dropdown')
      ->set('font_selector.plugin_settings.dropdown.path', drupal_get_path('module', 'image_effects') . '/tests/fonts/LinLibertineTTF_5.3.0_2012_07_02')
      ->save();

    // Change Textimage settings.
    $config = \Drupal::configFactory()->getEditable('textimage.settings');
    $config
      ->set('url_generation.enabled', TRUE)
      ->set('debug', TRUE)
      ->save();

    // Set default font.
    $this->drupalGet($this->textimageAdmin);
    $edit = [
      'settings[main][default_font_uri]' => 'LinLibertine_Rah.ttf',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save configuration'));

    // Create a test image style.
    $style_name = 'textimage_test';
    $style_label = 'Textimage Test';
    $style_path = 'admin/config/media/image-styles/manage/' . $style_name;
    $edit = [
      'name' => $style_name,
      'label' => $style_label,
    ];
    $this->drupalPostForm('admin/config/media/image-styles/add', $edit, t('Create new style'));
    $this->assertRaw(t('Style %name was created.', ['%name' => $style_label]));

    // Create a test image_effects_text_overlay effect.
    $effect_edits = [
      'image_effects_text_overlay' => [
        'data[text_default][text_string]' => 'Test preview',
      ],
    ];
    foreach ($effect_edits as $effect => $edit) {
      // Add the effect.
      $this->drupalPostForm($style_path, ['new' => $effect], t('Add'));
      if (!empty($edit)) {
        $this->drupalPostForm(NULL, $edit, t('Add effect'));
      }
    }
  }

  /**
   * Asserts a Textimage.
   */
  protected function assertTextimage($path, $width, $height) {
    $image = \Drupal::service('image.factory')->get($path);
    $w_error = abs($image->getWidth() - $width);
    $h_error = abs($image->getHeight() - $height);
    $tolerance = 0.1;
    $this->assertTrue($w_error < $width * $tolerance && $h_error < $height * $tolerance, "Textimage {$path} width and height ({$image->getWidth()}x{$image->getHeight()}) approximate expected results ({$width}x{$height})");
  }

  /**
   * Returns the URI of a Textimage based on style name and text.
   */
  protected function getTextimageUriFromStyleAndText($style_name, $text) {
    return $this->textimageFactory->get()
      ->setStyle(ImageStyle::load($style_name))
      ->process($text)
      ->getUri();
  }

}
