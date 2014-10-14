<?php
/*! pimpmylog - 1.2.2 - 9a33cb1ddeb7bd8ba31267bb07e4d3ef2d7295ea*/
/*
 * pimpmylog
 * http://pimpmylog.com
 *
 * Copyright (c) 2014 Potsky, contributors
 * Licensed under the GPLv3 license.
 */
?><?php
if(function_exists('xdebug_disable')) { xdebug_disable(); }

/*
|--------------------------------------------------------------------------
| Global parameters
|--------------------------------------------------------------------------
|
*/
define( 'YEAR'                               , @date( "Y" ) );
define( 'PHP_VERSION_REQUIRED'               , '5.2' );
define( 'HELP_URL'                           , 'http://pimpmylog.com' );
define( 'CONFIG_FILE_NAME'                   , 'config.user.json' );
define( 'CONFIG_FILE_TEMP'                   , '../' . CONFIG_FILE_NAME . '.tmp' );
define( 'CONFIG_FILE'                        , '../' . CONFIG_FILE_NAME );
define( 'CONFIG_FILE_MODE'                   , 0444 );
define( 'DEFAULT_LOCALE'                     , 'gb_GB' );
define( 'DEFAULT_TITLE'                      , 'Pimp my Log' );
define( 'DEFAULT_TITLE_FILE'                 , 'Pimp my Log [%f]' );
define( 'DEFAULT_NAV_TITLE'                  , '' );
define( 'DEFAULT_FOOTER'                     , '&copy; <a href="http://www.potsky.com" target="doc">Potsky</a> 2007-' . YEAR . ' - <a href="http://pimpmylog.com" target="doc">Pimp my Log</a>');
define( 'DEFAULT_LOGS_MAX'                   , 50 );
define( 'DEFAULT_LOGS_REFRESH'               , 0 );
define( 'DEFAULT_NOTIFICATION'               , false );
define( 'DEFAULT_PULL_TO_REFRESH'            , true );
define( 'DEFAULT_NOTIFICATION_TITLE'         , 'New logs [%f]' );
define( 'DEFAULT_GOOGLE_ANALYTICS'           , 'UA-XXXXX-X' );
define( 'DEFAULT_GEOIP_URL'                  , 'http://www.geoiptool.com/en/?IP=%p' );
define( 'DEFAULT_CHECK_UPGRADE'              , true );
define( 'DEFAULT_PIMPMYLOG_VERSION_URL'      , 'http://demo.pimpmylog.com/version.js' );
define( 'DEFAULT_PIMPMYLOG_ISSUE_LINK'       , 'https://github.com/potsky/PimpMyLog/issues/' );
define( 'DEFAULT_MAX_SEARCH_LOG_TIME'        , 5 );
define( 'DEFAULT_FILE_SELECTOR'              , 'bs' );
define( 'DEFAULT_USER_CONFIGURATION_DIR'     , 'config.user.d' );


$tz_available     = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
$locale_default   = 'en_GB';
$locale_available = array(
	'en_GB' => 'English',
	'fr_FR' => 'Français',
	'pt_BR' => 'Português do Brasil',
);
$locale_numeraljs = array(
	'en_GB' => 'en-gb',
	'fr_FR' => 'fr',
	'pt_BR' => 'pt-br',
);


/**
 * Simply return a localized text or empty string if the key is empty
 * Useful when localize variable which can be empty
 *
 * @param string  $text the text key
 *
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
 *
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
 * @param string  $types      A array of types for fields
 *
 * @return  mixed             An array where keys are internal tokens and values the corresponding values extracted from the log file. Or false if line is not matchable.
 */
