<?php
/*! pimpmylog - 1.1 - 3bd72fd3e5c16505d276f59f25ae9c549d6536f3*/
/*
 * pimpmylog
 * http://pimpmylog.com
 *
 * Copyright (c) 2014 Potsky, contributors
 * Licensed under the GPLv3 license.
 */
?><?php
include_once 'global.inc.php';
config_load( '../config.user.json' );
init();


/////////////
//  Check  //
/////////////
if (( ! isset( $_POST['file'] ) ) ||
	( ! isset( $_POST['max'] ) ) ||
	( ! isset( $_POST['ldv'] ) )
) {
	die();
}


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
		$logs['singlewarning'] = sprintf( __('<strong>PHP Warning</strong> [%s] %s') , $errno , $errstr );
		break;

	case E_USER_NOTICE:
		$logs['singlenotice'] = sprintf( __('<strong>PHP Notice</strong> [%s] %s') , $errno , $errstr );
		break;

	default:
		$logs['singlewarning'] = sprintf( __('<strong>PHP Unknown error</strong> [%s] %s') , $errno , $errstr );
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



/////////////
// Prepare //
/////////////
$start               = microtime( true );
$logs                = array();
$file_id             = $_POST['file'];
$load_default_values = $_POST['ldv'];
$max                 = (int)$_POST['max'];
$reset               = @(int)$_POST['reset'];
$old_file_size       = @(int)$_POST['filesize'];
$search              = @$_POST['search'];
$old_lastline        = @$_POST['lastline'];

header('Content-type: application/json');

if 	( ! csrf_verify() ) {
	$logs['error'] = __( 'Please refresh the page.' );
	echo json_encode( $logs );
	die();
}

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

$errors = config_check();
if ( is_array( $errors ) ) {
	$logs['error'] = __( 'Configuration file has changed and is buggy now. Please refresh the page.' );
	echo json_encode( $logs );
	die();
}


$regex     = $files[ $file_id ][ 'format' ][ 'regex' ];
$match     = $files[ $file_id ][ 'format' ][ 'match' ];
$types     = $files[ $file_id ][ 'format' ][ 'types' ];
$multiline = ( isset( $files[ $file_id ][ 'format' ][ 'multiline' ] ) ) ? $files[ $file_id ][ 'format' ][ 'multiline' ] : '';
$exclude   = isset( $files[ $file_id ][ 'format' ][ 'exclude' ] ) ? $files[ $file_id ][ 'format' ][ 'exclude' ] : array();



// Check the search regexp
$regsearch = false;
if ( $search != '' ) {
	$test      = @preg_match( $search , 'this is just a test !' );
	$regsearch = ( $test === false ) ? false : true;
}


//////////////
// Timezone //
//////////////
$now = new DateTime();
if ( ! is_null( $tz ) ) {
	$now->setTimezone( new DateTimeZone( $tz ) );
}
$now = $now->format( 'Y/m/d H:i:s' );


//////////////
// Let's Go //
//////////////
$found           = false;
$bytes           = 0;
$skip            = 0;
$error           = 0;
$abort           = false;
$full            = false;
$tofarline       = '';
$file_lastline   = '';
$search_lastline = true;
$buffer          = array();
$fl              = fopen( $file_path , "r" );
if ( $fl === false ) {
	$logs['error'] = sprintf( __( 'File <code>%s</code> for file ID <code>%s</code> does not exist anymore...' ) , $file_path , $file_id );
	echo json_encode( $logs );
	die();
}


//////////////////////////////////////////
// Check how many bytes we have to read //
//////////////////////////////////////////
$new_file_size = filesize( $file_path ); // Must be the nearest of fseek !
if ( $reset == 1 ) {
	$full           = true;
	$data_to_parse  = $new_file_size;
}
else {
	$data_to_parse = $new_file_size - $old_file_size;
	if ( $data_to_parse < 0 ) { // Log file has been rotated, read all. It is not possible on apache because server is restarted gracefully but perhaps user has done something...
		$data_to_parse  = $new_file_size;
		$full           = true;
		$logs['notice'] = '<strong>'. $now . '</strong> &gt; ' . sprintf( __('Log file has been rotated (previous size was %s and new one is %s)') , human_filesize($old_file_size) , human_filesize($new_file_size) );
	}
	if ( $old_file_size == 0 ) {
		$full           = true;
	}
}


