<?php

namespace Drupal\Tests\views_bulk_edit\Functional;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityFormMode;
use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the Views Bulk Edit feature.
 *
 * @group views_bulk_edit
 */
class ViewsBulkEditTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['views_bulk_edit', 'node'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->createContentType(['type' => 'page', 'name' => 'Page']);
    $this->createContentType(['type' => 'article', 'name' => 'Article']);
    $admin = $this->createUser([
      'bypass node access',
      'access content overview',
      'use views bulk edit',
      'access content',
      'administer content types',

      // Likely a bug in core, NodeAccessControlHandler::checkFieldAccess().
      'administer nodes',
    ]);
    $this->drupalLogin($admin);
  }

  /**
   * Test VBE from the UI using the node module.
   */
  public function testViewsBulkEdit() {
    $storage = $this->container->get('entity_type.manager')->getStorage('node');
    $page1 = $this->createNode();
    $page2 = $this->createNode();
    $article1 = $this->createNode(['type' => 'article']);

    // Test editing a single article with properties and fields.
    $this->drupalPostForm('/admin/content', [
      'action' => 'node_bulk_edit',
      'node_bulk_form[0]' => $this->getBulkKey($page1),
      'node_bulk_form[1]' => $this->getBulkKey($page2),
    ], 'Apply to selected items');

    $random_title = $this->randomMachineName();
    $this->drupalPostForm(NULL, [
      'page[field_selector][title]' => '1',
      'page[title][0][value]' => $random_title,
    ], 'Save');

    // Assert property was changes. Assert field was changed.
    $storage->resetCache();
    $nodes = array_values($storage->loadMultiple([
      $page1->id(),
      $page2->id(),
      $article1->id(),
    ]));
    $this->assertEquals($random_title, $nodes[0]->getTitle());
    $this->assertEquals($random_title, $nodes[1]->getTitle());
    $this->assertNotEquals($random_title, $nodes[2]->getTitle());

    // Visiting the path directly when we have no selected entities should
    // show us a warning.
    $this->drupalGet('/admin/content/bulk-edit');
    $this->assertSession()->pageTextContains('You must use a valid bulk operations form to first select the entities to change');
  }

  /**
   * Test editing an article and a page bundle.
   */
  public function testBulkEditMultipleBundles() {
    $page1 = $this->createNode();
    $article1 = $this->createNode(['type' => 'article']);
    $this->drupalPostForm('/admin/content', [
      'action' => 'node_bulk_edit',
      'node_bulk_form[0]' => $this->getBulkKey($page1),
      'node_bulk_form[1]' => $this->getBulkKey($article1),
    ], 'Apply to selected items');

    // @TODO, also change a field.
    $random_title = $this->randomMachineName();
    $this->drupalPostForm(NULL, [
      'page[field_selector][title]' => '1',
      'page[title][0][value]' => $random_title,
      'article[field_selector][title]' => '1',
      'article[title][0][value]' => $random_title,
    ], 'Save');

    // Assert property and field is changed.
    $storage = $this->container->get('entity_type.manager')->getStorage('node');
    $storage->resetCache();
    $nodes = array_values($storage->loadMultiple([$page1->id(), $article1->id()]));
    $this->assertEquals($random_title, $nodes[0]->getTitle());
    $this->assertEquals($random_title, $nodes[1]->getTitle());
  }

  /**
   * Values that are not selected or displayed are never changed.
   */
  public function testOnlySelectedValuesAreChanged() {
    // Test submitting form with new fields for a field and a property but not
    // selecting them to be changed does not cause a change.
    $page1 = $this->createNode();
    $this->drupalPostForm('/admin/content', [
      'action' => 'node_bulk_edit',
      'node_bulk_form[0]' => $this->getBulkKey($page1),
    ], 'Apply to selected items');

    $random_title = $this->randomMachineName();
    $this->drupalPostForm(NULL, [
      'page[title][0][value]' => $random_title,
    ], 'Save');
    $storage = $this->container->get('entity_type.manager')->getStorage('node');
    $storage->resetCache();
    $this->assertNotEquals($random_title, $storage->load($page1->id())->getTitle());
  }

  /**
   * Test non-configured fields are not displayed.
   */
  public function testFieldsNotDisplayedAreIgnored() {
    EntityFormMode::create([
      'id' => 'node.bulk_edit',
      'label' => 'Bulk Edit',
      'targetEntityType' => 'node',
    ])->save();
    $display = EntityFormDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'page',
      'mode' => 'bulk_edit',
      'status' => TRUE,
    ]);

    $page1 = $this->createNode();
    $this->drupalPostForm('/admin/content', [
      'action' => 'node_bulk_edit',
      'node_bulk_form[0]' => $this->getBulkKey($page1),
    ], 'Apply to selected items');
    $this->assertSession()->fieldExists('page[field_selector][title]');

    // Update the display to hide the title.
    $display
      ->removeComponent('title')
      ->save();

    // Node the title field should no longer be displayed.
    $this->drupalPostForm('/admin/content', [
      'action' => 'node_bulk_edit',
      'node_bulk_form[0]' => $this->getBulkKey($page1),
    ], 'Apply to selected items');
    $this->assertSession()->fieldNotExists('page[field_selector][title]');
  }

  /**
   * Generate the entity bulk key for the form.
   */
  protected function getBulkKey(NodeInterface $entity) {
    $key_parts = [$entity->language()->getId(), $entity->id()];
    $json = json_encode($key_parts);
    return base64_encode($json);
  }

}
