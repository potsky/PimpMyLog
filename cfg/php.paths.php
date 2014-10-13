<?php
/*! pimpmylog - 1.2.1 - ff3f188288e4c2db6475c2dce8443e25233667c7*/
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
