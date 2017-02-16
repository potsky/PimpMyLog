/*global title_file,file_selector,numeral,logs_refresh_default,logs_max_default,files,notification_title,badges,lemma,port_url,geoip_url,pull_to_refresh,csrf_token,querystring,notification_default, UAParser */
/*jshint unused:false*/

var file,
	notification,
	displayed_th,
	auto_refresh_timer,
	fingerprint,
	first_launch,
	file_size,
	last_line,
	loading,
	reset,
	sort,
	sorto,
	has_loaded_more        = false,
	notification_displayed = false;


// Read a page's GET URL variables and return them as an associative array.
// Source: http://jquery-howto.blogspot.com/2009/09/get-url-parameters-values-with-jquery.html
var query_parameters = function () {
	var vars = [], hash;
	var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
	for(var i = 0; i < hashes.length; i++) {
		hash = hashes[i].split('=');
		vars.push(hash[0]);
		vars[hash[0]] = decodeURIComponent( hash[1] );
	}
	return vars;
}();


/**
 * Command to enable a copy to clipboard button
 *
 * @param   {string}  btn    the DOM selector of buttons on which to apply the copy to clipboard action when clicked
 * @param   {string}  ctn    the DOM selector of the content retrieved by the jQuery text() method
 * @param   {string}  where  where to display the tooltip on successfull copy (left, right, bottom, top)
 * @param   {string}  text   the text to display in the tooltip
 *
 * @return  {void}
 */
var clipboard_enable = function( btn , ctn , where , text ) {

	$(btn).on( 'mouseover', function() {

		//turn off this listening event for the element that triggered this
	    $(btn).off('mouseover');

		$(btn).zclip({
			path:'js/ZeroClipboard.swf',
			copy:function(){
				return ( $(ctn).val() ) ? $(ctn).val() : $(ctn).text();
			},
			afterCopy:function() {

				$( btn ).popover( {
					html      : true ,
					animation : true ,
					placement : where,
					delay     : { show: 100, hide: 5000 },
					content   : text,
				} ).popover( 'show' );

				setTimeout( function() { $( btn ).popover('hide') } , 2000 );
				$(btn).on('hidden.bs.popover', function () {
					$( btn ).show();
				});
			}
		});
	});
};

/**
 * Reload the page with query string context
 *
 * @param   {boolean}  urlonly  if set to false or undefined, the page will be reloaded. If set to something else, only the brower url will be updated.
 *
 * @return  {void}
 */
var reload_page = function( urlonly ) {
	"use strict";

	urlonly = typeof urlonly !== 'undefined' ? urlonly : false;
	var p = {
		'tz' : $('select#cog-tz').val(),
		'l'  : $('select#cog-lang').val(),
		'w'  : $('#cog-wide').data("value"),
		'o'  : sort,
		'p'  : sorto,
		'i'  : file,
		'm'  : $('#max').val(),
		'r'  : $('#autorefresh').val(),
		's'  : $('#search').val(),
		'n'  : notification,
	};

	if ( custom_columns() === true ) {
		p.t = get_columns();
	}

	var u = window.location.href.split('?')[0] + '?' + $.param( p );

	if ( urlonly === false ) {
		document.location.href = u;
	}
	else {
		window.history.pushState({"pageTitle":document.title},"", u);
	}
};


/**
 * Set the autorefresh selector to a given value
 *
 * @param  {string}  a  the value of the wanted selected option
 */
var set_auto_refresh = function( a ) {
	"use strict";
	$('#autorefresh').val( a );
};


/**
 * Set the max selector to a given value
 *
 * @param  {string}  a  the value of the wanted selected option
 */
var set_max = function( a ) {
	"use strict";
	$('#max').val( a );
};


/**
 * Get the displayed columns
 *
 * @return  {Array}
 */
var get_columns = function( a ) {
	"use strict";
	if ( $.isArray( displayed_th ) === true ) {
		return displayed_th.join(',');
	} else {
		return false;
	}
};

/**
 * Set the displayed columns
 *
 * @param  {string}  a  the value of the wanted selected option
 */
var set_columns = function( a ) {
	"use strict";
	if ( $.isArray( a ) === true ) {
		if ( a.length === 0 ) {
			displayed_th = false;
		} else {
			displayed_th = a;
		}
	} else {
		displayed_th = false;
	}
};


/**
 * Set the color icon according to completed column list
 */
var set_column_icon = function() {
	if ( $('.thmenuoff').length > 0 ) {
		$('.thmenuicon').addClass( 'text-danger' );
	} else {
		$('.thmenuicon').removeClass( 'text-danger' );
	}
};

/**
 * Parse the DOM to set columns
 */
var parse_columns = function() {
	var a = [];
	$('.thmenuon').each(function() {
		a.push( $(this).data('h') );
	});
	set_columns( a );

	set_column_icon();
	reload_page( true );
};

/**
 * Tell if there are some custom columns or not
 *
 * @return  {[type]}  [description]
 */
