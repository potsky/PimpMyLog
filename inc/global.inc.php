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
/*
|--------------------------------------------------------------------------
| Disable direct call
|--------------------------------------------------------------------------
|
| People cannot access this page directly in their browser
|
*/
if ( realpath( __FILE__ ) === realpath( $_SERVER[ "SCRIPT_FILENAME" ] ) )
{
	header( $_SERVER[ 'SERVER_PROTOCOL' ] . ' 404 Not Found' );
	die();
}


/*
|--------------------------------------------------------------------------
| Disable XDebug
|--------------------------------------------------------------------------
|
| We do not need xdebug and if enabled, it is really slow !
|
*/
if ( function_exists( 'xdebug_disable' ) )
{
	xdebug_disable();
}


/*
|--------------------------------------------------------------------------
| Define root directories
|--------------------------------------------------------------------------
|
*/
if ( ! defined( 'PML_BASE' ) )
{
	define( 'PML_BASE' , realpath( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' ) );
}

if ( isset( $_SERVER[ 'PML_CONFIG_BASE' ] ) )
{
	define( 'PML_CONFIG_BASE' , $_SERVER[ 'PML_CONFIG_BASE' ] );
}

if ( ! defined( 'PML_CONFIG_BASE' ) )
{
	if ( upgrade_is_composer() )
	{
		define( 'PML_CONFIG_BASE' , realpath( PML_BASE . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.' ) );
	}
	else
	{
		define( 'PML_CONFIG_BASE' , PML_BASE );
	}
}


/*
|--------------------------------------------------------------------------
| MB fallback functions
|--------------------------------------------------------------------------
|
*/
if ( ! function_exists( 'mb_strlen' ) )
{
	function mb_strlen( $a )
	{
		return strlen( $a );
	}

	define( 'MB_SUPPORT' , false );
}
else
{
	define( 'MB_SUPPORT' , true );
}

if ( ! function_exists( 'mb_strtolower' ) )
{
	function mb_strtolower( $a , $b = '' )
	{
		return strtolower( $a );
	}
}

if ( ! function_exists( 'mb_check_encoding' ) )
{
	function mb_check_encoding( $a , $b )
	{
		return false;
	}
}

if ( ! function_exists( 'mb_substr' ) )
{
	function mb_substr( $a , $b , $c = 9999999 )
	{
		return substr( $a , $b , $c );
	}
}

if ( ! function_exists( 'mb_convert_encoding' ) )
{
	function mb_convert_encoding( $a , $b , $c )
	{
		return utf8_encode( $a );
	}
}


/*
|--------------------------------------------------------------------------
| Check for Suhosin
|--------------------------------------------------------------------------
|
| Suhosin is used in CPanel installations for example and users do not always
| know what is Suhosin and what it does
|
*/
define( 'SUHOSIN_LOADED' , ( extension_loaded( 'suhosin' ) ) );
define( 'SUHOSIN_URL' , "http://support.pimpmylog.com/kb/configuration/php-installations-with-suhosin-extension-and-exotic-ini-settings" );


/*
|--------------------------------------------------------------------------
| Enable safe mode
|--------------------------------------------------------------------------
|
| User can create a file named SAFE_MODE at root directory to tell Pimp My Log
| to avoid using functions like exec, ini_get, etc...
|
| These functions can be forbidden by PHP or Suhosin and are aimed to help
| user but there are not mandatory.
|
*/
define( 'SAFE_MODE' , file_exists( PML_CONFIG_BASE . DIRECTORY_SEPARATOR . 'SAFE_MODE' ) );
define( 'TIME_ZONE_SUPPORT_URL' , 'http://support.pimpmylog.com/discussions/problems/46-timezone-uncaught-exception' );


/*
|--------------------------------------------------------------------------
| Disable magic quotes
|--------------------------------------------------------------------------
|
| PHP 5.2 and 5.3 can have magic quotes enabled on the whole PHP install.
| http://support.pimpmylog.com/discussions/problems/56-regex-tester-match-is-not-a-valid-associative-array
|
*/
if ( get_magic_quotes_gpc() )
{
	$process = array( &$_GET , &$_POST , &$_COOKIE , &$_REQUEST );
	while ( list( $key , $val ) = each( $process ) )
	{
		foreach ( $val as $k => $v )
		{
			unset( $process[ $key ][ $k ] );
			if ( is_array( $v ) )
			{
				$process[ $key ][ stripslashes( $k ) ] = $v;
				$process[]                             = &$process[ $key ][ stripslashes( $k ) ];
			}
			else
			{
				$process[ $key ][ stripslashes( $k ) ] = stripslashes( $v );
			}
		}
	}
	unset( $process );
}


/*
|--------------------------------------------------------------------------
| Global internal parameters
|--------------------------------------------------------------------------
|
| These constants are defined for internal use only, do not change
|
*/
define( 'YEAR' , @date( "Y" ) );
define( 'PHP_VERSION_REQUIRED' , '5.2' );
define( 'CONFIG_FILE_MODE' , 0444 );
define( 'AUTH_CONFIGURATION_FILE' , 'config.auth.user.php' );
define( 'CONFIG_FILE_NAME' , 'config.user.php' );
define( 'CONFIG_FILE_NAME_BEFORE_1_5_0' , 'config.user.json' );
define( 'CONFIG_FILE_TEMP' , 'config.user.tmp.php' );


/*
|--------------------------------------------------------------------------
| Global internal default parameters
|--------------------------------------------------------------------------
|
| These constants are defined for internal use only.
| These constants will overwrite custom global parameters if their are not defined
| Overwrite them in your configuration file, not in this code directly !
|
*/
define( 'DEFAULT_AUTH_LOG_FILE_COUNT' , 100 );
define( 'DEFAULT_AUTO_UPGRADE' , false );
define( 'DEFAULT_CHECK_UPGRADE' , true );
define( 'DEFAULT_EXPORT' , true );
define( 'DEFAULT_FILE_SELECTOR' , 'bs' );
define( 'DEFAULT_FOOTER' , '&copy; <a href="http://www.potsky.com" target="doc">Potsky</a> 2007-' . YEAR . ' - <a href="http://pimpmylog.com" target="doc">Pimp my Log</a>' );
define( 'DEFAULT_FORGOTTEN_YOUR_PASSWORD_URL' , 'http://support.pimpmylog.com/kb/misc/forgotten-your-password' );
define( 'DEFAULT_GEOIP_URL' , 'http://www.geoiptool.com/en/?IP=%p' );
define( 'DEFAULT_PORT_URL' , 'http://www.adminsub.net/tcp-udp-port-finder/%p' );
define( 'DEFAULT_GOOGLE_ANALYTICS' , 'UA-XXXXX-X' );
define( 'DEFAULT_HELP_URL' , 'http://pimpmylog.com' );
define( 'DEFAULT_LOCALE' , 'gb_GB' );
define( 'DEFAULT_LOGS_MAX' , 50 );
define( 'DEFAULT_LOGS_REFRESH' , 0 );
define( 'DEFAULT_MAX_SEARCH_LOG_TIME' , 5 );
define( 'DEFAULT_NAV_TITLE' , '' );
define( 'DEFAULT_NOTIFICATION' , true );
define( 'DEFAULT_NOTIFICATION_TITLE' , 'New logs [%f]' );
define( 'DEFAULT_PIMPMYLOG_ISSUE_LINK' , 'https://github.com/potsky/PimpMyLog/issues/' );
define( 'DEFAULT_PIMPMYLOG_VERSION_URL' , 'http://demo.pimpmylog.com/version.js' );
define( 'DEFAULT_PULL_TO_REFRESH' , true );
define( 'DEFAULT_SORT_LOG_FILES' , 'default' );
define( 'DEFAULT_TAG_DISPLAY_LOG_FILES_COUNT' , true );
define( 'DEFAULT_TAG_NOT_TAGGED_FILES_ON_TOP' , true );
define( 'DEFAULT_TAG_SORT_TAG' , 'displayiasc' );
define( 'DEFAULT_TITLE' , 'Pimp my Log' );
define( 'DEFAULT_TITLE_FILE' , 'Pimp my Log [%f]' );
define( 'DEFAULT_UPGRADE_MANUALLY_URL' , 'http://pimpmylog.com/getting-started/#update' );
define( 'DEFAULT_USER_CONFIGURATION_DIR' , 'config.user.d' );


/*
|--------------------------------------------------------------------------
| Lang parameters
|--------------------------------------------------------------------------
|
| $locale_numeraljs is an associative array to convert a PHP locale to a
| javascript locale used by numeralJS
|
*/
$locale_default   = 'en_GB';
$locale_available = array(
	'en_GB' => 'English' ,
	'fr_FR' => 'Français' ,
	'pt_BR' => 'Português do Brasil' ,
);
$locale_numeraljs = array(
	'en_GB' => 'en-gb' ,
	'fr_FR' => 'fr' ,
	'pt_BR' => 'pt-br' ,
);


/*
|--------------------------------------------------------------------------
| Class autoloader
|--------------------------------------------------------------------------
|
*/
function my_autoloader( $ClassName )
{
	@include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $ClassName . ".php" );
}

spl_autoload_register( 'my_autoloader' );


/*
|--------------------------------------------------------------------------
| Functions declarations
|--------------------------------------------------------------------------
|
| Will be removed in PML v2.0, all functions will be grouped in classes
|
*/

/**
 * htmlentities
 *
 * @param string $text the text key
 *
 * @return   string     the translation
 */
function h( $text )
{
	return htmlentities( $text , ENT_QUOTES , 'UTF-8' );
}

/**
 * htmlentities for echo
 *
 * @param string $text the text key
 *
 * @return   string     the translation
 */
function _h( $text )
{
	_e( htmlentities( $text , ENT_QUOTES , 'UTF-8' ) );
}

/**
 * Simply return a localized text or empty string if the key is empty
 * Useful when localize variable which can be empty
 *
 * @param string $text the text key
 *
 * @return   string                      the translation
 */
function __( $text )
{
	if ( empty( $text ) )
	{
		return '';
	}
	else
	{
		return gettext( $text );
	}
}

/**
 * Simply echo a localized text
 *
 * @param string $text the text key
 *
 * @return   void
 */
function _e( $text )
{
	echo __( $text );
}

/**
 * Load all unset constants
 *
 * @return
 */
function load_default_constants()
{
	$defaults = array(
		'AUTH_LOG_FILE_COUNT' ,
		'AUTO_UPGRADE' ,
		'CHECK_UPGRADE' ,
		'DEFAULT_HELP_URL' ,
		'EXPORT' ,
		'FILE_SELECTOR' ,
		'FOOTER' ,
		'FORGOTTEN_YOUR_PASSWORD_URL' ,
		'GEOIP_URL' ,
		'PORT_URL' ,
		'GOOGLE_ANALYTICS' ,
		'LOCALE' ,
		'LOGS_MAX' ,
		'LOGS_REFRESH' ,
		'MAX_SEARCH_LOG_TIME' ,
		'NAV_TITLE' ,
		'NOTIFICATION' ,
		'NOTIFICATION_TITLE' ,
		'PIMPMYLOG_ISSUE_LINK' ,
		'PIMPMYLOG_VERSION_URL' ,
		'PULL_TO_REFRESH' ,
		'SORT_LOG_FILES' ,
		'TAG_SORT_TAG' ,
		'TAG_NOT_TAGGED_FILES_ON_TOP' ,
		'TAG_DISPLAY_LOG_FILES_COUNT' ,
		'TITLE' ,
		'TITLE_FILE' ,
		'UPGRADE_MANUALLY_URL' ,
		'USER_CONFIGURATION_DIR' ,
	);
	foreach ( $defaults as $d )
	{
		if ( ! defined( $d ) )
		{
			if ( defined( 'DEFAULT_' . $d ) )
			{
				define( $d , constant( 'DEFAULT_' . $d ) );
			}
			else
			{
				die( "Constant 'DEFAULT_$d' is not defined!" );
			}
		}
	}
}

/**
 * Try to find the main configuration file path
 * Configuration file can be a PHP file since 1.5.0 or a json file below
 * Both files contains a JSON configuration array but the PHP version is not callable by a guest on web
 *
 * @return  string  the path in a string or null if not found
 */
function get_config_file_path()
{
	$files = array(
		CONFIG_FILE_NAME ,
		CONFIG_FILE_NAME_BEFORE_1_5_0 ,
	);
	foreach ( $files as $f )
	{
		if ( file_exists( PML_CONFIG_BASE . DIRECTORY_SEPARATOR . $f ) )
		{
			return realpath( PML_CONFIG_BASE . DIRECTORY_SEPARATOR . $f );
		}
	}

	return null;
}

/**
 * Return the configuration file name
 *
 * @param   string $path the configuration file path or false if the function has to compute itself
 *
 * @return  string         the file name or null if configuration not found
 */
function get_config_file_name( $path = false )
{
	if ( $path === false )
	{
		$path = get_config_file_path();
	}
	if ( is_null( $path ) )
	{
		return null;
	}

	return basename( $path );
}

/**
 * Return the configuration array of a configuration file
 *
 * @param   string $path the configuration file path or false to let the function load the global configuration
 *
 * @return  array           the configuration array or null if file is invalid or if configuration file does not exist
 */
function get_config_file( $path = false )
{
	if ( $path === false )
	{
		$path = get_config_file_path();
	}
	if ( is_null( $path ) )
	{
		return null;
	}

	if ( strtolower( substr( $path , -3 , 3 ) ) === 'php' )
	{
		ob_start();
		require $path;
		$string = ob_get_clean();
	}
	else
	{
		$string = @file_get_contents( $path );
	}

	return json_decode( $string , true );
}

/**
 * Load config file
 *
 * @param   string  $path                        the configuration file path
 * @param   boolean $load_user_configuration_dir do we have to parse all user configuration files ? No for upgrade for
 *                                               example...
 *
 * @return  array    [ badges , files , $tz ]
 */
function config_load( $load_user_configuration_dir = true )
{
	$badges = false;
	$files  = false;
	$tz     = false;

	// Read config file
	$config = get_config_file();

	if ( is_null( $config ) )
	{
		return array( $badges , $files , $tz );
	}

	// Get badges
	$badges = $config[ 'badges' ];

	// Set user constant
	foreach ( $config[ 'globals' ] as $cst => $val )
	{
		if ( $cst == strtoupper( $cst ) )
		{
			if ( ! defined( $cst ) )
			{
				define( $cst , $val );
			}
		}
	}

	// Set unset constants
	load_default_constants();

	// Load the time zone
	$tz = get_user_time_zone();

	// Set time limit
	@set_time_limit( MAX_SEARCH_LOG_TIME + 2 );

	// Append files from the USER_CONFIGURATION_DIR
	if ( $load_user_configuration_dir === true )
	{
		if ( is_dir( PML_CONFIG_BASE . DIRECTORY_SEPARATOR . USER_CONFIGURATION_DIR ) )
		{
			$dir       = PML_CONFIG_BASE . DIRECTORY_SEPARATOR . USER_CONFIGURATION_DIR;
			$userfiles = new RegexIterator(
				new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator( $dir , RecursiveDirectoryIterator::SKIP_DOTS ) ,
					RecursiveIteratorIterator::SELF_FIRST ,
					RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
				) ,
				'/^.+\.(json|php)$/i' ,
				RecursiveRegexIterator::GET_MATCH
			);
			foreach ( $userfiles as $userfile )
			{
				$filepath = realpath( $userfile[ 0 ] );
				$c        = get_config_file( $filepath );
				if ( ! is_null( $c ) )
				{
					foreach ( $c as $k => $v )
					{
						$fileid                                          = get_slug( str_replace( PML_CONFIG_BASE , '' , $filepath ) . '/' . $k );
						$config[ 'files' ][ $fileid ]                    = $v;
						$config[ 'files' ][ $fileid ][ 'included_from' ] = $filepath;
					}
				}
			}
		}
	}

	// Oups, there is no file... abort
	if ( ! isset( $config[ 'files' ] ) )
	{
		return array( $badges , $files , $tz );
	}

	// Try to generate the files tree if there are globs...
	$files_tmp = $config[ 'files' ];
	$files     = array();

	foreach ( $files_tmp as $fileid => $file )
	{

		$path   = $file[ 'path' ];
		$count  = max( 1 , @(int)$file[ 'count' ] );
		$gpaths = glob( $path , GLOB_MARK | GLOB_NOCHECK );

		if ( count( $gpaths ) == 0 )
		{
		}
		else if ( count( $gpaths ) == 1 )
		{
			$files[ $fileid ]           = $file;
			$files[ $fileid ][ 'path' ] = $gpaths[ 0 ];
		}
		else
		{
			$new_paths = array();
			$i         = 1;

			foreach ( $gpaths as $path )
			{
				$new_paths[ $path ] = filemtime( $path );
			}

			// The most recent file will be the first
			arsort( $new_paths , SORT_NUMERIC );

			// The first file id is the ID of the configuration file then others files are suffixed with _2, _3, etc...
			foreach ( $new_paths as $path => $lastmodified )
			{
				$ext = ( $i > 1 ) ? '_' . $i : '';

				$files[ $fileid . $ext ]               = $file;
				$files[ $fileid . $ext ][ 'oid' ]      = $fileid;
				$files[ $fileid . $ext ][ 'odisplay' ] = $files[ $fileid . $ext ][ 'display' ];
				$files[ $fileid . $ext ][ 'path' ]     = $path;
				$files[ $fileid . $ext ][ 'display' ] .= ' > ' . basename( $path );
				if ( $i >= $count )
				{
					break;
				}
				$i++;
			}
		}
	}

	// Remove forbidden files
	if ( Sentinel::isAuthSet() )
	{ // authentication is enabled on this instance

		$username = Sentinel::getCurrentUsername();
		$final    = array();

		// Anonymous access only
		if ( is_null( $username ) )
		{
			foreach ( $files as $fileid => $file )
			{
				$a = $fileid;
				// glob file
				if ( isset( $files[ $fileid ][ 'oid' ] ) )
				{
					$a = $files[ $fileid ][ 'oid' ];
				}
				if ( Sentinel::isLogAnonymous( $a ) )
				{
					$final[ $fileid ] = $file;
				}
			}
		}

		// Anonymous access + User access
		else
		{
			foreach ( $files as $fileid => $file )
			{
				$a = $fileid;
				// glob file
				if ( isset( $files[ $fileid ][ 'oid' ] ) )
				{
					$a = $files[ $fileid ][ 'oid' ];
				}
				if ( ( Sentinel::userCanOnLogs( $a , 'r' , true , $username ) ) || ( Sentinel::isLogAnonymous( $a ) ) )
				{
					$final[ $fileid ] = $file;
				}
			}
		}

		$files = $final;
	}

	// Fix missing values with defaults
	foreach ( $files as $fileid => $file )
	{
		foreach ( array(
					  'max'     => LOGS_MAX ,
					  'refresh' => LOGS_REFRESH ,
					  'notify'  => NOTIFICATION ,
				  ) as $fix => $value )
		{
			if ( ! isset( $file[ $fix ] ) )
			{
				$files[ $fileid ][ $fix ] = $value;
			}
		}
	}

	// Finally sort files
	if ( ! function_exists( 'display_asc' ) )
	{
		function display_asc( $a , $b ) { return strcmp( $a[ "display" ] , $b[ "display" ] ); }
	}
	if ( ! function_exists( 'display_desc' ) )
	{
		function display_desc( $a , $b ) { return strcmp( $b[ "display" ] , $a[ "display" ] ); }
	}
	if ( ! function_exists( 'display_insensitive_asc' ) )
	{
		function display_insensitive_asc( $a , $b ) { return strcmp( $a[ "display" ] , $b[ "display" ] ); }
	}
	if ( ! function_exists( 'display_insensitive_desc' ) )
	{
		function display_insensitive_desc( $a , $b ) { return strcmp( $b[ "display" ] , $a[ "display" ] ); }
	}
	switch ( trim( str_replace( array( '-' , '_' , ' ' , 'nsensitive' ) , '' , SORT_LOG_FILES ) ) )
	{
		case 'display':
		case 'displayasc':
			uasort( $files , 'display_asc' );
			break;
		case 'displayi':
		case 'displayiasc':
			uasort( $files , 'display_insensitive_asc' );
			break;
		case 'displaydesc':
			uasort( $files , 'display_desc' );
			break;
		case 'displayidesc':
			uasort( $files , 'display_insensitive_desc' );
			break;
		default:
			# do not sort
			break;
	}

	return array( $badges , $files , $tz );
}

/**
 * Check the $files array and fix it with default values
 * If there is a problem, return an array of errors
 * If everything is ok, return true;
 *
 * @param   array $files log files
 *
 * @return  mixed  true if ok, otherwise an array of errors
 */
function config_check( $files )
{
	$errors = array();

	if ( ! is_array( $files ) )
	{
		if ( Sentinel::isAuthSet() )
		{
			return false;
		}

		$errors[] = __( 'No file is defined in <code>files</code> array' );

		return $errors;
	}

	if ( count( $files ) === 0 )
	{
		if ( Sentinel::isAuthSet() )
		{
			return false;
		}

		$errors[] = __( 'No file is defined in <code>files</code> array' );

		return $errors;
	}

	foreach ( $files as $file_id => &$file )
	{
		// error
		foreach ( array( 'display' , 'path' , 'format' ) as $mandatory )
		{
			if ( ! isset( $file[ $mandatory ] ) )
			{
				$errors[] = sprintf( __( '<code>%s</code> is mandatory for file ID <code>%s</code>' ) , $mandatory , $file_id );
			}
		}
	}

	if ( count( $errors ) == 0 )
	{
		return true;
	}
	else
	{
		return $errors;
	}
}

/**
 * Extract tags from the confiuration files
 *
 * @param   array $files the files configuration array
 *
 * @return  array          an array of tags with fileids
 */
function config_extract_tags( $files )
{
	$tags = array( '_' => array() );

	foreach ( $files as $fileid => $file )
	{
		// Tag found
		if ( isset( $file[ 'tags' ] ) )
		{
			if ( is_array( $file[ 'tags' ] ) )
			{
				foreach ( $file[ 'tags' ] as $tag )
				{
					$tags[ strval( $tag ) ][] = $fileid;
				}
			}
			else
			{
				$tags[ strval( $file[ 'tags' ] ) ][] = $fileid;
			}
		}
		// No tag
		else
		{
			$tags[ '_' ][] = $fileid;
		}
	}

	switch ( trim( str_replace( array( '-' , '_' , ' ' , 'nsensitive' ) , '' , TAG_SORT_TAG ) ) )
	{
		case 'display':
		case 'displayasc':
			if ( version_compare( PHP_VERSION , '5.4.0' ) >= 0 )
			{
				ksort( $tags , SORT_NATURAL );
			}
			else
			{
				ksort( $tags );
			}
			break;
		case 'displayi':
		case 'displayiasc':
			if ( version_compare( PHP_VERSION , '5.4.0' ) >= 0 )
			{
				ksort( $tags , SORT_NATURAL | SORT_FLAG_CASE );
			}
			else
			{
				ksort( $tags );
			}
			break;
		case 'displaydesc':
			if ( version_compare( PHP_VERSION , '5.4.0' ) >= 0 )
			{
				krsort( $tags , SORT_NATURAL );
			}
			else
			{
				krsort( $tags );
			}
			break;
		case 'displayidesc':
			if ( version_compare( PHP_VERSION , '5.4.0' ) >= 0 )
			{
				krsort( $tags , SORT_NATURAL | SORT_FLAG_CASE );
			}
			else
			{
				krsort( $tags );
			}
			break;
		default:
			# do not sort
			break;
	}

	return $tags;
}


/**
 * Get the list of refresh duration
 * The list is the default one below + :
 * - a custom value defined by user in PHP constant LOGS_REFRESH
 * - a custom value defined by user in all files in PHP array $files
 * The list must by unique and sorted
 *
 * @param   array $files log files
 *
 * @return  array  the list of selectable values
 */
function get_refresh_options( $files )
{
	$options                      = array(
		1  => 1 ,
		2  => 2 ,
		3  => 3 ,
		4  => 4 ,
		5  => 5 ,
		10 => 10 ,
		15 => 15 ,
		30 => 30 ,
		45 => 45 ,
		60 => 60 ,
	);
	$options[ (int)LOGS_REFRESH ] = (int)LOGS_REFRESH;
	foreach ( $files as $file_id => $file )
	{
		$options[ (int)@$file[ 'refresh' ] ] = (int)@$file[ 'refresh' ];
	}
	unset( $options[ 0 ] );
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
 * @param   array $files log files
 *
 * @return  array  the list of selectable values
 */
function get_max_options( $files )
{
	$options                  = array(
		5   => 5 ,
		10  => 10 ,
		20  => 20 ,
		50  => 50 ,
		100 => 100 ,
		200 => 200 ,
	);
	$options[ (int)LOGS_MAX ] = (int)LOGS_MAX;
	foreach ( $files as $file_id => $file )
	{
		$options[ (int)@$file[ 'max' ] ] = (int)@$file[ 'max' ];
	}
	unset( $options[ 0 ] );
	sort( $options );

	return $options;
}

/**
 * Return a human representation of a size
 *
 * @param   string  $bytes    the string representation (can be an int)
 * @param   integer $decimals the number of digits in the float part
 *
 * @return  string              the human size
 */
function human_filesize( $bytes , $decimals = 0 )
{
	$sz     = __( 'B KBMBGBTBPB' );
	$factor = floor( ( strlen( $bytes ) - 1 ) / 3 );

	return sprintf( "%.{$decimals}f" , $bytes / pow( 1024 , $factor ) ) . @$sz[ (int)$factor * 2 ];
}

/**
 * Get a Cross Script Request Forgery token
 *
 * @return  string  a token
 */
function csrf_get()
{
	Session::start();
	if ( ! isset( $_SESSION[ 'csrf_token' ] ) )
	{
		$_SESSION[ 'csrf_token' ] = md5( uniqid( '' , true ) );
	}
	Session::write_close();

	return $_SESSION[ 'csrf_token' ];
}

/**
 * Verify a Cross Script Request Forgery token
 *
 * @return  boolean   verified ?
 */
function csrf_verify()
{
	Session::start();
	$s = @$_SESSION[ 'csrf_token' ];
	Session::write_close();
	if ( ! isset( $_POST[ 'csrf_token' ] ) )
	{
		return false;
	}

	return ( $s === @$_POST[ 'csrf_token' ] );
}

/**
 * [get_slug description]
 *
 * @param   string $string    the string to slugify
 * @param   string $separator the separator
 *
 * @return  string              th slugified string
 */
function get_slug( $string , $separator = '-' )
{
	$accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
	$special_cases = array( '&' => 'and' );
	$string        = mb_strtolower( trim( $string ) , 'UTF-8' );
	$string        = str_replace( array_keys( $special_cases ) , array_values( $special_cases ) , $string );
	$string        = preg_replace( $accents_regex , '$1' , htmlentities( $string , ENT_QUOTES , 'UTF-8' ) );
	$string        = preg_replace( "/[^a-z0-9]/u" , "$separator" , $string );
	$string        = preg_replace( "/[$separator]+/u" , "$separator" , $string );

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
function json_indent( $json )
{
	$result      = '';
	$pos         = 0;
	$strLen      = strlen( $json );
	$indentStr   = '  ';
	$newLine     = "\n";
	$prevChar    = '';
	$outOfQuotes = true;
	for ( $i = 0 ; $i <= $strLen ; $i++ )
	{
		$char = substr( $json , $i , 1 );
		if ( $char == '"' && $prevChar != '\\' )
		{
			$outOfQuotes = ! $outOfQuotes;
		}
		elseif ( ( $char == '}' || $char == ']' ) && $outOfQuotes )
		{
			$result .= $newLine;
			$pos--;
			for ( $j = 0 ; $j < $pos ; $j++ )
			{
				$result .= $indentStr;
			}
		}
		$result .= $char;
		if ( ( $char == ',' || $char == '{' || $char == '[' ) && $outOfQuotes )
		{
			$result .= $newLine;
			if ( $char == '{' || $char == '[' )
			{
				$pos++;
			}
			for ( $j = 0 ; $j < $pos ; $j++ )
			{
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
 * @param   string $data the json file with callback
 *
 * @return  string         the json file without callback
 */
function clean_json_version( $data )
{
	return str_replace( array( '/*PSK*/pml_version_cb(/*PSK*/' , '/*PSK*/);/*PSK*/' , '/*PSK*/)/*PSK*/' ) , array( '' , '' , '' ) , $data );
}

/**
 * Do nothing for set_error_handler PHP5.2 style
 *
 * @param   integer $errno
 * @param   string  $errstr
 *
 * @return  void
 */
function dumb_test( $errno , $errstr )
{

}

/**
 * Try to guess who runs the server
 *
 * @return  string  a user information
 */
function get_server_user()
{
	if ( strtoupper( substr( PHP_OS , 0 , 3 ) ) === 'WIN' )
	{
		return '';
	}
	else if ( SAFE_MODE === true )
	{
		// for Suhosin
		return '';
	}
	else
	{
		// for PHP disabled_func
		set_error_handler( "dumb_test" );

		$a = exec( 'whoami' );
		restore_error_handler();

		return $a;
	}
}

/**
 * Tell whether this is a associative array (object in javascript) or not (array in javascript)
 *
 * @param   array $arr the array to test
 *
 * @return  boolean        true if $arr is an associative array
 */
function is_assoc( $arr )
{
	return array_keys( $arr ) !== range( 0 , count( $arr ) - 1 );
}

/**
 * Generate a random string
 *
 * @param   integer $l the string length
 * @param   string  $c a list of char in a string taken to generate the string
 *
 * @return  string       a random string of $l chars
 */
function mt_rand_str( $l , $c = 'abcdefghijklmnopqrstuvwxyz1234567890_-ABCDEFGHIJKLMNOPQRSTUVWXYZ' )
{
	for ( $s = '' , $cl = strlen( $c ) - 1 , $i = 0 ; $i < $l ; $s .= $c[ mt_rand( 0 , $cl ) ] , ++$i )
	{
		;
	}

	return $s;
}


/**
 * Get the local ip address of the current client according to proxy and more...
 *
 * @return  string  an ip address
 */
function get_client_ip()
{
	$ip = '';
	if ( isset( $_SERVER[ 'HTTP_CLIENT_IP' ] ) )
	{
		$ip = $_SERVER[ 'HTTP_CLIENT_IP' ];
	}
	else if ( isset( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) )
	{
		$ip = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
	}
	else if ( isset( $_SERVER[ 'HTTP_X_FORWARDED' ] ) )
	{
		$ip = $_SERVER[ 'HTTP_X_FORWARDED' ];
	}
	else if ( isset( $_SERVER[ 'HTTP_FORWARDED_FOR' ] ) )
	{
		$ip = $_SERVER[ 'HTTP_FORWARDED_FOR' ];
	}
	else if ( isset( $_SERVER[ 'HTTP_FORWARDED' ] ) )
	{
		$ip = $_SERVER[ 'HTTP_FORWARDED' ];
	}
	else if ( isset( $_SERVER[ 'REMOTE_ADDR' ] ) )
	{
		$ip = $_SERVER[ 'REMOTE_ADDR' ];
	}

	return $ip;
}

/**
 * Get the current url
 *
 * @param   boolean $q include the query string
 *
 * @return  string  current url
 */
function get_current_url( $q = false )
{
	if ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) )
	{ // only web, not unittests
		$s        = &$_SERVER;
		$ssl      = ( ! empty( $s[ 'HTTPS' ] ) && $s[ 'HTTPS' ] == 'on' ) ? true : false;
		$sp       = strtolower( @$s[ 'SERVER_PROTOCOL' ] );
		$protocol = substr( $sp , 0 , strpos( $sp , '/' ) ) . ( ( $ssl ) ? 's' : '' );
		$port     = @$s[ 'SERVER_PORT' ];
		$port     = ( ( ! $ssl && $port == '80' ) || ( $ssl && $port == '443' ) ) ? '' : ':' . $port;
		$host     = isset( $s[ 'HTTP_X_FORWARDED_HOST' ] ) ? $s[ 'HTTP_X_FORWARDED_HOST' ] : ( isset( $s[ 'HTTP_HOST' ] ) ? $s[ 'HTTP_HOST' ] : null );
		$host     = isset( $host ) ? $host : @$s[ 'SERVER_NAME' ] . $port;
		$uri      = $protocol . '://' . $host . @$s[ 'REQUEST_URI' ];
		$segments = explode( '?' , $uri , 2 );
		$url      = $segments[ 0 ];
		if ( $q === true )
		{
			if ( isset( $_SERVER[ 'QUERY_STRING' ] ) )
			{
				$url .= '?' . $_SERVER[ 'QUERY_STRING' ];
			}
		}

		return $url;
	}

	return null;
}

/**
 * Tell if the provided IP address is local or not
 *
 * @param   string $ip an ipv4 address
 *
 * @return  boolean       true if address is local
 */
function is_not_local_ip( $ip )
{
	$ip = trim( $ip );
	if ( $ip === '127.0.0.1' )
	{
		return false;
	}

	return filter_var( $ip , FILTER_VALIDATE_IP , FILTER_FLAG_NO_PRIV_RANGE );
}

/**
 * Return a 404 error
 *
 * @return  void
 */
function http404()
{
	header( $_SERVER[ 'SERVER_PROTOCOL' ] . ' 404 Not Found' );
	die();
}

/**
 * Return a 403 error
 *
 * @return  void
 */
function http403()
{
	header( $_SERVER[ 'SERVER_PROTOCOL' ] . ' 403 Forbidden' );
	die();
}

/**
 * Return a 500 error
 *
 * @return  void
 */
function http500()
{
	header( $_SERVER[ 'SERVER_PROTOCOL' ] . ' 500 Internal Server Error' );
	die();
}


/**
 * Clean an array recursivly
 *
 * @param   array $input the array to clean up
 *
 * @return  array          the cleaned array
 */
function array_filter_recursive( $input )
{
	foreach ( $input as &$value )
	{
		if ( is_array( $value ) )
		{
			$value = array_filter_recursive( $value );
		}
		else if ( is_object( $value ) )
		{
			$value = array_filter_recursive( (array)$value );
		}
	}

	return array_filter( $input );
}

/**
 * Return the current Pimp My Log Version
 *
 * @return  string  the version string or empty if not available
 */
function get_current_pml_version()
{
	$v    = '';
	$file = dirname( __FILE__ ) . '/../version.js';
	if ( file_exists( $file ) )
	{
		$j = json_decode( clean_json_version( @file_get_contents( $file ) ) , true );
		$v = @$j[ 'version' ];
	}

	return $v;
}

/**
 * Return the current Pimp My Log Version
 *
 * @return  string  the version string or empty if not available
 */
function get_current_pml_version_infos()
{
	$i    = array();
	$file = dirname( __FILE__ ) . '/../version.js';
	if ( file_exists( $file ) )
	{
		$j        = json_decode( clean_json_version( @file_get_contents( $file ) ) , true );
		$v        = @$j[ 'version' ];
		$i        = @$j[ 'changelog' ][ $v ];
		$i[ 'v' ] = $v;
	}

	return $i;
}

/**
 * Generate a xml string of the provided array
 *
 * @param   array  $array     the array to convert in XML
 * @param   string $node_name the node name for numerical arrays
 *
 * @return  string              the xml string
 */
function generate_xml_from_array( $array , $node_name )
{
	$xml = '';
	if ( is_array( $array ) || is_object( $array ) )
	{
		foreach ( $array as $key => $value )
		{
			if ( is_numeric( $key ) )
			{
				$key = $node_name;
			}

			$xml .= '<' . $key . '>' . generate_xml_from_array( $value , $node_name ) . '</' . $key . '>';
		}
	}
	else
	{
		$xml = htmlspecialchars( $array , ENT_QUOTES );
	}

	return $xml;
}


/**
 * Return a csv file from an array
 *
 * @param array $array
 *
 * @return null|string
 */
function array2csv( $array )
{
	if ( count( $array ) == 0 )
	{
		return null;
	}
	ob_start();
	$df = fopen( "php://output" , 'w' );
	fputcsv( $df , array_keys( reset( $array ) ) , "\t" );
	foreach ( $array as $row )
	{
		fputcsv( $df , $row , "\t" );
	}
	fclose( $df );

	return ob_get_clean();
}


/**
 * Return a UTC timestamp from a timestamp computed in a specific timezone
 *
 * @param   integer $timestamp the epoch timestamp
 * @param   string  $tzfrom    the timezone where the timesamp has been computed
 *
 * @return  integer              the epoch in UTC
 */
function get_non_UTC_timstamp( $timestamp = null , $tzfrom = null )
{
	if ( is_null( $tzfrom ) )
	{
		$tzfrom = date_default_timezone_get();
	}
	if ( is_null( $timestamp ) )
	{
		$timestamp = time();
	}

	$d = new DateTime( "@" . $timestamp );
	$d->setTimezone( new DateTimeZone( $tzfrom ) );

	return $timestamp - $d->getOffset();
}

/**
 * Try to guess if Pimp My Log is installed with composer
 *
 * @return  boolean
 */
function upgrade_is_composer()
{
	$a = false;

	// Catch errors for people who has activated open_basedir restrictions
	set_error_handler( "dumb_test" );

	if ( basename( PML_BASE ) !== 'pimp-my-log' )
	{
		$a = false;
	}
	else if ( ! is_dir( PML_BASE . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'potsky' ) )
	{
		$a = false;
	}
	else if ( ! is_dir( PML_BASE . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' ) )
	{
		$a = false;
	}
	else if ( ! file_exists( PML_BASE . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'composer.json' ) )
	{
		$a = false;
	}
	else
	{
		$a = true;
	}

	restore_error_handler();

	return $a;
}

/**
 * Try to guess if Pimp My Log is installed with git
 *
 * @return  boolean
 */
function upgrade_is_git()
{
	if ( SAFE_MODE === true )
	{
		return false;
	}

	if ( ! is_dir( PML_BASE . DIRECTORY_SEPARATOR . '.git' ) )
	{
		return false;
	}

	return true;
}

/**
 * Try to guess if Pimp My Log can pull with git
 *
 * @return  mixed
 */
function upgrade_can_git_pull()
{
	if ( SAFE_MODE === true )
	{
		return false;
	}

	$base = PML_BASE;

	// Check if git is callable and if all files are not changed
	$a = exec( 'cd ' . escapeshellarg( $base ) . '; git status -s' , $lines , $code );

	// Error while executing this comand
	if ( $code !== 0 )
	{
		return array( $code , $lines );
	}

	// Error, files have been modified
	if ( count( $lines ) !== 0 )
	{
		return array( $code , $lines );
	}

	// can write all files with this webserver user ?
	$canwrite = true;
	$lines    = array();
	$git      = mb_strlen( realpath( $base ) ) + 1;
	$pmlfiles = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $base ) ,
		RecursiveIteratorIterator::SELF_FIRST
	);

	foreach ( $pmlfiles as $f )
	{

		// Ignore all .git/* files
		if ( ( mb_substr( $f->getPathname() , $git , 4 ) ) === '.git' )
		{
			continue;
		}

		// check if this file is writable
		if ( ! $f->isWritable() )
		{

			// check if it ignored or not
			$b = exec( "git ls-files " . escapeshellarg( $f->getPathname() ) );
			if ( ! empty( $b ) )
			{
				$canwrite = false;
				$lines[]  = $f->getPathname();
			}
		}
	}

	if ( $canwrite === false )
	{
		return array( 2706 , $lines );
	}

	return true;
}


/**
 * Return the correct timezone according to settings
 *
 * @return bool|string
 */
function get_user_time_zone()
{
	$tz = '';
	if ( isset( $_POST[ 'tz' ] ) )
	{
		$tz = $_POST[ 'tz' ];
	}
	elseif ( isset( $_GET[ 'tz' ] ) )
	{
		$tz = $_GET[ 'tz' ];
	}
	elseif ( defined( 'USER_TIME_ZONE' ) )
	{
		$tz = USER_TIME_ZONE;
	}
	if ( ! in_array( $tz , DateTimeZone::listIdentifiers() ) )
	{
		$tz = @date( 'e' );
	}

	return $tz;
}


/*
|--------------------------------------------------------------------------
| Uniq ID
|--------------------------------------------------------------------------
|
| We generate a uniq ID for the current user in order to track how many people
| are currently using Pimp My Log. This value is stored in a cookie in order to
| keep it
|
*/
if ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) )
{ // only web, not unittests
	if ( ! isset( $_COOKIE[ 'u' ] ) )
	{
		$uuid = sha1( json_encode( $_SERVER ) . uniqid( '' , true ) );
		setcookie( 'u' , $uuid , time() + 60 * 60 * 24 * 3000 , '/' );
	}
	else
	{
		$uuid = $_COOKIE[ 'u' ];
	}
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

if ( function_exists( 'bindtextdomain' ) )
{

	if ( isset( $_GET[ 'l' ] ) )
	{
		$locale = $_GET[ 'l' ];
	}
	elseif ( isset( $_COOKIE[ 'pmllocale' ] ) )
	{
		$locale = $_COOKIE[ 'pmllocale' ];
	}
	elseif ( defined( 'LOCALE' ) )
	{
		$locale = LOCALE;
	}
	elseif ( isset( $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] ) )
	{
		@list( $locale , $dumb ) = @explode( ',' , $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] , 2 );
	}

	$locale = str_replace( '-' , '_' , $locale );
	@list( $lang , $b ) = explode( '_' , $locale );
	$locale = strtolower( $lang ) . '_' . strtoupper( $b );

	if ( ! array_key_exists( $locale , $locale_available ) )
	{
		$locale = $locale_default;
	}

	putenv( 'LC_ALL=' . $locale );
	putenv( 'LANGUAGE=' . $locale );

	if ( ( ! isset( $_COOKIE[ 'pmllocale' ] ) ) || ( $_COOKIE[ 'pmllocale' ] !== $locale ) )
	{
		if ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) )
		{ // only web, not unittests
			setcookie( 'pmllocale' , $locale , time() + 60 * 60 * 24 * 3000 );
		}
	}

	if ( $lang == 'fr' )
	{
		setlocale( LC_ALL , $locale , $locale . '.utf8' , 'fra' );
	}
	elseif ( $lang == 'de' )
	{
		setlocale( LC_ALL , $locale , $locale . '.utf8' , 'deu_deu' , 'de' , 'ge' );
	}
	else
	{
		setlocale( LC_ALL , $locale , $locale . '.utf8' );
	}

	bindtextdomain( 'messages' , dirname( __FILE__ ) . '/../lang' );
	bind_textdomain_codeset( 'messages' , 'UTF-8' );
	textdomain( 'messages' );

	define( 'GETTEXT_SUPPORT' , true );
}
else
{
	/**
	 * Fallback function for retrieving texts
	 *
	 * @param string $text the string to display
	 *
	 * @return string the same string but not translated!
	 */
	function gettext( $text )
	{
		return $text;
	}

	define( 'GETTEXT_SUPPORT' , false );

}

?>