<?php
//////////////////////////
// Set common variables //
//////////////////////////
define( 'YEAR'                 , date( "Y" ) );
define( 'PHP_VERSION_REQUIRED' , '5.2' );
define( 'HELP_URL'             , 'http://pimpmylog.com' );


/**
 * Simply return a localized text or empty string if the key is empty
 * Useful when localize variable which can be empty
 *
 * @param string  $text the text key
 * @return   string                      the translation
 */
function __( $text ) {
	if ( empty( $text ) )
		return '';
	else
		return gettext( $text );
}


/**
 * Simply echo a localized text
 *
 * @param string  $text the text key
 * @return   void
 */
function _e( $text ) {
	echo __( $text );
}


/**
 * The log parser
 *
 * @param string  $regex      The regex which describes the user log format
 * @param array   $match      An array which links internal tokens to regex matches
 * @param string  $log        The text log
 * @param string  $dateformat The wanted date format
 * @param string  $separator  The wanted seperator between matches when the message is composed
 *
 * @return  mixed             An array where keys are internal tokens and values the corresponding values extracted from the log file. Or false if line is not matchable.
 */
function parser( $regex , $match , $log , $dateformat = 'Y/m/d H:i:s' , $separator = ' :: ' ) {
	$result = array();
	preg_match_all( $regex , $log , $out, PREG_PATTERN_ORDER );
	if ( @count( $out[0] )==0 ) {
		return false;
	}
	foreach ( $match as $token => $key ) {
		if ( $token == 'Date' ) {
			if ( is_array( $key ) ) {
				$newdate = array();
				foreach ( $key as $k => $v ) {
					$newdate[$k] = @$out[ $v ][0];
				}
				$str = $newdate['M'] . ' ' . $newdate['D'] . ' ' . $newdate['H'] . ':' . $newdate['I'] . ':' . $newdate['S'] . ' ' . $newdate['Y'];
			}
			else {
				$str = @$out[ $key ][0];
			}
			if ( ( $timestamp = strtotime( $str ) ) === false ) {
				$date = "ERROR ! Unable to convert this string to date : <code>$str</code>";
			} else {
				$date = date( $dateformat , $timestamp );
			}
			$result[ $token ] = $date;
		}
		else if ( is_array( $key ) ) {
			$r = array();
			foreach ( $key as $k )
				$r[] = @$out[ $k ][0];
			$result[ $token ] = implode( $separator , $r );
		}
		else {
			$result[ $token ] = @$out[ $key ][0];
		}
	}
	return $result;
}


/**
 * Set unset constants
 */
function set_default_constants() {
	if ( ! defined( 'TITLE'                      ) ) define( 'TITLE'                      , 'Pimp my Log' );
	if ( ! defined( 'NAV_TITLE'                  ) ) define( 'NAV_TITLE'                  , '' );
	if ( ! defined( 'FOOTER'                     ) ) define( 'FOOTER'                     , '&copy; <a href="http://www.potsky.com" target="doc">Potsky</a> ' . date('Y') . ' - <a href="http://pimpmylog.com" target="doc">Pimp my Log</a>');
	if ( ! defined( 'LOGS_MAX'                   ) ) define( 'LOGS_MAX'                   , 50 );
	if ( ! defined( 'LOGS_REFRESH'               ) ) define( 'LOGS_REFRESH'               , 0 );
	if ( ! defined( 'NOTIFICATION'               ) ) define( 'NOTIFICATION'               , false );
	if ( ! defined( 'PULL_TO_REFRESH'            ) ) define( 'PULL_TO_REFRESH'            , true );
	if ( ! defined( 'NOTIFICATION_TITLE'         ) ) define( 'NOTIFICATION_TITLE'         , 'New logs [%f]' );
	if ( ! defined( 'GOOGLE_ANALYTICS'           ) ) define( 'GOOGLE_ANALYTICS'           , 'UA-XXXXX-X' );
	if ( ! defined( 'SEVERITY_COLOR_ON_ALL_COLS' ) ) define( 'SEVERITY_COLOR_ON_ALL_COLS' , true );
	if ( ! defined( 'GEOIP_URL'                  ) ) define( 'GEOIP_URL'                  , 'http://www.geoiptool.com/en/?IP=%p' );
	if ( ! defined( 'CHECK_UPGRADE'              ) ) define( 'CHECK_UPGRADE'              , true );
	if ( ! defined( 'PIMPMYLOG_VERSION_URL'      ) ) define( 'PIMPMYLOG_VERSION_URL'      , 'http://raw.github.com/potsky/PimpMyLog/master/version.json' );
	if ( ! defined( 'PIMPMYLOG_ISSUE_LINK'       ) ) define( 'PIMPMYLOG_ISSUE_LINK'       , 'https://github.com/potsky/PimpMyLog/issues/' );
	if ( ! defined( 'MAX_SEARCH_LOG_TIME'        ) ) define( 'MAX_SEARCH_LOG_TIME'        , 5 );
}


