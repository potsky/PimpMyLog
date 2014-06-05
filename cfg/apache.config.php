<?php
/*! pimpmylog - 1.0.5 - 304e44fae52b81256e7624dbca2a9cb3d005808e*/
/*
 * pimpmylog
 * http://pimpmylog.com
 *
 * Copyright (c) 2014 Potsky, contributors
 * Licensed under the GPLv3 license.
 */
?>
<?php

function apache_load_software() {
	return array(
		'name'    => __('Apache'),
		'desc'    => __('Apache Hypertext Transfer Protocol Server'),
		'home'    => __('http://httpd.apache.org'),
		'notes'   => __('All versions 2.x are supported.'),
		'load'    => ( stripos( $_SERVER["SERVER_SOFTWARE"] , 'Apache' ) !== false )
	);
}


/*
You must escape anti-slash 4 times and escape $ in regex.
(Two for PHP and finally two for json)
 */


function apache_get_config( $type , $file , $software , $counter ) {

error_log($file);
error_log( realpath( $file ) );

	$file_json_encoded = json_encode( $file );

	/////////////////////////////////////////////////////////
	// Apache error files are not the same on 2.2 and 2.4 //
	/////////////////////////////////////////////////////////
	if ( $type == 'error' ) {


		// Get the first 10 lines and try to guess
		// This is not really
		$firstline = '';
		$handle    = @fopen( $file , 'r' );
		$remain    = 10;
		$test      = 0;
		if ( $handle ) {
			while ( ( $buffer = fgets( $handle , 4096 ) ) !== false ) {
				$test = @preg_match('|^\[(.*) (.*) (.*) (.*):(.*):(.*)\.(.*) (.*)\] \[(.*):(.*)\] \[pid (.*)\] .*\[client (.*):(.*)\] (.*)(, referer: (.*))*$|U', $buffer );
				if ( $test === 1 ) {
					break;
				}
				$remain--;
				if ($remain<=0) {
					break;
				}
			}
			fclose($handle);
		}


		/////////////////////
		// Error 2.4 style //
		/////////////////////
		if ( $test === 1 ) {

			return<<<EOF
		"$software$counter": {
			"display" : "Apache Error #$counter",
			"path"    : $file_json_encoded,
			"refresh" : 5,
			"max"     : 10,
			"notify"  : true,
			"format"  : {
				"type" : "HTTPD 2.4",
				"regex": "|^\\\\[(.*) (.*) (.*) (.*):(.*):(.*)\\\\.(.*) (.*)\\\\] \\\\[(.*):(.*)\\\\] \\\\[pid (.*)\\\\] .*\\\\[client (.*):(.*)\\\\] (.*)(, referer: (.*))*\$|U",
				"match": {
					"Date"    : {
						"M" : 2,
						"d" : 3,
						"H" : 4,
						"i" : 5,
						"s" : 6,
						"Y" : 8
					},
					"IP"       : 12,
					"Log"      : 14,
					"Severity" : 10,
					"Referer"  : 16
				},
				"types": {
					"Date"     : "date:H:i:s",
					"IP"       : "ip:http",
					"Log"      : "pre",
					"Severity" : "badge:severity",
					"Referer"  : "link"
				},
				"exclude": {
					"Log": ["\/PHP Stack trace:\/", "\/PHP *[0-9]*\\\\. \/"]
				}
			}
		}
EOF;

		}


		/////////////////////
		// Error 2.2 style //
		/////////////////////
		else {

			return<<<EOF
		"$software$counter": {
			"display" : "Apache Error #$counter",
			"path"    : $file_json_encoded,
			"refresh" : 5,
			"max"     : 10,
			"notify"  : true,
			"format"  : {
				"type" : "HTTPD 2.2",
				"regex": "|^\\\\[(.*)\\\\] \\\\[(.*)\\\\] (\\\\[client (.*)\\\\] )*((?!\\\\[client ).*)(, referer: (.*))*\$|U",
				"match": {
					"Date"     : 1,
					"IP"       : 4,
					"Log"      : 5,
					"Severity" : 2,
					"Referer"  : 7
				},
				"types": {
					"Date"     : "date:H:i:s",
					"IP"       : "ip:http",
					"Log"      : "pre",
					"Severity" : "badge:severity",
					"Referer"  : "link"
				},
				"exclude": {
					"Log": ["\/PHP Stack trace:\/", "\/PHP *[0-9]*\\\\. \/"]
				}
			}
		}
EOF;

		}
	}

	////////////////
	// Access log //
	////////////////
	else if ( $type == 'access' ) {

		return<<<EOF
		"$software$counter": {
			"display" : "Apache Access #$counter",
			"path"    : $file_json_encoded,
			"refresh" : 0,
			"max"     : 10,
			"notify"  : false,
			"format"  : {
				"type" : "NCSA",
				"regex": "|^((\\\\S*) )*(\\\\S*) (\\\\S*) (\\\\S*) \\\\[(.*)\\\\] \"(\\\\S*) (.*) (\\\\S*)\" ([0-9]*) (.*)( \\"(.*)\\" \\"(.*)\\"( [0-9]*/([0-9]*))*)*\$|U",
				"match": {
					"Date"    : 6,
					"IP"      : 3,
					"CMD"     : 7,
					"URL"     : 8,
					"Code"    : 10,
					"Size"    : 11,
					"Referer" : 13,
					"UA"      : 14,
					"User"    : 5,
					"\u03bcs" : 16
				},
				"types": {
					"Date"    : "date:H:i:s",
					"IP"      : "ip:geo",
					"URL"     : "txt",
					"Code"    : "badge:http",
					"Size"    : "numeral:0b",
					"Referer" : "link",
					"UA"      : "ua:{os.name} {os.version} | {browser.name} {browser.version}\/100",
					"\u03bcs" : "numeral:0,0"
				},
				"exclude": {
					"URL": ["\/favicon.ico\/", "\/\\\\.pml\\\\.php\\\\.*\$\/"],
					"CMD": ["\/OPTIONS\/"]
				}
			}
		}
EOF;

	}
}
