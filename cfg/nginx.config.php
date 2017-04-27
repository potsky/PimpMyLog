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

function nginx_load_software() {
	return array(
		'name'    => __('NGINX'),
		'desc'    => __('The high performance reverse proxy, load balancer, edge cache, origin server'),
		'home'    => __('http://nginx.com'),
		'notes'   => __('Default log formats are supported'),
		'load'    => ( stripos( $_SERVER["SERVER_SOFTWARE"] , 'nginx' ) !== false )
	);
}


function nginx_get_config( $type , $file , $software , $counter ) {

	$file_json_encoded = json_encode( $file );

	/////////////////////////////////////////////////////////
	// nginx error files are not the same on 2.2 and 2.4 //
	/////////////////////////////////////////////////////////
	if ( $type == 'error' ) {

		return<<<EOF
		"$software$counter": {
			"display" : "NGINX Error #$counter",
			"path"    : $file_json_encoded,
			"refresh" : 5,
			"max"     : 10,
			"notify"  : true,
			"format"    : {
				"type"         : "NGINX",
				"regex"        : "@^(.*)/(.*)/(.*) (.*):(.*):(.*) \\\\[(.*)\\\\] [0-9#]*: \\\\*[0-9]+ (((.*), client: (.*), server: (.*), request: \"(.*) (.*) HTTP.*\", host: \"(.*)\"(, referrer: \"(.*)\")*)|(.*))\$@U",
				"export_title" : "Error",
				"match"        : {
					"Date"     : [1,"\/",2,"\/",3," ",4,":",5,":",6],
					"Severity" : 7,
					"Error"    : [10,18],
					"Client"   : 11,
					"Server"   : 12,
					"Method"   : 13,
					"Request"  : 14,
					"Host"     : 15,
					"Referer"  : 17
				},
				"types"    : {
					"Date"     : "date:d\/m\/Y H:i:s \/100",
					"Severity" : "badge:severity",
					"Error"    : "pre",
					"Client"   : "ip:http",
					"Server"   : "txt",
					"Method"   : "txt",
					"Request"  : "txt",
					"Host"     : "ip:http",
					"Referer"  : "link"
				}
			}
		}
EOF;

	}


	////////////////
	// Access log //
	////////////////
	else if ( $type == 'access' ) {

		return<<<EOF
		"$software$counter": {
			"display" : "NGINX Access #$counter",
			"path"    : $file_json_encoded,
			"refresh" : 0,
			"max"     : 10,
			"notify"  : false,
			"format"  : {
				"type"         : "NCSA Extended",
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
					"User"    : 5
				},
				"types": {
					"Date"    : "date:H:i:s",
					"IP"      : "ip:geo",
					"URL"     : "txt",
					"Code"    : "badge:http",
					"Size"    : "numeral:0b",
					"Referer" : "link",
					"UA"      : "ua:{os.name} {os.version} | {browser.name} {browser.version}\/100"
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
