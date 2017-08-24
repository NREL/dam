<?php

namespace Drupal\Tests\dam_iptc\Unit;

use Drupal\dam_iptc\IPTC;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the extraction of IPTC values from an image.
 *
 * @group media_entity
 */
class IPTCExtractionTest extends UnitTestCase {

  public function testExtractingIPTCFields() {
    $iptc_class = new IPTC();

    // First we need a reference file which we know the IPTC fields for
    $path_to_photos = '/Users/drew.michael/projects/nrel-dam3/digitalassetmanagement/docroot//profiles/contrib/dam/modules/custom/dam_iptc/iptc_test_photos';

    $images_to_test = [];
    $images_to_test[] = [
      'filename' => '02157-C.JPG',
      'expected_values' => [
        'Special Instructions' => 'This system is no longer in use.',
        'Creation Date' => '19960601',
        'Author By line' => 'Warren Gretz',
        'Author Title' => 'NREL Photographer',
        'Headline' => 'Solar Two proves the technology is in place for producing utility-scale power from the sun when you need it - during periods of peak electricity demand by consumers',
        'Source' => 'Warren Gretz / NREL',
        'Photo Source' => 'NREL',
        'Caption' => 'Supplying 10 MW - enough to power 10,000 homes - to Southern California Edison Company\'s electric distribution grid during periods of peak demand, Solar Two is proving the value and technical capability of power towers.',
      ],
      'filename' => '20151-C.JPG',
      'expected_values' => [
        'Document Title' => 'Virgin Islands Environmental Research Station (VIERS)',
        'Creation Date' => '20120227',
        'Author By line' => 'Don Buchanan',
        'Author Title' => 'Outside Photographer',
        'City' => 'St John',
        'Sublocation' => 'Environmental Research Station',
        'Country' => 'U.S. Virgin Islands',
        'Original Transmission reference' => 'Virgin Islands Environmental Research Station (VIERS)',
        'Headline' => 'Solar panels on the roof of a solar classroom the Virgin Islands Environmental Research Station',
        'Source' => 'Don Buchanan / VIEO',
        'Photo Source' => 'Don Buchanan / VIEO',
        'Caption' => 'In 2011, the Virgin Islands Environmental Research Station (VIERS) installed a 9.4 kW solar system funded by a 0,000 American Recovery and Reinvestment Act (ARRA) grant of nearly 0,000, which was administered by the Energy Office. Adding an educational component to the project, VIERS developed a solar classroom to educate young people in the U.S. Virgin Islands about renewable energy technologies and their energy and environmental impacts. (Photo by Don Buchanan / VIEO',
      ],

    ];

    $iptc_field_labels_to_keys = $iptc_class->dam_iptc_full_field_list();
    foreach ($images_to_test AS $image_data) {
      $file_absolute_path = "{$path_to_photos}/{$image_data['filename']}";
      // Now check if the image has the fields in it
      getimagesize($file_absolute_path, $info);
      $this->assertArrayHasKey('APP13', $info, "IPTC fields missing.");
      if (isset($info['APP13'])) {
        $iptc_extracted_values = iptcparse($info['APP13']);
        foreach ($image_data['expected_values'] AS $label => $expected_value) {
          $extracted_value = $iptc_extracted_values[$iptc_field_labels_to_keys[$label]][0];
          $this->assertEquals($expected_value, trim($extracted_value), "IPTC field {$label} value '{$extracted_value}' does not match expected value of '{$expected_value}'");
        }
      }
    }
  }

}
