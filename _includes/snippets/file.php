<?php
include_once 'inc/global.inc.php';
if ( version_compare( PHP_VERSION , PHP_VERSION_REQUIRED ) < 0 ) {
	$title    = __( 'Oups!' );
	$message  = sprintf( __( 'PHP version %s is required but your server run %s.') , PHP_VERSION_REQUIRED , PHP_VERSION );
	$link_url = HELP_URL;
	$link_msg = __('Learn more');
	include_once 'inc/error.inc.php';
	die();
}
?>
