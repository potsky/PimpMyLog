<?php
include_once 'global.inc.php';
list( $badges , $files ) = config_load();

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
	case 'get_rss_link':

		$url = str_replace(
			array( 'http://' , 'https://' , 'rss.pml.php' ),
			array( 'feed://' , 'feed://' , 'rss.php' ),
			get_current_url()
		) . '?f=' . urlencode( $_POST['file'] );

		if ( ! empty( $_POST['search'] ) ) {
			$url = $url . '&s=' . urlencode( $_POST['search'] );
		}

		$current_user = Sentinel::attempt();

		// We authenticate the url if a user is logged in
		// -> if log is anonymous, the request will be authenticated and if an admin remove
		//    the anonymous log, this user will always be able to get it
		// -> if the log file is protected, this user will be able to get ot according to its rights
		if ( ! is_null( $current_user ) ) {
			$username = Sentinel::getCurrentUsername();
			$user     = Sentinel::getUser( $username );
			$token    = $user[ 'at' ];
			$hash     = Sentinel::sign( array( 'f' => $_POST['file'] ) , $username );

			$url = $url . '&t=' . urlencode( $token ) . '&h=' . urlencode( $hash );
		}

		$return['url'] = $url;

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
