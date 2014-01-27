<?php

$paths = array();
$files = array( 'error' => array() );


$path  = @ini_get('error_log');
if ( $path != '' ) {
	$paths[]          = dirname( $path ) . DIRECTORY_SEPARATOR ;
	$files['error'][] = basename( $path );
}
