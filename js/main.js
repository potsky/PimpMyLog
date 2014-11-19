/*global title_file,file_selector,numeral,logs_refresh_default,logs_max_default,files,notification_title,badges,lemma,geoip_url,pull_to_refresh,csrf_token,querystring,notification_default, UAParser */
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
 * Refresh button in the RSS exModal
 *
 * @return  {void}
 */
var refresh_rss = function() {
	$('#exModalRefresh').button('loading');
	$.ajax({
		url      : $("#exModalUrl").text(),
		dataType : 'text',
		success  : function( a ) {
			$('#exModalRefresh').button('reset');
			$("#exModalCtn").text( a );
		}
	});
};

/**
 * Download the EXPORT feed
 *
 * @return  {boolean}  false
 */
var get_rss = function( format ) {
	$('#exModalResultLoading').show();
	$('#exModalResult').hide();
	$('#exModalRefresh').button('loading');

	$.ajax( {
		url      : 'inc/rss.pml.php?' + (new Date()).getTime() + '&' + querystring,
		type     : 'POST',
		dataType : 'json',
		data     : {
			'csrf_token' : csrf_token,
			'action'     : 'get_rss_link',
			'file'       : file,
			'search'     : $('#search').val(),
			'format'     : format
		}
	} )
	.always( function() {
	})
	.fail( function ( e ) {
		$('#prBody').html( get_alert( 'danger' , e.responseText , false ) );
	})
	.done( function ( re ) {
		if ( re.singlewarning ) {
			pml_singlealert( re.singlewarning , 'warning' );
		}
		else if ( re.singlenotice ) {
			pml_singlealert( re.singlenotice , 'info' );
		}
		else if ( re.error ) {
			pml_singlealert( re.error , 'danger' );
		}
		else {
			// Force the file download
			if ( re.met === 'if' ) {
	  			document.body.innerHTML += "<iframe src='" + re.url + "' style='display: none;' ></iframe>";
			}
			// Open in a new window
			else {

				// No result preview
				if ( re.met === 'nd' ) {
					$('#exModalResultLoading').hide();
					$('#exModalResult').hide();
					$('#exModalRefresh').button('reset');
				}
				else {
					// Load the preview
					$.ajax({
						url      : re.url,
						dataType : 'text',
						success  : function( a ) {
							$("#exModalCtn").text( a );

							$('#exModalResultLoading').hide();
							$('#exModalResult').show();
							$('#exModalRefresh').button('reset');

						}
					});
				}

				// Title name
				$("#exModalFormat").text( format );

				// Display the URL
				$("#exModalUrl").text( re.url );

				// Local address warning
				if ( re.war === false ) {
					$("#exModalWar").hide();
				} else {
					$("#exModalWar").show();
				}

				// Active the link
				$("#exModalOpen").attr('href',re.url);

				// Show the modal
				$("#exModal").modal('show');

			}

		}
	});
	return false;
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
 * Set the btn with the provided value
 * If the value is not set, it will simply ajust inner variables
 *
 * @param  {string}  a  the value of the wanted selected option
 */
var notification_class = 'warning';
var set_notification   = function( a ) {
	"use strict";
	if ( a === undefined ) {
		a = notification;
	}
	if ( a === true ) {
		$('#notification').removeClass('btn-warning btn-success btn-danger btn-default').addClass('active btn-' + notification_class );
		notification = true;
	} else {
		$('#notification').removeClass('btn-warning btn-success btn-danger active').addClass('btn-default' );
		notification = false;
	}
};


/**
 * Return if notification is set or not
 *
 * @return  {Boolean}
 */
var is_notification = function() {
	"use strict";
	return $('#notification').hasClass('active');
};


/**
 * Just display a notification on the desktop
 *
 * @param   {string}  title    the title
 * @param   {string}  message  the message
 *
 * @return  {void}
 */
var notify = function ( title , message ) {
	"use strict";
	if ( 'webkitNotifications' in window ) {
		var havePermission = window.webkitNotifications.checkPermission();
		if ( havePermission === 0 ) {
			notification_class = 'success';
			set_notification();
			if ( ( notification === true ) && ( title !== undefined ) && ( notification_displayed === false ) ) {
				notification_displayed = true;
				var noti = window.webkitNotifications.createNotification(
					'img/icon72.png', title , message
				);
				noti.onclick = function () {
					window.focus();
					noti.close();
				};
				noti.onclose = function() {
					notification_displayed = false;
				};
				noti.show();
				setTimeout(	function(){try {noti.close();}catch(e){}} , 5000 );
			}
		}
		else if ( havePermission === 2 ) {
			notification_class = 'danger';
			set_notification();
		}
		else {
			notification_class = 'warning';
			set_notification();

			window.webkitNotifications.requestPermission(function() {
				notify( title , message );
			});
		}
	}
	else if ( 'Notification' in window ) {
		if ( window.Notification.permission === 'default') {
			notification_class = 'warning';
			set_notification();

			window.Notification.requestPermission(function () {
				notify( title , message );
			});
		}
		else if ( window.Notification.permission === 'granted') {
			notification_class = 'success';
			set_notification();
			if ( ( notification === true ) && ( title !== undefined ) && ( notification_displayed === false ) ) {
				notification_displayed = true;
				var n = new window.Notification( title , { 'body': message , 'tag' : 'Pimp My Log' } );
				n.onclick = function () {
					this.close();
				};
				n.onclose = function () {
					notification_displayed = false;
				};
			}
		}
		else if ( window.Notification.permission === 'denied') {
			notification_class = 'danger';
			set_notification();
			return;
		}
	}
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
 * Cut a type and returns an object with set values
 *
 * type_parser( 'date:H:i:s' )     => parser = date, param = 'H:i:s' , cut = -1
 * type_parser( 'date:H:i/s/100' ) => parser = date, param = 'H:i/s' , cut = 100
 * type_parser( 'date' )           => parser = date, param = ''      , cut = -1
 * type_parser( )                  => parser = txt,  param = ''      , cut = -1
 *
 * @param   {string}  type  the type
 *
 * @return  {object}        the well-formatted object with parser,param,cut keys
 */
var type_parser = function( type ) {
	"use strict";
	var parser = 'txt';
	var param  = '';
	var cut    = 0;
	if ( type !== undefined ) {
		var a = type.split( '/' );
		if ( a[1] !== undefined ) {
			cut    = parseInt( type.split( '/' ).slice(-1) , 10 );
			parser = type.split( '/' ).slice(0,-1).join( '/' );
		} else {
			parser = type;
		}
		a = parser.split( ':' );
		if ( a[1] !== undefined ) {
			param  = parser.split( ':' ).slice(1).join( ':' );
			parser = a[0];
		}
	}
	return { 'parser' : parser , 'param' : param , 'cut' : cut };
};



/**
 * Cut val at cut chars and add &hellip; if val is really cutted
 * if cut > 0 : nianian...
 * if cut < 0 : ...aniania
 *
 * @param   {string}   val  the value to cut
 * @param   {integer}  cut  the count of chars to keep
 *
 * @return  {string}        the cutted value
 */
var val_cut = function( val , cut ) {
	"use strict";
	if ( cut === undefined ) {
		return val;
	}
	if ( cut === 0 ) {
		return val;
	}
	if ( val.length <= Math.abs(cut) ) {
		return val;
	}
	if ( cut > 0 ) {
		return val.substr( 0 , cut ) + '&hellip;';
	}
	else {
		return '&hellip;' + val.substr( cut );
	}
};


/**
 * Sort column according to field in right direction
 *
 * @param   {string}  field      the name of the column
 * @param   {integer} direction  the direction 1 ascending or -1 descending
 *
 * @return  {void}
 */
function s( field , direction ) {
	"use strict";
	sort  = field;
	sorto = direction;
	reload_page( true );
	get_logs( false , true );
}


/**
 * Return the offset in bytes in the log file of the oldest displayed log line
 *
 * @return  {integer}  the offset in bytes
 */
function get_top_offset() {
	return parseInt( $('#logsbody').find( 'tr:last-child' ).data('offset') , 10);
}


/**
 * Ajax call to get logs
 *
 * @param   {boolean}  load_default_values  If set to true, the ajax will use default values for the selected file if there are available
 * @param   {boolean}  load_full_file       If set to true, the log file will be parsed without keeping history. It is a slow process but mandatory when search of file have changed.
 * @param   {boolean}  load_from_get        If set to true, the GET parameters will override default values. This is used for the first launch for example.
 * @param   {boolean}  load_more            If set to true, we will load more logs append to the bottom of the table
 *
 * @return  {void}
 */
var get_logs     = function( load_default_values , load_full_file , load_from_get , load_more ) {
	"use strict";

	var wanted_lines;

	// Disable load more button
	$('.loadmore').button('loading');

	// Auto refresh stop
	if ( auto_refresh_timer !== null ) {
		clearTimeout( auto_refresh_timer );
		auto_refresh_timer = null;
	}

	// Load default values from file
	if ( load_default_values === true ) {

		// Load parameters from the query string
		if ( load_from_get === true ) {

			var found;

			// Notification
			if ( query_parameters.n === 'true' ) {
				set_notification( true );
			} else if ( query_parameters.n === 'false' ) {
				set_notification( false );
			} else {
				set_notification( files[file].notify );
			}

			// Max
			found = files[file].max;
			if ( typeof query_parameters.m !== undefined ) {
				$('#max option').each(function(){
					if ( this.value === query_parameters.m ) {
						found = query_parameters.m;
					}
				});
			}
			set_max( found );

			// Auto-refresh
			found = files[file].refresh;
			if ( typeof query_parameters.r !== undefined ) {
				$('#autorefresh option').each(function(){
					if ( this.value === query_parameters.r ) {
						found = query_parameters.r;
					}
				});
			}
			set_auto_refresh( found );

			// Displayed columns
			if ( query_parameters.t !== undefined ) {
				set_columns( query_parameters.t.split(',') );
			} else {
				set_columns( files[file].thinit );
			}

			// Sorting
			if ( query_parameters.o !== undefined ) {
				sort = query_parameters.o;
			} else {
				sort = files[file].sort;
			}

			if ( query_parameters.p !== undefined ) {
				sorto = query_parameters.p;
			} else {
				sorto = files[file].order;
			}
		}
		else {
			set_max( files[file].max );
			set_auto_refresh( files[file].refresh );
			set_notification( files[file].notify );
			set_columns( files[file].thinit );
			sort = files[file].sort;
		}
		// Manage the export button
		if ( files[file].export === false ) {
			$('#export').hide();
			$('#noexport').show();
		}
		else if ( ( export_default === false ) && ( ! files[file].export ) ) {
			$('#export').hide();
			$('#noexport').show();
		}
		else {
			$('#noexport').hide();
			$('#export').show();
		}

		load_full_file = true;
	}
	else {
		load_default_values = false;
	}

	// Set new paremeters in the url
	reload_page( true );

	// Load full logs and not increment
	if ( load_full_file === true ) {
		reset           = 1;
		file_size       = 0;
		load_more       = false;
		last_line       = '';
		has_loaded_more = false;
	}
	else {
		reset     = 0;
		load_full_file = false;
	}

	$('.loader').toggle();
	loading      = true;
	wanted_lines = $('#max').val();

	var post_values = {
			'ldv'         : load_default_values,
			'file'        : file,
			'filesize'    : file_size,
			'max'         : wanted_lines,
			'search'      : $('#search').val(),
			'csrf_token'  : csrf_token,
			'lastline'    : last_line,
			'reset'       : reset,
	};

	// We must read more logs
	if ( load_more === true ) {

		// set the offset to the last parsed byte to tell ajax where to begin
		post_values['sp'] = get_top_offset();

		// We will never clear logs again and we will display all logs on page until a full reset
		has_loaded_more   = true;
	}

	// Go for ajaxing
	$.ajax( {
		url      : 'inc/getlog.pml.php?' + (new Date()).getTime() + '&' + querystring,
		data     : post_values,
		type     : 'POST',
		dataType : 'json'
	} )
	.fail( function ( logs ) {
		// Layout
		$('.loader').toggle();
		loading = false;

		if ( logs.responseText.indexOf( 'Pimp My Log Login Match' ) > -1 ) {
			notify( "Pimp my Logs [" + files[file].display + "]" , lemma.youhavebeendisconnected );
			document.location.reload();
			return;
		}

		// Error
		if ( logs.error ) {
			$(".result").hide();
			$("#error").show();
			$('#errortxt').html( logs.responseText );
			notify( "Pimp my Logs [" + files[file].display + "]" , lemma.error );
			return;
		}

	})
	.done( function ( logs ) {

		// Layout
		$('.loader').toggle();
		loading   = false;

		// The last line is not sent when we load more logs
		// It is only sent when we are on the bottom of the log file
		if ( logs.lastline ) {
			last_line = logs.lastline;
			file_size = logs.newfilesize;
		}

		// Error
		if ( logs.error ) {
			$(".result").hide();
			$("#error").show();
			$('#errortxt').html( logs.error );
			notify( notification_title.replace( '%i' , file ).replace( '%f' , files[file].display ) , lemma.error );
			return;
		}

		// PHP Internal notices
		if ( logs.warning ) {
			pml_alert( logs.warning , 'warning' );
		}
		if ( logs.notice ) {
			pml_alert( logs.notice , 'info' );
		}
		if ( logs.singlewarning ) {
			pml_singlealert( logs.singlewarning , 'warning' );
		}
		if ( logs.singlenotice ) {
			pml_singlealert( logs.singlenotice , 'info' );
		}

		// Render
		$( '#error' ).hide();
		$( '.result' ).show();

		// No log message
		if ( logs.full ) {
			if ( logs.found === false ) {
				var nolog = lemma.no_log;
				if ( logs.search !== '' ) {
					if ( logs.regsearch ) {
						nolog = lemma.search_no_regex.replace( '%s' , '<code>' + logs.search + '</code>' );
					} else {
						nolog = lemma.search_no_regular.replace( '%s' , '<code>' + logs.search + '</code>' );
					}
				}
				$( '#nolog' ).html( nolog ).show();
				$( '#logshead' ).hide();
			}
			else {
				$( '#nolog' ).text( '' ).hide();
				$( '#logshead' ).show();
			}
		}
		else {
			if ( logs.logs ) {
				$( '#nolog' ).text( '' ).hide();
				$( '#logshead' ).show();
			}
		}

		// Search regex understood ?
		if ( logs.regsearch ) {
			$( '#searchctn' ).addClass('has-success');
			$( '#searchctn' ).prop( 'title' , lemma.regex_valid );
		} else {
			$( '#searchctn' ).removeClass('has-success');
			$( '#searchctn' ).prop( 'title' , lemma.regex_invalid );
		}

		// Header
		if ( logs.headers ) {
			$( '#logshead' ).text( '' );
			$( '.thmenucol' ).remove();

			var thtr = $('<tr>').addClass( file );
			sorto = ( parseInt( sorto , 10 ) === 1 ) ? 1 : -1;
			var sortn = -1;
			for ( var h in logs.headers ) {

				$('.thmenuicon').removeClass( 'text-danger' );

				var ic = '';
				if ( sort === h ) {
					sortn = sorto * -1;
					var q = ( sorto === 1 ) ? 'up' : 'down';
					ic    = '&nbsp;<span class="glyphicon glyphicon-chevron-' + q + '"></span>';
				}
				var a = $( '<th style="white-space:nowrap;"><a href=\'javascript:s("' + h + '",' + sortn + ')\'>' + logs.headers[ h ] + ic + '</a></th>' ).addClass( h ).appendTo( thtr );

				var f;
				if ( is_column_displayed( h ) ) {
					f = 'on';
				} else {
					$(a).hide();
					f = 'off';
				}
				$( '<li class="thmenucol"><a href="#" class="btn btn-default thmenuitem thmenu' + f + '" data-h="' + h + '" title="' + lemma.toggle_column.replace( '%s' , logs.headers[ h ] ) + '">' + logs.headers[ h ] + '</a></li>' ).appendTo( '.thmenu' );
			}
			thtr.appendTo( '#logshead' );
			set_column_icon();

			// Refresh th menu buttons when click
			$('.thmenuitem').click(function(e) {
				e.stopPropagation(); // Do not close the dropdown
				if ( $(this).hasClass('thmenuon') ) {
					remove_column( $(this).attr('data-h') );
				}
				else {
					add_column( $(this).attr('data-h') );
				}
			});
		}


		// Body
		if ( logs.full ) {
			$( '#logsbody' ).text( '' );
		}

		// New logs are available so remove the previous new log let marker
		if ( logs.logs !== undefined ) {
			$( '#logsbody tr' ).removeClass( 'newlog' );
		}

		var uaparser = new UAParser();
		var rowidx   = 0;
		var rows     = [];

		for ( var log in logs.logs ) {

			var tr = $('<tr>')
						.addClass( file )
						.data( 'log' , logs.logs[ log ].pml )
						.data( 'offset' , logs.logs[ log ].pmlo );

			for ( var c in logs.logs[ log ] ) {

				if ( ( 'pml' === c ) || ( 'pmlo' === c ) || ( 'pmld' === c ) ) {
					continue;
				}

				var type  = type_parser( files[file].format.types[ c ] );
				var val   = logs.logs[log][ c ];
				var title = val;

				// Prepare values
				if ( val === '-' ) {
					val = '';
				}

				if ( type.parser === 'uaw3c' ) {
					type.parser = 'ua';
					val = val.replace( /\+/g , ' ' );
				}

				// Parse values
				if ( 'badge' === type.parser ) {
					var clas;
					if ( type.param === 'http' ) {
						clas = badges[ type.param ][ logs.logs[log][ c ].substr( 0 , 1 ) ];
					} else if ( type.param === 'severity' ) {
						clas = badges[ type.param ][ logs.logs[log][ c ].toLowerCase() ];
						if ( clas === undefined ) {
							clas = badges[ type.param ][ logs.logs[log][ c ] ];
						}
					}
					if ( clas === undefined ) {
						clas = 'default';
					}
					val = '<span class="label label-' + clas + '">' + val_cut( val , type.cut ) + '</span>';
				}
				else if ( 'date' === type.parser ) {
					title = logs.logs[ log ].pml;
					val   = val_cut( val , type.cut );
				}
				else if ( 'numeral' === type.parser ) {
					if ( val !== '' ) {
						if ( type.param !== '' ) {
							val = numeral( val ).format( type.param );
						}
					}
				}
				else if ( 'ip' === type.parser ) {
					if ( type.param === 'geo' ) {
						val = '<a href="' + geoip_url.replace( "%p" , val ) + '" target="linkout">' + val_cut( val , type.cut ) + '</a>';
					} else {
						val = '<a href="' + type.param + '://' + val + '" target="linkout">' + val_cut( val , type.cut ) + '</a>';
					}
				}
				else if ( 'link' === type.parser ) {
					val = '<a href="' + val + '" target="linkout">' + val_cut( val , type.cut ) + '</a>';
				}
				else if ( 'ua' === type.parser ) {
					var ua  = uaparser.setUA( val ).getResult();
					var uas = type.param.match(/\{[a-zA-Z.]*\}/g);
					var uaf = false;
					for (var k in uas) {
						var d;
						try {
							/*jshint -W061 */
							d = eval( 'ua.' + uas[k].replace('{','').replace('}','') );
							if ( d === undefined ) {
								d = '';
							}
						} catch (e) {
							d = '';
						}
						if ( d !== '' ) {
							uaf        = true;
							type.param = type.param.replace( uas[k] , d );
						}
					}
					if ( uaf === true ) {
						val = $.trim( type.param );
					}
				}
				else if ( 'prefake' === type.parser ) {
					val = val_cut( val.replace(/(?:\r\n|\r|\n)/g,'<br/>') , type.cut );
				}
				else {
					val = val_cut( val , type.cut );
				}

				var b = $( '<td>&nbsp;' + val + '</td>' ).prop( "title" , title ).addClass( 'pml-' + c + " pml-" + type.parser ).appendTo( tr );
				if ( ! is_column_displayed( c ) ) $( b ).hide();
			}

			if ( ! logs.full ) {
				tr.addClass('newlog');
				rowidx++;
			}

			rows.push( tr );
		}

		// display all logs so append to bottom
		if ( ( logs.full ) || ( load_more === true ) ) {
			$('#logsbody').append( rows );
		}

		// display only new logs, so append to top
		else {
			$('#logsbody').prepend( rows );

			// Do not remove lines if user has clicked on Load More button at least one time
			if ( has_loaded_more !== true ) {
				var rowd = $('#logsbody tr').length;
				if ( rowd > wanted_lines ) {
					rowd = rowd - wanted_lines;
					$('#logsbody').find( 'tr:nth-last-child(-n+' + rowd + ')' ).remove();
				}
			}
		}

		// Load more button
		// We can call get_top_offset() now because rows have not been sorted and the older log line is the last one
		var older_line_offset = get_top_offset();
		if ( ( older_line_offset <= 1 ) || ( ( logs.search !== '' ) && ( parseInt( logs.lpo , 10 ) <= 1) ) ) {
			$('.loadmore').text( $('.loadmore').data('nomore-text') ).addClass('disabled').prop('disabled','disabled').attr('title','');
		} else {
			$('.loadmore').button('reset').attr( 'title' , sprintf( lemma.loadmore , numeral( older_line_offset ).format('0 b') ) );
		}

		// Sort table
		if ( sort !== undefined ) {
			// Find column
			var i   = 0,
				col = -1;
			$('#logshead tr th').each(function() {
				if ( $(this).hasClass( sort ) ) {
					col = i;
				}
				i++;
			});
			if ( col >= 0 ) {
				var tbody = document.getElementById( 'logsbody' ),
					trs   = Array.prototype.slice.call( tbody.rows , 0 );
				trs = trs.sort( function (a, b) {
					a = a.cells[col].getAttribute("title");
					b = b.cells[col].getAttribute("title");
					return ( $.isNumeric( a ) && $.isNumeric( b ) ) ? sorto * ( parseFloat( a ) - parseFloat( b ) ) : sorto * a.toLowerCase().localeCompare( b.toLowerCase() );
				});
				for( i = 0 ; i < trs.length; ++i ) {
					tbody.appendChild( trs[i] );
				}
			}
		}

		// Footer
		var rowct = '';
		var rowc  = $('#logsbody tr').length;
		if ( rowc === 1 ) {
			rowct = lemma.display_log + ' ';
		} else if ( rowc > 1 ) {
			rowct = lemma.display_nlogs.replace( '%s' , rowc ) + ' ';
		}
		$("#footer").html( rowct + logs.footer );

		// Notification
		if ( first_launch === false ) {
			if ( logs.full ) {
				if ( logs.fingerprint !== fingerprint ) {
					notify( notification_title.replace( '%i' , file ).replace( '%f' , files[file].display ) , lemma.new_logs );
					fingerprint = logs.fingerprint;
				}
			} else {
				if ( rowidx === 1 ) {
					notify( notification_title.replace( '%i' , file ).replace( '%f' , files[file].display ) , lemma.new_log );
				} else if ( rowidx > 1 ) {
					notify( notification_title.replace( '%i' , file ).replace( '%f' , files[file].display ) , lemma.new_nlogs.replace( '%s' , rowidx ) );
				}
			}
		}
		first_launch = false;

		// Auto refresh go
		var p = Math.max( 0 , parseInt( $('#autorefresh').val() , 10 ) );
		if ( p > 0 ) {
			auto_refresh_timer = setTimeout( function() { get_logs(); } , p * 1000 );
		}

	} )
	.always( function () {
	});
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

