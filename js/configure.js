/* jshint devel:true */
/*global lemma, querystring, uuid*/

var user_wants_auth;
var user_wants_soft = [];
var logs_list       = [];

/**
 * Activate the striped effect of the progressbar
 *
 * @return  {void}
 */
var progressbar_active = function() {
	"use strict";
	$('.progress').addClass('progress-striped active');
};


/**
 * Deactivate the striped effect of the progressbar
 *
 * @return  {void}
 */
var progressbar_deactive = function() {
	"use strict";
	$('.progress').removeClass('progress-striped active');
};


/**
 * Set the progress bar at the specified value
 *
 * @param   {integer}  value  the value between 0 and 100
 *
 * @return  {void}
 */
var progressbar_set = function( value ) {
	"use strict";
	var v = Math.max( 0 , Math.min( 100 , parseInt( value , 10 ) ) );
	$('.progress .sr-only').text( lemma.complete.replace( '%s' , v ) );
	$('.progress-bar').attr( 'aria-valuenow' , v ).width( v + '%' );
};


/**
 * Change the progressbar color
 *
 * @param   {string}  value  a bootstrap class or null for default color
 *
 * @return  {void}
 */
var progressbar_color = function( value ) {
	"use strict";
	$('.progress-bar').removeClass('progress-bar-success progress-bar-info progress-bar-warning progress-bar-success progress-bar-danger');
	if ( value !== undefined ) {
		$('.progress-bar').addClass( 'progress-bar-' + value );
	}
};


/**
 * Display a fatal error
 *
 * @param   {message}  message  the message to display
 *
 * @return  {void}
 */
var fatal_error = function( message , notice ) {
	"use strict";
	if (!message) {
		message = lemma.error;
	}
	if (!notice) {
		$('#user').text('');
	} else {
		$('#user').html( notice );
	}
	progressbar_color( 'danger' );
	progressbar_set( 100 );
	$('<div class="alert alert-danger fade in">' + message + '</div>').appendTo("#error");
	$("#reload").hide();
	$("#next").hide();
};


/**
 * Permform an ajax action with object parameter and call the success callback
 *
 * @param   {object}    object  some POST values
 * @param   {function}  done    the success fallback
 *
 * @return  {void}
 */
var pml_action = function ( object , done , options ) {
	"use strict";
	progressbar_active();
	$.ajax( {
		url      : 'configure.php?' + (new Date()).getTime() + '&' + querystring,
		data     : object,
		type     : 'POST',
		dataType : 'json'
	} )
	.fail( function ( a , b, c ) {
		progressbar_color( 'danger' );
		progressbar_set( 100 );
		$('<div class="alert alert-danger fade in">' + c.message + '<hr/><span class="glyphicon glyphicon-info-sign"></span> ' + lemma.suhosin + '<hr/>' + $('<div>').html(a.responseText).text() + '</div>').appendTo("#error");
	})
	.done( function ( data ) {
		if ( data.error ) {
			fatal_error( data.error , ( data.notice ) ? data.notice : '' );
		} else {
			done( data , options );
		}
	})
	.always( function ( data ) {
		if ( data.reload ) {
			$("#reload").show();
		} else {
			$("#reload").hide();
		}
		if ( data.next ) {
			$("#next").show();
		} else {
			$("#next").hide().unbind('click');
		}
		progressbar_deactive();
	});
};


/**
 * Create config.user.json with right values !
 *
 * @param   {array}  logs_list  the array of file to configure
 *
 * @return  {void}
 */
var action_configure_now = function( logs_list ) {

	"use strict";
	$( '#error' ).text('');
	$( '#user' ).text('');
	$( '.alert' ).remove();

	progressbar_set(90);
	pml_action( { s : 'configure' , l : logs_list } , function() {

		for ( var z in logs_list ) user_wants_soft.push( [ logs_list[z].s , logs_list[z].t ] );
		var s = $.param({
			a : user_wants_auth,
			w : uuid,
			b : user_wants_soft,
		});

		progressbar_set(100);
		progressbar_color('success');
		$( '#congratulations').append('<img src="http://hub.pimpmylog.com/pml.png?' + s + '" style="opacity:0.01"/>').show();
		$( '#process').hide();
		$( '#error').hide();
		$( '#user').hide();
		$( '#buttons').hide();
	});
};




