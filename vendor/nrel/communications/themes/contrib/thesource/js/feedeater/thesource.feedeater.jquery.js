/*
 * THIS PLUGIN IS NOT MAINTAINED. CONSIDER USING THE NREL NEWSY PLUGIN ON GITHUB.NREL.GOV
 *
 *  Project: theSOURCE JSON Feed parser
 *  Description: AJAX plugin to parse JSON(P) feed and display it in html
 *  Author: Michael Oakley
 *  License: None
 *  To do:
 *   1) abstract out more of the NREL Now JSON specific structure
 *   2) add user templating options for html (and json?)
 */


;(function ( $, window, document, undefined ) {


    'use strict'

    // Create the defaults
    var pluginName = 'feedeater';

    var defaults = {
        type:      'GET'
        ,dataType: 'jsonp'
        ,url:      ''
        ,ajaxcallback: ''
        ,tmplRegex:'{{((.)?.*?)}}'
        ,templateWrapper: ''
        ,template: '<p><a href="{{Link}}">{{Headline}}</a><br>{{Date}}</p>'
        ,successCallback: function(){}
    };




    // The actual plugin constructor
    function FeedEater( element, userOptions ) {
        this.element = element;

        this.options = $.extend( {}, defaults, userOptions );

        this._defaults = defaults;
        this._name = pluginName;

        this.init();
    }



    FeedEater.prototype = {

        init: function() {
            this.doAjax();
        },

        doAjax: function (){

            var self = this;
            var $container = $( this.element );


            //get the data
            var deferredObj = $.ajax({
                type:           this.options.type
                ,dataType:      this.options.dataType
                ,url:           this.options.url
                ,jsonpCallback: this.options.ajaxcallback
            });


            // success callback
            deferredObj.done(function(json){

                var html = '';
                var re = /(<)([a-z]*)(.*?)(>)/
                var tagparts = ''
                var closingtag = ''

                $container.empty();

                $(json.nodes).each(function(index, el) {
                    html += self.buildhtml(el);
                });

                if( self.options.templateWrapper.length ) {
                    tagparts = self.options.templateWrapper.match(re)
                    closingtag = tagparts[1] + '/' + tagparts[2] + tagparts[4]
                    $container.append(self.options.templateWrapper + html + closingtag );

                } else {
                    $container.append( html );
                }

                return self.options.successCallback();
            });


            // error callback
            deferredObj.fail(function(){

                $container.empty();
                $container.append("<h3>Sorry. I couldn't load the feed.</h3>");

            });

        },

        buildhtml: function(el) {
            return this.tmpl(this.options.template, this.options, el.node);
        },




        tmpl: function( str, opts /*, ... */) {
            var regex = new RegExp( opts.tmplRegex || $.fn.feedeater.defaults.tmplRegex, 'g' );
            var args  = $.makeArray( arguments );
            args.shift();

            return str.replace(regex, function(_, str) {
                    var i, j, obj, prop, names = str.split('.');
                    for (i=0; i < args.length; i++) {
                        obj = args[i];
                        if ( ! obj )
                            continue;
                        if (names.length > 1) {
                            prop = obj;
                            for (j=0; j < names.length; j++) {
                                obj = prop;
                                prop = prop[ names[j] ] || str;
                            }
                        } else {
                            prop = obj[str];
                        }

                        if ($.isFunction(prop))
                            return prop.apply(obj, args);
                        if (prop !== undefined && prop !== null && prop != str)
                            return prop;
                    }
                    return str;
            });
        }

    };



    // A really lightweight plugin wrapper around the constructor, preventing against multiple instantiations
    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, "plugin_" + pluginName)) {
                 $.data(this, "plugin_" + pluginName, new FeedEater( this, options ));
            }
        });
    };

})( jQuery, window, document );