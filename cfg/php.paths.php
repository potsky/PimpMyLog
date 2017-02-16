<?php
/*! pimpmylog - 1.7.12 - 22dae58317b3bf9647c48f09986f80e7d51f841e*/
/*
 * pimpmylog
 * http://pimpmylog.com
 *
 * Copyright (c) 2017 Potsky, contributors
 * Licensed under the GPLv3 license.
 */
?>
<?php

$paths = array();
$files = array(
	'error' => array(
	)
);

$path = ( SAFE_MODE === true ) ? '' : ini_get('error_log');

if ( $path !== '' ) {
	$paths[]          = dirname( $path ) . DIRECTORY_SEPARATOR ;
	$files['error'][] = basename( $path );
}
