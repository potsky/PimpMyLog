<?php
if(function_exists('xdebug_disable')) { xdebug_disable(); }
include_once 'functions.inc.php';



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

