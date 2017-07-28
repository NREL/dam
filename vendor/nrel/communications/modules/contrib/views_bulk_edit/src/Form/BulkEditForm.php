<?php

namespace Drupal\views_bulk_edit\Form;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The bulk edit form.
 */
class BulkEditForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Private temp store factory.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $privateTempStoreFactory;

  /**
   * BulkEditForm constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, PrivateTempStoreFactory $privateTempStoreFactory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->privateTempStoreFactory = $privateTempStoreFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('user.private_tempstore')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bulk_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if (!$this->getBulkEditEntityData()) {
      $form['direct_access']['#markup'] = $this->t('You must use a valid bulk operations form to first select the entities to change');
      return $form;
    }

    $form['#attributes']['class'] = ['bulk-edit-form'];
    $form["#attached"]['library'][] = 'views_bulk_edit/views_bulk_edit.edit_form';
    $form['#tree'] = TRUE;
    $form['selector'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];
    foreach ($this->getBulkEditEntityData() as $entity_type_id => $bundle_entities) {
      foreach ($bundle_entities as $bundle => $entities) {
        $form = $this->getForm($entity_type_id, $bundle, $form, $form_state);
      }
    }

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#submit' => ['::submitForm'],
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * Gets the form for this entity display.
   */
  protected function getForm($entity_type_id, $bundle, $form, FormStateInterface $form_state) {
    $form[$bundle] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#parents' => [$bundle],
      '#title' => $bundle,
    ];
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
    $entity = $this->entityTypeManager->getStorage($entity_type_id)->create([
      $entity_type->getKey('bundle') => $bundle,
    ]);

    $form_display = EntityFormDisplay::collectRenderDisplay($entity, 'bulk_edit');
    $form_display->buildForm($entity, $form[$bundle], $form_state);
    $form_state->set('form_display', $form_display);

    $form[$bundle] += $this->getSelectorForm($bundle, $form[$bundle]);

    return $form;
  }

  /**
   * Builds the selector form.
   *
   * Given an entity form, create a selector form to provide options to update
   * values.
   *
   * @param string $bundle
   *   The bundle machine name.
   * @param array $form
   *   The form we're building the selection options for.
   *
   * @return array
   *   The new selector form.
   */
  protected function getSelectorForm($bundle, array &$form) {
    $selector['field_selector'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Select fields to change'),
      '#weight' => -50,
      '#tree' => TRUE,
    ];

    foreach (Element::children($form) as $key) {
      if ($key == 'field_selector' || !$element = &$this->findFormElement($form[$key])) {
        continue;
      }

      $element['#required'] = FALSE;
      $element['#tree'] = TRUE;

      // Add the toggle field to the form.
      $selector['field_selector'][$key] = [
        '#type' => 'checkbox',
        '#title' => $element['#title'],
        '#tree' => TRUE,
      ];

      // Force the original value to be hidden unless the checkbox is enabled.
      $form[$key]['#states'] = [
        'visible' => [
          sprintf('[name="%s[field_selector][%s]"]', $bundle, $key) => ['checked' => TRUE],
        ],
      ];
    }
    return $selector;
  }

  /**
   * Finds the deepest most form element and returns it.
   *
   * @param array $form
   *   The form element we're searching.
   *
   * @return array|null
   *   The deepest most element if we can find it.
   */
  protected function &findFormElement(array &$form) {
    foreach (Element::children($form) as $key) {
      if (isset($form[$key]['#title']) && isset($form[$key]['#type'])) {
        return $form[$key];
      }
      elseif (is_array($form[$key])) {
        $element = &$this->findFormElement($form[$key]);
        return $element;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entities_by_bundle = $this->getEntitiesByBundle();
    $count = 0;
    $form_state->cleanValues();

    foreach ($entities_by_bundle as $bundle => $entities) {
      $update_values = array_filter($form_state->getValue([$bundle, 'field_selector']));
      $count += count($entities);

      foreach ($entities as $entity) {
        $form['#parents'] = [$bundle];
        $form_state->setValue($bundle, $this->filterOnKey($form_state->getValue($bundle), function ($key) use ($update_values) {
          return !empty($update_values[$key]);
        }));

        $form_state->get('form_display')->extractFormValues($entity, $form, $form_state);
        $entity->save();
      }
    }

    drupal_set_message(t('Updated @count entities', ['@count' => $count]));

    $this->clearBulkEditEntityData();
  }

  /**
   * Provides same functionality as ARRAY_FILTER_USE_KEY for PHP 5.5.
   *
   * @param array $array
   *   The array of data to filter.
   * @param callable $callback
   *   The function we're going to use to determine the filtering.
   *
   * @return array
   *   The filtered data.
   */
  protected function filterOnKey(array $array, callable $callback) {
    $filtered_values = [];
    foreach ($array as $key => $value) {
      if ($callback($key)) {
        $filtered_values[$key] = $value;
      }
    }
    return $filtered_values;
  }

  /**
   * Gets the saved entity data.
   *
   * @return array
   *   An array of saved entity data.
   */
  protected function getBulkEditEntityData() {
    $privateStore = $this->privateTempStoreFactory->get('views_bulk_edit');
    return $privateStore->get('entity_data') ?: [];
  }

  /**
   * Clear the saved entities once we've finished with them.
   */
  protected function clearBulkEditEntityData() {
    $this->privateTempStoreFactory->get('views_bulk_edit')->delete('entity_data');
  }

  /**
   * Gets the loaded entities.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of loaded entities.
   */
  protected function getEntitiesByBundle() {
    $entities_by_bundle = [];
    foreach ($this->getBulkEditEntityData() as $entity_type_id => $bundle_entity_ids) {
      foreach ($bundle_entity_ids as $bundle => $entity_ids) {
        $entities_by_bundle[$bundle] = $this->entityTypeManager
          ->getStorage($entity_type_id)
          ->loadMultiple($entity_ids);
      }
    }
    return $entities_by_bundle;
  }

}