function parser( $regex , $match , $log , $types , $tz = NULL ) {

	$result = array();
	preg_match_all( $regex , $log , $out, PREG_PATTERN_ORDER );
	if ( @count( $out[0] )==0 ) {
		return false;
	}
	foreach ( $match as $token => $key ) {

		$type = ( isset ( $types[ $token ] ) ) ? $types[ $token ] : 'txt';

		if ( substr( $type , 0 , 4 ) === 'date' ) {

			// Date is an array description with keys ( 'Y' : 5 , 'M' : 2 , ... )
			if ( is_array( $key ) && ( is_assoc( $key ) ) ) {
				$newdate = array();
				foreach ( $key as $k => $v ) {
					$newdate[ $k ] = @$out[ $v ][ 0 ];
				}
				if ( isset( $newdate['M'] ) ) {
					$str = $newdate['M'] . ' ' . $newdate['d'] . ' ' . $newdate['H'] . ':' . $newdate['i'] . ':' . $newdate['s'] . ' ' . $newdate['Y'];
				}
				else if ( isset( $newdate['m'] ) ) {
					$str = $newdate['Y'] . '/' . $newdate['m'] . '/' . $newdate['d'] . ' ' . $newdate['H'] . ':' . $newdate['i'] . ':' . $newdate['s'];
				}
			}
			// Date is an array description without keys ( 2 , ':' , 3 , '-' , ... )
			else if ( is_array( $key ) ) {
				$str = '';
				foreach ( $key as $v ) {
					$str .= ( is_string( $v ) ) ? $v : @$out[ $v ][0];
				}
			}
			else {
				$str = @$out[ $key ][0];
			}

			// remove part next to the last /
			$dateformat = ( substr( $type , 0 , 5 ) === 'date:' ) ? substr( $type , 5 ) : 'Y/m/d H:i:s';
			if ( ( $p = strrpos(	$dateformat , '/' ) ) !== false ) {
				$dateformat = substr( $dateformat , 0 , $p );
			}
			if ( ( $timestamp = strtotime( $str ) ) === false ) {
				$date = "ERROR ! Unable to convert this string to date : <code>$str</code>";
			}
			else {
				$date = new DateTime( );
				$date->setTimestamp( $timestamp );
				if ( ! is_null( $tz ) ) {
					$date->setTimezone( new DateTimeZone( $tz ) );
				}
				$date = $date->format( $dateformat );
			}

			$result[ $token ] = $date;
		}
		// Array description without keys ( 2 , ':' , 3 , '-' , ... )
		else if ( is_array( $key ) ) {
			$r = '';
			foreach ( $key as $v ) {
				$r .= ( is_string( $v ) ) ? $v : @$out[ $v ][0];
			}
			$result[ $token ] = $r;
		}

		else {
			$result[ $token ] = @$out[ $key ][0];
		}
	}
	return $result;
}


/**
 * Load all unset constants
 *
 * @return
 */
