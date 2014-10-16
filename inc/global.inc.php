<?php
/*
|--------------------------------------------------------------------------
| Disable direct call
|--------------------------------------------------------------------------
|
| People cannot access this page directly in their browser
|
*/
if ( realpath( __FILE__ ) === realpath( $_SERVER[ "SCRIPT_FILENAME" ] ) ) {
    header( $_SERVER[ 'SERVER_PROTOCOL' ] . ' 404 Not Found');
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
if ( function_exists( 'xdebug_disable' ) ) { xdebug_disable(); }

/*
|--------------------------------------------------------------------------
| Global internal parameters
|--------------------------------------------------------------------------
|
| These constants are defined for internal use only, do not change
|
*/
define( 'YEAR'                               , @date( "Y" ) );
define( 'PHP_VERSION_REQUIRED'               , '5.2' );
define( 'HELP_URL'                           , 'http://pimpmylog.com' );
define( 'CONFIG_FILE_MODE'                   , 0444 );
define( 'AUTH_CONFIGURATION_FILE'            , 'config.auth.user.php' );
define( 'CONFIG_FILE_NAME'                   , 'config.user.php' );
define( 'CONFIG_FILE_NAME_BEFORE_1_5_0'      , 'config.user.json' );
define( 'CONFIG_FILE_TEMP'                   , 'config.user.tmp.php' );


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
define( 'DEFAULT_AUTH_LOG_FILE'              , 'log.auth.user.php' );
define( 'DEFAULT_AUTH_LOG_FILE_COUNT'        , 100 );
define( 'DEFAULT_SORT_LOG_FILES'             , 'default' );



/*
|--------------------------------------------------------------------------
| Lang parameters
|--------------------------------------------------------------------------
|
| $locale_numeraljs is an associative array to convert a PHP locale to a
| javascript locale used by numeralJS
|
*/
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
function __($text)
{
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
function _e($text)
{
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
function parser($regex , $match , $log , $types , $tz = NULL)
{
    $result = array();
    preg_match_all( $regex , $log , $out, PREG_PATTERN_ORDER );
    if ( @count( $out[0] )==0 ) {
        return false;
    }
    foreach ($match as $token => $key) {

        $type = ( isset ( $types[ $token ] ) ) ? $types[ $token ] : 'txt';

        if ( substr( $type , 0 , 4 ) === 'date' ) {

            // Date is an array description with keys ( 'Y' : 5 , 'M' : 2 , ... )
            if ( is_array( $key ) && ( is_assoc( $key ) ) ) {
                $newdate = array();
                foreach ($key as $k => $v) {
                    $newdate[ $k ] = @$out[ $v ][ 0 ];
                }
                if ( isset( $newdate['M'] ) ) {
                    $str = $newdate['M'] . ' ' . $newdate['d'] . ' ' . $newdate['H'] . ':' . $newdate['i'] . ':' . $newdate['s'] . ' ' . $newdate['Y'];
                } elseif ( isset( $newdate['m'] ) ) {
                    $str = $newdate['Y'] . '/' . $newdate['m'] . '/' . $newdate['d'] . ' ' . $newdate['H'] . ':' . $newdate['i'] . ':' . $newdate['s'];
                }
            }
            // Date is an array description without keys ( 2 , ':' , 3 , '-' , ... )
            else if ( is_array( $key ) ) {
                $str = '';
                foreach ($key as $v) {
                    $str .= ( is_string( $v ) ) ? $v : @$out[ $v ][0];
                }
            } else {
                $str = @$out[ $key ][0];
            }

            // remove part next to the last /
            $dateformat = ( substr( $type , 0 , 5 ) === 'date:' ) ? substr( $type , 5 ) : 'Y/m/d H:i:s';
            if ( ( $p = strrpos(	$dateformat , '/' ) ) !== false ) {
                $dateformat = substr( $dateformat , 0 , $p );
            }
            if ( ( $timestamp = strtotime( $str ) ) === false ) {
                $date = "ERROR ! Unable to convert this string to date : <code>$str</code>";
            } else {
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
            foreach ($key as $v) {
                $r .= ( is_string( $v ) ) ? $v : @$out[ $v ][0];
            }
            $result[ $token ] = $r;
        } else {
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
function load_default_constants()
{
	$defaults = array(
		'LOCALE',
		'TITLE',
		'TITLE_FILE',
		'NAV_TITLE',
		'FOOTER',
		'LOGS_MAX',
		'LOGS_REFRESH',
		'NOTIFICATION',
		'PULL_TO_REFRESH',
		'NOTIFICATION_TITLE',
		'GOOGLE_ANALYTICS',
		'GEOIP_URL',
		'CHECK_UPGRADE',
		'PIMPMYLOG_VERSION_URL',
		'PIMPMYLOG_ISSUE_LINK',
		'MAX_SEARCH_LOG_TIME',
		'FILE_SELECTOR',
		'USER_CONFIGURATION_DIR',
		'AUTH_LOG_FILE',
		'SORT_LOG_FILES',
		'AUTH_LOG_FILE_COUNT',
	);
	foreach ( $defaults as $d ) {
		if ( ! defined( $d ) ) {
			if ( defined( 'DEFAULT_' . $d ) ) {
				define( $d , constant( 'DEFAULT_' . $d ) );
			} else {
				die( "Constant 'DEFAULT_$d' is not defined!" );
			}
		}
	}
}

/**
 * Try to find the main configuration file path
 * Configuration file can be a PHP file sinc 1.5.0 or a json file below
 * Both files contains a JSON configuration array but the PHP version is not callable by a guest on web
 *
 * @return  string  the path in a string or null if not found
 */
function get_config_file_path()
{
    $root  = dirname( __FILE__ ) . '/../';
    $files = array(
        CONFIG_FILE_NAME,
        CONFIG_FILE_NAME_BEFORE_1_5_0,
    );
    foreach ($files as $f) {
        if ( file_exists( $root . $f ) ) {
            return realpath( $root . $f );
        }
    }

    return null;
}

/**
 * Return the configuration file name
 *
 * @param   string  $path  the configuration file path or false if the function has to compute itself
 *
 * @return  string         the file name or null if configuration not found
 */
function get_config_file_name($path = false)
{
    if ( $path === false ) $path = get_config_file_path();
    if ( is_null( $path ) ) return null;
    return basename( $path );
}

/**
 * Return the configuration array of a configuration file
 *
 * @param   string  $path   the configuration file path or false to let the function load the global configuration
 *
 * @return  array           the configuration array or null if file is invalid or if configuration file does not exist
 */
function get_config_file($path = false)
{
    if ( $path === false ) $path = get_config_file_path();
    if ( is_null( $path ) ) return null;

    if ( strtolower( substr( $path , -3 , 3 ) ) === 'php' ) {
        ob_start();
        require $path;
        $string = ob_get_clean();
    } else {
        $string = @file_get_contents( $path );
    }

    return json_decode( $string , true );
}

/**
 * Load config file
 *
 * @param   string   $path                         the configuration file path
 * @param   boolean  $load_user_configuration_dir  do we have to parse all user configuration files ? No for upgrade for example...
 *
 * @return  array    [ badges , files ]
 */
function config_load($load_user_configuration_dir = true)
{
    $badges = false;
    $files  = false;

    // Read config file
    $config = get_config_file();

    if ( is_null( $config ) ) {
        return array( $badges , $files );
    }

    // Get badges
    $badges = $config[ 'badges' ];

    // Set user constant
    foreach ($config[ 'globals' ] as $cst => $val) {
        if ( $cst == strtoupper( $cst ) ) {
            @define( $cst , $val );
        }
    }

    // Set unset constants
    load_default_constants();

    // Append files from the USER_CONFIGURATION_DIR
    if ($load_user_configuration_dir === true) {
        $dir  = null;
        $base = dirname( __FILE__ ) . '/../';

        // Can be an absolute path or a request by index.php for example
        if ( is_dir( USER_CONFIGURATION_DIR ) ) {
            $dir  = USER_CONFIGURATION_DIR;
        }
        // Relative path from a subfolder or test suite
        else if ( is_dir( $base . USER_CONFIGURATION_DIR ) ) {
            $dir  = $base . USER_CONFIGURATION_DIR;
        }

        if ( ! is_null( $dir ) ) {
            $userfiles = new \RegexIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator( $dir , \RecursiveDirectoryIterator::SKIP_DOTS ),
                    \RecursiveIteratorIterator::SELF_FIRST,
                    \RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
                    ),
                '/^.+\.(json|php)$/i',
                \RecursiveRegexIterator::GET_MATCH
            );
            foreach ($userfiles as $userfile) {
                $c = get_config_file( $userfile[0] );
                if ( ! is_null( $c ) ) {
                    foreach ($c as $k => $v) {
                        $fileid = get_slug( $userfile[0] . '/' . $k );
                        $config[ 'files' ][ $fileid ]                  = $v;
                        $config[ 'files' ][ $fileid ]['included_from'] = $userfile[0];
                    }
                }
            }
        }
    }

    // Oups, there is no file... abort
    if ( ! isset( $config[ 'files' ] ) ) {
        return array( $badges , $files );
    }

    // Try to generate the files tree if there are globs...
    $files_tmp = $config[ 'files' ];
    $files     = array();

    foreach ($files_tmp as $fileid => $file) {

        $path   = $file['path'];
        $count  = max( 1 , @(int) $file['count']);
        $gpaths = glob( $path , GLOB_MARK | GLOB_NOCHECK );

        if ( count( $gpaths ) == 0 ) {
        } elseif ( count( $gpaths ) == 1 ) {
            $files[ $fileid ]            = $file;
            $files[ $fileid ]['path']    = $gpaths[0];
        } else {
            $new_paths = array();
            $i         = 1;

            foreach ($gpaths as $path) {
                $new_paths[ $path ] = filemtime( $path );
            }

            arsort( $new_paths , SORT_NUMERIC );

            foreach ($new_paths as $path => $lastmodified) {
                $files[ $fileid . '_' . $i ]            = $file;
                $files[ $fileid . '_' . $i ]['path']    = $path;
                $files[ $fileid . '_' . $i ]['display'].= ' > ' . basename( $path );
                if ($i >= $count) {
                    break;
                }
                $i++;
            }
        }
    }

    // Finaly sort files
	if ( ! function_exists( 'display_asc' ) )              { function display_asc($a, $b) { return strcmp( $a["display"] , $b["display"] ); } }
	if ( ! function_exists( 'display_desc' ) )             { function display_desc($a, $b) { return strcmp( $b["display"] , $a["display"] ); } }
	if ( ! function_exists( 'display_insensitive_asc' ) )  { function display_insensitive_asc($a, $b) { return strcmp( $a["display"] , $b["display"] ); } }
	if ( ! function_exists( 'display_insensitive_desc' ) ) { function display_insensitive_desc($a, $b) { return strcmp( $b["display"] , $a["display"] ); } }
	switch ( trim( str_replace( array( '-' , '_' , ' ' , 'nsensitive' ) , '' , SORT_LOG_FILES ) ) ) {
		case 'display':
		case 'displayasc':
			usort( $files , 'display_asc' );
			break;
		case 'displayi':
		case 'displayiasc':
			usort( $files , 'display_insensitive_asc' );
			break;
		case 'displaydesc':
			usort( $files , 'display_desc' );
			break;
		case 'displayidesc':
			usort( $files , 'display_insensitive_desc' );
			break;
		default:
			# do not sort
			break;
	}

    return array( $badges , $files );
}

/**
 * Check the $files array and fix it with default values
 * If there is a problem, return an array of errors
 * If everything is ok, return true;
 *
 * @param   array  $files  log files
 *
 * @return  mixed  true if ok, otherwise an array of errors
 */
function config_check( $files )
{
    $errors = array();

    if ( ! is_array( $files ) ) {
        $errors[] = __( 'No file is defined in <code>files</code> array' );

        return $errors;
    }

    if ( count( $files ) === 0 ) {
        $errors[] = __( 'No file is defined in <code>files</code> array' );

        return $errors;
    }

    foreach ($files as $file_id => &$file) {
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
    } else {
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
 * @param   array  $files  log files
 *
 * @return  array  the list of selectable values
 */
function get_refresh_options($files)
{
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
    $options[ (int) LOGS_REFRESH ] = (int) LOGS_REFRESH;
    foreach ($files as $file_id => $file) {
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
 * @param   array  $files  log files
 *
 * @return  array  the list of selectable values
 */
function get_max_options($files)
{
    $options = array(
        5   => 5,
        10  => 10,
        20  => 20,
        50  => 50,
        100 => 100,
        200 => 200
    );
    $options[ (int) LOGS_MAX ] = (int) LOGS_MAX;
    foreach ($files as $file_id => $file) {
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
function human_filesize($bytes, $decimals = 0)
{
    $sz = __( 'B KBMBGBTBPB' );
    $factor = floor( ( strlen( $bytes ) - 1 ) / 3 );

    return sprintf( "%.{$decimals}f", $bytes / pow( 1024, $factor ) ) . @$sz[$factor*2];
}

/**
 * Get a Cross Script Request Forgery token
 *
 * @return  string  a token
 */
function csrf_get()
{
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
function csrf_verify()
{
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
function get_slug($string, $separator = '-')
{
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
function json_indent($json)
{
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
        } elseif (($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        $result .= $char;
        if ( ( $char == ',' || $char == '{' || $char == '[' ) && $outOfQuotes ) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            for ($j = 0 ; $j < $pos ; $j++) {
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
function clean_json_version($data)
{
    return str_replace(	array( '/*PSK*/pml_version_cb(/*PSK*/' , '/*PSK*/);/*PSK*/' , '/*PSK*/)/*PSK*/' ) , array( '' , '' , '' ) , $data );
}

/**
 * Try to guess who runs the server
 *
 * @return  string  a user information
 */
function get_server_user()
{
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
function is_assoc($arr)
{
    return array_keys( $arr ) !== range( 0 , count( $arr ) - 1 );
}



/**
 * Generate a random string
 *
 * @param   integer  $l  the string length
 * @param   string   $c  a list of char in a string taken to generate the string
 *
 * @return  string       a random string of $l chars
 */
function mt_rand_str ($l, $c = 'abcdefghijklmnopqrstuvwxyz1234567890_-ABCDEFGHIJKLMNOPQRSTUVWXYZ') {
    for ($s = '', $cl = strlen($c)-1, $i = 0; $i < $l; $s .= $c[mt_rand(0, $cl)], ++$i);
    return $s;
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
} elseif ( defined( 'USER_TIME_ZONE' ) ) {
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
    } elseif ( defined( 'LOCALE' ) ) {
        $locale = LOCALE;
    } elseif ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
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

    if ($lang == 'fr') {
        setlocale( LC_ALL , $locale , $locale . '.utf8' , 'fra' );
    } elseif ($lang == 'de') {
        setlocale( LC_ALL , $locale , $locale . '.utf8' , 'deu_deu' , 'de' , 'ge' );
    } else {
        setlocale( LC_ALL , $locale , $locale . '.utf8' );
    }

    bindtextdomain( 'messages' , dirname( __FILE__ ) . '/../lang' );
    bind_textdomain_codeset( 'messages' , 'UTF-8' );
    textdomain( 'messages' );
} else {
    /**
     * Fallback function for retrieving texts
     *
     * @param string $text the string to display
     *
     * @return string the same string but not translated!
     */
    function gettext($text)
    {
        return $text;
    }

}

?>
