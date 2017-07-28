/**
 * @file
 * Provides Slick loader.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Attaches slick behavior to HTML element identified by CSS selector .slick.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.slick = {
    attach: function (context) {
      var me = this;

      $('.slick', context).once('slick').each(function () {
        var that = this;
        var b;
        var t = $('> .slick__slider', that).length ? $('> .slick__slider', that) : $(that);
        var a = $('> .slick__arrow', that);
        var o = $.extend({}, drupalSettings.slick, t.data('slick'));

        // Populate defaults + globals into each breakpoint.
        if ($.type(o.responsive) === 'array' && o.responsive.length) {
          for (b in o.responsive) {
            if (o.responsive.hasOwnProperty(b)
              && o.responsive[b].settings !== 'unslick') {
              o.responsive[b].settings = $.extend(
                {},
                drupalSettings.slick,
                me.globals(t, a, o),
                o.responsive[b].settings);
            }
          }
        }

        // Update the slick settings object.
        t.data('slick', o);
        o = t.data('slick') || {};

        // Build the Slick.
        me.beforeSlick(t, a, o);
        t.slick(me.globals(t, a, o));
        me.afterSlick(t, o);

        // Destroy Slick if it is an enforced unslick.
        // This allows Slick lazyload to run, but prevents further complication.
        // Should use lazyLoaded event, but images are not always there.
        if (t.hasClass('unslick')) {
          t.slick('unslick');
        }
      });
    },

    /**
     * The event must be bound prior to slick being called.
     *
     * @param {HTMLElement} t
     *   The slick HTML element.
     * @param {HTMLElement} a
     *   The slick arrow HTML element.
     * @param {object} o
     *   The slick options object.
     */
    beforeSlick: function (t, a, o) {
      var me = this;
      var r = $('.slide--0 .media--ratio', t);

      me.randomize(t, o);

      t.on('setPosition.slick', function (e, slick) {
        me.setPosition(t, a, o, slick);
      });

      // Fixed for broken slick with Blazy, aspect ratio, hidden containers.
      if (r.length && r.is(':hidden')) {
        r.removeClass('media--ratio').addClass('js-media--ratio');
      }

      $('.media--loading', t).closest('.slide').addClass('slide--loading');

      // Blazy integration.
      if (o.lazyLoad === 'blazy' && Drupal.blazy) {
        t.on('beforeChange.slick', function () {
          // .b-lazy can be attached to IMG, or DIV as CSS background.
          var $src = $('.slide--loading .b-lazy', t);
          var $loaded = $('.b-loaded', t);

          if ($src.length) {
            // Enforces lazyload ahead to smoothen the UX.
            Drupal.blazy.init.load($src);
          }

          $loaded.closest('.slide').removeClass('slide--loading');
        });

        Drupal.blazy.init.options.success = function (elm) {
          $(elm).closest('.slide').removeClass('slide--loading');
          Drupal.blazy.clearing(elm);
        };
      }
    },

    /**
     * The event must be bound after slick being called.
     *
     * @param {HTMLElement} t
     *   The slick HTML element.
     * @param {object} o
     *   The slick options object.
     */
    afterSlick: function (t, o) {
      var me = this;
      var slick = t.slick('getSlick');
      var $ratio = $('.js-media--ratio', t);

      // Arrow down jumper.
      t.parent().on('click.slick.load', '.slick-down', function (e) {
        e.preventDefault();
        var b = $(this);
        $('html, body').stop().animate({
          scrollTop: $(b.data('target')).offset().top - (b.data('offset') || 0)
        }, 800, o.easing || 'swing');
      });

      if (o.mouseWheel) {
        t.on('mousewheel.slick.load', function (e, delta) {
          e.preventDefault();
          return (delta < 0) ? t.slick('slickNext') : t.slick('slickPrev');
        });
      }

      // Fixed for broken slick with Blazy, aspect ratio, hidden containers.
      if ($ratio.length) {
        // t[0].slick.refresh();
        t.trigger('resize');
        $ratio.addClass('media--ratio').removeClass('js-media--ratio');
      }

      t.on('lazyLoaded lazyLoadError', function (e, slick, img) {
        me.setBackground(img);
      });

      t.trigger('afterSlick', [me, slick, slick.currentSlide]);
    },

    /**
     * Turns images into CSS background if so configured.
     *
     * @param {object} img
     *   The image object.
     */
    setBackground: function (img) {
      var $img = $(img);
      var $bg = $img.closest('.media--background');

      $img.closest('.media').removeClass('media--loading').addClass('media--loaded');
      $img.closest('.slide--loading').removeClass('slide--loading');

      if ($bg.length) {
        $bg.css('background-image', 'url(' + $img.attr('src') + ')');
        $bg.find('> img').remove();
        $bg.removeAttr('data-lazy');
      }
    },

    /**
     * Randomize slide orders, for ads/products rotation within cached blocks.
     *
     * @param {HTMLElement} t
     *   The slick HTML element.
     * @param {object} o
     *   The slick options object.
     */
    randomize: function (t, o) {
      if (o.randomize && !t.hasClass('slick-initiliazed')) {
        t.children().sort(function () {
          return 0.5 - Math.random();
        })
        .each(function () {
          t.append(this);
        });
      }
    },

    /**
     * Updates arrows visibility based on available options.
     *
     * @param {HTMLElement} t
     *   The slick HTML object.
     * @param {HTMLElement} a
     *   The slick arrow HTML object.
     * @param {object} o
     *   The slick options object.
     * @param {object} slick
     *   The slick instance object.
     *
     * @return {string}
     *   The visibility of slick arrows controlled by CSS class visually-hidden.
     */
    setPosition: function (t, a, o, slick) {
      // Be sure the most complex slicks are taken care of as well, e.g.:
      // asNavFor with the main display containing nested slicks.
      if (t.attr('id') === slick.$slider.attr('id')) {
        // Removes padding rules, if no value is provided to allow non-inline.
        if (!o.centerPadding || o.centerPadding === '0') {
          slick.$list.css('padding', '');
        }

        // Do not remove arrows, to allow responsive have different options.
        return slick.slideCount <= o.slidesToShow || o.arrows === false
          ? a.addClass('visually-hidden') : a.removeClass('visually-hidden');
      }
    },

    /**
     * Declare global options explicitly to copy into responsive settings.
     *
     * @param {HTMLElement} t
     *   The slick HTML element.
     * @param {HTMLElement} a
     *   The slick arrow HTML element.
     * @param {object} o
     *   The slick options object.
     *
     * @return {object}
     *   The global options common for both main and responsive displays.
     */
    globals: function (t, a, o) {
      return {
        slide: o.slide,
        lazyLoad: o.lazyLoad,
        dotsClass: o.dotsClass,
        rtl: o.rtl,
        appendDots: o.appendDots === '.slick__arrow'
          ? a : (o.appendDots || $(t)),
        prevArrow: $('.slick-prev', a),
        nextArrow: $('.slick-next', a),
        appendArrows: a,
        customPaging: function (slick, i) {
          var tn = slick.$slides.eq(i).find('[data-thumb]') || null;
          var alt = Drupal.t(tn.attr('alt')) || '';
          var img = '<img alt="' + alt + '" src="' + tn.data('thumb') + '">';
          var dotsThumb = tn.length && o.dotsClass.indexOf('thumbnail') > 0 ?
            '<div class="slick-dots__thumbnail">' + img + '</div>' : '';
          return slick.defaults.customPaging(slick, i).add(dotsThumb);
        }
      };
    }
  };

})(jQuery, Drupal, drupalSettings);