var custom_columns = function() {
	"use strict";
	return $.isArray( displayed_th );
};



/**
 * Return if a colum should be displayed or not
 *
 * @return  {Boolean}
 */
var is_column_displayed = function( h ) {
	"use strict";
	if ( $.isArray( displayed_th ) === true ) {
		return ( $.inArray( h , displayed_th ) > -1 );
	} else {
		return true;
	}
};


/**
 * Remove a column
 */
var remove_column = function( target ) {
	"use strict";
	$( '.thmenuitem[data-h="' + target + '"]' ).removeClass('thmenuon');
	$( '.thmenuitem[data-h="' + target + '"]' ).addClass('thmenuoff');
	$( "." + target ).hide();
	$( ".pml-" + target ).hide();

	parse_columns();
};


/**
 * Add a column
 */
var add_column = function( target ) {
	"use strict";
	$( '.thmenuitem[data-h="' + target + '"]' ).removeClass('thmenuoff');
	$( '.thmenuitem[data-h="' + target + '"]' ).addClass('thmenuon');
	$( "." + target ).show();
	$( ".pml-" + target ).show();

	parse_columns();
};


/**
 * Set the window title according to the current displayed file
 */
var set_title = function() {
	"use strict";

	document.title = title_file.replace( '%i' , file ).replace( '%f' , files[ file ].display );
};


/**
 * Append a alert on user browser
 *
 * @param   {string}  message   the message
 * @param   {string}  severity  the severity in danger, warning, success, info
 *
 * @return  {void}
 */
