<?php

namespace Drupal\realistic_dummy_content_api\includes;

use Drupal\realistic_dummy_content_api\Framework\Framework;

/**
 * Abstract base "recipe" class.
 *
 * Recipes are sequential dummy content generators, for example if a system
 * defines "school board" and "school" content types, we might want to generate
 * five dummy school boards followed by 20 dummy schools.
 *
 * Custom modules can extend this class to create their own recipes; an example
 * can be found in this module at
 * ./realistic_dummy_content/recipe/realistic_dummy_content.recipe.inc.
 */
abstract class RealisticDummyContentRecipe {
  private static $log;

  /**
   * Run this recipe.
   */
  public static function run($log) {
    self::startTime('run');
    self::$log = $log;
    $objects = self::findObjects();

    foreach ($objects as $object) {
      $object->_Run_();
    }
    self::$log->log(t('Realistic dummy content generation operation completed in @time milliseconds', array('@time' => self::stopTime('run'))));
  }

  /**
   * Find all recipe objects defined by all modules.
   */
  public static function findObjects() {
    $objects = array();
    // We need to cycle through all active modules and look for those
    // which contain a class module_name_realistic_dummy_content_recipe
    // in the file realistic_dummy_content/recipe/module_name.recipe.inc.
    $modules = Framework::instance()->moduleList();
    foreach ($modules as $module) {
      $candidate = $module . '_realistic_dummy_content_recipe';
      if (self::loadRecipeClass($module) && class_exists($candidate)) {
        $objects[] = new $candidate();
      }
    }
    return $objects;
  }

  /**
   * Loads the recipe class file only if it is valid and exists.
   *
   * Version 2.x introduced the requirement that the following line should
   * be added to recipe files:
   *
   *   use
   *   Drupal\realistic_dummy_content_api\includes\RealisticDummyContentRecipe;
   *
   * So we will throw an exception warning users of previous versions to add
   * that line to use the 2.x branch of realistic_dummy_content.
   *
   * @param string $module
   *   A module name.
   *
   * @return bool|string
   *   FALSE or the full path to the loaded recipe class file.
   *
   * @throws \Exception
   */
  static public function loadRecipeClass($module) {
    $path = Framework::instance()->getPath('module', $module) . '/realistic_dummy_content/recipe/' . $module . '.recipe.inc';
    $fullpath = Framework::instance()->frameworkRoot() . '/' . $path;
    if (!file_exists($fullpath)) {
      return FALSE;
    }
    $contents = file_get_contents($fullpath);
    if (!preg_match('/use Drupal*/s', $contents)) {
      throw new \Exception('As of the 2.x version you need to add the following line to the top of your recipe at ' . $fullpath . ': use Drupal\realistic_dummy_content_api\includes\RealisticDummyContentRecipe');
    }
    return module_load_include('inc', $module, 'realistic_dummy_content/recipe/' . $module . '.recipe');
  }

  /**
   * Return a concrete generator class to generate content.
   *
   * @param array $more
   *   Can contain:
   *     kill => TRUE|FALSE.
   */
  public static function getGenerator($type, $bundle, $count, $more) {
    if (in_array($type, array('user', 'node'))) {
      if (Framework::instance()->moduleExists('devel_generate')) {
        return new RealisticDummyContentDevelGenerateGenerator($type, $bundle, $count, $more);
      }
      else {
        self::$log->error(t("Please enable devel's devel_generate module to generate users or nodes."));
      }
    }
    else {
      self::$log->error(t('Entity types other than user and node are not supported for realistic dummy content recipe.'));
    }
  }

  /**
   * Create new entities.
   */
  public function newEntities($type, $bundle, $count, $more = array()) {
    self::startTime(array($type, $bundle, $count));
    if ($generator = self::getGenerator($type, $bundle, $count, $more)) {
      $generator->generate();
    }
    else {
      self::$log->error(t('Could not find a generator for @type @bundle.', array('@type' => $type, '@bundle' => $bundle)));
    }
    $time = self::stopTime(array($type, $bundle, $count));
    self::$log->log(t('@type @bundle: @n created in @time milliseconds', array(
      '@type' => $type,
      '@bundle' => $bundle,
      '@n' => $count,
      '@time' => $time,
    )));
  }

  /**
   * Log the start time.
   */
  public static function startTime($id) {
    Framework::instance()->timerStart(serialize($id));
  }

  /**
   * Get the end time.
   */
  public static function stopTime($id) {
    $timer = Framework::instance()->timerStop(serialize($id));
    return $timer['time'];
  }

}
