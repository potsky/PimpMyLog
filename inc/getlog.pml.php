<?php
include_once 'global.inc.php';
list( $badges , $files ) = config_load();


/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
|
*/
$current_user = Sentinel::attempt();

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
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    global $logs;
    if ( !( error_reporting() & $errno ) ) {
        return;
    }
    switch ($errno) {
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

/*
|--------------------------------------------------------------------------
| Prepare
|--------------------------------------------------------------------------
|
*/
$start               = microtime( true );
$logs                = array();
$file_id             = $_POST['file'];
$load_default_values = $_POST['ldv'];
$max                 = (int) $_POST['max'];
$reset               = @(int) $_POST['reset'];
$old_file_size       = @(int) $_POST['filesize'];
$search              = @$_POST['search'];
$old_lastline        = @$_POST['lastline'];


header('Content-type: application/json');

if ( ! csrf_verify() ) {
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

$errors = config_check( $files );
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
if ($search != '') {
    $test      = @preg_match( $search , 'this is just a test !' );
    $regsearch = ( $test === false ) ? false : true;
}

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
| Let's go
|--------------------------------------------------------------------------
|
*/
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
if ($fl === false) {
    $logs['error'] = sprintf( __( 'File <code>%s</code> for file ID <code>%s</code> does not exist anymore...' ) , $file_path , $file_id );
    echo json_encode( $logs );
    die();
}


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
	$start_offset = (float)$_POST['sp'] - 1;
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
if ($reset === 1) {
    $full          = true;
    $data_to_parse = $new_file_size;
}
else {
    $data_to_parse = $new_file_size - $old_file_size;
    if ($data_to_parse < 0) { // Log file has been rotated, read all. It is not possible on apache because server is restarted gracefully but perhaps user has done something...
        $data_to_parse  = $new_file_size;
        $full           = true;
        $logs['notice'] = '<strong>'. $now . '</strong> : ' . sprintf( __('Log file has been rotated (previous size was %s and new one is %s)') , human_filesize($old_file_size) , human_filesize($new_file_size) );
    }
    if ($old_file_size == 0) {
        $full = true;
    }
}



/*
|--------------------------------------------------------------------------
| Read file
|--------------------------------------------------------------------------
|
*/
for ($x_pos = $start_offset, $ln = 0, $line = '', $still = true; $still ; $x_pos--) {

    // We have reached the beginning of file
    // Validate the previous read chars by simulating a NL
    if ( fseek( $fl, $x_pos, $start_from ) === -1 ) {
        $still = false;
        $char  = "\n";
    }

    // Read a char on a log line
    else {
        $char = fgetc( $fl );
    }

    // If the read char if a NL, we need to manage the previous buffered chars as a line
    if ($char === "\n") {

        // Copy the log line as an utf8 line
        $deal = utf8_encode( $line );

        // Reset the line for future reads
        $line = '';

        // Manage the new line
        if ($deal !== '') {

            // Get the last line of the file to compute the hash of this line
            if ( $search_lastline === true ) {
                $file_lastline   = sha1( $deal );
                $search_lastline = false;
            }

            // Check if we have reach the previous line in normal mode
            // We don't have to manage this when loading older logs
            if ( $load_more === false ) {

                // We have reach the count bytes to manage
                if ( $bytes > $data_to_parse ) {

                    // So the new line should be the last line of the previous time
                    if ( $old_lastline !== sha1( $deal ) ) {

                        // This is not the case, so the file has been rotated and the new log file is bigger than the previous time
                        // So we have to continue computing to find the user wanted count of lines (and alert user about the file change)
                        $logs['notice'] = '<strong>'. $now . '</strong> &gt; ' . __('Log file has been rotated');
                        $full = true;
                    }

                    // Ok lines are the same so just stop and return new found lines
                    else {
                        break;
                    }
                }
            }

            // Parse the new line
            $log = parser( $regex , $match , $deal , $types , $tz );

            // The line has been successfully parsed by the parser (user regex ok)
            if ( is_array( $log ) ) {

                // We will get this log by default but search can exclude this log later
                $return_log        = true;

                // If we previously have parsed some multilines, we need now to include them
                $last_field_append = ( count( $buffer ) > 0 ) ? "\n" . implode( "\n" ,  array_reverse( $buffer ) ) : '';;
                $buffer            = array();

                foreach ($log as $key => $value) {

                    // Manage multilines
                    if ( $key === $multiline ) {
                        $value .= $last_field_append;
                        $deal  .= $last_field_append;
                        $log[ $key ] = $value;
                    }

                    // Is this log excluded ?
                    if ( ( isset( $exclude[ $key ] ) ) && ( is_array( $exclude[ $key ] ) ) ) {
                        foreach ($exclude[ $key ] as $ekey => $reg) {
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

                // This line should be skipped because it has been excluded by user configuration
                if ($return_log === false) {
                    $skip++;
                }

                // Filter now this line by search
                else {

                    if ($search !== '') {

                        // Regex
                        if ($regsearch) {
                            $return_log = preg_match( $search , $deal . $last_field_append );
                            if ( $return_log === 0 ) $return_log = false;
                        }

                        // Simple search
                        else {
                            $return_log = strpos( $deal . $last_field_append, $search );
                        }
                    }

                    // Search excludes this line
                    if ($return_log === false) {
                        $skip++;
                    }

                    // Search includes this line
                    else {
                        $found            = true;
                        $log[ 'pml' ]     = $deal . $last_field_append;
                        $log[ 'pmlo'   ]  = ftell($fl);
                        $logs[ 'logs' ][] = $log;
                        $ln++;
                    }
                }
            }

            // The line has not been successfully parsed by the parser but multiline feature is enabled so we treat this line as a multiline
            elseif ( $multiline !== '' ) {
                $buffer[] = $deal;
            }

            // No multiline feature and unknown line : add this line as an error
            else {
                $error++;
            }

			// Break if we have found the wanted count of logs
            if ($ln >= $max) {
                break;
            }

        }

		// Break if time computing is too high
        if ( microtime( true ) - $start > MAX_SEARCH_LOG_TIME ) {
            $abort = true;
            break;
        }

		// continue directly without keeping the \n
        continue;
    }

    // Prepend the read char to the previous buffered chars
    $line = $char . $line;
    $bytes++;
}

// We need to store this value for load more when a search is active
// The last searched line to display os certainly not the first line of the file
// So if the value of $last_parsed_offset is 1, even if the last displayed line is not at offset 0 or 1, we must disable the Load More button
$last_parsed_offset = ftell($fl);

fclose( $fl );


/*
|--------------------------------------------------------------------------
| Return headers and meta
|--------------------------------------------------------------------------
|
*/
if ($found) {
    foreach ($match as $k => $v) {
        $logs['headers'][ $k ] = __( $k );
    }
}

$logs['found']       = $found;
$logs['abort']       = $abort;
$logs['regsearch']   = $regsearch;
$logs['search']      = $search;
$logs['full']        = $full;
$logs['lpo']         = $last_parsed_offset;
$logs['fingerprint'] = md5( serialize( @$logs['logs'] ) ); // Used to avoid notification on full refresh when nothing has finally changed

if ( $load_more === false ) {
    // Only mark the lastline for new logs, not when we load older log lines
    $logs['lastline']    = $file_lastline;
    $logs['newfilesize'] = $new_file_size;
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
| End tuning
|--------------------------------------------------------------------------
|
*/
$now              = microtime( true );
$duration         = (int) ( ( $now - $start ) * 1000 );
$logs['footer']   = sprintf( __( '%s in <code>%sms</code> with <code>%s</code> of logs, <code>%s</code> skipped line(s), <code>%s</code> unreadable line(s).<br/>File <code>%s</code> was last modified on <code>%s</code> at <code>%s</code>, size is <code>%s</code>%s' )
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
    , $duration
    , human_filesize($bytes)
    , $skip
    , $error
    , $file_path
    , $filem
    , $tz
    , human_filesize( $new_file_size )
    , ( isset( $files[ $file_id ][ 'format' ][ 'type' ] ) )
        ? ', ' . sprintf( __('log type is <code>%s</code>') , $files[ $file_id ][ 'format' ][ 'type' ] )
        : ''
);

echo json_encode( $logs );

die();

?>
