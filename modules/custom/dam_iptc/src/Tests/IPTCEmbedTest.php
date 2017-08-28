<?php

namespace Drupal\Tests\dam_iptc\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\dam_iptc\IPTC;

class IPTCEmbedTest extends UnitTestCase {

  public function testEmbeddingIPTCFields() {
    $iptc_class = new IPTC();

    // Set up some default IPTC tags to test
    $iptc_data = $iptc_data_with_keys = [];
    $iptc_data['Copyright'] = '© 2017, Drew Michael, All Rights Reserved.';
    $iptc_data['City'] = 'Boulder';
    $iptc_data['State'] = 'CO';
    $iptc_data['Creation Date'] = '20170821';

    $iptc_data['Keywords'] = [
      'Keyword 1',
      'Keyword 2',
      'Keyword 3',
      'Keyword 4',
      'Keyword 5',
    ];

    // Map the readable labels above to the correct IPTC key values
    $label_mapping = $iptc_class->dam_iptc_full_field_list();
    foreach ($iptc_data AS $label => $value) {
      $iptc_data_with_keys[$label_mapping[$label]] = $value;
    }

    // Create a blank jpeg image
    $file_absolute_path = "/tmp/testimage.jpg";
    $img = imagecreatetruecolor(120, 20);
    $bg = imagecolorallocate ( $img, 255, 255, 255 );
    imagefilledrectangle($img,0,0,120,20,$bg);
    imagejpeg($img, $file_absolute_path, 100);

    // Clear out IPTC fields
    $buffer = iptcembed("", $file_absolute_path, 0);
    $fp = fopen($file_absolute_path, "w");
    fwrite($fp, $buffer);
    fclose($fp);

    // Embed the IPTC fields into an image
    $iptc_class->embed_iptc_fields_into_image($file_absolute_path, $iptc_data_with_keys);

    // Now check if the image has the fields in it
    getimagesize($file_absolute_path, $info);
    // This section will create empty IPTC tags if the image does not have any at all
    $this->assertArrayHasKey('APP13', $info, "IPTC fields missing.");
    if (isset($info['APP13'])) {
      $iptc_result = iptcparse($info['APP13']);
      $iptc_field_labels_to_keys = $iptc_class->dam_iptc_full_field_list();
      foreach ($iptc_field_labels_to_keys AS $label => $key) {
        if (isset($iptc_data[$label])) {
          if (is_array($iptc_data[$label])) {
            foreach ($iptc_data[$label] AS $count => $str) {
              $this->assertEquals(htmlentities($str), $iptc_result[$key][$count], "IPTC field {$label} value '{$iptc_result[$key][0]}' does not match expected value of '{$str}'");
            }
          } else {
            $this->assertEquals(htmlentities($iptc_data[$label]), $iptc_result[$key][0], "IPTC field {$label} value '{$iptc_result[$key][0]}' does not match expected value of '{$iptc_data[$label]}'");
          }
        }
      }
    }
  }
}
