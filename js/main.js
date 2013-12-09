var file           = '';
var loading        = false;
var fingerprint    = '';
var notification;
var auto_refresh_timer;


/**
 * Just display a notification on the desktop
 * @return  {void}
 */
var notification_displayed = false;
var notify = function ( title , message ) {
	if ( 'webkitNotifications' in window ) {
		var havePermission = window.webkitNotifications.checkPermission();
		if (havePermission === 0) {
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
		else if (havePermission === 2) {
			notification_class = 'danger';
			set_notification();
		}
		else {
			notification_class = 'warning';
			set_notification();

			window.webkitNotifications.requestPermission(function (a) {
				notify( title , message );
			});
		}
	}
	else if ('Notification' in window) {
		if (Notification.permission === 'default') {
			notification_class = 'warning';
			set_notification();

			Notification.requestPermission(function () {
				notify( title , message );
			});
		}
		else if (Notification.permission === 'granted') {
			notification_class = 'success';
			set_notification();
			if ( ( notification === true ) && ( title !== undefined ) && ( notification_displayed === false ) ) {
				notification_displayed = true;
				var n = new Notification( title , { 'body': message , 'tag' : 'Pimp My Log' } );
				n.onclick = function () {
					this.close();
				};
				n.onclose = function () {
					notification_displayed = false;
				};
			}
		}
		else if (Notification.permission === 'denied') {
			notification_class = 'danger';
			set_notification();
			return;
		}
	}
};



var pml_alert = function( message , severity) {
	$('<div class="alert alert-' + severity + ' alert-dismissable fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + message + '</div>').appendTo("#notice");
};



/**
 * Ajax call to get logs
 *
 * @param   {boolean}  load_default_values  If set to true, the ajax will use default values for the selected file if there are available
 *
 * @return  {void}
 */
var first_launch = true;
var get_logs = function( load_default_values ) {

	if ( load_default_values === true ) {
		set_max( files[file].max );
		set_auto_refresh( files[file].refresh );
		set_notification( files[file].notify );
	}
	else {
		load_default_values = false;
	}

	$('.loader').toggle();
	loading = true;

	$.ajax( {
		url     : 'inc/getlog.pml.php' ,
		data    : {
			'ldv'        : load_default_values,
			'file'       : file,
			'max'        : $('#max').val(),
			'search'     : $('#search').val(),
			'csrf_token' : csrf_token,
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
			return;
		}
	})
	.done( function ( logs ) {

		// Layout
		$('.loader').toggle();
		loading = false;

		// Error
		if ( logs.error ) {
			$("#result").hide();
			$("#error").show();
			$('#errortxt').html( logs.error );
			return;
		}

		// PHP Internal notices
		if ( logs.warning )
			pml_alert( logs.warning , 'warning' );
		if ( logs.notice )
			pml_alert( logs.notice , 'notice' );

		// Render
		$("#error").hide();
		$("#result").show();
		$("#footer").html( logs.footer );
		$( "#logshead" ).text( '' );
		$( "#logsbody" ).text( '' );

		// No log
		if ( logs.found === false ) {
			$('#nolog').show();
		} else {
			$('#nolog').hide();
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
		var sort   = 'Date';
		var sortsc = 'down';
		for ( var h in logs.headers ) {
			var s = ( sort == h ) ? '<span class="glyphicon glyphicon-chevron-' + sortsc + '"/></span>' : '';
			$( "<th>" + logs.headers[ h ] + s + "</th>" ).addClass( h ).appendTo( '#logshead' );
		}

		var uaparser = new UAParser();

		for ( var log in logs.logs ) {

			var tr = $('<tr>');

			for ( var c in logs.logs[log] ) {

				var val           = logs.logs[log][ c ];
				var title         = val;
				var severityclass = '';
				if ( val == '-' ) {
					val = '';
				}
				if ( 'pml' == c ) {
					continue;
				}
				if ( 'Severity' == c ) {
					severityclass = severities[ logs.logs[log][ c ] ];
					if (severity_color_on_all_cols) {
						if ( severityclass !== '') {
							tr.addClass( severityclass );
						}
						severityclass = '';
					} else {
						if ( severityclass === '') {
							severityclass = 'default';
						}
						val = '<span class="label label-' + severityclass + '">' + val + '</span>';
					}
				}
				else if ( 'Code' == c ) {
					httpcodeclass = httpcodes[ logs.logs[log][ c ].substring(0,1) ];
					if ( ( httpcodeclass !== undefined ) && ( httpcodeclass !== '') ) {
						val = '<span class="label label-' + httpcodeclass + '">' + val + '</span>';
					}
				}
				else if ( 'UA' == c ) {
					var ua = uaparser.setUA(val).getResult();
					if (ua.os.name !== undefined) {
							val = ua.os.name;
							if (ua.os.version !== undefined ) {
									val += ' ' + ua.os.version;
							}
							val+= ' | ';
					}
					if (ua.browser.name !== undefined) {
							val+= ua.browser.name;
							if (ua.browser.version !== undefined ) {
									val += ' ' + ua.browser.version;
							}
					}
				}
				else if ( 'Size' == c ) {
					if ( val != '-' ) {
						val = numeral(val).format('0b');
					}
				}
				else if ( 'Date' == c ) {
					var tmp = val.split(' ');
					title = logs.logs[log].pml;
					val = '<span class="day">' + tmp[0] + '</span> <span class="time">' + tmp[1] + '</span>';
				}
				else if ( 'IP' == c ) {
					val = '<a href="' + geoip_url.replace( "%p" , val ) + '" target="geoip">' + val + '</a>';
				}
				else if ( 'Referer' == c ) {
					val = '<a href="' + val + '" target="referer">' + val + '</a>';
				}
				$( '<td>' + val + '</td>' ).prop( "title" , title ).addClass( severityclass + c ).appendTo( tr );
			}

			tr.appendTo('#logsbody');
		}

		// Notification
		if ( first_launch === false ) {
			if ( logs.fingerprint != fingerprint ) {
				notify( notification_title.replace( /%f/g , files[file].display ) , 'New logs !' );
				fingerprint = logs.fingerprint;
			}
		}
		first_launch = false;

		// Auto refresh
		if ( auto_refresh_timer !== null ) {
			clearTimeout( auto_refresh_timer );
			auto_refresh_timer = null;
		}
		var i = Math.max( 0 , parseInt( $('#autorefresh').val() , 10 ) );
		if ( i > 0 ) {
			auto_refresh_timer = setTimeout( function() { get_logs(); } , i * 1000 );
		}


	} )
	.always( function () {
	});
};


/**
 * Set the max selector to a given value
 *
 * @param  {string}  a  the value of the wanted selected option
 */
var set_auto_refresh = function( a ) {
	$('#autorefresh').val( a );
};


/**
 * Set the autorefresh selector to a given value
 *
 * @param  {string}  a  the value of the wanted selected option
 */
var set_max = function( a ) {
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
	return $('#notification').hasClass('active');
};


/**
 * Initialization
 *
 * @return  {void}
 */
$(function() {

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

	// Search input
	$( '#search' ).change( function() {
		$( '#search' ).blur();
		get_logs();
		if ( $('#search').val() === '') {
			$( '#searchreset' ).hide();
			$( '#search' ).removeClass( 'pmlinput' );
		} else {
			$( '#searchreset' ).show();
			$( '#search' ).addClass( 'pmlinput' );
		}
	});

	// Init Search reset button
	if ( $( '#search' ).val() === '') {
		$( '#searchreset' ).hide();
		$( '#search' ).removeClass( 'pmlinput' );
	} else {
		$( '#searchreset' ).show();
		$( '#search' ).addClass( 'pmlinput' );
	}

	// Click on the reset button
	$( '#searchreset' ).mouseup( function() {
		$( '#search' ).val('');
		$( '#search' ).change();
	});

	// Refresh hotkey
	$( '#search' ).keypress( function(e) {
		var keycode = (e.keyCode ? e.keyCode : e.which);
		if ( keycode == '13' ) {
			$( '#search' ).blur();
		}
	});

	// Autorefresh menu
	set_auto_refresh( logs_refresh_default );
	$('#autorefresh').change( function() {
		get_logs();
	});

	// Autorefresh menu
	set_max( logs_max_default );
	$('#max').change( function() {
		get_logs();
	});

	// Check for upgrade
	$.ajax( {
		url     : 'inc/upgrade.pml.php' ,
		dataType: 'json'
	} ).done( function ( upgrade ) {
		$( '#upgradefooter' ).html( ' - ' + upgrade.footer);
		var hide = $.cookie( 'upgrade' + upgrade.to );
		if ( hide !== 'hide' ) {
			$( '#upgrademessage' ).html( upgrade.alert );
			$( '#upgradestop' ).click( function() {
				$.cookie( 'upgrade' + $(this).data('version') , 'hide' );
				$("#upgradealert").alert('close');
			});
		}
	} );


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

});
