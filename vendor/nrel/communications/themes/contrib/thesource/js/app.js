/**!
 *  moakley
 *  20141101
 *  Global actions to take on all NREL.gov pages
 *  Set breadcrumbs, side navigation, top nav, and webmaster link.
 *  Label icons
 *  Init lightbox
 *
 *  todo: encapsulate all this.
 */

'use strict';



/*
 *  Initialize the json feed reader in the center column
 **************************************************/
$(document).ready(function(e) {


	/*
	 *  Initialize the JSON feed for announcements
	 **************************************************/
	$('.nrelnow').feedeater({
		url: 'https://nrelnow.thesource.nrel.gov/json/nrelnow_wyntktw.json?callback=nrelnow_wyntktw',
		ajaxcallback:  'nrelnow_wyntktw',
		templateWrapper: '<ul class="hp-list">',
		template: '<li><a href="{{Link}}" target="_blank">{{Headline}}</a></li>'
	});


	/*
	 *  Initialize the JSON feed for News from NREL Now
	 **************************************************/
	 var html = '' +
		'<div class="row hp-nrelnow">' +
		'    <div class="col-md-4"><a href="{{Link}}" target="_blank"><img src="{{Image.src}}" alt="{{Image.alt}}" class="img-responsive" ></a></div>'+
		'    <div class="col-md-8"><h4><a href="{{Link}}" target="_blank">{{Headline}}</a></h4><p>{{Summary}}</p></div>' +
		'</div>'

	$('#rotator').feedeater({
		url: 'https://nrelnow.thesource.nrel.gov/json/nrelnow_news.json?callback=nrelnow_news',
		ajaxcallback:  'nrelnow_news',
		templateWrapper: '<div>',
		template: html
	});

});



$(document).ready(function(){

	var $navlink
	  , $navitem
	  , slash
	  , nrel
	  , pv
	  , isHomePage = false
	  //, homepages = ['/','index.html','index.cfm','index.php']

	// shorthand alias for our page variables
	nrel = window.nrel || {};
	pv = nrel.pagevars || {};




	pv.pagename = $('h1').text();

	slash = location.pathname.lastIndexOf('/') + 1;

	if (pv.pageurl === undefined) {
		pv.pageurl  = location.pathname;                 //  /foo/bar/baz/boink.html or /foo/ if foo home page
	}

	if (pv.siteurl === undefined) {
		pv.siteurl  = location.pathname.substr(0,slash); //  /foo/bar/baz/
	}

	if ( pv.pageurl === pv.siteurl || pv.pageurl.match(/index\./) ) {
		isHomePage = true
	}

	/*
	 *  TOPNAV
	 ******************/

	 $('#topnav [data-topnav*=' + pv.topnav + ']').addClass('active');


	/*
	 *  BREADCRUMBS
	 ******************/

	// build the 2nd breadcrumb
	if( pv.sitename ) {
		$( '.breadcrumb' ).append( '<li><a class="bc-siteurl" href="' + pv.siteurl + '"><span class="bc-sitename">' + pv.sitename  + '</span></a></li>' );
	}

	// build the 3rd (last) breadcrumb
	if( pv.pagename && pv.breadcrumbs !== false  ) {
			$( '.breadcrumb' ).append( '<li><span class="bc-pagename">' + pv.pagename + '</span></li>' );
		}


	// show the breadcrumbs, except on pages without H1s (such as home pages)
	if( pv.pagename.length ) {
		$( '.breadcrumb' ).removeClass( 'invisible' );
	}


	/*
	 * LEFTNAV
	 ******************/

	/*
	 * Home pages aren't in the nav. Show the top level.
	 *
	 * If the developer set `sidenav`, show the menu.
	 * (this is usually the case for landing pages that aren't listed in the nav)
	 *
	 * If the developer set `sidenavButton` manually, respect that.
	 * (this is usually the case for sub buttons that aren't listed in the nav)
	 *
	 * If the filename was found in a leftnav hyperlink, unlink it and show the upstream menus.
	 *
	 */


	if ( isHomePage) {

		$('.sidenav').children('.nav').removeClass('hide')

	}

	if ( pv.sidenav ) {

		$('.sidenav [data-sidenav=' + pv.sidenav + ']').removeClass('hide')

	}

	$navlink = $( '.sidenav a[href="' + pv.pageurl + '"]' ) // Find an <a> tag that links to us

	if ( pv.sidenavButton ) {

		// Accept sidenavButton vals like /mysite/mypage.html and mypage.html
		// If it's just mypage.html, prepend the siteurl
		// (special catch for sites like news that live within other sites)
		if ( ! pv.sidenavButton.match( pv.siteurl ) ) {

			if ( pv.siteurl == location.pathname.substr(0,slash) ) {

				pv.sidenavButton = pv.siteurl + pv.sidenavButton

			}
		}

		$navlink = $( '.sidenav a[href="' + pv.sidenavButton + '"]' )

	} else {

		$navlink.removeAttr('href')

	}

	$navitem = $navlink.parent() // grab the <li>
	$navitem.addClass('active') // Activate the button
	$navitem.children('.nav').removeClass('hide') // show any immediate downstream menus
	$navitem.parentsUntil('.sidenav', '.hide').removeClass('hide') // show any hidden menus upstream as needed





	/*
	 * Contact Us footer link
	 * if the site doesn't defer to the globalwebmaster, use the local one
	 **************************/
	if( !pv.globalwebmaster && pv.sitename ) {
		$('#contact-link').attr( 'href', pv.siteurl + 'contacts.html' );
	} else {
		$('#contact-link').attr( 'href', '/webmaster.html' );
	}

});




/*
 *  Label icons
 */
$(document).ready(function(){

	$('body').iconomatic({
		 ajax       :  true
		,dataMode   :  true
	});

});


/*
 *  Init lightbox
 */
$(document).ready(function(){
	// Init a modal lightbox for clicks on any matching elements
	$(document).on( 'click', '*[data-toggle="lightbox"]',  function(event) {
		event.preventDefault()
		$(this).ekkoLightbox()
	});

});


/*
 *  Crazy Egg
 */
setTimeout(function(){
	var a=document.createElement("script");
	var b=document.getElementsByTagName('script')[0];
	a.src=document.location.protocol+"//dnn506yrbagrg.cloudfront.net/pages/scripts/0011/5883.js?"+Math.floor(new Date().getTime()/3600000);
	a.async=true;
	a.type="text/javascript";
	b.parentNode.insertBefore(a,b)
}, 1);
