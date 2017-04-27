<?php
/*! pimpmylog - 1.7.14 - 025d83c29c6cf8dbb697aa966c9e9f8713ec92f1*/
/*
 * pimpmylog
 * http://pimpmylog.com
 *
 * Copyright (c) 2017 Potsky, contributors
 * Licensed under the GPLv3 license.
 */
?>
<?php
function php_load_software() {
	$path = ( SAFE_MODE === true ) ? '' : ini_get('error_log');
	if ( $path !== '' ) {
		return array(
			'name'    => __('PHP'),
			'desc'    => __('PHP'),
			'home'    => __('http://www.php.net/manual/errorfunc.configuration.php#ini.error-log' ),
			'notes'   => __('PHP logs defined via the <code>error_log</code> ini parameter.' ) . ' ' .
						 sprintf( __('Pimp My Log has detected <code>%s</code> on your server.' ) , $path ),
			'load'    => true,
		);
	} else {
		return array(
			'name'    => __('PHP'),
			'desc'    => __('PHP'),
			'home'    => __('http://www.php.net/manual/errorfunc.configuration.php#ini.error-log'),
			'notes'   => __('Pimp My Log has not detected any path in the ini parameter <code>error_log</code>.') . ' ' .
						 __('Activate this software only if you use <code>ini_set(\'error_log\')</code> directly in your scripts for example.'),
			'load'    => false,
		);
	}
}


function php_get_config( $type , $file , $software , $counter ) {

	$file_json_encoded = json_encode( $file );

	return<<<EOF
	"$software$counter": {
		"display" : "PHP Error #$counter",
		"path"    : $file_json_encoded,
		"refresh" : 5,
		"max"     : 10,
		"notify"  : true,
		"format"    : {
			"type"         : "PHP",
			"regex"        : "@^\\\\[(.*)-(.*)-(.*) (.*):(.*):(.*)( (.*))*\\\\] ((PHP (.*):  (.*) in (.*) on line (.*))|(.*))\$@U",
			"export_title" : "Error",
			"match"        : {
				"Date"     : [ 2 , " " , 1 , " " , 4 , ":" , 5 , ":" , 6 , " " , 3 ],
				"Severity" : 11,
				"Error"    : [ 12 , 15 ],
				"File"     : 13,
				"Line"     : 14
			},
			"types"    : {
				"Date"     : "date:H:i:s",
				"Severity" : "badge:severity",
				"File"     : "pre:\/-69",
				"Line"     : "numeral",
				"Error"    : "pre"
			},
			"exclude": {
				"Log": ["\\\\/PHP Stack trace:\\\\/", "\\\\/PHP *[0-9]*\\\\. \\\\/"]
			}
		}
	}
EOF;
}