function load_default_constants() {
	if ( ! defined( 'LOCALE'                     ) ) define( 'LOCALE'                 , DEFAULT_LOCALE );
	if ( ! defined( 'TITLE'                      ) ) define( 'TITLE'                  , DEFAULT_TITLE );
	if ( ! defined( 'TITLE_FILE'                 ) ) define( 'TITLE_FILE'             , DEFAULT_TITLE_FILE );
	if ( ! defined( 'NAV_TITLE'                  ) ) define( 'NAV_TITLE'              , DEFAULT_NAV_TITLE );
	if ( ! defined( 'FOOTER'                     ) ) define( 'FOOTER'                 , DEFAULT_FOOTER );
	if ( ! defined( 'LOGS_MAX'                   ) ) define( 'LOGS_MAX'               , DEFAULT_LOGS_MAX );
	if ( ! defined( 'LOGS_REFRESH'               ) ) define( 'LOGS_REFRESH'           , DEFAULT_LOGS_REFRESH );
	if ( ! defined( 'NOTIFICATION'               ) ) define( 'NOTIFICATION'           , DEFAULT_NOTIFICATION );
	if ( ! defined( 'PULL_TO_REFRESH'            ) ) define( 'PULL_TO_REFRESH'        , DEFAULT_PULL_TO_REFRESH );
	if ( ! defined( 'NOTIFICATION_TITLE'         ) ) define( 'NOTIFICATION_TITLE'     , DEFAULT_NOTIFICATION_TITLE );
	if ( ! defined( 'GOOGLE_ANALYTICS'           ) ) define( 'GOOGLE_ANALYTICS'       , DEFAULT_GOOGLE_ANALYTICS );
	if ( ! defined( 'GEOIP_URL'                  ) ) define( 'GEOIP_URL'              , DEFAULT_GEOIP_URL );
	if ( ! defined( 'CHECK_UPGRADE'              ) ) define( 'CHECK_UPGRADE'          , DEFAULT_CHECK_UPGRADE );
	if ( ! defined( 'PIMPMYLOG_VERSION_URL'      ) ) define( 'PIMPMYLOG_VERSION_URL'  , DEFAULT_PIMPMYLOG_VERSION_URL );
	if ( ! defined( 'PIMPMYLOG_ISSUE_LINK'       ) ) define( 'PIMPMYLOG_ISSUE_LINK'   , DEFAULT_PIMPMYLOG_ISSUE_LINK );
	if ( ! defined( 'MAX_SEARCH_LOG_TIME'        ) ) define( 'MAX_SEARCH_LOG_TIME'    , DEFAULT_MAX_SEARCH_LOG_TIME );
	if ( ! defined( 'FILE_SELECTOR'              ) ) define( 'FILE_SELECTOR'          , DEFAULT_FILE_SELECTOR );
	if ( ! defined( 'USER_CONFIGURATION_DIR'     ) ) define( 'USER_CONFIGURATION_DIR' , DEFAULT_USER_CONFIGURATION_DIR );
}


/**
 * Load config file
 *
 * The file object will be load in a global $files variable.
 * Global beeeeurk ? Yeah I know... pml v2 will be full OO
 *
 * @param   string   $path                         the configuration file path
 * @param   boolean  $load_user_configuration_dir  do we have to parse all user configuration files ? No for upgrade for example...
 *
 * @return  boolean        Is there an error ?
 */
function config_load( $load_user_configuration_dir = true ) {
	global $files, $badges;

	$path = '';
	if ( file_exists( CONFIG_FILE ) ) {
		$path = CONFIG_FILE;
	} else if ( file_exists( CONFIG_FILE_NAME ) ) {
		$path = CONFIG_FILE_NAME;
	} else {
		return false;
	}

	// Read config file
	$config = json_decode( file_get_contents( $path ) , true );
	if ( $config == null ) {
		return false;
	}

	// Get badges
	$badges = $config[ 'badges' ];

	// Set user constant
	foreach ( $config[ 'globals' ] as $cst => $val ) {
		if ( $cst == strtoupper( $cst ) ) {
			@define( $cst , $val );
		}
	}

	// Set unset constants
	load_default_constants();

	// Append files from the USER_CONFIGURATION_DIR
	if ( $load_user_configuration_dir === true ) {
		$dir = null;
		if ( is_dir( USER_CONFIGURATION_DIR ) ) {
			$dir  = USER_CONFIGURATION_DIR;
			$base = 0;
		} else if ( is_dir( '..' . DIRECTORY_SEPARATOR . USER_CONFIGURATION_DIR ) ) {
			$dir  = '..' . DIRECTORY_SEPARATOR . USER_CONFIGURATION_DIR;
			$base = 3;
		}

		if ( ! is_null( $dir ) ) {
            $userfiles = new \RegexIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator( $dir , \RecursiveDirectoryIterator::SKIP_DOTS ),
                    \RecursiveIteratorIterator::SELF_FIRST,
                    \RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
                    ),
                '/^.+\.json$/i',
                \RecursiveRegexIterator::GET_MATCH
            );
            foreach ( $userfiles as $userfile ) {
				$c = json_decode( file_get_contents( $userfile[0] ) , true );
				if ( ! is_null( $c ) ) {
					foreach ( $c as $k => $v ) {
						$fileid = get_slug( mb_substr( $userfile[0] . '/' . $k , $base ) );
						$config[ 'files' ][ $fileid ] = $v;
					}
				}
            }
        }
    }

	// Try to generate the files tree if there are globs...
	$files_tmp = $config[ 'files' ];
	$files     = array();

	foreach ( $files_tmp as $fileid => $file ) {

		$path   = $file['path'];
		$count  = max( 1 , @(int)$file['count']);
		$gpaths = glob( $path , GLOB_MARK | GLOB_NOCHECK );

		if ( count( $gpaths ) == 0 ) {
		}
		else if ( count( $gpaths ) == 1 ) {
			$files[ $fileid ]            = $file;
			$files[ $fileid ]['path']    = $gpaths[0];
		}
		else {
			$new_paths = array();
			$i         = 1;

			foreach ( $gpaths as $path ) {
				$new_paths[ $path ] = filemtime( $path );
			}

			arsort( $new_paths , SORT_NUMERIC );

			foreach ( $new_paths as $path => $lastmodified ) {
				$files[ $fileid . '_' . $i ]            = $file;
				$files[ $fileid . '_' . $i ]['path']    = $path;
				$files[ $fileid . '_' . $i ]['display'].= ' > ' . basename( $path );
				if ( $i >= $count ) {
					break;
				}
				$i++;
			}
		}
	}

	return true;
}