/**
 * Check the $files array and fix it with default values
 * If there is a problem, return an array of errors
 * If everything is ok, return true;
 *
 * @return  mixed  true if ok, otherwise an array of errors
 */
function check_config() {
	global $files;
	$errors = array();

	set_default_constants();

	if ( ! isset( $files ) ) {
		$errors[] = __('array <code>$files</code> is not defined');
		return $errors;
	}

	if ( ! is_array( $files ) ) {
		$errors[] = __('<code>$files</code> is not an array');
		return $errors;
	}

	if ( count( $files ) == 0 ) {
		$errors[] = __('No file is defined in <code>$files</code> array');
		return $errors;
	}

	foreach ( $files as $file_id => &$file ) {
		// error
		foreach ( array( 'display' , 'path' , 'format' ) as $mandatory ) {
			if ( ! isset( $file[ $mandatory ] ) ) {
				$errors[] = sprintf( __('<code>%s</code> is mandatory for file ID <code>%s</code>') , $mandatory , $file_id );
			}
		}
		// fix
		foreach ( array(
			'max'     => LOGS_MAX,
			'refresh' => LOGS_REFRESH,
			'notify'  => NOTIFICATION,
		) as $fix => $value ) {
			if ( ! isset( $file[ $fix ] ) ) {
				$file[ $fix ] = $value;
			}
		}
	}

	if ( count($errors) == 0 ) {
		return true;
	}
	else {
		return $errors;
	}
}


/**
 * Get the list of refresh duration
 * The list is the default one below + :
 * - a custom value defined by user in PHP constant LOGS_REFRESH
 * - a custom value defined by user in all files in PHP array $files
 * The list must by unique and sorted
 *
 * @return  array  the list of selectable values
 */
function get_refresh_options() {
	global $files;
	$options = array(
		1 => 1,
		2 => 2,
		3 => 3,
		4 => 4,
		5 => 5,
		10 => 10,
		15 => 15,
		30 => 30,
		45 => 45,
		60 => 60
	);
	$options[ (int)LOGS_REFRESH ] = (int)LOGS_REFRESH;
	foreach ( $files as $file_id => $file ) {
		$options[ (int) @$file['refresh'] ] = (int) @$file['refresh'];
	}
	unset( $options[0] );
	sort( $options );
	return $options;
}


/**
 * Get the list of displayed logs count
 * The list is the default one below + :
 * - a custom value defined by user in PHP constant LOGS_MAX
 * - a custom value defined by user in all files in PHP array $files
 * The list must by unique and sorted
 *
 * @return  array  the list of selectable values
 */
function get_max_options() {
	global $files;
	$options = array(
		5 => 5,
		10 => 10,
		20 => 20,
		50 => 50,
		100 => 100,
		200 => 200
	);
	$options[ (int)LOGS_MAX ] = (int)LOGS_MAX;
	foreach ( $files as $file_id => $file ) {
		$options[ (int) @$file['max'] ] = (int) @$file['max'];
	}
	unset( $options[0] );
	sort( $options );
	return $options;
}


/**
 * Return a human representation of a size
 *
 * @param   string   $bytes     the string representation (can be an int)
 * @param   integer  $decimals  the number of digits in the float part
 *
 * @return  string              the human size
 */
function human_filesize( $bytes, $decimals = 0 ) {
	$sz = __( 'B KBMBGBTBPB' );
	$factor = floor( ( strlen( $bytes ) - 1 ) / 3 );
	return sprintf( "%.{$decimals}f", $bytes / pow( 1024, $factor ) ) . @$sz[$factor*2];
}


/**
 * Get a Cross Script Request Forgery token
 *
 * @return  string  a token
 */
function csrf_get() {
	if ( ! isset( $_SESSION[ 'csrf_token' ] ) ) {
		session_start();
		$_SESSION[ 'csrf_token' ] = md5( uniqid( '' , true ) );
		session_write_close();
	}
	return $_SESSION[ 'csrf_token' ];
}


/**
 * Verify a Cross Script Request Forgery token
 *
 * @return  boolean   verified ?
 */
function csrf_verify() {
	session_start();
	$s = @$_SESSION[ 'csrf_token' ];
	session_write_close();
	if ( ! isset( $_POST[ 'csrf_token' ] ) )
		return false;
	return ( $s === @$_POST[ 'csrf_token' ] );
}

