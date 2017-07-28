<?php

namespace Drupal\realistic_dummy_content_api\includes;

use Drupal\realistic_dummy_content_api\Framework\Framework;

/**
 * The "devel generate" dummy content generator.
 */
class RealisticDummyContentDevelGenerateGenerator extends RealisticDummyContentGenerator {

  /**
   * {@inheritdoc}
   */
  public function implementGenerate() {
    $info['entity_type'] = $this->getType();
    $info['kill'] = $this->getKill();
    $info['num'] = $this->getNum();
    $info['max_comments'] = 5;
    if ($this->getType() == 'node') {
      // See https://www.drupal.org/node/2324027
      $info = array_merge($info, array(
        'node_types' => array(
          $this->getBundle() => $this->getBundle(),
        ),
        'users' => array(
          1,
        ),
        'title_length' => 3,
      ));
    }
    Framework::instance()->develGenerate($info);
  }

}
