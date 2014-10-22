<?php
include_once 'global.inc.php';
list( $badges , $files ) = config_load();

/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
|
*/
if ( ! Sentinel::isAuthSet() ) die();
$current_user = Sentinel::attempt();


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
		if ( strlen( $password2 ) < 6  ) {
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
			$return['ok'] = __( 'Password has been successfully changed!' );
			Sentinel::setUser( $username , $password2 );
			Sentinel::save();
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
		$users = array();

		foreach ( Sentinel::getUsers() as $username => $user ) {
			unset( $user['pwd'] );
			if (isset($user[ 'lastlogin' ]['ts'])) $user[ 'lastlogin' ]['ts'] = date( 'Y/m/d H:i:s' , (int)$user[ 'lastlogin' ]['ts'] );
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

		$username = $_POST['u'];
		$user     = Sentinel::getUser( $username );

		if ( is_null( $user ) ) {
			$return['e'] = sprintf( __('User %s does not exist') , '<code>' . $username . '</code>' );
		}
		else {
			unset( $user['pwd'] );
			if (isset( $user[ 'lastlogin' ]['ts'] ) ) $user[ 'lastlogin' ]['ts'] = date( 'Y/m/d H:i:s' , (int)$user[ 'lastlogin' ]['ts'] );
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

		$username = $_POST['u'];
		$user     = Sentinel::getUser( $username );

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
			$return['b']      = $user;
			$return['b']['u'] = $username;
		}

		break;





	/*
	|--------------------------------------------------------------------------
	| List auth logs
	|--------------------------------------------------------------------------
	|
	*/
	case 'authlog':
		$logs = array();
		foreach ( Sentinel::getLogs() as $log ) {
			$log[ 2 ] = date( 'Y/m/d H:i:s' , (int)$log[ 2 ] );
			$logs[]   = $log;
		}
		$return['b'] = $logs;
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
