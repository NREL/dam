(function ($) {

  "use strict";

  // Adds the JS that binds the textarea with the farbtastic element.

  // Find each colorpicker placeholder:
  // initialize it,
  // then find the nearby textfield that is of type colorentry
  // and attach the colorpicker behavior to it.
  // This is so we can support more that one per page if neccessary.

  Drupal.behaviors.colorpicker = {

    attach: function (context) {
      $('.colorpicker').each(function () {
        var $this = $(this);
        var linked_target = $('.colorentry', $this.closest('.colorform'));
        $.farbtastic($this, linked_target);
      });
    }

  };

})(jQuery);
