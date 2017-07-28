<?php

namespace Drupal\realistic_dummy_content_api\includes;

/**
 * Legacy base class for recipes.
 *
 * Included for backward-compatibility.
 */
abstract class RealisticDummyContentAPIRecipe extends RealisticDummyContentRecipe {
  // Prior to beta4, RealisticDummyContentAPIRecipe was used. To avoid modifying
  // all sites which use this funcitonality, this class is included for
  // backward-compatibility with beta3 (see
  // https://www.drupal.org/node/2451125).
}
