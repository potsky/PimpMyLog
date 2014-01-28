<?php
/*! pimpmylog - 1.0.0 - 18864f94ebcc087a4c568137670e2efd1fdbae6f*/
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
