<?php
/*! pimpmylog - 1.7.11 - d666559cb0e141ca9c4984773e180f75b7c53664*/
/*
 * pimpmylog
 * http://pimpmylog.com
 *
 * Copyright (c) 2016 Potsky, contributors
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
