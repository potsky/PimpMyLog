<?php
include_once 'global.inc.php';


/*
|--------------------------------------------------------------------------
| Get parameters
|--------------------------------------------------------------------------
|
*/
if ( ! isset( $_GET['f'] ) ) {
	http404();
}
$file_id = $_GET['f'];
$search = @$_GET['s'];
$tz     = @$_GET['tz'];

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
|
*/
$user = null;

if ( ( isset( $_GET['t'] ) ) && ( isset( $_GET['h'] ) ) ) {

	if ( Sentinel::isAuthSet() ) { // perhaps auth has been deactivated since link generation
		$accesstoken = $_GET['t'];
		$username    = Sentinel::getUsernameFromAccessToken( $accesstoken );

		if ( is_null( $username ) ) { // User does not exist anymore
			http404();
		}

		$user = Sentinel::signInWithAccessToken( $accesstoken );

		if ( ! Sentinel::isSignValid( $_GET['h'] , array( 'f' => $file_id ) , $username ) ) {
			http403();
		}
	}

}
else if ( ( ! isset( $_GET['t'] ) ) && ( isset( $_GET['h'] ) ) ) {
	http404();
}
else if ( ( isset( $_GET['t'] ) ) && ( ! isset( $_GET['h'] ) ) ) {
	http404();
}


/*
|--------------------------------------------------------------------------
| Load config
|--------------------------------------------------------------------------
|
*/
list( $badges , $files ) = config_load();

if ( ! isset( $files[ $file_id ] ) ) {
	http403();
}

if ( ( isset( $files[ $file_id ]['export'] ) ) && ( $files[ $file_id ]['export'] === false ) ) {
	http403();
}

if ( ( EXPORT === false ) && ( ! isset( $files[ $file_id ]['export'] ) ) ) {
	http403();
}


/*
|--------------------------------------------------------------------------
| Get logs
|--------------------------------------------------------------------------
|
*/
$regex         = $files[ $file_id ][ 'format' ][ 'regex' ];
$match         = $files[ $file_id ][ 'format' ][ 'match' ];
$types         = $files[ $file_id ][ 'format' ][ 'types' ];
$multiline     = ( isset( $files[ $file_id ][ 'format' ][ 'multiline' ] ) ) ? $files[ $file_id ][ 'format' ][ 'multiline' ] : '';
$exclude       = ( isset( $files[ $file_id ][ 'format' ][ 'exclude' ]   ) ) ? $files[ $file_id ][ 'format' ][ 'exclude' ] : array();
$wanted_lines  = 50;
$file_path     = $files[$file_id]['path'];
$start_offset  = 0;
$start_from    = SEEK_END;
$load_more     = false;
$old_lastline  = '';
$data_to_parse = filesize( $file_path );
$full          = true;
$timeout       = 2;
$logs          = LogParser::getLines( $regex , $match , $types , $tz , $wanted_lines , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline ,  $search , $data_to_parse , $full , $timeout );

/*
|--------------------------------------------------------------------------
| Error while getting logs
|--------------------------------------------------------------------------
|
*/
if ( ! is_array( $logs ) ) {
	http500();
}


/*
|--------------------------------------------------------------------------
| File Modification
|--------------------------------------------------------------------------
|
*/
$filem = new DateTime( );
$filem->setTimestamp( filemtime( $file_path ) );
if ( ! is_null( $tz ) ) {
    $filem->setTimezone( new DateTimeZone( $tz ) );
}
$filem = $filem->format( 'Y/m/d H:i:s' );

/*
|--------------------------------------------------------------------------
| RSS
|--------------------------------------------------------------------------
|
*/
var_dump($logs);

?>
