<?php
/**
 * @file ExifHelp.php
 * @Contains \Drupal\exif\ExifHelp
 */

namespace Drupal\exif;

use Drupal\exif\ExifInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\SafeMarkup;

class ExifHelp {


  /**
   * Just some help page. Gives you an overview over the available tags
   * @return string html
   */
  static function content() {
    global $base_url;
    $filepath = drupal_get_path('module', 'exif') . '/sample.jpg';
    $imageUrl = $base_url. '/'. drupal_get_path('module', 'exif') . '/sample.jpg';
    ////$url = \Drupal::url($filepath);
    //$imageUrl = Url::fromUri($filepath)->toString();

    $taxonomyUrl = Url::fromRoute('entity.taxonomy_vocabulary.collection')->toString();
    $permissionUrl = Url::fromRoute('user.admin_permissions')->toString();

    $output = '';
    $output .= '<h2>' . t('About') . '</h2>';
    $output .= '<p>' . t('The Exif module allows you :');
    $output .= '<ul><li>' . t('extract metadata from an image') . '</li>';
    $output .= '<li>' . t('to classify your images by settings terms in taxonamy vocabulary') . '</li></ul>';
    $output .= t('To classify images, you define <em>vocabularies</em> that contain related <em>terms</em>, and then assign the vocabularies to content types. For more information, see the online handbook entry for the <a href="http://drupal.org/handbook/modules/taxonomy/">Taxonomy module</a>.');
    $output .= '</p>';
    $output .= '<h2>' . t('Uses') . '</h2>';
    $output .= '<p>';
    $output .= ' Several step are needed to get metadata information and be able to classify image and associated content';
    $output .= '<ul>';
    $output .= '<li><a href="#create-vocabulary">create a vocabulary</a></li>';
    $output .= '<li><a href="#choose-extraction-solution">choose extraction solution</a></li>';
    $output .= '<li><a href="#create-fields">create content types and fields </a></li>';
    $output .= '</ul>';
    $output .= '</p>';
    $output .= '<h4 id="create-vocabulary">' . t('Creating vocabularies') . '</h4>';
    $output .= '<p>';
    $output .= t('Users with sufficient <a href="'.$permissionUrl.'">permissions</a> can create <em>vocabularies</em> through the <a href="'.$taxonomyUrl.'">Taxonomy page</a>. The page listing the terms provides a drag-and-drop interface for controlling the order of the terms and sub-terms within a vocabulary, in a hierarchical fashion.');
    $output .= t('This module will automatically create in the chosen vocabulary (by default "Photographies\' metadata"), the following structure:');
    $output .= '</p>';
    $output .= '<ul><li>' . t('<em>vocabulary</em>: Photographies\'metadata') . '</li>';
    $output .= '<ul><li>' . t('<em>term</em>: iptc') . '</li>';
    $output .= '<ul><li>' . t('<em>sub-term</em>: keywords') . '</li>';
    $output .= '<ul><li>' . t('<em>ursub-term</em>: Paris') . '</li>';
    $output .= '<li>' . t('<em>sub-term</em>: Friends') . '</li>';
    $output .= '</ul></ul>';
    $output .= '<ul><li>' . t('<em>sub-term</em>: caption') . '</li>';
    $output .= '<ul><li>' . t('<em>sub-term</em>: Le louvre') . '</li>';
    $output .= '</ul></ul></ul>';
    $output .= '<ul><li>' . t('<em>term</em>: exif') . '</li>';
    $output .= '<ul><li>' . t('<em>sub-term</em>: model') . '</li>';
    $output .= '<ul><li>' . t('<em>sub-term</em>: KINON DE800') . '</li>';
    $output .= '</ul></ul>';
    $output .= '<ul><li>' . t('<em>sub-term</em>: isospeedratings') . '</li>';
    $output .= '<ul><li>' . t('<em>sub-term</em>: 200') . '</li>';
    $output .= '</ul></ul></ul></ul>';
    $output .= '<h4 id="choose-extraction-solution">' . t('Choose the solution to extract metadata') . '</h4>';
    $output .= '<p>';
    $output .= ' Several solution are now implemented to extract metadata from image :';
    $output .= '<ul>';
    $output .= '<li><span>the php extension</span> is the \'standard\' solution. Advantages are simplicity to install and compatibility with all PHP supported platforms. Drawback is a lower support of metadata information.';
    $output .= '<li><span>the simple exiftool</span> is a\'intermediate\' solution. Main advantage is exiftool better metadata support. Drawbacks are some non supported platforms and a slowest solution.';
    $output .= '<!--<li><span>the gearman exiftool </span>is the \'scalable\' solution. Advantages is exiftool better metadata support and scalability.Drawbacks is the complexity of installation and some non supported platforms.-->';
    $output .= '</ul>';
    $output .= '</p>';
    $output .= '<h4 id="create-fields">' . t('Creating fields to store metadata information') . '</h4>';
    $output .= '<p>';
    $output .= t('To get metadata information of an image, you have to choose on which node type the extraction should be made.');
    $output .= t('You also have to create fields with specific names using the Field UI.'). '</p>';
    $output .= t('The type of the field can be :');
    $output .= '<ul><li>' . t('<em>text field</em>: extract information and put it in the text field.') . '</li>';
    $output .= '<li>' . t('<em>date field</em>: extract information and put it in the date field.') . '</li>';
    $output .= '<li>' . t('<em>term reference field</em>: extract information, create terms and sub-terms if needed and put it in the field.') . '</li>';
    $output .= '</ul>';
    $output .= t('Please, if you want to use term reference field, ensure :');
    $output .= '<ul><li>' . t('you choose the autocompletion widget and') . '</li>';
    $output .= '<li>' . t('the chosen Vocabulary exists')." (".t('see previous section').' <a href="#create-vocabulary">'.t('Creating vocabularies').'</a>)' . '</li>';
    $output .= '</ul>';
    $output .= '<b>'.t('Important !').'</b> : '.t('Note for iptc and exif fields that have several values (like field iptc "keywords" as an example), ');
    $output .= t('if you want to get all the values, do not forget to configure the field to use unlimited number of values (by default, set to 1).');
    $output .= '</p>';
    $rows = array();
    $help = '';
    //TODO drupal_add_css(drupal_get_path('module', 'exif') . '/exif.admin.css');
    $exif = ExifFactory::getExifInterface();
    $fullmetadata = $exif->readMetadataTags($filepath);
    if (is_array($fullmetadata) && sizeof($fullmetadata)>0) {
      foreach ($fullmetadata as $section => $section_data) {
        $rows[] = array(
          'data' => array($section, $help),
          'class' => array('tag_type')
        );
        foreach ($section_data as $key => $value) {
          if ($value != NULL && $value != '' && !$exif->startswith($key, 'undefinedtag')) {
            $resultTag = "";
            if (is_array($value)) {
              foreach ($value as $innerkey => $innervalue) {
                if (($innerkey + 1) != count($value)) {
                  $resultTag .= $innervalue . "; ";
                }
                else {
                  $resultTag .= $innervalue;
                }
              }
            }
            else {
              $resultTag = SafeMarkup::checkPlain($value);
            }
            $rows[] = array(
              'data' => array(
                "field_" . $section . "_" . $key,
                $resultTag
              ),
              'class' => array('tag')
            );
          }
        }
      }
      $output .= '<div class="sample-image">';
      $output .= '<h3 class="sample-image">';
      $output .= t('Example of field name and the metadata extracted');
      $output .= '</h3>';
      $output .= '<img class="sample-image" src="' . $imageUrl . '"/>';
      $output .= '</div>';
      $output .= '<p>';
      $output .= '<table><thead><tr><th>key</th><th>value</th></tr></thead><tbody';
      foreach ($rows as $row) {
        $output .= '<tr class="'.$row['class'].'"><td>'.$row['data'][0].'</td><td>'.$row['data'][1].'</td></tr>';
      }
      $output .= "</tbody></table>";
      $output .= '</p>';
    }
    return $output;
  }
}