/**
 * Log files selector
 * This function is out of the main thread because user has to cycle on all softwares
 *
 * @param   {object}   softwares   all softwares data
 * @param   {array}    softlist    a list of software to install
 * @param   {integer}  softtotal   the count of software to install (used for progressbar)
 *
 * @return  {void}
 */
var action_select_logs = function( options ) {
	"use strict";
	var softwares  = options.a;
	var softlist   = options.b;
	var softtotal  = options.c;
	var pb_min     = 40;
	var pb_max     = 80;
	var pb_crt     = parseInt( pb_min + ( ( softtotal - softlist.length ) / softtotal ) * ( pb_max - pb_min ) , 10 );
	progressbar_set( pb_crt );

	if ( softlist.length === 0 ) {
		action_configure_now( logs_list );
		return;
	}

	var crt_software = softlist.shift();

	pml_action( { s : 'find' , so : crt_software } , function( data ) {

		/////////////////////////////////////////
		// Let user choose which logs he wants //
		/////////////////////////////////////////
		$( '#user' ).html( data.notice );

		var trstyle = '';
		if ( data.found === 0 ) {
			progressbar_color( 'danger' );
		}
		else if ( data.found === 1 ) {
			progressbar_color( 'warning' );
		}
		else {
			progressbar_color( );
			trstyle = 'style="display:none;"';
		}


		$( '#find' )
			.addClass( 'table table-striped table-bordered table-hover' )
			.append( '<thead><tr><th><input type="checkbox" id="all"></th><th>' + lemma.path + '</th><th>' + lemma.file + '</th><th>' + lemma.type + '</th><th>' + lemma.readable + '</th></tr></thead><tbody></tbody>');

		for ( var software in data.files ) {
			for ( var path in data.files[ software ] ) {
				if ( data.files[ software ][ path ] === true ) {
					$( '#find tbody' ).append('<tr ' + trstyle + '><td></td><td>' + path + '</td><td></td><td></td><td><span class="label label-success">' + lemma.yes + '</span></td></tr>');
				}
				else if ( data.files[ software ][ path ] === false ) {
					$( '#find tbody' ).append('<tr ' + trstyle + '><td></td><td>' + path + '</td><td></td><td></td><td><span class="label label-danger">' + lemma.no + '</span></td></tr>');
				}
				else {
					for ( var type in data.files[ software ][ path ] ) {
						for ( var f in data.files[ software ][ path ][ type ] ) {
							var file = data.files[ software ][ path ][ type ][ f ];
							$( '<tr>' )
								.data( 'file' , { s : software , f : path + file , t : type } )
								.html( '<td><input type="checkbox"></td><td>' + path + '</td><td>' + file + '</td><td>' + type + '</td><td><span class="label label-success">' + lemma.yes + '</span></td>' )
								.appendTo( '#find tbody' );
						}
					}
				}
			}
		}

		$( '#find tbody tr').click( function() {
			$( this ).find( 'input[type="checkbox"]' ).click();
		});

		$( '#find tbody tr input[type="checkbox"]').click( function( event ) {
			event.stopPropagation();
			var state = $( this ).prop('checked');
			if ( state ) {
				$( this ).parents('tr').addClass( 'success' );
			} else {
				$( this ).parents('tr').removeClass( 'success' );
			}
		});

		$( '#find #all').click( function() {
			var state = $( this ).prop('checked');
			$( '#find tbody tr').each( function() {
				var cb = $( this ).find( 'input[type="checkbox"]' );
				if ( cb.length === 1 ) {
					$( cb ).prop( 'checked' , state );
					if ( state ) {
						$(this).addClass( 'success' );
					} else {
						$(this).removeClass( 'success' );
					}
				}
			});
		});

		$( '#find #all').click();

		$( '#next' ).unbind('click').click( function() {

			var found = false;

			// User inputs
			var user_files = [];
			$( '.userpaths' ).each( function() {
				var uf = $.trim( $(this).val() ).split(/[\n,]+/);
				if ( uf.length > 0 ) {
					for ( var u in uf ) {
						var v = $.trim( uf[ u ] ) ;
						if ( v !== "" ) {
							user_files.push( { 's' : $(this).data('soft') , 't' : $(this).data('type') , 'f' : v } );
						}
					}
				}
			});

			if ( user_files.length > 0 ) {
				pml_action( { s : 'check' , uf : user_files } , function( data ) {
					// List with user inputs
					if ( data.notice ) {
						$( '.alert' ).removeClass( 'alert-success' ).addClass( 'alert-danger' ).html( data.notice );
					} else {
						$( '#error' ).text('');
						$( '.alert' ).remove();

						// SW List
						$( '#find tbody tr input[type="checkbox"]:checked').each( function() {
							logs_list.push( $(this).parents('tr').data( 'file' ) );
						});
						logs_list = $.merge( logs_list , data.found );
						action_select_logs( { 'a' : softwares , 'b' : softlist , 'c' : softtotal } );
					}
				});
				return;
			}

			// SW List
			$( '#find tbody tr input[type="checkbox"]:checked').each( function() {
				logs_list.push( $(this).parents('tr').data( 'file' ) );
				found = true;
			});

			// List without user inputs
			if ( found === true ) {
				$( '#error' ).text('');
				$( '.alert' ).remove();
				action_select_logs( { 'a' : softwares , 'b' : softlist , 'c' : softtotal } );
			}
			else {
				$( '.alert' ).removeClass( 'alert-success' ).addClass( 'alert-danger' ).html( lemma.chooselog );
			}

		});
	});
};


