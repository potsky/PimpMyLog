/**
 * Initialization
 *
 * @return  {void}
 */
$(function() {
	"use strict";

	/*
	|--------------------------------------------------------------------------
	| Change Password
	|--------------------------------------------------------------------------
	|
	| Reset modal between two launches
	|
	*/
	$('#cpModal').on('show.bs.modal', function (e) {
		$('#cpErr').hide();
		$('#password1group').removeClass('has-error');
		$('#password2group').removeClass('has-error');
		$('#password3group').removeClass('has-error');
		$('#cpSave').button('reset');
		$('#password1').val('');
		$('#password2').val('');
		$('#password3').val('');
	});


	/*
	|--------------------------------------------------------------------------
	| Change Password
	|--------------------------------------------------------------------------
	|
	| On submit
	|
	*/
	$('#changepassword').on('submit', function () {

		$('#password1group').removeClass('has-error');
		$('#password2group').removeClass('has-error');
		$('#password3group').removeClass('has-error');
		$('#cpErr').hide();
		$('#cpSave').button('loading');

		$('#cpErr').hide();

		$.ajax( {
			url      : 'inc/users.pml.php?' + (new Date()).getTime() + '&' + querystring,
			type     : 'POST',
			dataType : 'json',
			data     : {
				'password1'  : $('#password1').val(),
				'password2'  : $('#password2').val(),
				'password3'  : $('#password3').val(),
				'csrf_token' : csrf_token,
				'action'     : 'change_password',
			}
		} )
		.always( function() {
			$('#cpSave').button('reset');
		})
		.fail( function ( e ) {
			$('#cpErrM').html( e.responseText ).show();
			$('#cpErr').show();
		})
		.done( function ( r ) {
			if ( r.ok ) {
				$('#notice').html( get_alert( 'success' , r.ok , true ) );
				$('#cpModal').modal( 'hide' );
			}
			else if ( r.errors ) {
				$( '#cpErrUl').html('');
				for ( var a in r.errors ) {
					$( '#cpErrUl').append( '<li>' + r.errors[a] + '</li>' );
				}
				for ( var a in r.fields ) {
					$( '#' + r.fields[a] + 'group' ).addClass('has-error');
				}
				$('#cpErr').show();
			}
		});

		event.preventDefault();
		return false;
	});


	/*
	|--------------------------------------------------------------------------
	| User Management
	|--------------------------------------------------------------------------
	|
	| Catch the modal opening and the tab change
	|
	*/
	$('#umModal').on('show.bs.modal', function (e) {
		users_load( $('#usermanagement div.tab-pane.active').attr('id') );
	});

	$('#usermanagement a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
		users_load( $(e.target).attr('href') );
	});

	$('.roles-user').click(function() {
		$(this).parent().find('label.roles-admin').removeClass('btn-danger').addClass('btn-default');
		$(this).parent().find('label.roles-user').addClass('btn-primary');
		$(this).parent().parent().parent().parent().find('.logs-selector').show();
	});

	$('.roles-admin').click(function() {
		$(this).parent().find('label.roles-user').removeClass('btn-primary').addClass('btn-default');
		$(this).parent().find('label.roles-admin').addClass('btn-danger');
		$(this).parent().parent().parent().parent().find('.logs-selector').hide();
	});

	$('.logs-selector-yes').click(function() {
		$(this).parent().find('label.logs-selector-no').removeClass('btn-danger').addClass('btn-default');
		$(this).parent().find('label.logs-selector-yes').addClass('btn-success');
	});

	$('.logs-selector-no').click(function() {
		$(this).parent().find('label.logs-selector-yes').removeClass('btn-success').addClass('btn-default');
		$(this).parent().find('label.logs-selector-no').addClass('btn-danger');
	});
});



/**
 * Get a bootstrap alert
 *
 * @param   {string}  severity  severiry
 * @param   {string}  text      text to display
 * @param   {boolean} close     whether a close button is displayed or not
 *
 * @return  {string}            the HTML alert
 */
function get_alert( severity , text , close ) {
	var a = '<div class="alert alert-' + severity + '" role="alert">';
	if ( close === true ) a += '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>';
	a += text;
	a += '</div>';
	return a;
}


/**
 * Load the wanted action
 *
 * @param   {string}  type  an action token
 *
 * @return  {void}
 */
