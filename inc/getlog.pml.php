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
$current_user = Sentinel::attempt( $files );

/*
|--------------------------------------------------------------------------
| Checks
|--------------------------------------------------------------------------
|
*/
if (( ! isset( $_POST['file'] ) ) ||
    ( ! isset( $_POST['max'] ) ) ||
    ( ! isset( $_POST['ldv'] ) )
) {
    die();
}

/*
|--------------------------------------------------------------------------
| Error handling
|--------------------------------------------------------------------------
|
*/
/*
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    global $return;

    //error_log( print_r( $errstr , true ) );
    //error_log( print_r( $errfile , true ) );
    //error_log( print_r( $errline , true ) );
    //error_log( print_r( debug_backtrace() , true ) );

    if ( !( error_reporting() & $errno ) ) {
        return;
    }

    switch ($errno) {
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

function shutdown()
{
    $error = error_get_last();
    if ($error['type'] === E_ERROR) {
        echo json_encode(
            array(
                'error' => sprintf( __('<strong>PHP Error</strong> line %s: %s') , $error['line'] , $error['message'] )
            )
        );
    }
}
*/

/*
|--------------------------------------------------------------------------
| Prepare
|--------------------------------------------------------------------------
|
*/
$return              = array();
$file_id             = $_POST['file'];
$load_default_values = $_POST['ldv'];
$max                 = (int) $_POST['max'];
$reset               = (int) @$_POST['reset'];
$old_file_size       = (int) @$_POST['filesize'];
$search              = @$_POST['search'];
$old_lastline        = @$_POST['lastline'];

header('Content-type: application/json');

if ( ! csrf_verify() ) {
    $return['error'] = __( 'Please refresh the page.' );
    echo json_encode( $return );
    die();
}

if ( ! isset( $files[$file_id] ) ) {
    $return['error'] = sprintf( __( 'File ID <code>%s</code> does not exist, please review your configuration file and stop playing!' ) , $file_id );
    echo json_encode( $return );
    die();
}

$file_path = @$files[$file_id]['path'];
if ( ! file_exists( $file_path ) ) {
    $return['error'] = sprintf( __( 'File <code>%s</code> for file ID <code>%s</code> does not exist, please review your configuration file.' ) , $file_path , $file_id );
    echo json_encode( $return );
    die();
}

$errors = config_check( $files );
if ( is_array( $errors ) ) {
    $return['error'] = __( 'Configuration file has changed and is buggy now. Please refresh the page.' );
    echo json_encode( $return );
    die();
}

$regex     = $files[ $file_id ][ 'format' ][ 'regex' ];
$match     = $files[ $file_id ][ 'format' ][ 'match' ];
$types     = $files[ $file_id ][ 'format' ][ 'types' ];
$multiline = ( isset( $files[ $file_id ][ 'format' ][ 'multiline' ] ) ) ? $files[ $file_id ][ 'format' ][ 'multiline' ] : '';
$exclude   = ( isset( $files[ $file_id ][ 'format' ][ 'exclude' ]   ) ) ? $files[ $file_id ][ 'format' ][ 'exclude' ] : array();

/*
|--------------------------------------------------------------------------
| Timezone
|--------------------------------------------------------------------------
|
*/
$now = new DateTime();
if ( ! is_null( $tz ) ) {
    $now->setTimezone( new DateTimeZone( $tz ) );
}
$now = $now->format( 'Y/m/d H:i:s' );

/*
|--------------------------------------------------------------------------
| Set the beginning of the parser
|--------------------------------------------------------------------------
|
| If sp parameter exists, we need to beginning to count from the top and put an offset of sp
| If not set, just begin at the end of the file
|
*/
if ( isset( $_POST['sp'] ) ) {
    $start_offset = (int)$_POST['sp'] - 1;
    $start_from   = SEEK_SET;
    $load_more    = true;
}
else {
    $start_offset = 0;
    $start_from   = SEEK_END;
    $load_more    = false;
}

