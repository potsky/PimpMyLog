<?php
/*! pimpmylog - 1.7.14 - 025d83c29c6cf8dbb697aa966c9e9f8713ec92f1*/
/*
 * pimpmylog
 * http://pimpmylog.com
 *
 * Copyright (c) 2017 Potsky, contributors
 * Licensed under the GPLv3 license.
 */
?><?php
include_once 'global.inc.php';
list( $badges , $files , $tz ) = config_load();

/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
|
*/
if ( ! Sentinel::isAuthSet() ) die();
$current_user = Sentinel::attempt( $files );


/*
|--------------------------------------------------------------------------
| Error handling
|--------------------------------------------------------------------------
|
*/
function myErrorHandler( $errno, $errstr, $errfile, $errline ) {
	global $return;
	if ( !( error_reporting() & $errno ) ) {
		return;
	}
	switch ( $errno ) {
	case E_USER_ERROR:
		echo json_encode( array( 'error' => $errstr ) );
		exit( 1 );
		break;

	case E_USER_WARNING:
		$return['singlewarning'] = sprintf( __('<strong>PHP Warning</strong> [%s] %s') , $errno , $errstr );
		break;

	case E_USER_NOTICE:
		$return['singlenotice'] = sprintf( __('<strong>PHP Notice</strong> [%s] %s') , $errno , $errstr );
		break;

	default:
		$return['singlewarning'] = sprintf( __('<strong>PHP Unknown error</strong> [%s] %s') , $errno , $errstr );
		break;
	}
	return true;
}

$old_error_handler = set_error_handler( "myErrorHandler" );

register_shutdown_function( 'shutdown' );

function shutdown() {
	$error = error_get_last();
	if ( $error['type'] === E_ERROR ) {
		echo json_encode(
			array(
				'error' => sprintf( __('<strong>PHP Error</strong> line %s: %s') , $error['line'] , $error['message'] )
			)
		);
	}
}


/*
|--------------------------------------------------------------------------
| Prepare
|--------------------------------------------------------------------------
|
*/
header('Content-type: application/json');

$return = array();

if 	( ! csrf_verify() ) {
	$return['error'] = __( 'Please refresh the page.' );
	echo json_encode( $return );
	die();
}