/**
 * Check the $files array and fix it with default values
 * If there is a problem, return an array of errors
 * If everything is ok, return true;
 *
 * @return  mixed  true if ok, otherwise an array of errors
 */
function config_check() {
	global $files;
	$errors = array();

	if ( count( $files ) == 0 ) {
		$errors[] = __( 'No file is defined in <code>files</code> array' );
		return $errors;
	}

	foreach ( $files as $file_id => &$file ) {
		// error
		foreach ( array( 'display' , 'path' , 'format' ) as $mandatory ) {
			if ( ! isset( $file[ $mandatory ] ) ) {
				$errors[] = sprintf( __( '<code>%s</code> is mandatory for file ID <code>%s</code>' ) , $mandatory , $file_id );
			}
		}
		// fix
		foreach ( array(
				'max'       => LOGS_MAX,
				'refresh'   => LOGS_REFRESH,
				'notify'    => NOTIFICATION,
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
		1  => 1,
		2  => 2,
		3  => 3,
		4  => 4,
		5  => 5,
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
		5   => 5,
		10  => 10,
		20  => 20,
		50  => 50,
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
	session_start();
	if ( ! isset( $_SESSION[ 'csrf_token' ] ) ) {
		$_SESSION[ 'csrf_token' ] = md5( uniqid( '' , true ) );
	}
	session_write_close();
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


/**
 * [get_slug description]
 *
 * @param   string  $string     the string to slugify
 * @param   string  $separator  the separator
 *
 * @return  string              th slugified string
 */
function get_slug( $string, $separator = '-' ) {
	$accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
	$special_cases = array( '&' => 'and');
	$string        = mb_strtolower( trim( $string ), 'UTF-8' );
	$string        = str_replace( array_keys($special_cases), array_values( $special_cases), $string );
	$string        = preg_replace( $accents_regex, '$1', htmlentities( $string, ENT_QUOTES, 'UTF-8' ) );
	$string        = preg_replace("/[^a-z0-9]/u", "$separator", $string);
	$string        = preg_replace("/[$separator]+/u", "$separator", $string);
    return $string;
}


/**
 * Indents a flat JSON string to make it more human-readable.
 * For PHP < 5.4
 *
 * @param string $json The original JSON string to process.
 *
 * @return string Indented version of the original JSON string.
 */
function json_indent( $json ) {
	$result      = '';
	$pos         = 0;
	$strLen      = strlen($json);
	$indentStr   = '  ';
	$newLine     = "\n";
	$prevChar    = '';
	$outOfQuotes = true;
	for ($i=0; $i<=$strLen; $i++) {
		$char = substr($json, $i, 1);
		if ($char == '"' && $prevChar != '\\') {
			$outOfQuotes = !$outOfQuotes;
		} else if(($char == '}' || $char == ']') && $outOfQuotes) {
			$result .= $newLine;
			$pos --;
			for ($j=0; $j<$pos; $j++) {
				$result .= $indentStr;
			}
		}
		$result .= $char;
		if ( ( $char == ',' || $char == '{' || $char == '[' ) && $outOfQuotes ) {
			$result .= $newLine;
			if ( $char == '{' || $char == '[' ) {
				$pos ++;
			}
			for ( $j = 0 ; $j < $pos ; $j++ ) {
				$result .= $indentStr;
			}
		}
		$prevChar = $char;
	}
	return $result;
}


/**
 * Remove jsonp callback from a version file
 *
 * @param   string  $data  the json file with callback
 *
 * @return  string         the json file without callback
 */
function clean_json_version( $data ) {
	return str_replace(	array( '/*PSK*/pml_version_cb(/*PSK*/' , '/*PSK*/);/*PSK*/' , '/*PSK*/)/*PSK*/' ) , array( '' , '' , '' ) , $data );
}


/**
 * Try to guess who runs the server
 *
 * @return  string  a user information
 */
function get_server_user() {
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		return '';
	} else {
		return @exec( 'whoami' );
	}
}


/**
 * Tell whether this is a associative array (object in javascript) or not (array in javascript)
 *
 * @param   array   $arr  the array to test
 *
 * @return  boolean        true if $arr is an associative array
 */
function is_assoc( $arr ) {
    return array_keys( $arr ) !== range( 0 , count( $arr ) - 1 );
}


/*
|--------------------------------------------------------------------------
| Timezone
|--------------------------------------------------------------------------
|
*/
$tz = '';
if ( isset( $_GET['tz'] ) ) {
	$tz = $_GET['tz'];
}
else if ( defined( 'USER_TIME_ZONE' ) ) {
	$tz = USER_TIME_ZONE;
}
if ( ! in_array( $tz , $tz_available ) ) {
	$tz = date('e');
}


/*
|--------------------------------------------------------------------------
| Define locale and translation
|--------------------------------------------------------------------------
|
*/
$lang     = '';
$locale   = $locale_default;
$localejs = $locale_numeraljs[ $locale_default ];

if ( function_exists( 'bindtextdomain' ) ) {

	if ( isset( $_GET['l'] ) ) {
		$locale = $_GET['l'];
	}
	else if ( defined( 'LOCALE' ) ) {
		$locale = LOCALE;
	}
	else if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
		@list( $locale, $dumb ) = @explode( ',', $_SERVER['HTTP_ACCEPT_LANGUAGE'], 2 );
	}

	$locale            = str_replace( '-', '_', $locale );
	@list( $lang, $b ) = explode( '_', $locale );
	$locale            = strtolower( $lang ).'_'.strtoupper( $b );

	if ( ! array_key_exists( $locale, $locale_available ) ) {
		$locale = $locale_default;
	}

	putenv( 'LC_ALL=' . $locale );
	putenv( 'LANGUAGE=' . $locale );

	if ( $lang == 'fr' ) {
		setlocale( LC_ALL , $locale , $locale . '.utf8' , 'fra' );
	}
	else if ( $lang == 'de' ) {
		setlocale( LC_ALL , $locale , $locale . '.utf8' , 'deu_deu' , 'de' , 'ge' );
	}
	else {
		setlocale( LC_ALL , $locale , $locale . '.utf8' );
	}

	bindtextdomain( 'messages' , dirname( __FILE__ ) . '/../lang' );
	bind_textdomain_codeset( 'messages' , 'UTF-8' );
	textdomain( 'messages' );
}

else {

	function gettext( $text ) {
		return $text;
	}

}

?>