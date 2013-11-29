<?php
include_once 'global.inc.php';
include_once '../config.inc.php';

$logs = array();

////////////////////
// Error handling //
////////////////////
function myErrorHandler( $errno, $errstr, $errfile, $errline ) {
	global $logs;
	if ( !( error_reporting() & $errno ) ) {
		return;
	}
	switch ( $errno ) {
	case E_USER_ERROR:
		echo json_encode( array( 'error' => $errstr ) );
		exit( 1 );
		break;

	case E_USER_WARNING:
		$logs['warning'] = sprintf( __('<strong>PHP Warning</strong> [%s] %s') , $errno , $errstr );
		break;

	case E_USER_NOTICE:
		$logs['notice'] = sprintf( __('<strong>PHP Notice</strong> [%s] %s') , $errno , $errstr );
		break;

	default:
		$logs['warning'] = sprintf( __('<strong>PHP Unknown error</strong> [%s] %s') , $errno , $errstr );
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
				'error' => sprintf( __('<strong>PHP Error</strong> line %s : %s') , $error['line'] , $error['message'] )
			)
		);
	}
}


/////////////
// Prepare //
/////////////
$start               = microtime( true );
$file_id             = $_GET['file'];
$user_max            = $_GET['max'];
$load_default_values = $_GET['ldv'];

if ( ! isset( $files[$file_id] ) ) {
	$logs['error'] = sprintf( __( 'File ID <code>%s</code> does not exist, please review your configuration file and stop playing!' ) , $file_id );
	echo json_encode( $logs );
	die();
}

$file_path = @$files[$file_id]['path'];
if ( ! file_exists( $file_path ) ) {
	$logs['error'] = sprintf( __( 'File <code>%s</code> for file ID <code>%s</code> does not exist, please review your configuration file.' ) , $file_path , $file_id );
	echo json_encode( $logs );
	die();
}

$errors = check_config();
if ( is_array( $errors ) ) {
	$logs['error'] = __( 'Configuration file has changed and is buggy now. Please refresh the page.' );
	echo json_encode( $logs );
	die();
}

$file_max = ( isset( $files[$file_id]['max'] ) ) ? (int)$files[$file_id]['max'] : LOGS_MAX;
$max      = ( $load_default_values == 'true' ) ? $file_max : $user_max;
$regex    = $files[ $file_id ][ 'format' ][ 'regex' ];
$match    = $files[ $file_id ][ 'format' ][ 'match' ];
$exclude  = $files[ $file_id ][ 'format' ][ 'exclude' ];


//////////////
// Let's Go //
//////////////

$fl = fopen( $file_path , "r" );
if ( $fl === false ) {
	$logs['error'] = sprintf( __( 'File <code>%s</code> for file ID <code>%s</code> does not exist anymore...' ) , $file_path , $file_id );
	echo json_encode( $logs );
	die();
}

$found = false;
$bytes = 0;
for ( $x_pos = 0, $ln = 0, $line=''; fseek( $fl, $x_pos, SEEK_END ) !== -1; $x_pos-- ) {

	$char = fgetc( $fl );

	if ( $char === "\n" ) {
		$deal = $line;
		$line = '';
		if ( $deal != '' ) {
			$log        = parser( $regex , $match , $deal , 'Y/m/d H:i:s' , ' :: ' );
			if ( is_array( $log ) ) {
				$return_log = true;
				foreach ( $log as $key => $value ) {
					if ( ( isset( $exclude[$key] ) ) && ( is_array( $exclude[$key] ) ) ) {
						foreach ( $exclude[$key] as $ekey => $reg ) {
							try {
								if ( preg_match( $reg , $value ) ) {
									$return_log = false;
									break 2;
								}
							} catch ( Exception $e ) {
							}
						}
					}
				}
				if ( $return_log === true ) {
					$found = true;
					$logs[ 'logs' ][] = $log;
					$ln++;
				}
			}
			else {
			}
		}
		if ( $ln >= $max ) break;
		continue;
	}
	$line = $char . $line;
	$bytes++;
}
fclose( $fl );


/////////////////////////////
// Return headers and meta //
/////////////////////////////
if ( $found ) {
	foreach ( $match as $k => $v ) {
		$logs['headers'][ $k ] = __( $k );
	}
}

$logs['found']        = $found;
$logs['lastmodified'] = sprintf( __( 'File <code>%s</code> was last modified on <code>%s</code>' ) , $file_path , date( 'Y/m/d H:i:s' , filemtime( $file_path ) ) );
$logs['fingerprint']  = md5( serialize( $logs['logs'] ) );
$logs['bytes']        = $bytes;


////////////////
// End Tuning //
////////////////
$now              = microtime( true );
$duration         = (int) ( ( $now - $start ) * 1000 );
$logs['duration'] = sprintf( __( 'Computed in %sms' ) , $duration );

echo json_encode( $logs );
