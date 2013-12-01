<?php
include_once 'functionzzz.inc.php';


//////////////////////////
// Set common variables //
//////////////////////////
define( 'YEAR'     , date( "Y" ) );
define( 'HELP_URL' , 'https://github.com/potsky/PHPApacheLogViewer' );


///////////////////////////////////
// Define locale and translation //
///////////////////////////////////
$lang = '';
if ( function_exists( 'bindtextdomain' ) ) {
	$locale = '';
	if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
		@list( $locale, $dumb ) = @explode( ',', $_SERVER['HTTP_ACCEPT_LANGUAGE'], 2 );
	}
	if ( defined( 'WIDGET_LOCALE' ) ) {
		$locale = WIDGET_LOCALE;
	}
	if ( isset( $_GET['l'] ) ) {
		$locale = $_GET['l'];
	}
	$locale         = str_replace( '-', '_', $locale );
	@list( $lang, $b ) = explode( '_', $locale );
	$locale         = strtolower( $lang ).'_'.strtoupper( $b );
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
	bindtextdomain( 'messages' , '../lang' );
	bind_textdomain_codeset( 'messages' , 'UTF-8' );
	textdomain( 'messages' );
}
else {
	function gettext( $text ) {
		return $text;
	}
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

	if ( ! defined( 'TITLE'                      ) ) define( 'TITLE'                      , 'Pimp my Log' );
	if ( ! defined( 'FOOTER'                     ) ) define( 'FOOTER'                     , '&copy; <a href="http://www.potsky.com" target="doc">Potsky</a> ' . date('Y') . ' - <a href="https://github.com/potsky/PHPApacheLogViewer" target="doc">Pimp my Log</a>');
	if ( ! defined( 'LOGS_MAX'                   ) ) define( 'LOGS_MAX'                   , 50 );
	if ( ! defined( 'LOGS_REFRESH'               ) ) define( 'LOGS_REFRESH'               , 0 );
	if ( ! defined( 'NOTIFICATION'               ) ) define( 'NOTIFICATION'               , false );
	if ( ! defined( 'PULL_TO_REFRESH'            ) ) define( 'PULL_TO_REFRESH'            , true );
	if ( ! defined( 'NOTIFICATION_TITLE'         ) ) define( 'NOTIFICATION_TITLE'         , 'Logs [%f]' );
	if ( ! defined( 'GOOGLE_ANALYTICS'           ) ) define( 'GOOGLE_ANALYTICS'           , 'UA-XXXXX-X' );
	if ( ! defined( 'SEVERITY_COLOR_ON_ALL_COLS' ) ) define( 'SEVERITY_COLOR_ON_ALL_COLS' , true );
	if ( ! defined( 'GEOIP_URL'                  ) ) define( 'GEOIP_URL'                  , 'http://www.geoiptool.com/en/?IP=%p' );

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
