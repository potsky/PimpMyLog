<?php
include_once 'global.inc.php';
include_once '../config.inc.php';

/////////////
// Prepare //
/////////////
$start               = microtime( true );
$logs                = array();
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
for ( $x_pos = 0, $ln = 0, $line=''; fseek( $fl, $x_pos, SEEK_END ) !== -1; $x_pos-- ) {

	$char = fgetc( $fl );

	if ( $char === "\n" ) {

		/*
		if ( preg_matcha( $exclude, $line ) ) {
			$deal = '';
		}
		else {
			$deal = $line;
		}
		$line = '';
		*/
		$deal = $line;
		$line = '';

		if ( $deal != '' ) {
			$found = true;
			$log = parser( $regex , $match , $deal , 'Y/m/d h:i:s' , ' :: ' );
			if ( is_array( $log ) ) {
				$logs[ 'logs' ][] = $log;
			}
			$ln++;
			if ( $ln >= $max ) break;
		}

		continue;
	}

	$line = $char . $line;
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
$logs['found']          = $found;
$logs['file_last_time'] = filemtime( $file_path );


////////////////
// End Tuning //
////////////////
$now                    = microtime( true );
$duration               = (int) ( ( $now - $start ) * 1000 );
$logs['duration']       = sprintf( __( 'Computed in %sms' ) , $duration );

echo json_encode( $logs );
