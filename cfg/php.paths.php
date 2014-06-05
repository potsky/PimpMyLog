<?php
/*! pimpmylog - 1.0.5 - 25b2c7b3c902ec75852dcf5d4c6c6dd8cbeffc0c*/
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
$files = array(
	'error' => array(
	)
);


$path  = @ini_get('error_log');

if ( $path != '' ) {
	$paths[]          = dirname( $path ) . DIRECTORY_SEPARATOR ;
	$files['error'][] = basename( $path );
}
