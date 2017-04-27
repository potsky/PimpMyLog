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
function iis_load_software() {
	return array(
		'name'    => __('IIS'),
		'desc'    => __('A flexible & easy-to-manage web server...'),
		'home'    => __('http://www.iis.net'),
		'notes'   => __('NCSA, IIS and W3C log formats are supported'),
		'load'    => ( stripos( $_SERVER["SERVER_SOFTWARE"] , 'iis' ) !== false )
		);
}


function iis_get_config( $type , $file , $software , $counter ) {

	$file_json_encoded = json_encode( $file );

	// Type W3C
	//
	$ufields = array();
	$handle  = @fopen( $file , "r" );
	if ( $handle ) {
		while ( ! feof( $handle ) ) {
			$buffer = fgets($handle);
			if ( substr( $buffer , 0 , 9 ) == '#Fields: ' ) {
				$ufields = explode( ' ' , substr( $buffer , 9 ) );
				if ( count( $ufields ) > 0 )	{
					break;
				}
			}
		}
		fclose($handle);
	}
	if ( count( $ufields ) > 0 ) {
		$regex = array();
		$types = array();
		$match = array(
			'Date'    => false,
			'IP'      => false,
			'Site'    => false,
			'CMD'     => false,
			'URL'     => false,
			'QS'      => false,
			'Code'    => false,
			'Size'    => false,
			'Referer' => false,
			'UA'      => false,
			'User'    => false,
			'ms'      => false,
		);
		$types = array(
			'Date'         => "date:H:i:s",
			'Site'         => 'txt',
			'CMD'          => 'txt',
			'URL'          => 'txt',
			'QS'           => 'txt',
			'User'         => 'txt',
			'IP'           => "ip:geo",
			'UA'           => "uaw3c:{os.name} {os.version} | {browser.name} {browser.version}/100",
			'Referer'      => "link",
			'Code'         => "badge:http",
			'Size'         => "numeral:0b",
			'ms'         => "numeral:0,0"
		);
		$fields = array(
			'date'            => 'Date',
			'time'            => 'Date',
			's-sitename'      => 'Site',
//			's-computername'  => 0,
//			's-ip'            => 0,
			'cs-method'       => 'CMD',
			'cs-uri-stem'     => 'URL',
			'cs-uri-query'    => 'QS',
//			's-port'          => 0,
			'cs-username'     => 'User',
			'c-ip'            => 'IP',
//			'cs-version'      => 0,
			'cs(User-Agent)'  => 'UA',
//			'cs(Cookie)'      => 0,
			'cs(Referer)'     => 'Referer',
//			'cs-host'         => 0,
			'sc-status'       => 'Code',
//			'sc-substatus'    => 0,
//			'sc-win32-status' => 0,
			'sc-bytes'        => 'Size',
//			'cs-bytes'        => 0,
			'time-taken'      => 'ms',
		);

		$position = 1;

		foreach ( $ufields as $field ) {
			$regex[] = '(.*)';
			$field   = trim($field);
			if ( isset( $fields[ $field ] ) ) {
				$thismatch = $fields[ $field ];

				if ( array_key_exists( $thismatch , $match ) ) {
					if ( $match[ $thismatch ] === false ) {
						$match[ $thismatch ] = $position;
					} else if ( is_array( $match[ $thismatch ] ) ) {
						$match[ $thismatch ][] = $position;
						$match[ $thismatch ][] = ' ';
					} else {
						$save = $match[ $thismatch ];
						$match[ $thismatch ] = array( $save , ' ' , $position , ' ' );
					}
				}
			}
			$position++;
		}

		$match = array_filter( $match );
		$regex = '|^' . implode( ' ' , $regex ) . '$|U';

		$regex_json_encoded = json_encode( $regex );
		$match_json_encoded = json_encode( $match );
		$types_json_encoded = json_encode( $types );

		return<<<EOF
		"$software$counter": {
			"display" : "IIS Access #$counter",
			"path"    : $file_json_encoded,
			"refresh" : 0,
			"max"     : 10,
			"notify"  : false,
			"format"  : {
				"type"         : "W3C Extended",
				"regex"        : $regex_json_encoded,
				"export_title" : "URL",
				"match"        : $match_json_encoded,
				"types"        : $types_json_encoded,
				"exclude"      : {
					"URL": ["\/favicon.ico\/", "\/\\\\.pml\\\\.php\\\\.*\$\/"],
					"CMD": ["\/OPTIONS\/"]
				}
			}
		}
EOF;
	}


	// Type IIS
	//
	$handle    = @fopen( $file , 'r' );
	$remain    = 10;
	$test      = 0;
	if ( $handle ) {
		while ( ( $buffer = fgets( $handle , 4096 ) ) !== false ) {
			$test = @preg_match('|^(.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*),(.*)$|U', $buffer );
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
	if ( $test === 1 ) {
		return<<<EOF
		"$software$counter": {
			"display" : "IIS Access #$counter",
			"path"    : $file_json_encoded,
			"refresh" : 0,
			"max"     : 10,
			"notify"  : false,
			"format"  : {
				"type"         : "IIS",
				"regex"        : "|^(.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*), (.*),(.*)\$|U",
				"export_title" : "URL",
				"match"        : {
					"Date"    : [3," ",4],
					"IP"      : 1,
					"Site"    : 5,
					"CMD"     : 13,
					"URL"     : 14,
					"Code"    : 11,
					"Size"    : 10,
					"User"    : 2,
					"ms"      : 8
				},
				"types" : {
					"Date"    : "date:H:i:s",
					"IP"      : "ip:geo",
					"URL"     : "txt",
					"Code"    : "badge:http",
					"Size"    : "numeral:0b",
					"ms"      : "numeral:0,0"
				},
				"exclude" : {
					"URL": ["\/favicon.ico\/", "\/\\\\.pml\\\\.php\\\\.*\$\/"],
					"CMD": ["\/OPTIONS\/"]
				}
			}
		}
EOF;
	}

	// Type NCSA
	//
	return<<<EOF
	"$software$counter": {
		"display" : "IIS Access #$counter",
		"path"    : $file_json_encoded,
		"refresh" : 0,
		"max"     : 10,
		"notify"  : false,
		"format"  : {
			"type"         : "NCSA Common",
			"regex"        : "|^((\\\\S*) )*(\\\\S*) (\\\\S*) (\\\\S*) \\\\[(.*)\\\\] \"(\\\\S*) (.*) (\\\\S*)\" ([0-9]*) (.*)( \\"(.*)\\" \\"(.*)\\"( [0-9]*/([0-9]*))*)*\$|U",
			"export_title" : "URL",
			"match"        : {
				"Date"    : 6,
				"IP"      : 3,
				"CMD"     : 7,
				"URL"     : 8,
				"Code"    : 10,
				"Size"    : 11,
				"User"    : 5
			},
			"types" : {
				"Date"    : "date:H:i:s",
				"IP"      : "ip:geo",
				"URL"     : "txt",
				"Code"    : "badge:http",
				"Size"    : "numeral:0b"
			},
			"exclude" : {
				"URL": ["\/favicon.ico\/", "\/\\\\.pml\\\\.php\\\\.*\$\/"],
				"CMD": ["\/OPTIONS\/"]
			}
		}
	}
EOF;

}