/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
|
*/
switch ( @$_POST['action'] ) {

	/*
	|--------------------------------------------------------------------------
	| Change password
	|--------------------------------------------------------------------------
	|
	*/
	case 'change_password':
		$password1 = $_POST['password1'];
		$password2 = $_POST['password2'];
		$password3 = $_POST['password3'];
		$username  = Sentinel::getCurrentUsername();
		$errors    = array();
		$fields    = array();
		$doit      = true;

		if ( ! Sentinel::isValidPassword( $username , $password1 ) ) {
			$errors[] = __( 'Current password is not valid' );
			$fields[] = 'password1';
			$doit    = false;
		}
		if ( mb_strlen( $password2 ) < 6  ) {
			$errors[] = __( 'Password must contain at least 6 chars' );
			$fields[] = 'password2';
			$doit    = false;
		}
		if ( $password2 !== $password3 ) {
			$errors[] = __( 'Password confirmation is not the same' );
			$fields[] = 'password3';
			$doit    = false;
		}

		if ( $doit === true ) {
			if ( $_SERVER['SERVER_NAME'] === 'demo.pimpmylog.com' ) {
				$return['ok'] = __( 'Password has been fakely changed on the demo!' );
			} else {
				$return['ok'] = __( 'Password has been successfully changed!' );
				Sentinel::changePassword( $username , $password2 );
			}
		}
		else {
			$return['errors'] = $errors;
			$return['fields'] = $fields;
		}

		break;

	/*
	|--------------------------------------------------------------------------
	| List users
	|--------------------------------------------------------------------------
	|
	*/
	case 'users_list':

		if ( ! Sentinel::isAdmin() ) {
			$return['error'] = 'vaffanculo';
			break;
		}

		$users = array();

		foreach ( Sentinel::getUsers() as $username => $user ) {
			unset( $user['pwd'] );
			if (isset($user[ 'lastlogin' ]['ts']))     $user[ 'lastlogin' ]['ts']     = date( 'Y/m/d H:i:s' , (int)$user[ 'lastlogin' ]['ts'] );
			if (isset($user[ 'api_lastlogin' ]['ts'])) $user[ 'api_lastlogin' ]['ts'] = date( 'Y/m/d H:i:s' , (int)$user[ 'api_lastlogin' ]['ts'] );
			$user[ 'cd' ] = date( 'Y/m/d H:i:s' , (int)$user[ 'cd' ] );
			$user[ 'u' ]  = $username;
			$users[]      = $user;
		}
		$return['b'] = $users;
		break;


	/*
	|--------------------------------------------------------------------------
	| View a single user
	|--------------------------------------------------------------------------
	|
	*/
	case 'users_view':

		if ( ! Sentinel::isAdmin() ) {
			$return['error'] = 'vaffanculo';
			break;
		}

		$username = $_POST['u'];
		$user     = Sentinel::getUser( $username );

		if ( is_null( $user ) ) {
			$return['e'] = sprintf( __('User %s does not exist') , '<code>' . $username . '</code>' );
		}
		else {
			unset( $user['pwd'] );
			if (isset($user[ 'lastlogin' ]['ts']))     $user[ 'lastlogin' ]['ts']     = date( 'Y/m/d H:i:s' , (int)$user[ 'lastlogin' ]['ts'] );
			if (isset($user[ 'api_lastlogin' ]['ts'])) $user[ 'api_lastlogin' ]['ts'] = date( 'Y/m/d H:i:s' , (int)$user[ 'api_lastlogin' ]['ts'] );
			$user[ 'cd' ]     = date( 'Y/m/d H:i:s' , (int)$user[ 'cd' ] );
			$return['b']      = $user;
			$return['b']['u'] = $username;
		}

		break;


	/*
	|--------------------------------------------------------------------------
	| Edit a single user
	|--------------------------------------------------------------------------
	|
	*/
	case 'users_edit':

		if ( ! Sentinel::isAdmin() ) {
			$return['error'] = 'vaffanculo';
			break;
		}

		$username = $_POST['u'];
		$user     = Sentinel::getUser( $_POST['u'] );

		if ( is_null( $user ) ) {
			$return['e'] = sprintf( __('User %s does not exist') , '<code>' . $username . '</code>' );
		}
		else {
			unset( $user['pwd'] );
			unset( $user['cb'] );
			unset( $user['cd'] );
			unset( $user['lastlogin'] );
			unset( $user['logincount'] );
			$user['pwd']  = '';
			$user['pwd2'] = '';
			$return['b']  = $user;
		}

		break;


	/*
	|--------------------------------------------------------------------------
	| Save a user
	|--------------------------------------------------------------------------
	|
	*/
	case 'users_add':

		if ( ! Sentinel::isAdmin() ) {
			$return['error'] = 'vaffanculo';
			break;
		}

		$username  = trim($_POST['username']);
		$password  = $_POST['password'];
		$password2 = $_POST['password2'];
		$roles     = $_POST['roles'];
		$type      = $_POST['add-type'];

		unset( $_POST['csrf_token'] );
		unset( $_POST['action'] );
		unset( $_POST['username'] );
		unset( $_POST['password'] );
		unset( $_POST['password2'] );
		unset( $_POST['roles'] );
		unset( $_POST['add-type'] );

		$logfiles = $_POST;

		$errors = array();

		if ( empty( $username ) ) {
			$errors[ 'username' ] = __( 'Username is required' );
		}
		else if ( ( $type === 'add' ) && ( Sentinel::userExists( $username ) ) ) {
			$errors[ 'username' ] = sprintf( __('User %s already exists') , '<code>' . $username . '</code>' );
		}

		if ( ( ( $type === 'edit' ) && ( ! empty( $password ) ) ) || ( $type === 'add' ) ) {
			if ( mb_strlen( $password ) < 6 ) {
				$errors[ 'password' ] = __( 'Password must contain at least 6 chars' );
			}
			if ( $password !== $password2 ) {
				$errors[ 'password2' ] = __( 'Password confirmation is not the same' );
			}
		}

		if ( count( $errors ) === 0 ) {
			if ( empty( $password ) ) $password = null;

			if ( $roles === 'admin' ) {
				Sentinel::setAdmin( $username , $password );
				if ( $type === 'add' ) Sentinel::log( 'addadmin ' . $username , $current_user );
			}
			else {
				$logs = array();
				foreach( $logfiles as $fileid => $access ) {
					if ( substr( $fileid , 0 , 2 ) === 'f-' ) {
						if ( (int)$access === 1 ) {
							$logs[ substr( $fileid , 2 ) ] = array( 'r' => true );
						}
					}
					else if ( substr( $fileid , 0 , 2 ) === 't-' ) {
						if ( (int)$access === 1 ) {
							$tags[ substr( $fileid , 2 ) ] = array( 'r' => true );
						}
					}
				}
    			Sentinel::setUser( $username , $password , array('user') , $logs );
				if ( $type === 'add' ) Sentinel::log( 'adduser ' . $username , $current_user );
			}
			Sentinel::save();

		}

		$return['c'] = count( $errors );
		$return['e'] = $errors;

		break;


	/*
	|--------------------------------------------------------------------------
	| Delete a single user
	|--------------------------------------------------------------------------
	|
	*/
	case 'users_delete':

		if ( ! Sentinel::isAdmin() ) {
			$return['error'] = 'vaffanculo';
			break;
		}

		$username = $_POST['u'];

		if ( $username === $current_user ) {
			$return['error'] = __('Please do not shoot yourself in the foot!');
		}
		else {
			Sentinel::deleteUser( $username );
	        Sentinel::log( 'deleteuser ' . $username , $current_user );
			Sentinel::save();
		}

		break;


	/*
	|--------------------------------------------------------------------------
	| Sign in as a user
	|--------------------------------------------------------------------------
	|
	*/
	case 'users_signinas':

		if ( ! Sentinel::isAdmin() ) {
			$return['error'] = 'vaffanculo';
			break;
		}

		$username = $_POST['u'];

		if ( $username === $current_user ) {
			$return['error'] = 'You are a recursive guy, right?!';
		}
		else {
			Sentinel::signInAs( $username );
		}

		break;


	/*
	|--------------------------------------------------------------------------
	| List auth logs
	|--------------------------------------------------------------------------
	|
	*/
	case 'authlog':

		if ( ! Sentinel::isAdmin() ) {
			$return['error'] = 'vaffanculo';
			break;
		}

		$logs = array();
		foreach ( Sentinel::getLogs() as $log ) {
			$log[ 2 ] = date( 'Y/m/d H:i:s' , (int)$log[ 2 ] );
			$logs[]   = $log;
		}
		$return['b'] = $logs;
		break;


	/*
	|--------------------------------------------------------------------------
	| Anonymous list files
	|--------------------------------------------------------------------------
	|
	*/
	case 'anonymous_list':

		if ( ! Sentinel::isAdmin() ) {
			$return['error'] = 'vaffanculo';
			break;
		}

		if ( Sentinel::isAnonymousEnabled( $files ) ) {
			$r = '<div class="alert alert-info">' . __('<strong>Anonymous access is enabled</strong>. Genuine users have to click on the user menu to sign in and access more logs.') . '</div>';
		} else {
			$r = '<div class="alert alert-info">' . __('<strong>Anonymous access is disabled</strong>. All users have to sign in from the sign in screen.') . '</div>';
		}

		$r .= '<div class="form-group">';
		$r .= '	<label class="col-sm-4 control-label"></label>';
		$r .= '	<div class="col-sm-8">';
		$r .= __("Select which log files can be viewed without being logged.");
		$r .= '		(<a href="#" class="logs-selector-toggler">'. __('Toggle all log files') . '</a>)';
		$r .= '	</div>';
		$r .= '</div>';

		foreach( $files as $file_id => $file ) {

			$fid     = h( $file_id );
			$display = $files[ $file_id ][ 'display' ];
			$paths   = $files[ $file_id ][ 'path' ];
			$color   = 'default';

			if ( isset( $files[ $file_id ][ 'oid' ] ) ) {
				if ( $files[ $file_id ][ 'oid' ] !== $file_id ) continue;
				$display = $files[ $file_id ][ 'odisplay' ];
				if ( isset( $files[ $file_id ][ 'count' ] ) ) {
					$remain = (int)$files[ $file_id ][ 'count' ] - 1;
					if ( $remain === 1 ) {
						$paths .= ' ' . __( 'and an other file defined by glob pattern' );
					}
					else if ( $remain > 1 ) {
						$paths .= ' ' . sprintf( __( 'and %s other possible files defined by glob pattern' ) , $remain );
					}
				}
				$color = 'warning';
			}

			if ( Sentinel::isLogAnonymous( $file_id ) ) {
				$e  = 'active btn-success';
				$d  = 'btn-default';
				$ec = ' checked="checked"';
				$dc = '';
			} else {
				$e  = 'btn-default';
				$d  = 'active btn-danger';
				$ec = '';
				$dc = ' checked="checked"';
			}

			$r .= '<div class="form-group" data-fileid="' . $fid . '">';
			$r .= '	<label for="' . $fid . '" class="col-sm-4 control-label text-' . $color . '">' . $display . '</label>';
			$r .= '	<div class="col-sm-8">';
			$r .= '		<div class="btn-group" data-toggle="buttons">';
			$r .= '			<label class="btn btn-xs logs-selector-yes ' . $e . '">';
			$r .= '				<input type="radio" name="f-' . $fid . '" id="anonymous-f-' . $fid . '-true" value="1"' . $ec . '/> '.__('Yes');
			$r .= '			</label>';
			$r .= '			<label class="btn btn-xs logs-selector-no ' . $d . '">';
			$r .= '				<input type="radio" name="f-' . $fid . '" id="anonymous-f-' . $fid . '-false" value="0"' . $dc . '/> '.__('No');
			$r .= '			</label>';
			$r .= '		</div>';
			$r .= '	<span class="glyphicon glyphicon-question-sign text-muted" data-toggle="tooltip" data-placement="right" data-html="true" title="<div class=\'hyphen\'>' . h( $paths ) . '</div>"></span>';
			$r .= '	</div>';
			$r .= '</div>';
		}

		$r .= '<script>$(function(){$(\'[data-toggle="tooltip"]\').tooltip()})</script>';

		$return['b'] = $r;

		break;

	/*
	|--------------------------------------------------------------------------
	| Anonymous save
	|--------------------------------------------------------------------------
	|
	*/
	case 'anonymous_save':

		if ( ! Sentinel::isAdmin() ) {
			$return['error'] = 'vaffanculo';
			break;
		}

		unset( $_POST['csrf_token'] );
		unset( $_POST['action'] );

		$logfiles = $_POST;

		foreach( $logfiles as $fileid => $access ) {
			if ( substr( $fileid , 0 , 2 ) === 'f-' ) {
				Sentinel::setLogAnonymous( substr( $fileid , 2 ) , ( (int)$access === 1 ) );
			}
		}

		Sentinel::save();

		break;

	/*
	|--------------------------------------------------------------------------
	| Profile get
	|--------------------------------------------------------------------------
	|
	*/
	case 'profile_get':
		$user        = Sentinel::getUser( $current_user );
		$accesstoken = $user['at'];
		$hashpresalt = $user['hp'];
		$r           = '';

		$r .= '<div class="form-group">';
		$r .= 	'<label for="" class="col-sm-3 control-label">' . __('Access token') . '</label>';
		$r .= 	'<div class="col-sm-5">';
		$r .= 		'<div class="input-group">';
		$r .= 			'<span class="input-group-addon"><span class="glyphicon glyphicon-certificate"></span></span>';
		$r .= 			'<input type="text" id="prat" class="form-control" value="' . h( $accesstoken ) . '" disabled="disabled"/>';
		$r .= 		'</div>';
		$r .= 	'</div>';
		$r .= 	'<div class="col-sm-4">';
		$r .=     '<a class="btn btn-xs btn-primary clipboardat">' . __('Copy to clipboard') . '</a>';
		$r .= 	'</div>';
		$r .= '</div>';
		$r .= '<script>clipboard_enable( "a.clipboardat", "#prat" , "top" , "' . h( __('Access token copied!') ) . '" );</script>';

		$r .= '<div class="form-group">';
		$r .= 	'<label for="" class="col-sm-3 control-label">' . __('Presalt key') . '</label>';
		$r .= 	'<div class="col-sm-5">';
		$r .= 		'<div class="input-group">';
		$r .= 			'<span class="input-group-addon"><span class="glyphicon glyphicon-flash"></span></span>';
		$r .= 			'<input type="text" id="prhp" class="form-control" value="' . h( $hashpresalt ) . '" disabled="disabled"/>';
		$r .= 		'</div>';
		$r .= 	'</div>';
		$r .= 	'<div class="col-sm-4">';
		$r .=     '<a class="btn btn-xs btn-primary clipboardhp">' . __('Copy to clipboard') . '</a>';
		$r .= 	'</div>';
		$r .= '</div>';
		$r .= '<script>clipboard_enable( "a.clipboardhp" , "#prhp" , "top" , "' . h( __('Presalt key copied!') ) . '" );</script>';

		$r .= '<div class="form-group">';
		$r .= 	'<label for="" class="col-sm-3 control-label"></label>';
		$r .= 	'<div class="col-sm-9">';
        $r .=   '<input type="checkbox" name="regenerate" value="1"/> ' . __('Check to generate both new Access token and new Presalt key');
   		$r .= 	'</div>';
		$r .= '</div>';

		$r .= '<hr/>';

		if ( ( ! isset( $user['api_logincount'] ) ) || ( (int)$user['api_logincount'] === 0 ) ) {
			$r .= __('Your credentials have not been used');
		}
		else {
			$r .= '<p>';
			if ( (int)$user['api_logincount'] === 1 ) {
				$r .= __('API has been called 1 time');
			} else {
				$r .= sprintf( __('API has been called %s times') , (int)$user['api_logincount'] );
			}
			$r .= '</p>';

			$r .= sprintf( __('Last API call has been done at %s by IP address %s with user agent %s on URL %s')
				, '<code>' . date( 'Y/m/d H:i:s' , (int)$user[ 'api_lastlogin' ]['ts'] ) . '</code>'
				, '<code>' . $user[ 'api_lastlogin' ]['ip'] . '</code>'
				, '<code>' . $user[ 'api_lastlogin' ]['ua'] . '</code>'
				, '<a href="' . h( $user[ 'api_lastlogin' ]['ur'] ) . '" class="hyphen" target="_blank">' . $user[ 'api_lastlogin' ]['ur'] . '</a>'
			);
		}

		$return['b'] = $r;

		break;

	/*
	|--------------------------------------------------------------------------
	| Profile save
	|--------------------------------------------------------------------------
	|
	*/
	case 'profile_save':

		if ( @$_POST['regenerate'] === '1' ) {
			Sentinel::setUser( $current_user , null , null , null , true );
			Sentinel::save();
		}

		break;

	/*
	|--------------------------------------------------------------------------
	| Unknown action...
	|--------------------------------------------------------------------------
	|
	*/
	default:
		error_log( 'Unknown action ' . @$_POST['action'] );
		break;
}


/*
|--------------------------------------------------------------------------
| End tuning
|--------------------------------------------------------------------------
|
*/
echo json_encode( $return );
die();

?>