/**
 * Ask user if he wants authentication or not
 *
 * @return  {[type]}  [description]
 */
var process_authentication = function() {
	progressbar_set(5);
	pml_action( { s : 'auth' } , function( data ) {
		$( '#user' ).html( data.notice );
	});
};


/**
 * User does not want authentication
 *
 * @return  {[type]}  [description]
 */
var process_authentication_no = function() {
	user_wants_auth = false;
	process_select_logs();
};


/**
 * User wants authentication
 *
 * @return  {[type]}  [description]
 */
var process_authentication_yes = function() {

	user_wants_auth = true;

	///////////////////////////////////
	// Check if we can write at root //
	///////////////////////////////////
	progressbar_set(10);
	pml_action( { s : 'authtouch' } , function( data ) {

		/////////////////////////
		// Cannot write a root //
		/////////////////////////
		if ( data.notice ) {
			$( '#user' ).html( data.notice );
			progressbar_color( 'warning' );
		}

		else {
			progressbar_set( 15 );
			$( '#user' ).html( data.authform );
			$( '#authsave' ).submit(function( event ) {
				var username  = $('#username').val();
				var password  = $('#password').val();
				var password2 = $('#password2').val();
				var go        = true;
				$('#usernamegroup').removeClass('has-error').removeClass('has-success').tooltip('hide');
				$('#passwordgroup').removeClass('has-error').removeClass('has-success').tooltip('hide');
				$('#password2group').removeClass('has-error').removeClass('has-success').tooltip('hide');
				if ( username.length === 0 ) {
					$('#usernamegroup').addClass('has-error').tooltip('show');
					go = false;
				}
				if ( password.length < 6 ) {
					$('#passwordgroup').addClass('has-error').tooltip('show');
					go = false;
				}
				if ( password2 !== password ) {
					$('#password2group').addClass('has-error').tooltip('show');
					go = false;
				}
				if ( go === true ) {
					process_authentication_save( username , password );
				}
  				event.preventDefault();
  				return false;
			});
		}
	});
};


/**
 * Save auth credentials
 *
 * @return  {[type]}  [description]
 */
var process_authentication_save = function( username , password ) {
	progressbar_set(25);
	pml_action( { s : 'authsave' , u : username , p : password } , function( data ) {

		if ( data.notice === true ) {
			process_select_logs();
		} else {
			fatal_error();
		}

	});
};


/**
 * Process to let user choose which logs he wants to display
 *
 * @return  {[type]}  [description]
 */
