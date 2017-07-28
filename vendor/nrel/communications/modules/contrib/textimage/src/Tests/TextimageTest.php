<?php

namespace Drupal\textimage\Tests;

use Drupal\image\Entity\ImageStyle;

/**
 * Basic functionality of the Textimage module.
 *
 * @group Textimage
 */
class TextimageTest extends TextimageTestBase {

  /**
   * Test functionality of the module.
   */
  public function testTextimage() {

    $public_directory_path = \Drupal::service('stream_wrapper_manager')->getViaScheme('public')->getDirectoryPath();
    $private_directory_path = \Drupal::service('stream_wrapper_manager')->getViaScheme('private')->getDirectoryPath();

    // Generate a few derivatives and render images via theme
    // 'textimage_formatter'.
    $input = [
      [
        'text' => ['preview text image'],
        'width' => 171,
        'height' => 24,
      ],
      [
        'text' => ['Предварительный просмотр текста'],
        'width' => 335,
        'height' => 24,
      ],
      [
        'text' => ['προεπισκόπηση της εικόνας κείμενο'],
        'width' => 325,
        'height' => 24,
      ],
      [
        'text' => ['Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'],
        'width' => 1104,
        'height' => 24,
      ],
    ];

    // Generate files on public.
    foreach ($input as $item) {
      $textimage = $this->textimageFactory->get()
        ->setStyle(ImageStyle::load('textimage_test'))
        ->process($item['text']);
      $element = [
        '#theme' => 'textimage_formatter',
        '#uri' => $textimage->getUri(),
        '#width' => $textimage->getWidth(),
        '#height' => $textimage->getHeight(),
      ];
      $textimage->getBubbleableMetadata()->applyTo($element);
      $this->renderer->renderRoot($element);
      $this->assertFalse(file_exists($textimage->getUri()));
      $this->drupalGet($textimage->getUrl());
      $this->assertTrue(file_exists($textimage->getUri()));
      $this->assertTextimage($textimage->getUri(), $item['width'], $item['height']);
    }

    // Check that files were generated on public.
    $files_count = count(file_scan_directory($public_directory_path . '/textimage_store/cache/styles/textimage_test', '/.*/'));
    $this->assertEqual(4, $files_count);

    // Check that cache entries were generated.
    foreach ($input as $item) {
      $textimage = $this->textimageFactory->get()
        ->setStyle(ImageStyle::load('textimage_test'))
        ->process($item['text']);
      $cached = $this->container->get('cache.textimage')->get('tiid:' . $textimage->id());
      $this->assertEqual($textimage->getUri(), $cached->data['uri']);
    }

    // Delete cache, files are still there upon re-processing, before
    // ::buildImage.
    $this->container->get('cache.textimage')->deleteAll();
    foreach ($input as $item) {
      $textimage = $this->textimageFactory->get()
        ->setStyle(ImageStyle::load('textimage_test'))
        ->process($item['text']);
      $this->assertTrue(file_exists($textimage->getUri()));
    }

    // Set image storage to 'private' wrapper.
    $edit = [
      'textimage_options[uri_scheme]' => 'private',
    ];
    $this->drupalPostForm('admin/config/media/image-styles/manage/textimage_test', $edit, t('Update style'));

    // Generate files on private.
    foreach ($input as $item) {
      $textimage = $this->textimageFactory->get()
        ->setStyle(ImageStyle::load('textimage_test'))
        ->process($item['text']);
      $element = [
        '#theme' => 'textimage_formatter',
        '#uri' => $textimage->getUri(),
        '#width' => $textimage->getWidth(),
        '#height' => $textimage->getHeight(),
      ];
      $textimage->getBubbleableMetadata()->applyTo($element);
      $this->renderer->renderRoot($element);
      $this->assertFalse(file_exists($textimage->getUri()));
      $this->drupalGet($textimage->getUrl());
      $this->assertTrue(file_exists($textimage->getUri()));
      $this->assertTextimage($textimage->getUri(), $item['width'], $item['height']);
    }

    // Check that files were generated on private.
    $files_count = count(file_scan_directory($private_directory_path . '/textimage_store/cache/styles/textimage_test', '/.*/'));
    $this->assertEqual(4, $files_count);

    // Try loading a missing Textimage ID, should fail with not found.
    $this->drupalGet($public_directory_path . '/textimage_store/cache/styles/textimage_test/8/8f/8f3f0c1a0d01c0487f97d068b2a77c792964eedfbe7e2f24eb1207429118aaff.png');
    $this->assertResponse(404);

    // Test failure of a Textimage derivative via URL, on image style set to
    // private.
    $this->drupalGet($public_directory_path . '/textimage/textimage_test/url_preview_text_image---additional text.png');
    $this->assertResponse(403);

    // Set image storage to 'public' wrapper.
    $edit = [
      'textimage_options[uri_scheme]' => 'public',
    ];
    $this->drupalPostForm('admin/config/media/image-styles/manage/textimage_test', $edit, t('Update style'));

    // Test build of a Textimage derivative via URL, on image style set to
    // public.
    $this->drupalGet($public_directory_path . '/textimage/textimage_test/url_preview_text_image---additional text.png');
    $this->assertResponse(200);
    $files_count = count(file_scan_directory($public_directory_path . '/textimage/textimage_test', '/.*/'));
    $this->assertTrue($files_count == 1, 'Textimage generation via request URL.');
    $this->assertTextimage('public://textimage/textimage_test/url_preview_text_image---additional text.png', 217, 24);

    // Test build a textimage at target URI via API.
    $this->textimageFactory->get()
      ->setStyle(ImageStyle::load('textimage_test'))
      ->setTargetUri('public://textimage-testing/bingo-bongo.png')
      ->process('test')
      ->buildImage();
    $files_count = count(file_scan_directory('public://textimage-testing', '/.*/'));
    $this->assertTrue($files_count == 1, 'Textimage generation at target URI via API.');
    $this->assertTextimage('public://textimage-testing/bingo-bongo.png', 33, 24);

    // Test build another textimage at same target URI.
    $this->textimageFactory->get()
      ->setStyle(ImageStyle::load('textimage_test'))
      ->setTargetUri('public://textimage-testing/bingo-bongo.png')
      ->process('another test')
      ->buildImage();
    // Check file was replaced.
    $files_count = count(file_scan_directory('public://textimage-testing', '/.*/'));
    $this->assertTrue($files_count == 1, 'Textimage replaced at target URI via API.');
    $this->assertTextimage('public://textimage-testing/bingo-bongo.png', 107, 24);
  }

  /**
   * Test execution of Textimage cron hook.
   */
  public function testTextimageCronRun() {
    // Build a temporary textimage via API.
    $textimage = $this->textimageFactory->get();
    $textimage
      ->setStyle(ImageStyle::load('textimage_test'))
      ->setTemporary(TRUE)
      ->process(['text image for cron run'])
      ->buildImage();

    // Temp file should be created at location.
    $files_count = count(file_scan_directory('public://textimage_store/temp', '/.*/'));
    $this->assertTrue($files_count === 1);

    // Run cron.
    $this->cronRun();

    // Temp directory should be removed.
    $this->assertFalse(is_dir('public://textimage_store/temp'));
  }

}
