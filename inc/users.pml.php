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

	default:
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
