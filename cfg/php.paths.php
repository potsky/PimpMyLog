<?php
/*! pimpmylog - 1.0.0 - 707747a88ef4f48a6969038f23d56a727084002b*/
/*
 * pimpmylog
 * http://pimpmylog.com
 *
 * Copyright (c) 2014 Potsky, contributors
 * Licensed under the GPLv3 license.
 */
?>
<?php

$paths = array();
$files = array( 'error' => array() );


$path  = @ini_get('error_log');
if ( $path != '' ) {
	$paths[]          = dirname( $path ) . DIRECTORY_SEPARATOR ;
	$files['error'][] = basename( $path );
}
