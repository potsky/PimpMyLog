<?php
/*! pimpmylog - 1.0.0 - 0a648001138e011a5721bf1e552b62958a8c94fc*/
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
