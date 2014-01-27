<?php
function iis_load_software() {
	return array(
		'name'    => __('IIS'),
		'desc'    => __('A flexible & easy-to-manage web server...'),
		'home'    => __('http://www.iis.net'),
		'notes'   => __(''),
		'load'    => ( stripos( $_SERVER["SERVER_SOFTWARE"] , 'iis' ) !== false )
	);
}


function iis_get_config( $type , $file , $software , $counter ) {

	$file_json_encoded = json_encode( $file );

	return<<<EOF
		"$software$counter": {
			"display" : "IIS Access #$counter",
			"path"    : $file_json_encoded,
			"refresh" : 0,
			"max"     : 10,
			"notify"  : false,
			"format"  : {
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