var users_load = function( type ) {
	switch (type.replace(/#/g,'') ) {
		case 'umUsers':
			users_list();
			break;
		case 'umLogFiles':
			users_logfiles();
			break;
		case 'umAuthLog':
			users_authlog();
			break;
		default:
			console.log( 'Oups ! User action ' + type + ' is unknown !' );
			break;
	}
};


/**
 * List users
 *
 * @return  {boolean}  false
 */
var users_list = function() {
	$('#umUsersListBody').html('<img src="img/loader.gif"/>');
	$('#umUsersList').show();
	$('#umUsersView').hide();
	$('#umUsersEdit').hide();
	$('#umUsersAdd').hide();

	$.ajax( {
		url      : 'inc/users.pml.php?' + (new Date()).getTime() + '&' + querystring,
		type     : 'POST',
		dataType : 'json',
		data     : {
			'csrf_token' : csrf_token,
			'action'     : 'users_list',
		}
	} )
	.always( function() {
	})
	.fail( function ( e ) {
		$('#umUsersListBody').html( get_alert( 'danger' , e.responseText , false ) );
	})
	.done( function ( re ) {
		var r = '';
		var l = re.b.length;

		r += '<div class="row">';
		r += 	'<div class="col-sm-6"><p class="lead">' + l + ' ';
		r += ( l > 1 ) ? lemma.users : lemma.user;
		r += 	'</p></div>';
		r += 	'<div class="col-sm-6 text-right">';
		r +=		'<a href="#" onclick="users_add()" class="btn btn-xs btn-primary">' + lemma.adduser + '</a>';
		r += 	'</div>';
		r += '</div>';

		r += '<table class="table table-striped table-hover">';
		r += 	'<thead>';
		r += 		'<tr>';
		r += 			'<th>' + lemma.username + '</th>';
		r += 			'<th>' + lemma.roles + '</th>';
		r += 			'<th>' + lemma.creationdate + '</th>';
		r += 			'<th>' + lemma.lastlogin + '</th>';
		r += 		'</tr>';
		r += 	'</thead>';
		r += 	'<tbody>';
		for ( var i in re.b ) {
			var user       = re.b[i];
			var username   = user['u'];
			var roles      = user['roles'];
			var cd         = user['cd'];
			var logs       = user['logs'];
			var lastlogin  = user['lastlogin'];

			var rolelist = '';
			for( var j in roles ) {
				switch ( roles[j] ) {
					case 'admin':
						rolelist += '<span class="label label-danger">' + roles[j] + '</span>'
						break;
					case 'user':
						rolelist += '<span class="label label-primary">' + roles[j] + '</span>'
						break;
					default:
						rolelist += '<span class="label label-default">' + roles[j] + '</span>'
						break;
				}
			}

			if ( lastlogin ) {
				lastlogin = lastlogin['ts'];
			}

			r += '<tr>';
			r += 	'<td><a href="#" onclick="users_view(this)">' + username + '</a></td>';
			r += 	'<td>' + rolelist + '</td>';
			r += 	'<td>' + cd + '</td>';
			r += 	'<td>' + lastlogin + '</td>';
			r += '</tr>';

		}
		r += 	'</tbody>';
		r += '</table>';

		$('#umUsersListBody').html( r );
	});

	return false;
};


/**
 * View a user
 *
 * @return  {boolean}  false
 */
var users_view = function( obj ) {
	$('#umUsersViewBody').html('<img src="img/loader.gif"/>');
	$('#umUsersList').hide();
	$('#umUsersView').show();
	$('#umUsersEdit').hide();
	$('#umUsersAdd').hide();

	var username = ( $(obj).data('user') ) ? $(obj).data('user') : $(obj).text();

	$('#umUserEditBtn').data( 'user' , username ).show();

	if ( currentuser === username ) {
		$('#umUserEditBtn').attr('disabled','disabled');
	} else {
		$('#umUserEditBtn').removeAttr('disabled');
	}

	$.ajax( {
		url      : 'inc/users.pml.php?' + (new Date()).getTime() + '&' + querystring,
		type     : 'POST',
		dataType : 'json',
		data     : {
			'csrf_token' : csrf_token,
			'action'     : 'users_view',
			'u'          : username,
		}
	} )
	.always( function() {
	})
	.fail( function ( e ) {
		$('#umUsersViewBody').html( get_alert( 'danger' , e.responseText , false ) );
	})
	.done( function ( re ) {

		if ( re.e ) {
			$('#umUsersViewBody').html( get_alert( 'danger' , re.e , false ) );
			return false;
		}

		var r        = '';
		var uaparser = new UAParser();

		r += '<p class="lead">' + re.b['u'] + '</p>';
		r += '<table class="table table-striped table-hover">';
		r += 	'<tbody>';
		for ( var i in re.b ) {
			var val = re.b[i];
			if ( i === 'lastlogin' ) {
				var ua = uaparser.setUA( val['ua'] ).getResult();
				val    = val['ts'] + '<br/>' + val['ip'] + '<br/>' + ua.browser.name + ' ' + ua.browser.version + ' - ' + ua.os.name + ' ' + ua.os.version;
			}
			else if ( i === 'cb' ) {
				if ( ! val ) val = '<span class="label label-default">' + lemma.system + '</span>';
			}
			else if ( i === 'u' ) {
				continue;
			}
			else if ( i === 'roles' ) {
				var rolelist = '';
				for( var j in val ) {
					switch ( val[j] ) {
						case 'admin':
							rolelist += '<span class="label label-danger">' + val[j] + '</span>'
							break;
						case 'user':
							rolelist += '<span class="label label-primary">' + val[j] + '</span>'
							break;
						default:
							rolelist += '<span class="label label-default">' + val[j] + '</span>'
							break;
					}
				}
				val = rolelist;
			}
			r += '<tr>';
			r += '<th>' + lemma[ 'user_' + i ] + '</th>';
			r += '<td>' + val + '</td>';
			r += '</tr>';
		}
		r += 	'</tbody>';
		r += '</table>';

		$('#umUsersViewBody').html( r );
	});

	return false;
};


/**
 * Edit a user
 *
 * @return  {boolean}  false
 */
var users_add = function() {
	$('#umUsersList').hide();
	$('#umUsersView').hide();
	$('#umUsersEdit').hide();
	$('#umUsersAdd').show();
	return false;
};



/**
 * Edit a user
 *
 * @return  {boolean}  false
 */
var users_edit = function( obj ) {
	$('#umUsersEditBody').html('<img src="img/loader.gif"/>');
	$('#umUsersList').hide();
	$('#umUsersView').hide();
	$('#umUsersEdit').show();
	$('#umUsersAdd').hide();

	var username = $(obj).data('user');
	$('#umUserViewBtn').data( 'user' , username );
	$('#umUserSaveBtn').data( 'user' , username );

console.log(username);

	$('#umUsersEditBody').html(username);

return false;

	$.ajax( {
		url      : 'inc/users.pml.php?' + (new Date()).getTime() + '&' + querystring,
		type     : 'POST',
		dataType : 'json',
		data     : {
			'csrf_token' : csrf_token,
			'action'     : 'users_edit',
			'u'          : username,
		}
	} )
	.always( function() {
	})
	.fail( function ( e ) {
		$('#umUsersEditBody').html( get_alert( 'danger' , e.responseText , false ) );
	})
	.done( function ( re ) {

		if ( re.e ) {
			$('#umUsersEditBody').html( get_alert( 'danger' , re.e , false ) );
			return false;
		}

		$('#umUsersEditBody').html( r );
	});

	return false;
};


var users_logfiles = function() {

};


/**
 * Display auth logs
 *
 * @return  {boolean}  false
 */
var users_authlog = function() {
	$('#umAuthLogBody').html('<img src="img/loader.gif"/>');
	$.ajax( {
		url      : 'inc/users.pml.php?' + (new Date()).getTime() + '&' + querystring,
		type     : 'POST',
		dataType : 'json',
		data     : {
			'csrf_token' : csrf_token,
			'action'     : 'authlog',
		}
	} )
	.always( function() {
	})
	.fail( function ( e ) {
		$('#umAuthLogBody').html( get_alert( 'danger' , e.responseText , false ) );
	})
	.done( function ( re ) {
		var l = re.b.length;
		var r = '';
		if ( l > 0 ) {
			var uaparser = new UAParser();
			r  = '<table class="table table-striped table-hover">';
			r += 	'<thead>';
			r += 		'<tr>';
			r += 			'<th>' + lemma.date + '</th>';
			r += 			'<th>' + lemma.action + '</th>';
			r += 			'<th>' + lemma.username + '</th>';
			r += 			'<th>' + lemma.ip + '</th>';
			r += 			'<th>' + lemma.useragent + '</th>';
			r += 		'</tr>';
			r += 	'</thead>';
			r += 	'<tbody>';
			for ( var i = 0 ; i < l ; i++ ) {
				var date     = re.b[i][2];
				var action   = re.b[i][0];
				var username = re.b[i][1];
				var ip       = re.b[i][3];
				var ua       = uaparser.setUA( re.b[i][4] ).getResult();
				ua           = ua.browser.name + ' ' + ua.browser.version + ' - ' + ua.os.name + ' ' + ua.os.version;

				switch ( action ) {
					case 'signin' :
						action = '<span class="label label-success">' + lemma.signin + '</span>';
						break;
					case 'signout' :
						action = '<span class="label label-default">' + lemma.signout + '</span>';
						break;
				}

				r += '<tr>';
				r += 	'<td>' + date + '</td>';
				r += 	'<td>' + action + '</td>';
				r += 	'<td>' + username + '</td>';
				r += 	'<td>' + ip + '</td>';
				r += 	'<td title="' + $('<div/>').text(re.b[i][4]).html() + '">' + ua + '</td>';
				r += '</tr>';

			}
			r += 	'</tbody>';
			r += '</table>';
		}
		else {
			r = get_alert( 'info' , lemma.authlogerror , false );
		}
		$('#umAuthLogBody').html( r );
	});
	return false;
};





