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

	$file_json_encoded = json_encode( $file );

	/////////////////////////////////////////////////////////
	// Apache error files are not the same on 2.2 and 2.4 //
	/////////////////////////////////////////////////////////
	if ( $type == 'error' ) {

		// Write a line of log and try to guess the format
		$remain = 10;
		$test   = 0;
		error_log( 'Pimp my Log has been successfully configured with Apache' );
		foreach ( LogParser::getLinesFromBottom( $file , 10 ) as $line ) {
			$test = @preg_match('|^\[(.*) (.*) (.*) (.*):(.*):(.*)\.(.*) (.*)\] \[(.*):(.*)\] \[pid (.*)\] .*\[client (.*):(.*)\] (.*)(, referer: (.*))*$|U', $line );
			if ( $test === 1 ) {
				break;
			}
			$remain--;
			if ($remain<=0) {
				break;
			}
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
				"type"         : "HTTPD 2.4",
				"regex"        : "|^\\\\[(.*) (.*) (.*) (.*):(.*):(.*)\\\\.(.*) (.*)\\\\] \\\\[(.*):(.*)\\\\] \\\\[pid (.*)\\\\] .*\\\\[client (.*):(.*)\\\\] (.*)(, referer: (.*))*\$|U",
				"export_title" : "Log",
				"match"        : {
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
					"Log"      : "preformatted",
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
				"type"         : "HTTPD 2.2",
				"regex"        : "|^\\\\[(.*)\\\\] \\\\[(.*)\\\\] (\\\\[client (.*)\\\\] )*((?!\\\\[client ).*)(, referer: (.*))*\$|U",
				"export_title" : "Log",
				"match"        : {
					"Date"     : 1,
					"IP"       : 4,
					"Log"      : 5,
					"Severity" : 2,
					"Referer"  : 7
				},
				"types": {
					"Date"     : "date:H:i:s",
					"IP"       : "ip:http",
					"Log"      : "preformatted",
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
				"type"         : "NCSA",
				"regex"        : "|^((\\\\S*) )*(\\\\S*) (\\\\S*) (\\\\S*) \\\\[(.*)\\\\] \"(\\\\S*) (.*) (\\\\S*)\" ([0-9]*) (.*)( \\"(.*)\\" \\"(.*)\\"( [0-9]*/([0-9]*))*)*\$|U",
				"export_title" : "URL",
				"match"        : {
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
					"URL": ["\/favicon.ico\/", "\/\\\\.pml\\\\.php.*\$\/"],
					"CMD": ["\/OPTIONS\/"]
				}
			}
		}
EOF;

	}
}
