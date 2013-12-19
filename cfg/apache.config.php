<?php
/*
You must escape anti-slash 4 times and escape $ in regex.
(Two for PHP and finally two for json)
 */


function apache_get_config( $type , $file , $software , $counter ) {

	/////////////////////////////////////////////////////////
	// Apache error files are not the same on 2.2 and 2.4 //
	/////////////////////////////////////////////////////////
	if ( $type == 'error' ) {

		// Get the first 10 lines and try to guess
		// This is not really
		$firstline = '';
		$handle    = @fopen( $file , 'r' );
		$remain    = 10;
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
			"path"    : "$file",
			"refresh" : 5,
			"max"     : 10,
			"notify"  : true,
				"regex": "|^\\\\[(.*) (.*) (.*) (.*):(.*):(.*)\\\\.(.*) (.*)\\\\] \\\\[(.*):(.*)\\\\] \\\\[pid (.*)\\\\] .*\\\\[client (.*):(.*)\\\\] (.*)(, referer: (.*))*\$|U",
				"match": {
					"Date"    : {
						"M" : 2,
						"D" : 3,
						"H" : 4,
						"I" : 5,
						"S" : 6,
						"Y" : 8
					},
					"IP"       : 12,
					"Log"      : 14,
					"Severity" : 10,
					"Referer"  : 16,
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
			"path"    : "$file",
			"refresh" : 5,
			"max"     : 10,
			"notify"  : true,
			"format"  : {
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
			"path"    : "$file",
			"refresh" : 0,
			"max"     : 10,
			"notify"  : false,
			"format"  : {
				"regex": "|^((\\\\S*) )*(\\\\S*) (\\\\S*) (\\\\S*) \\\\[(.*)\\\\] \"(\\\\S*) (.*) (\\\\S*)\" ([0-9]*) (.*) \\"(.*)\\" \\"(.*)\\"( [0-9]*/([0-9]*))*\$|U",
				"match": {
					"Date"    : 6,
					"IP"      : 3,
					"CMD"     : 7,
					"URL"     : 8,
					"Code"    : 10,
					"Size"    : 11,
					"Referer" : 12,
					"UA"      : 13,
					"User"    : 5,
					"\u03bcs" : 15
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
