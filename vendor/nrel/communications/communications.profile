<?php

/**
 * @file
 * Enables modules and site configuration for the communications profile.
 */

// Add any custom code here, like hook implementations.

use Drupal\contact\Entity\ContactForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function communications_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
  // Account information defaults
  $form['admin_account']['account']['name']['#default_value'] = 'drupaladmin';
  $form['admin_account']['account']['mail']['#default_value'] = 'drupaladmin@nrel.gov';

  // // Date/time settings
  $form['regional_settings']['site_default_country']['#default_value'] = 'US';
  $form['regional_settings']['date_default_timezone']['#default_value'] = 'America/Denver';
}


/**
 * Implements hook_form_alter().
 */
function communications_form_alter(array &$form, FormStateInterface $form_state, $form_id) {
  // if ($form_id == 'user_login_form') {
  //   // Remove regular login button
  //   unset($form['actions']['submit']);
  // }
}
