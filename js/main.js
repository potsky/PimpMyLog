/*global numeral,logs_refresh_default,logs_max_default,files,notification_title,badges,lemma,geoip_url,pull_to_refresh,csrf_token,querystring,notification_default, UAParser */
/*jshint unused:false*/

var file,
	notification,
	auto_refresh_timer,
	fingerprint,
	first_launch,
	file_size,
	last_line,
	loading,
	reset,
	notification_displayed = false;


/**
 * Set the max selector to a given value
 *
 * @param  {string}  a  the value of the wanted selected option
 */
var set_auto_refresh = function( a ) {
	"use strict";
	$('#autorefresh').val( a );
};


/**
 * Set the autorefresh selector to a given value
 *
 * @param  {string}  a  the value of the wanted selected option
 */
var set_max = function( a ) {
	"use strict";
	$('#max').val( a );
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
		$('#notification').removeClass('btn-warning btn-success btn-danger').addClass('active btn-' + notification_class );
		notification = true;
	} else {
		$('#notification').removeClass('btn-warning btn-success btn-danger active');
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
					'img/icon_072.png', title , message
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
 * Ajax call to get logs
 *
 * @param   {boolean}  load_default_values  If set to true, the ajax will use default values for the selected file if there are available
 * @param   {boolean}  load_full_file       If set to true, the log file will be parsed without keeping history. It is a slow process but mandatory when search of file have changed.
 *
 * @return  {void}
 */
var get_logs     = function( load_default_values , load_full_file ) {
	"use strict";

	var wanted_lines;

	// Auto refresh stop
	if ( auto_refresh_timer !== null ) {
		clearTimeout( auto_refresh_timer );
		auto_refresh_timer = null;
	}

	// Load default values from file
	if ( load_default_values === true ) {
		set_max( files[file].max );
		set_auto_refresh( files[file].refresh );
		set_notification( files[file].notify );
		load_full_file = true;
	}
	else {
		load_default_values = false;
	}

	// Load full logs and not increment
	if ( load_full_file === true ) {
		reset     = 1;
		file_size = 0;
		last_line = '';
	}
	else {
		reset     = 0;
		load_full_file = false;
	}

	$('.loader').toggle();
	loading      = true;
	wanted_lines = $('#max').val();
	$.ajax( {
		url     : 'inc/getlog.pml.php?' + (new Date()).getTime() + '&' + querystring,
		data    : {
			'ldv'         : load_default_values,
			'file'        : file,
			'filesize'    : file_size,
			'max'         : wanted_lines,
			'search'      : $('#search').val(),
			'csrf_token'  : csrf_token,
			'lastline'    : last_line,
			'reset'       : reset,
		} ,
		type: 'POST',
		dataType: 'json'
	} )
	.fail( function ( logs ) {

		// Layout
		$('.loader').toggle();
		loading = false;

		// Error
		if ( logs.error ) {
			$("#result").hide();
			$("#error").show();
			$('#errortxt').html( logs.responseText );
			notify( notification_title.replace( /%f/g , files[file].display ) , lemma.error );
			return;
		}

	})
	.done( function ( logs ) {

		// Layout
		$('.loader').toggle();
		loading   = false;
		file_size = logs.newfilesize;
		last_line = logs.lastline;

		// Error
		if ( logs.error ) {
			$("#result").hide();
			$("#error").show();
			$('#errortxt').html( logs.error );
			notify( notification_title.replace( /%f/g , files[file].display ) , lemma.error );
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
		$( '#result' ).show();

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
		$(function() {
			if ( logs.headers ) {
				$( '#logshead' ).text( '' );
				var tr     = $('<tr>').addClass( file );
				var sort   = 'Date';
				var sortsc = 'down';
				for ( var h in logs.headers ) {
//					if ( sort === h ) {
//						$( '<th><a class="glyphicon glyphicon-chevron-' + sortsc + '"/> ' + logs.headers[ h ] + ' </a></th>' ).addClass( h ).appendTo( tr );
//					} else {
						$( '<th>' + logs.headers[ h ] + '</th>' ).addClass( h ).appendTo( tr );
//					}
				}
				tr.appendTo( '#logshead' );
			}
		});

		// Body
		if ( logs.full ) {
			$( '#logsbody' ).text( '' );
		}
		$( '#logsbody tr' ).removeClass( 'newlog' );

		var uaparser = new UAParser();
		var rowidx   = 0;
		var rows     = [];

		for ( var log in logs.logs ) {

			var tr = $('<tr>').addClass( file ).data( 'log' , logs.logs[ log ].pml );

			for ( var c in logs.logs[ log ] ) {

				if ( 'pml' === c ) {
					continue;
				}

				var type  = type_parser( files[file].format.types[ c ] );
				var val   = logs.logs[log][ c ];
				var title = val;
				if ( val === '-' ) {
					val = '';
				}

				if ( 'badge' === type.parser ) {
					var clas;
					if ( type.param === 'http' ) {
						clas = badges[ type.param ][ logs.logs[log][ c ].substr( 0 , 1 ) ];
					} else if ( type.param === 'severity' ) {
						clas = badges[ type.param ][ logs.logs[log][ c ] ];
					}
					if ( clas === undefined ) clas = 'default';
					val = '<span class="label label-' + clas + '">' + val_cut( val , type.cut ) + '</span>';
				}
				else if ( 'date' === type.parser ) {
					title = logs.logs[ log ].pml;
					val   = '<div class="nozclip" style="position: relative;">' + val_cut( val , type.cut ) + '</div>';
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
					var ua  = uaparser.setUA(val).getResult();
					var uas = type.param.match(/\{[a-zA-Z.]*\}/g);
					var uaf = false;
					for (var k in uas) {
						var a;
						try {
							/*jshint -W061 */
							a = eval( 'ua.' + uas[k].replace('{','').replace('}','') );
							if ( a === undefined ) {
								a = '';
							}
						} catch (e) {
							a = '';
						}
						if ( a !== '' ) {
							uaf        = true;
							type.param = type.param.replace( uas[k] , a );
						}
					}
					if ( uaf === true ) {
						val = $.trim( type.param );
					}
				}
				else {
					val = val_cut( val , type.cut );
				}

				$( '<td>' + val + '</td>' ).prop( "title" , title ).addClass( 'pml-' + c + " pml-" + type.parser ).appendTo( tr );

			}

			if ( ! logs.full ) {
				tr.addClass('newlog');
				rowidx++;
			}

			rows.push( tr );
		}

		// display all logs so append to bottom
		if ( logs.full ) {
			$('#logsbody').append( rows );
		}
		// display only new logs, so append to top
		else {
			$('#logsbody').prepend( rows );
			var rowd = $('#logsbody tr').length;
			if ( rowd > wanted_lines ) {
				rowd = rowd - wanted_lines;
				$('#logsbody').find( 'tr:nth-last-child(-n+' + rowd + ')' ).remove();
			}
		}

		/* Copy to clipboard too slow
		$('#logsbody tr td div.nozclip').each(function() {
			$(this).removeClass('nozclip').zclip({
				path : 'js/ZeroClipboard.swf',
				copy : $(this).parent().attr('title')
			});
		});
		*/

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
					notify( notification_title.replace( /%f/g , files[file].display ) , lemma.new_logs );
					fingerprint = logs.fingerprint;
				}
			} else {
				if ( rowidx === 1 ) {
					notify( notification_title.replace( /%f/g , files[file].display ) , lemma.new_log );
				} else if ( rowidx > 1 ) {
					notify( notification_title.replace( /%f/g , files[file].display ) , lemma.new_nlogs.replace( '%s' , rowidx ) );
				}
			}
		}
		first_launch = false;

		// Auto refresh go
		var i = Math.max( 0 , parseInt( $('#autorefresh').val() , 10 ) );
		if ( i > 0 ) {
			auto_refresh_timer = setTimeout( function() { get_logs(); } , i * 1000 );
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

	// File selector bootstrap
	if ( file_selector === 'bs' ) {
		// File menu > init
		$('#file_selector').text( $('.file_menu:first').text() );
		$('.file_menu:first').parent().addClass('active');
		file = $('.file_menu:first').parent().data('file');

		// File Menu > handler
		$('.file_menu').click( function() {
			$('#file_selector').text( $(this).text() );
			$('.file_menu').parent().removeClass('active');
			$(this).parent().addClass('active');
			file = $(this).parent().data('file');
			get_logs( true );
		});
	}

	// File selector select html
	else {

		// File menu > init
		file = $('#file_selector_big').val();

		// File Menu > handler
		$('#file_selector_big').change( function() {
			file = $('#file_selector_big').val();
			get_logs( true );
		});
	}

	// Logo click
	$('.logo').click(function() {
		location.reload();
	});

	// Refresh button
	$('#refresh').click( function() {
		notify();
		get_logs();
	});

	// Refresh hotkey
	$(document).keypress( 'r' , function(e) {
        if ( $(e.target).is('input, textarea') ) {
            return;
        }
		notify();
		get_logs();
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
			event.preventDefault();
			return false;
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
	});

	notify();


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


	// Here we go
	get_logs( true );


	// Finally check for upgrade
	$.ajax( {
		url      : 'inc/upgrade.pml.php?' + (new Date()).getTime() + '&' + querystring,
		dataType : 'json',
		data     : { 'csrf_token' : csrf_token } ,
		type     : 'POST',
	} ).done( function ( upgrade ) {
		$( '#upgradefooter' ).html( ' - ' + upgrade.footer);
		var hide = $.cookie( 'upgradehide' );
		if ( hide !== upgrade.to ) {
			$( '#upgrademessage' ).html( upgrade.alert );
			$( '#upgradestop' ).click( function() {
				$.cookie( 'upgradehide' , $(this).data('version') );
				$("#upgradealert").alert('close');
			});
		}
	} );
});
