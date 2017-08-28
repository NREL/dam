<?php

namespace Drupal\dam_iptc;

/**
 * Class for manipulating image IPTC data.
 */
class IPTC {

  /*
   * Embed IPTC fields into entity.
   * Takes an array of Labels => Values
   * Uses the labels from dam_iptc_full_field_list()
   * For example:
   * $iptc['Copyright'] = '© 2017, Drew Michael, All Rights Reserved.';
   * $iptc['City'] = 'Boulder';
   * $iptc['State'] = 'CO';
   * $iptc['Creation Date'] = '20170821';
   */
  function embed_iptc_fields_into_image($file_absolute_path, $iptc_data) {
    getimagesize($file_absolute_path, $info);
    // This section will create empty IPTC tags if the image does not have any at all
    if (!isset($info['APP13'])) {
      $buffer = iptcembed("", $file_absolute_path, 0);
      $fp = fopen($file_absolute_path, "w");
      fwrite($fp, $buffer);
      fclose($fp);
    }
    // Convert the IPTC tags into binary code
    $data = '';
    foreach ($iptc_data as $tag => $values) {
      $tag = substr($tag, 2);
      foreach ($values AS $value) {
        $data .= $this->iptc_make_tag(2, $tag, htmlentities($value));
      }
    }
    // Embed the IPTC data
    $content = iptcembed($data, $file_absolute_path);
    // Write the new image data out to the file.
    $fp = fopen($file_absolute_path, "wb");
    fwrite($fp, $content);
    fclose($fp);
  }

  // iptc_make_tag() function by Thies C. Arntzen
  function iptc_make_tag($rec, $data, $value) {
    $length = strlen($value);
    $retval = chr(0x1C) . chr($rec) . chr($data);

    if($length < 0x8000)
    {
      $retval .= chr($length >> 8) .  chr($length & 0xFF);
    }
    else
    {
      $retval .= chr(0x80) .
        chr(0x04) .
        chr(($length >> 24) & 0xFF) .
        chr(($length >> 16) & 0xFF) .
        chr(($length >> 8) & 0xFF) .
        chr($length & 0xFF);
    }

    return $retval . $value;
  }

  function dam_iptc_full_field_list() {
    return [
      'Character Set' => '1#090',
      'Document Title' => '2#005',
      'Category' => '2#015',
      'Subcategories' => '2#020',
      'Keywords' => '2#025',
      'Special Instructions' => '2#040',
      'Creation Date' => '2#055',
      'Creation Time' => '2#060',
      'Digital Creation Date' => '2#062',
      'Digital Creation Time' => '2#063',
      'Author By line' => '2#080',
      'Author Title' => '2#085',
      'City' => '2#090',
      'Sublocation' => '2#092',
      'State' => '2#095',
      'Country Code' => '2#100',
      'Country' => '2#101',
      'Original Transmission reference' => '2#103',
      'Headline' => '2#105',
      'Source' => '2#110',
      'Photo Source' => '2#115',
      'Copyright' => '2#116',
      'Contact' => '2#118',
      'Caption' => '2#120',
      'Caption Writer' => '2#122'
    ];
  }

}