///////////////
// Read file //
///////////////
for ( $x_pos = 0, $ln = 0, $line = '', $still = true; $still ; $x_pos-- ) {

	if ( fseek( $fl, $x_pos, SEEK_END ) === -1 ) {
		$still = false;
		$char = "\n";
	}
	else {
		$char = fgetc( $fl );
	}

	if ( $char === "\n" ) {
		$deal = $line;

		$line = '';

		if ( $deal != '' ) {

			if ( $search_lastline ) { // Get the last line of the file
				$file_lastline   = sha1( $deal );
				$search_lastline = false;
			}

			if ( $bytes > $data_to_parse ) { // We have reach the bytes to manage
				if ( $old_lastline != sha1( $deal ) ) { // So the new line should be the last line of the previous time
					// This is not the case, so the file has been rotated and the new log file is bigger than the previous time
					// So we have to contnue computing to find the user wanted count of lines (and alert user about the file change)
					$logs['notice'] = '<strong>'. $now . '</strong> &gt; ' . __('Log file has been rotated');
					$full = true;
				}
				else {
					// Ok lines are the same so just stop and return found lines
					break;
				}
			}

			$log = parser( $regex , $match , $deal , $types , $tz );
			if ( is_array( $log ) ) {
				$return_log        = true;
				$last_field_append = ( count( $buffer ) > 0 ) ? "\n" . implode( "\n" ,  array_reverse( $buffer ) ) : '';;
				$buffer            = array();

				foreach ( $log as $key => $value ) {
					if ( $key === $multiline ) {
						$value .= $last_field_append;
						$deal  .= $last_field_append;
						$log[ $key ] = $value;
					}
					if ( ( isset( $exclude[ $key ] ) ) && ( is_array( $exclude[ $key ] ) ) ) {
						foreach ( $exclude[ $key ] as $ekey => $reg ) {
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
				if ( $return_log === false ) {
					$skip++;
				}
				else {
					if ( $search != '' ) { // Search
						if ( $regsearch ) { // Regex
							$return_log = preg_match( $search , $deal . $last_field_append );
							if ( $return_log === 0 ) $return_log = false;
						}
						else { // simple string
							$return_log = strpos( $deal . $last_field_append, $search );
						}
					}
					if ( $return_log === false ) {
						$skip++;
					}
					else {
						$found            = true;
						$log['pml']       = $deal . $last_field_append;
						$logs[ 'logs' ][] = $log;
						$ln++;
					}
				}
			}
			else if ( $multiline != '' ) {
				$buffer[] = $deal;
			}
			else {
				$error++;
			}

			if ( $ln >= $max ) { // Break if we have found the wanted count of logs
				break;
			}

		}

		if ( microtime( true ) - $start > MAX_SEARCH_LOG_TIME ) { // Break if time computing is too high
			$abort = true;
			break;
		}

		continue; // continue without keeping the \n
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

$logs['found']       = $found;
$logs['abort']       = $abort;
$logs['regsearch']   = $regsearch;
$logs['search']      = $search;
$logs['full']        = $full;
$logs['newfilesize'] = $new_file_size;
$logs['lastline']    = $file_lastline;
$logs['fingerprint'] = md5( serialize( @$logs['logs'] ) );


///////////////////////
// File Modification //
///////////////////////
$filem = new DateTime( );
$filem->setTimestamp( filemtime( $file_path ) );
if ( ! is_null( $tz ) ) {
	$filem->setTimezone( new DateTimeZone( $tz ) );
}
$filem = $filem->format( 'Y/m/d H:i:s' );


////////////////
// End Tuning //
////////////////
$now              = microtime( true );
$duration         = (int) ( ( $now - $start ) * 1000 );
$logs['footer']   = sprintf( __( '%s in <code>%sms</code> with <code>%s</code> of logs, <code>%s</code> skipped line(s), <code>%s</code> unreadable line(s).<br/>File <code>%s</code> was last modified on <code>%s</code> at <code>%s</code>, size is <code>%s</code>%s' )
	, ( $ln > 1 ) ? sprintf( __('%s new logs found') , $ln ) : ( ( $ln ==0 ) ? __( 'no new log found') : __( '1 new log found') )
	, $duration
	, human_filesize($bytes)
	, $skip
	, $error
	, $file_path
	, $filem
	, $tz
	, human_filesize( $new_file_size )
	, ( isset( $files[ $file_id ][ 'format' ][ 'type' ] ) ) ? ', ' . sprintf( __('log type is <code>%s</code>') , $files[ $file_id ][ 'format' ][ 'type' ] ) : ''
);


echo json_encode( $logs );
die();

?>