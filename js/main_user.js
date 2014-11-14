/**
 * Initialization
 *
 * @return  {void}
 */
$(function() {
	"use strict";

	/*
	|--------------------------------------------------------------------------
	| Profile
	|--------------------------------------------------------------------------
	|
	| Reset modal between two launches
	|
	*/
	$('#prModal').on('show.bs.modal', function (e) {
		profile_get();
	});

	/**
	 * Get user profile
	 *
	 * @return  {boolean}  false
	 */
	var profile_get = function( severity , message , close ) {

		if ( severity !== undefined ) {
			$('#prAlert').html( get_alert( severity , message , close ) );
		} else {
			$('#prAlert').html('');
		}

		$('#prBody').html('<img src="img/loader.gif"/>');

		$.ajax( {
			url      : 'inc/users.pml.php?' + (new Date()).getTime() + '&' + querystring,
			type     : 'POST',
			dataType : 'json',
			data     : {
				'csrf_token' : csrf_token,
				'action'     : 'profile_get',
			}
		} )
		.always( function() {
		})
		.fail( function ( e ) {
			$('#prBody').html( get_alert( 'danger' , e.responseText , false ) );
		})
		.done( function ( re ) {
			if ( re.singlewarning ) {
				$('#prBody').html( get_alert( 'warning' , re.singlewarning , false ) );
			}
			else if ( re.singlenotice ) {
				$('#prBody').html( get_alert( 'info' , re.singlenotice , false ) );
			}
			else if ( re.error ) {
				$('#prBody').html( get_alert( 'danger' , re.error , false ) );
			}
			else {
				$('#prBody').html( re.b );
			}
		});

		return false;
	};

	/*
	|--------------------------------------------------------------------------
	| Profile Save
	|--------------------------------------------------------------------------
	|
	| On submit
	|
	*/
	$('#prForm').on('submit', function() {
		profile_save();
		event.preventDefault();
		return false;
	});

	/**
	 * Save user profile
	 *
	 * @return  {boolean}  false
	 */
	var profile_save = function() {

		$('#prSave').button('loading');

		var values = {
			'csrf_token' : csrf_token,
			'action'     : 'profile_save'
		};
		$.each( $('#prForm').serializeArray(), function(i, field) {
			values[ field.name ] = field.value;
		});

		$.ajax( {
			url      : 'inc/users.pml.php?' + (new Date()).getTime() + '&' + querystring,
			type     : 'POST',
			dataType : 'json',
			data     : values
		} )
		.always( function() {
			$('#prSave').button('reset');
		})
		.fail( function ( e ) {
			$('#prAlert').html( get_alert( 'danger' , e.responseText , false ) );
		})
		.done( function ( re ) {

			if ( re.singlewarning ) {
				$('#prBody').html( get_alert( 'warning' , re.singlewarning , false ) );
				return false;
			}
			else if ( re.singlenotice ) {
				$('#prBody').html( get_alert( 'info' , re.singlenotice , false ) );
				return false;
			}
			else if ( re.error ) {
				$('#prBody').html( get_alert( 'danger' , re.error , false ) );
				return false;
			}
			else  {
				profile_get( 'success' , lemma.profile_ok , true );
				return false;
			}
		});

		return false;
	};


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
	$('#changepassword').on('submit', function() {

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
			if ( r.singlewarning ) {
				$('#cpErr').html( get_alert( 'warning' , r.singlewarning , false ) ).show();
			}
			else if ( r.singlenotice ) {
				$('#cpErr').html( get_alert( 'info' , r.singlenotice , false ) ).show();
			}
			else if ( r.error ) {
				$('#cpErr').html( get_alert( 'danger' , r.error , false ) ).show();
			}
			else if ( r.ok ) {
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

	$('#umUsersAddForm').on('submit', function() {
		event.preventDefault();
		return users_add_save( this );
	});

	log_selector_init();

	/*
	|--------------------------------------------------------------------------
	| Anonymous
	|--------------------------------------------------------------------------
	|
	| Initialize form
	|
	*/
	$('#umAnonymousForm').on('submit', function() {
		event.preventDefault();
		return anonymous_save( this );
	});


});


function log_selector_init() {
	$('.logs-selector-yes').click(function() {
		$(this).parent().find('label.logs-selector-no').removeClass('btn-danger').addClass('btn-default');
		$(this).parent().find('label.logs-selector-yes').addClass('btn-success');
	});

	$('.logs-selector-no').click(function() {
		$(this).parent().find('label.logs-selector-yes').removeClass('btn-success').addClass('btn-default');
		$(this).parent().find('label.logs-selector-no').addClass('btn-danger');
	});

	$('.logs-selector-toggler').click( function() {
		var first = $(this).parents('.logs-selector').find('label.logs-selector-yes:first').hasClass('active');
		if ( first === true ) {
			$(this).parents('.logs-selector').find('label.logs-selector-no').click();
		} else {
			$(this).parents('.logs-selector').find('label.logs-selector-yes').click();
		}
	});
}


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
		case 'umAnonymous':
			anonymous_list();
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
var users_list = function( severity , message , close ) {

	if ( severity !== undefined ) {
		$('#umUsersListAlert').html( get_alert( severity , message , close ) );
	} else {
		$('#umUsersListAlert').html('');
	}

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
		if ( re.singlewarning ) {
			$('#umUsersListBody').html( get_alert( 'warning' , re.singlewarning , false ) );
		}
		else if ( re.singlenotice ) {
			$('#umUsersListBody').html( get_alert( 'info' , re.singlenotice , false ) );
		}
		else if ( re.error ) {
			$('#umUsersListBody').html( get_alert( 'danger' , re.error , false ) );
		}
		else {
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

			r += '<div class="table-responsive">';
			r += '<table class="table table-striped table-hover" data-sort-name="username" data-sort-order="asc" id="userlisttable">';
			r += 	'<thead>';
			r += 		'<tr>';
			r += 			'<th data-field="username" data-sortable="true">' + lemma.username + '</th>';
			r += 			'<th data-field="roles" data-sortable="true">' + lemma.roles + '</th>';
			r += 			'<th data-field="creationdate" data-sortable="true">' + lemma.creationdate + '</th>';
			r += 			'<th data-field="lastlogin" data-sortable="true">' + lemma.lastlogin + '</th>';
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

				if ( lastlogin !== undefined ) {
					lastlogin = lastlogin['ts'];
				} else {
					lastlogin = '';
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
			r += '</div>';

			$('#umUsersListBody').html( r );
			$('#userlisttable').bootstrapTable().bootstrapTable('hideLoading');
		}
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
	$('#umUsersViewAlert').html('');
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

		if ( re.singlewarning ) {
			$('#umUsersViewBody').html( get_alert( 'warning' , re.singlewarning , false ) );
			return false;
		}
		else if ( re.singlenotice ) {
			$('#umUsersViewBody').html( get_alert( 'info' , re.singlenotice , false ) );
			return false;
		}
		else if ( re.error ) {
			$('#umUsersViewBody').html( get_alert( 'danger' , re.error , false ) );
			return false;
		}
		else if ( re.e ) {
			$('#umUsersViewBody').html( get_alert( 'danger' , re.e , false ) );
			return false;
		}

		var r        = '';
		var uaparser = new UAParser();
		var is_admin = ($.inArray( 'admin' , re.b.roles ) > -1);

		r += '<div class="row del_base">';
		r += 	'<div class="col-sm-6"><p class="lead">' + re.b['u'] + '</p></div>';
		r += 	'<div class="col-sm-6 text-right">';
		if ( currentuser !== username ) {
			r += '<div class="btn-group">';
			r += '	<button type="button" class="btn btn-xs btn-danger dropdown-toggle" data-toggle="dropdown">' + lemma.deleteuser + '...</button>';
			r += '  <ul class="dropdown-menu" role="menu">';
			r += '    <li><a href="#" onclick="users_delete(this);">' + lemma.reallydeleteuser + '</a></li>';
			r += '  </ul>';
			r += '</div>';
			r += '&nbsp;';
			r += '<div class="btn-group">';
			r += '	<button type="button" class="btn btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">' + lemma.signinuser + '...</button>';
			r += '  <ul class="dropdown-menu" role="menu">';
			r += '    <li><a href="#" onclick="users_signinas(this);">' + lemma.reallysigninuser + '</a></li>';
			r += '  </ul>';
			r += '</div>';

		}
		r += 	'</div>';
		r += '</div>';

		r += '<table class="table table-striped table-hover">';
		r += 	'<tbody>';
		for ( var i in re.b ) {
			var val = re.b[i];
			if ( i === 'api_lastlogin' ) {
				var ua = uaparser.setUA( val['ua'] ).getResult();
				val    = '<a href="' + $('<div/>').text(val['ur']).html() + '" target="_blank" class="hyphen">' + val['ur'] + '</a><br/>' + val['ts'] + '<br/>' + val['ip'] + '<br/>' + ua.browser.name + ' ' + ua.browser.version + ' - ' + ua.os.name + ' ' + ua.os.version;
			}
			else if ( i === 'lastlogin' ) {
				var ua = uaparser.setUA( val['ua'] ).getResult();
				val    = val['ts'] + '<br/>' + val['ip'] + '<br/>' + ua.browser.name + ' ' + ua.browser.version + ' - ' + ua.os.name + ' ' + ua.os.version;
			}
			else if ( i === 'cb' ) {
				if ( ! val ) val = '<span class="label label-default">' + lemma.system + '</span>';
			}
			else if ( i === 'u' ) {
				continue;
			}
			else if ( i === 'at' ) {
				continue;
			}
			else if ( i === 'hp' ) {
				continue;
			}
			else if ( i === 'logs' ) {
				if ( is_admin === true ) {
					val = lemma.all_access;
				}
				else {
					var logslist = '';
					for( var j in val ) {
						if ( val[j].r === true ) {
							logslist += '<span class="label label-success">' + files[ j ].display + '</span> ';
						} else {
							logslist += '<span class="label label-danger">' + files[ j ].display + '</span> ';
						}
					}
					val = logslist;
				}
			}
			else if ( i === 'roles' ) {
				var rolelist = '';
				for( var j in val ) {
					switch ( val[j] ) {
						case 'admin':
							rolelist += '<span class="label label-danger">' + val[j] + '</span>';
							break;
						case 'user':
							rolelist += '<span class="label label-primary">' + val[j] + '</span>';
							break;
						default:
							rolelist += '<span class="label label-default">' + val[j] + '</span>';
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
 * Add a user
 *
 * @return  {boolean}  false
 */
var users_add = function() {
	$('#umUsersList').hide();
	$('#umUsersView').hide();
	$('#umUsersEdit').hide();
	$('#umUsersAdd').show();
	$('#umUsersAddLoader').hide();
	$('#umUsersAddBody').show();
	$('#umUsersAddPwdHelp').hide();

	// Reinit all fields
	$('#umUsersAdd').find('label.logs-selector-yes').click();
	$('#add-roles-user').click();
	$('#add-username').val('').removeAttr('readonly');
	$('#add-password').val('');
	$('#add-password2').val('');
	$('#umUsersAddAlert').html('');
	$.each( $('#umUsersAddForm').serializeArray(), function(i, field) {
		$( '#add-' + field.name + '-group' ).removeClass('has-error');
	});

	$('#add-type').val('add');
	$('#umUsersAddBtn').show();
	$('#umUsersViewBtn').hide();

	return false;
};


/**
 * Save user
 *
 * @return  {boolean}  false
 */
var users_add_save = function() {

	$('#umUsersAddSave').button('loading');

	var prefix = 'add-';
	var values = {
		'csrf_token' : csrf_token,
		'action'     : 'users_add'
	};
	$.each( $('#umUsersAddForm').serializeArray(), function(i, field) {
		$( '#add-' + field.name + '-group' ).removeClass('has-error');
		values[ field.name ] = field.value;
	});

	$.ajax( {
		url      : 'inc/users.pml.php?' + (new Date()).getTime() + '&' + querystring,
		type     : 'POST',
		dataType : 'json',
		data     : values
	} )
	.always( function() {
		$('#umUsersAddSave').button('reset');
	})
	.fail( function ( e ) {
		$('#umUsersAddAlert').html( get_alert( 'danger' , e.responseText , false ) );
	})
	.done( function ( re ) {

		if ( re.singlewarning ) {
			$('#umUsersAddAlert').html( get_alert( 'warning' , re.singlewarning , false ) );
			return false;
		}
		else if ( re.singlenotice ) {
			$('#umUsersAddAlert').html( get_alert( 'info' , re.singlenotice , false ) );
			return false;
		}
		else if ( re.error ) {
			$('#umUsersAddAlert').html( get_alert( 'danger' , re.error , false ) );
			return false;
		}
		else if ( re.c > 0 ) {
			var t = '<strong>' + lemma.form_invalid + '</strong><ul>';
			for ( var field in re.e ) {
				t += '<li>' + re.e[field] + '</li>';
				$( '#add-' + field + '-group' ).addClass('has-error');
			}
			t += '</ul>';

			$('#umUsersAddAlert').html( get_alert( 'danger' , t , false ) );
			return false;
		}

		users_list( 'success' , lemma.user_add_ok , true );
	});

	return false;
};


/**
 * Edit a user
 *
 * @return  {boolean}  false
 */
var users_edit = function( obj ) {

	var username = $(obj).data('user');

	$('#umUsersList').hide();
	$('#umUsersView').hide();
	$('#umUsersEdit').hide();
	$('#umUsersAdd').show();
	$('#umUsersAddLoader').show();
	$('#umUsersAddBody').hide();
	$('#umUsersAddPwdHelp').show();

	// Reinit all fields
	$('#umUsersAdd').find('label.logs-selector-no').click();
	$('#add-roles-user').click();
	$('#add-username').val( username ).attr('readonly','readonly');
	$('#add-password').val('');
	$('#add-password2').val('');
	$('#umUsersAddAlert').html('');
	$.each( $('#umUsersAddForm').serializeArray(), function(i, field) {
		$( '#add-' + field.name + '-group' ).removeClass('has-error');
	});

	$('#add-type').val('edit');
	$('#umUsersAddBtn').hide();
	$('#umUsersViewBtn').show().data( 'user' , username );

	$('#umUsersAddSave').button('loading');

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
		$('#umUsersAddSave').button('reset');
		$('#umUsersAddLoader').hide();
		$('#umUsersAddBody').show();
	})
	.fail( function ( e ) {
		$('#umUsersAddBody').html( get_alert( 'danger' , e.responseText , false ) );
	})
	.done( function ( re ) {
		if ( re.b.roles ) {
			for ( var i in re.b.roles ) {
				$( '#add-roles-' + re.b.roles[i] ).click();
			}
		}
		if ( re.b.logs ) {
			for ( var i in re.b.logs ) {
				$( '#add-logs-f-' + i + '-' + re.b.logs[i].r ).click();
			}
		}
	});

	return false;
};


/**
 * Delete a user
 *
 * @param   {object}  obj  HTML fragment
 *
 * @return  {boolean}       false
 */
var users_delete = function( obj ) {
	var username = $(obj).parents('.del_base').find('p.lead').text();

	$.ajax( {
		url      : 'inc/users.pml.php?' + (new Date()).getTime() + '&' + querystring,
		type     : 'POST',
		dataType : 'json',
		data     : {
			'csrf_token' : csrf_token,
			'action'     : 'users_delete',
			'u'          : username,
		}
	} )
	.always( function() {
	})
	.fail( function ( e ) {
		$('#umUsersViewAlert').html( get_alert( 'danger' , e.responseText , false ) );
	})
	.done( function ( re ) {

		if ( re.singlewarning ) {
			$('#umUsersViewAlert').html( get_alert( 'warning' , re.singlewarning , false ) );
			return false;
		}
		else if ( re.singlenotice ) {
			$('#umUsersViewAlert').html( get_alert( 'info' , re.singlenotice , false ) );
			return false;
		}
		else if ( re.error ) {
			$('#umUsersViewAlert').html( get_alert( 'danger' , re.error , false ) );
			return false;
		}
		else {
			users_list( 'success' , lemma.user_delete_ok , true );
			return false;
		}
	});

	return false;
};

/**
 * Sign in as a user
 *
 * @param   {object}  obj  HTML fragment
 *
 * @return  {boolean}       false
 */
var users_signinas = function( obj ) {
	var username = $(obj).parents('.del_base').find('p.lead').text();

	$.ajax( {
		url      : 'inc/users.pml.php?' + (new Date()).getTime() + '&' + querystring,
		type     : 'POST',
		dataType : 'json',
		data     : {
			'csrf_token' : csrf_token,
			'action'     : 'users_signinas',
			'u'          : username,
		}
	} )
	.always( function() {
	})
	.fail( function ( e ) {
		$('#umUsersViewAlert').html( get_alert( 'danger' , e.responseText , false ) );
	})
	.done( function ( re ) {

		if ( re.singlewarning ) {
			$('#umUsersViewAlert').html( get_alert( 'warning' , re.singlewarning , false ) );
			return false;
		}
		else if ( re.singlenotice ) {
			$('#umUsersViewAlert').html( get_alert( 'info' , re.singlenotice , false ) );
			return false;
		}
		else if ( re.error ) {
			$('#umUsersViewAlert').html( get_alert( 'danger' , re.error , false ) );
			return false;
		}
		else {
			document.location.reload();
			return false;
		}
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
		if ( re.singlewarning ) {
			$('#umAuthLogBody').html( get_alert( 'warning' , re.singlewarning , false ) );
			return false;
		}
		else if ( re.singlenotice ) {
			$('#umAuthLogBody').html( get_alert( 'info' , re.singlenotice , false ) );
			return false;
		}
		else if ( re.error ) {
			$('#umAuthLogBody').html( get_alert( 'danger' , re.error , false ) );
			return false;
		}

		var l = re.b.length;
		var r = '';
		if ( l > 0 ) {
			var uaparser = new UAParser();
			r += '<div class="table-responsive">';
			r += '<table class="table table-striped table-hover" data-sort-name="date" data-sort-order="desc" id="authlogtable">';
			r += 	'<thead>';
			r += 		'<tr>';
			r += 			'<th data-field="date" data-sortable="true">' + lemma.date + '</th>';
			r += 			'<th data-field="username" data-sortable="true">' + lemma.username + '</th>';
			r += 			'<th data-field="action" data-sortable="true">' + lemma.action + '</th>';
			r += 			'<th data-field="ip" data-sortable="true">' + lemma.ip + '</th>';
			r += 			'<th data-field="useragent" data-sortable="true">' + lemma.useragent + '</th>';
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
					case 'signinerr' :
						action = '<span class="label label-danger">' + lemma.signinerr + '</span>';
						break;
					case 'signout' :
						action = '<span class="label label-warning">' + lemma.signout + '</span>';
						break;
					case 'changepwd' :
						action = '<span class="label label-info">' + lemma.changepwd + '</span>';
						break;
				}
				action = action.replace(/^addadmin/,'<span class="label label-info">' + lemma.addadmin + '</span>');
				action = action.replace(/^adduser/,'<span class="label label-info">' + lemma.adduser + '</span>');
				action = action.replace(/^signinas/,'<span class="label label-success">' + lemma.signinas + '</span>');
				action = action.replace(/^deleteuser/,'<span class="label label-info">' + lemma.deleteuser + '</span>');

				r += '<tr>';
				r += 	'<td>' + date + '</td>';
				r += 	'<td>' + username + '</td>';
				r += 	'<td>' + action + '</td>';
				r += 	'<td>' + ip + '</td>';
				r += 	'<td title="' + $('<div/>').text(re.b[i][4]).html() + '">' + ua + '</td>';
				r += '</tr>';

			}
			r += 	'</tbody>';
			r += '</table>';
			r += '</div>';
		}
		else {
			r = get_alert( 'info' , lemma.authlogerror , false );
		}
		$('#umAuthLogBody').html( r );
		$('#authlogtable').bootstrapTable().bootstrapTable('hideLoading');
	});
	return false;
};


/**
 * Save anonymous logs
 *
 * @return  {boolean}  false
 */
var anonymous_save = function() {

	$('#umAnonymousSave').button('loading');

	var values = {
		'csrf_token' : csrf_token,
		'action'     : 'anonymous_save'
	};
	$.each( $('#umAnonymousForm').serializeArray(), function(i, field) {
		values[ field.name ] = field.value;
	});

	$.ajax( {
		url      : 'inc/users.pml.php?' + (new Date()).getTime() + '&' + querystring,
		type     : 'POST',
		dataType : 'json',
		data     : values
	} )
	.always( function() {
		$('#umAnonymousSave').button('reset');
	})
	.fail( function ( e ) {
		$('#umAnonymousAlert').html( get_alert( 'danger' , e.responseText , false ) );
	})
	.done( function ( re ) {

		if ( re.singlewarning ) {
			$('#umAnonymousBody').html( get_alert( 'warning' , re.singlewarning , false ) );
			return false;
		}
		else if ( re.singlenotice ) {
			$('#umAnonymousBody').html( get_alert( 'info' , re.singlenotice , false ) );
			return false;
		}
		else if ( re.error ) {
			$('#umAnonymousBody').html( get_alert( 'danger' , re.error , false ) );
			return false;
		}
		else  {
			anonymous_list( 'success' , lemma.anonymous_ok , true );
			return false;
		}
	});

	return false;
};


/**
 * List anonymous logs
 *
 * @return  {boolean}  false
 */
var anonymous_list = function( severity , message , close ) {

	if ( severity !== undefined ) {
		$('#umAnonymousAlert').html( get_alert( severity , message , close ) );
	} else {
		$('#umAnonymousAlert').html('');
	}

	$('#umAnonymousBody').html('<img src="img/loader.gif"/>');

	$.ajax( {
		url      : 'inc/users.pml.php?' + (new Date()).getTime() + '&' + querystring,
		type     : 'POST',
		dataType : 'json',
		data     : {
			'csrf_token' : csrf_token,
			'action'     : 'anonymous_list',
		}
	} )
	.always( function() {
	})
	.fail( function ( e ) {
		$('#umAnonymousBody').html( get_alert( 'danger' , e.responseText , false ) );
	})
	.done( function ( re ) {
		if ( re.singlewarning ) {
			$('#umAnonymousBody').html( get_alert( 'warning' , re.singlewarning , false ) );
		}
		else if ( re.singlenotice ) {
			$('#umAnonymousBody').html( get_alert( 'info' , re.singlenotice , false ) );
		}
		else if ( re.error ) {
			$('#umAnonymousBody').html( get_alert( 'danger' , re.error , false ) );
		}
		else {
			$('#umAnonymousBody').html( re.b );
			log_selector_init();
		}
	});

	return false;
};