/*
|--------------------------------------------------------------------------
| Check how many bytes we have to read
|--------------------------------------------------------------------------
|
*/
$new_file_size = filesize( $file_path ); // Must be the nearest of fseek !
$full          = false;
if ($reset === 1) {
    $full          = true;
    $data_to_parse = $new_file_size;
}
else {
    $data_to_parse = $new_file_size - $old_file_size;
    if ($data_to_parse < 0) { // Log file has been rotated, read all. It is not possible on apache because server is restarted gracefully but perhaps user has done something...
        $data_to_parse    = $new_file_size;
        $full             = true;
        $return['notice'] = '<strong>'. $now . '</strong> : ' . sprintf( __('Log file has been rotated (previous size was %s and new one is %s)') , human_filesize($old_file_size) , human_filesize($new_file_size) );
    }
    if ($old_file_size === 0) {
        $full = true;
    }
}

/*
|--------------------------------------------------------------------------
| Get logs
|--------------------------------------------------------------------------
|
*/
$logs = LogParser::getNewLines( $regex , $match , $types , $tz , $max , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline ,  $search , $data_to_parse , $full , MAX_SEARCH_LOG_TIME );

/*
|--------------------------------------------------------------------------
| Error while getting logs
|--------------------------------------------------------------------------
|
*/
if ( ! is_array( $logs ) ) {
    switch ( $logs ) {
        case '1':
            $return['error'] = sprintf( __( 'File <code>%s</code> for file ID <code>%s</code> does not exist anymore...' ) , $file_path , $file_id );
            break;

        default:
            $return['error'] = sprintf( __( 'Unknown error %s' ) , $logs );
            break;
    }
}

/*
|--------------------------------------------------------------------------
| Return
|--------------------------------------------------------------------------
|
*/
else {

    $return = array_merge( $return , $logs );
    $ln     = $return['count'];
    $filem  = $return['filemodif'];

    if ( @$logs['notice'] === 1 ) {
        $return[ 'notice' ] = '<strong>'. $now . '</strong> &gt; ' . __('Log file has been rotated');
    }


    /*
    |--------------------------------------------------------------------------
    | Return headers if logs have been found
    |--------------------------------------------------------------------------
    |
    */
    if ( $logs[ 'found' ] === true ) {
        foreach ( $match as $k => $v ) {
            $return['headers'][ $k ] = __( $k );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Only mark the lastline for new logs, not when we load older log lines
    |--------------------------------------------------------------------------
    |
    */
    if ( $load_more === true ) {
        unset( $return['lastline']    );
    } else {
        $return['newfilesize'] = $new_file_size;
    }

    /*
    |--------------------------------------------------------------------------
    | Footer
    |--------------------------------------------------------------------------
    |
    */
    $return['footer']   = sprintf( __( '%s in <code>%sms</code> with <code>%s</code> of logs, <code>%s</code> skipped line(s), <code>%s</code> unreadable line(s).<br/>File <code>%s</code> was last modified on <code>%s</code> at <code>%s</code>, size is <code>%s</code>%s' )
        , ( $load_more === false )
            ? (
                ( $ln > 1 )
                ? sprintf( __('%s new logs found') , $ln )
                : (
                    ( $ln === 0 )
                        ? __( 'no new log found')
                        : __( '1 new log found')
                )
            )
            : (
                ( $ln > 1 )
                ? sprintf( __('%s old logs found') , $ln )
                : (
                    ( $ln === 0 )
                        ? __( 'no old log found')
                        : __( '1 olg log found')
                )
            )
        , $return['duration']
        , human_filesize( $return['bytes'] )
        , $return['skiplines']
        , $return['errorlines']
        , $file_path
        , $filem
        , $tz
        , human_filesize( $new_file_size )
        , ( isset( $files[ $file_id ][ 'format' ][ 'type' ] ) )
            ? ', ' . sprintf( __('log type is <code>%s</code>') , $files[ $file_id ][ 'format' ][ 'type' ] )
            : ''
    );

}

echo json_encode( $return );

die();


?>