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


if ( ! isset( $files[ $_POST['file'] ] ) ) {
	$return['error'] = __( 'This log file does not exist.' );
	echo json_encode( $return );
	die();
}
$file_id = $_POST['file'];
$format  = $_POST['format'];

/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
|
*/
switch ( @$_POST['action'] ) {

	/*
	|--------------------------------------------------------------------------
	| Generate the RSS Link
	|--------------------------------------------------------------------------
	|
	*/
	case 'get_rss_link':

		$url = get_current_url();

		switch ( $format ) {
			case 'ATOM':
			case 'RSS':
				// http://en.wikipedia.org/wiki/Feed_URI_scheme
				/*
				$url = str_replace(
					array( 'http://' , 'https://' ),
					array( 'feed://' , 'feed://'  ),
					$url
				);
				*/
				$method = 'nd';
				break;
			case 'CSV':
				$method = 'nd';
				break;
			default:
				$method = 'nw';
				break;
		}

		$url = str_replace(
			array( 'rss.pml.php' ),
			array( 'rss.php' ),
			$url
		)
		. '?f=' . urlencode( $file_id )
		. '&l=' . urlencode( ( isset( $_GET['l'] ) ) ? $_GET['l'] : $lang )
		. '&tz=' . urlencode( $tz )
		. '&format=' . urlencode( $format )
		. '&count=' . ( ( isset( $files[ $file_id ][ 'max' ] ) ) ? urlencode( $files[ $file_id ][ 'max' ] ) : urlencode( LOGS_MAX ) )
		. '&timeout=' . urlencode( MAX_SEARCH_LOG_TIME )
		. '&search=' . urlencode( @$_POST['search'] )
		;

		$current_user = Sentinel::attempt( $files );

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

		$u  = parse_url($url);
		$ip = $u['host'];

		if ( filter_var( $ip , FILTER_VALIDATE_IP ) ) {
    		$return['war'] = ( ! is_not_local_ip( $ip ) );
		} else if ( $ip === 'localhost' ) {
    		$return['war'] = true;
		} else {
    		$return['war'] = false;
		}

		$return['url'] = $url;
		$return['met'] = $method;

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