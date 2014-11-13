<?php
/*! pimpmylog - 1.5.0 - 40177bd8ea13dab194ac1ca7b988de795d1505d9*/
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
