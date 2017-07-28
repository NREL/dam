(function ($) {

  Drupal.plupload_widget = Drupal.plupload_widget || {};

  // Add Plupload events for autoupload and autosubmit.
  Drupal.plupload_widget.filesAddedCallback = function (up, files) {
    setTimeout(function () { up.start(); }, 100);
  };

  Drupal.plupload_widget.uploadCompleteCallback = function (up, files) {
    var $this = $("#" + up.settings.container);
    var $container = $this.closest('.form-managed-file');
    // If there is submit_element trigger it.
    var submit_element = drupalSettings.plupload[$this.attr('id')].submit_element;
    if (submit_element) {
      // Trigger the upload button.
      var $button = $container.find(submit_element);
      var $event = drupalSettings['ajax'][$button.attr('id')]['event'];
      $button.trigger($event);
      // Now hide the uploader...
      // the ajax throbber will show.
      $(up).hide();
    }
  };

})(jQuery);
