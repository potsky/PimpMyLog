<?php
include_once 'functions.inc.php';


//////////////////////////
// Set common variables //
//////////////////////////
define( 'YEAR' , date("Y") );
define( 'HELP_URL' , 'https://github.com/potsky/PHPApacheLogViewer' );


///////////////////////////////////
// Define locale and translation //
///////////////////////////////////
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
	@list( $a, $b ) = explode( '_', $locale );
	$locale         = strtolower( $a ).'_'.strtoupper( $b );

	putenv( 'LC_ALL=' . $locale );
	putenv( 'LANGUAGE=' . $locale );
	if ( $a == 'fr' ) {
		setlocale( LC_ALL , $locale , $locale . '.utf8' , 'fra' );
	}
	else if ( $a == 'de' ) {
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
