var file           = '';
var loading        = false;
var file_last_time = 0;
var notification;
var auto_refresh_timer;


/**
 * Safari notifications
 * Just display a notification on the desktop
 *
 * @return  {void}
 */
var notify = function ( title , message ) {
	if (!'Notification' in window) {
		return;
	}
	if (notification === false) {
		return;
	}
	if (Notification.permission === 'default') {
		Notification.requestPermission(function () {
			notify();
		});
	}
	else if (Notification.permission === 'granted') {
		var n = new Notification( title , { 'body': message , 'tag' : 'phpapachelogviewer' } );
		n.onclick = function () {
			this.close();
		};
		n.onclose = function () {
		};
	}
	else if (Notification.permission === 'denied') {
		return;
	}
};


/**
 *
 *
 * @return  {void}
 */

/**
 * Ajax call to get logs
 *
 * @param   {boolean}  load_default_values  If set to true, the ajax will use default values for the selected file if there are available
 *
 * @return  {void}
 */
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
		url     : 'inc/get_logs.php' ,
		data    : {
			'ldv'  : load_default_values,
			'flt'  : file_last_time,
			'file' : file,
			'max'  : $('#max').val()
		} ,
		dataType: 'json'
	} )
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

		// Compute
		$("#error").hide();
		$("#result").show();
		$("#compute").text( logs.duration );

		// Render
		console.log(logs);

		$( "#logshead" ).text( '' );
		$( "#logsbody" ).text( '' );

		// header
		for ( var h in logs.headers ) {
			$( "<th>" + logs.headers[ h ] + "</th>" ).addClass( h ).appendTo( '#logshead' );
		}

		for ( var log in logs.logs ) {

			var tr = $('<tr>');

			for ( var c in logs.logs[log] ) {

				var val   = logs.logs[log][ c ];
				var title = val;

				if ( 'Severity' == c ) {
					var s = severities[ logs.logs[log][ c ] ];
					if ( s !== '' ) {
						tr.addClass( s );
					}
				}
				else if ( 'Date' == c ) {
					var tmp = val.split(' ');
					val = '<span class="day">' + tmp[0] + '</span> <span class="time">' + tmp[1] + '</span>';
				}

				$( "<td title=" + title + ">" + val + "</td>" ).addClass( c ).appendTo( tr );
			}

			tr.appendTo('#logsbody');
		}

		// File has changed
		var file_has_changed = (file_last_time == logs.file_last_time);
		file_last_time = logs.file_last_time;

		// Notification
		notify( notification_title.replace( /%f/g , files[file].display ) , 'New log !' );

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
var set_notification = function( a ) {
	if ( a === true ) {
		$('#notification').addClass('active btn-info');
		notification = true;
	} else {
		$('#notification').removeClass('active btn-info');
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

	// Refresh button
	$('#refresh').click( function() {
		get_logs();
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

	// Notification > init
	if ( 'Notification' in window ) {
		$('#notification').show();
		set_notification( notification_default );
	}
	$('#notification').click( function() {
		set_notification( ! is_notification() );
	});

	// Pull to refresh
	if ( pull_to_refresh === true ) {
		$('#hook').hook({
			dynamic: false,
			reloadPage: false,
			reloadEl: function(){
				get_logs();
			}
		});
	}

	// Here we go
	get_logs( true );

});

