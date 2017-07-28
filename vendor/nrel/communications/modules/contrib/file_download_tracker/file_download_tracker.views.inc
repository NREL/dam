<?php

/**
 * @file
 * Contains no_views_php\no_views_php.views.inc.
 * Provide a custom views field data that isn't tied to any other module.
 */

/**
 * Implements hook_views_data().
 */
function file_download_tracker_views_data() {

  $data['views']['table']['group'] = t('File download tracker');
  $data['views']['table']['join'] = array(
    // #global is a special flag which allows a table to appear all the time.
    '#global' => array(),
  );

  $data['views']['fdt_entity_name_field'] = array(
    'title' => t('Entity Name'),
    'help' => t('Enable to display file or page name'),
    'field' => array(
      'id' => 'fdt_entity_name_field',
    ),
  );

  return $data;
}
