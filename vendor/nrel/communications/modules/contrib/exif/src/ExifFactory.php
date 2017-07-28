<?php
/**
 * @file
 * Contains \Drupal\exif\ExifFactory
 */

namespace Drupal\exif;

use Drupal;

class ExifFactory {


  public static function getExtractionSolutions() {
    return array(
      "simple_exiftool" => "exiftool",
      "php_extensions"  => "php extensions"
    );
  }

  public static function getExifInterface() {
    $config = Drupal::configFactory()->get('exif.settings');
    $extractionSolution = $config->get('extraction_solution');
    $useExifToolSimple  = $extractionSolution == "simple_exiftool";
    if (isset($useExifToolSimple) && $useExifToolSimple && SimpleExifToolFacade::checkConfiguration()) {
      return SimpleExifToolFacade::getInstance();
    } else {
      //default case for now (same behavior as previous versions)
      return ExifPHPExtension::getInstance();
    }
  }

}