var pml_alert = function( message , severity) {
	"use strict";
	$('<div class="alert alert-' + severity + ' alert-dismissable fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + message + '</div>').appendTo("#notice");
};


/**
 * Display a single alert on user browser
 *
 * @param   {string}  message   the message
 * @param   {string}  severity  the severity in danger, warning, success, info
 *
 * @return  {void}
 */
var pml_singlealert = function( message , severity) {
	"use strict";
	$("#singlenotice").html('<div class="alert alert-' + severity + ' alert-dismissable fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + message + '</div>');
};


/**
 * Initialization
 *
 * @return  {void}
 */
$(function() {
	"use strict";

	// Display a log selector for several log files
	if ( ! $('#singlelog').length )  {

		// File selector bootstrap
		if ( file_selector === 'bs' ) {

			if ( ! $('.file_menup.active').length ) {
				$('.file_menup:first').addClass('active');
			}

			// File menu > init
			$('#file_selector').text( $('.file_menup.active:first a').text() );
			file = $('.file_menup.active').data('file');
			set_title();

			// File Menu > handler
			$('.file_menu').click( function() {
				$('#file_selector').text( $(this).text() );
				$('.file_menup').removeClass('active');
				$(this).parent().addClass('active');
				file = $(this).parent().data('file');
				set_title();
				get_logs( true );
			});
		}

		// File selector select html
		else {

			// File menu > init
			file = $('#file_selector_big').val();
			set_title();
			// File Menu > handler
			$('#file_selector_big').change( function() {
				file = $('#file_selector_big').val();
				set_title();
				get_logs( true );
			});
		}
	}

	// Only one log displayed
	else {
		file = $('#singlelog').data('file');
		set_title();
		get_logs( true );
	}

	// Logo click
	$('.logo').click(function() {
		document.location.href = '?';
	});

	// Refresh button
	$('#refresh').click( function() {
		notify();
		get_logs();
	});

	// Parameters type cog on/off
	$('.cog').click( function() {
		switch ( $(this).data('cog') ) {
			case 'wideview':
				if ( $(this).data("value") === 'on' ) {
					$(this).data('value','off');
					$(this).find('.cogon').hide();
					$(this).find('.cogoff').show();
					$('.tableresult').removeClass('containerwide').addClass('container');
				} else {
					$(this).data('value','on');
					$(this).find('.cogoff').hide();
					$(this).find('.cogon').show();
					$('.tableresult').addClass('containerwide').removeClass('container');
				}
				break;
			default:
		}
		reload_page( true );
	});
	$('.cog').each( function() {
		if ( $(this).data("value") === 'on' ) {
			$(this).find('.cogon').show();
			$(this).find('.cogoff').hide();
			$('.tableresult').addClass('containerwide').removeClass('container');
		} else {
			$(this).find('.cogon').hide();
			$(this).find('.cogoff').show();
			$('.tableresult').addClass('container').removeClass('containerwide');
		}
	});

	// Parameter language
	$('#cog-lang').change( function() {
		reload_page();
	});

	// Parameter time zone
	$('#cog-tz').change( function() {
		reload_page();
	});

	// Hotkeys
	$(document).keypress( function(e) {
		if ( $(e.target).is('input, textarea') ) {
			return;
		}
		if ( e.which === 114 ) { //r
			notify();
			get_logs();
		}
		else if ( ( e.which === 102 ) || ( e.which === 47 ) ) { //102=f 47=/
			e.preventDefault();
			$( '#search' ).focus();
		}
	});

	// Init Search reset button
	function tog(v){return v?'addClass':'removeClass';}
	$(document).on('input', '.clearable', function(){
		$(this)[tog(this.value)]('x');
	}).on('mousemove', '.x', function( e ){
		$(this)[tog(this.offsetWidth-18 < e.clientX-this.getBoundingClientRect().left)]('onX');
	}).on('click', '.onX', function(){
		$(this).removeClass('x onX').val('');
	});

	// Init the cross if there is a search term
	if ( $('#search.clearable').val() !== '' ) {
		$('#search.clearable')[tog($('#search.clearable').val())]('x');
	}

	// Search input
	$( '#search' ).blur( function() {
		get_logs( false , true );
	});

	// Search input enter button
	$( document ).keydown(function(event){
		if( event.keyCode === 13) {
			if ( $( '#search' ).is( ':focus' ) ) {
				$( '#search' ).blur();
				get_logs( false , true );
			}
//			event.preventDefault();
//			return false;
		}
	});

	// Auto-refresh menu
	set_auto_refresh( logs_refresh_default );
	$('#autorefresh').change( function() {
		get_logs();
	});

	// Line count menu
	set_max( logs_max_default );
	$('#max').change( function() {
		get_logs( false , true );
	});

	// Notification > init
	if ( ( 'Notification' in window ) || ( 'webkitNotifications' in window ) ) {
		$('#notification').show();
		set_notification( notification_default );
	}

	$('#notification').click( function() {
		if ( $(this).hasClass('btn-warning') ) {
			notify();
		}
		else if ( $(this).hasClass('btn-danger') ) {
			pml_alert( lemma.notification_deny , 'danger' );
		}
		else {
			set_notification( ! is_notification() );
		}
		reload_page( true );
	});

	notify();

	// Marker
	$('#logsbody').click(function(e) {
		var a = $(e.target);
		if ( $(a).hasClass('pml-date') ) {
			$(a).siblings().toggleClass('marker');
			$(a).toggleClass('marker');
		}
	});

	$('#clear-markers').click(function() {
		$('.marker').removeClass('marker');
	});

	// Pull to refresh
	if ( pull_to_refresh === true ) {
		$('#hook').hook({
			dynamic: false,
			reloadPage: false,
			reloadEl: function(){
				notify();
				get_logs();
			}
		});
	}

	// Load more
	$('.loadmore').click(function() {
		get_logs( false , false , false , true );
	});

	// Copy to clipboard for export
	clipboard_enable( "a.clipboardex", "#exModalUrl" , "right" , lemma.urlcopied );
	clipboard_enable( "a.clipboardexr", "#exModalCtn" , "right" , lemma.resultcopied );

	// Load tooltip for user modal for example
	$('[data-toggle="tooltip"]').tooltip();

	// Here we go
	get_logs( true , true , true );

	// Finally check for upgrade
	$.ajax( {
		url      : 'inc/upgrade.pml.php?' + (new Date()).getTime() + '&' + querystring,
		dataType : 'json',
		data     : { 'csrf_token' : csrf_token } ,
		type     : 'POST',
	} ).done( function ( upgrade ) {

		// Ignore Upgrade alert
		$( '#upgradefooter' ).html( ' - ' + upgrade.footer);
		var hide = $.cookie( 'upgradehide' );
		if ( hide !== upgrade.to ) {
			$( '#upgrademessage' ).html( upgrade.alert );
			$( '#upgradestop' ).click( function() {
				$.cookie( 'upgradehide' , $(this).data('version') );
				$("#upgradealert").alert('close');
			});
		}

		// Ignore Messages
		if ( upgrade.messagesto ) {
			var hidemessages = $.cookie( 'messageshide' );
			$( '#upgrademessages' ).html( upgrade.messages );
			$( '#messagesstop' ).click( function() {
				$.cookie( 'messageshide' , $(this).data('version') );
				$("#messagesalert").alert('close');
			});
		}

		$('#upgradegitpull').unbind().on('click', function() {
			$('#upgradegitpull').button('loading');
			$.ajax( {
				url      : 'inc/upgrade.pml.php?' + (new Date()).getTime() + '&' + querystring,
				dataType : 'json',
				data     : { 'csrf_token' : csrf_token , 'action' : 'upgradegitpull' } ,
				type     : 'POST',
			} ).fail( function ( a , b , c ) {
				$('#upgradegitpull').button('reset');
				$('#upgradeerror').html( c ).show();
				$('#upgradeerrorctn').show();
				$('#upgradealert').hide();
			} ).done( function ( upgrade ) {
				if ( upgrade.logs ) {
					document.location.reload();
				} else {
					$('#upgradeerror').html( upgrade.error );
					$('#upgradeerrorctn').show();
					$('#upgradealert').hide();
				}
			} );
		} );

	} );
});