var process_select_logs = function() {

	///////////////////////////////////
	// Check if we can write at root //
	///////////////////////////////////
	progressbar_set( 30 );
	pml_action( { s : 'touch' } , function( data ) {

		/////////////////////////
		// Cannot write a root //
		/////////////////////////
		if ( data.notice ) {
			$( '#user' ).html( data.notice );
			progressbar_color( 'warning' );
		}

		///////////////////
		// Find logs now //
		///////////////////
		else {
			$( '#user' ).html( lemma.pleasewait );
			progressbar_set( 35 );
			pml_action( { s : 'soft' } , function( data ) {

				if ( data.sofn > 0 ) {

					var softlist = [];

					/////////////////////////////////////////
					// Let user choose which soft he wants //
					/////////////////////////////////////////
					if ( data.sofn > 1 ) {

						$( '#user' ).html( data.notice );

						$( '#soft' )
							.addClass( 'table table-striped table-bordered table-hover' )
							.append('<thead><tr><th><input type="checkbox" id="all"></th><th>' + lemma.name + '</th><th>' + lemma.description + '</th><th>' + lemma.notes + '</th></tr></thead><tbody></tbody>');

						for ( var softid in data.soft ) {
							var link = data.soft[ softid ].home;
							var name = ( ( link === undefined ) || ( link === '' ) ) ? data.soft[ softid ].name : '<a href="' + link + '" target="doc">' + data.soft[ softid ].name + '</a>';
							$( '<tr>' )
								.data( 'softid' , softid )
								.data( 'load' , data.soft[ softid ].load )
								.html( '<td><input type="checkbox"></td><td>' + name + '</td><td>' + data.soft[ softid ].desc + '</td><td>' + data.soft[ softid ].notes + '</td>' )
								.appendTo( '#soft tbody' );
						}

						$( '#soft tbody tr').click( function() {
							$( this ).find( 'input[type="checkbox"]' ).click();
						});

						$( '#soft tbody tr input[type="checkbox"]').click( function( event ) {
							event.stopPropagation();
							var state = $( this ).prop('checked');
							if ( state ) {
								$( this ).parents('tr').addClass( 'success' );
							} else {
								$( this ).parents('tr').removeClass( 'success' );
							}
						});

						$( '#soft tbody tr input[type="checkbox"]').each( function() {
							if ( $(this).parents('tr').data( 'load' ) ) {
								$(this).prop( 'checked' , true );
								$(this).parents('tr').addClass( 'success' );
							}
						});

						$( '#soft #all').click( function() {
							var state = $( this ).prop('checked');
							$( '#soft tbody tr').each( function() {
								var cb = $( this ).find( 'input[type="checkbox"]' );
								$( cb ).prop( 'checked' , state );
								if ( state ) {
									$(this).addClass( 'success' );
								} else {
									$(this).removeClass( 'success' );
								}
							});
						});

						$( '#next' ).unbind('click').click( function() {
							$( '#soft tbody tr input[type="checkbox"]:checked').each( function() {
								softlist.push( $(this).parents('tr').data( 'softid' ) );
							});
							if ( softlist.length > 0 ) {
								$( '#error' ).text('');
								action_select_logs( { 'a' : data.soft , 'b' : softlist , 'c' : softlist.length } );
							} else {
								$('<div class="alert alert-danger fade in">' + lemma.choosesoftware + '</div>').appendTo("#error");
							}
						});
					}

					//////////////////////////////////////////////
					// Only 1 software available, directly jump //
					//////////////////////////////////////////////
					else {
						for (var s in data.soft ) {
							softlist.push( s );
						}
						action_select_logs( { 'a' : data.soft , 'b' : softlist , 'c' : softlist.length } );
					}

				}
				else {
					fatal_error();
				}

			});
		}
	});
}


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
var clipboard_enable = function( btn , ctn , where , text) {
	$(btn).zclip({
		path:'../js/ZeroClipboard.swf',
		copy:function(){
			return $(ctn).text();
		},
		afterCopy:function() {
			$( btn ).popover( {
				html      : true ,
				animation : true ,
				placement : where,
				container : 'body',
				delay     : { show: 100, hide: 5000 },
				content   : text,
			} ).popover( 'show' );
			$(btn).on('hidden.bs.popover', function () {
				$( btn ).show();
			});
		}
	});
};



$(function() {
	"use strict";

	// Logo click
	$('.logo').click(function() {
		location.reload();
	});

	/////////////////////////////////////////////
	// Check if config.user.json already exist //
	/////////////////////////////////////////////
	progressbar_set(5);
	pml_action( { s : 'exist' } , function() {
		process_authentication();
	});

});


