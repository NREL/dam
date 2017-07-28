/**
 * @file
 * Provide slick example behaviors.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.slickExample = {
    attach: function (context) {

      $('.slick__slider', context).on('afterChange.example', function (e, slick, currentSlide) {
        if (e.handled !== true) {
          // console.log('slick_example afterChange: ' + currentSlide);
          e.handled = true;
        }
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
