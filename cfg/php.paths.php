<?php
/*! pimpmylog - 1.6.4 - 642cbec03da43eef30a1caea506bf7a94c0f9c1a*/
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
