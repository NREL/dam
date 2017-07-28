<?php

namespace Drupal\imagick;

class ImagickConst {

  // All possible blur types
  const NORMAL_BLUR = 0;
  const ADAPTIVE_BLUR = 1;
  const GAUSSIAN_BLUR = 2;
  const MOTION_BLUR = 3;
  const RADIAL_BLUR = 4;

  public static function imagick_file_formats() {
    // @TODO use imagick::queryFormats() to generate full format list
    return array(
      'image/jpeg' => 'jpg',
      'image/gif' => 'gif',
      'image/png' => 'png'
    );
  }

}
