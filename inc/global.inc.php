<?php
/*! pimpmylog - 1.0.5 - 304e44fae52b81256e7624dbca2a9cb3d005808e*/
/*
 * pimpmylog
 * http://pimpmylog.com
 *
 * Copyright (c) 2014 Potsky, contributors
 * Licensed under the GPLv3 license.
 */
?><?php
if(function_exists('xdebug_disable')) { xdebug_disable(); }
include_once 'functions.inc.php';



$tz_available     = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
$locale_default   = 'gb_GB';
$locale_available = array(
	'gb_GB' => 'English',
	'fr_FR' => 'Français',
);


//////////////
// Timezone //
//////////////
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


///////////////////////////////////
// Define locale and translation //
///////////////////////////////////
$lang   = '';
$locale = $locale_default;

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

	$locale         = str_replace( '-', '_', $locale );
	@list( $lang, $b ) = explode( '_', $locale );
	$locale         = strtolower( $lang ).'_'.strtoupper( $b );

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