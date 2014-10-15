<?php if(realpath(__FILE__)===realpath($_SERVER["SCRIPT_FILENAME"])){header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");die();}?>
{
	"apache2filesPHP1": {
		"display" : "Apache Error #2",
		"path"    : "\/var\/log\/apache2\/error_log",
		"refresh" : 5,
		"max"     : 10,
		"notify"  : true,
		"format"  : {
			"type" : "HTTPD 2.2",
			"regex": "|^\\[(.*)\\] \\[(.*)\\] (\\[client (.*)\\] )*((?!\\[client ).*)(, referer: (.*))*$|U",
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
				"Log": ["\/PHP Stack trace:\/", "\/PHP *[0-9]*\\. \/"]
			}
		}
	},
	"apache2filesPHP2": {
		"display" : "Apache Access #3",
		"path"    : "\/var\/log\/apache2\/access_log",
		"refresh" : 0,
		"max"     : 10,
		"notify"  : false,
		"format"  : {
			"type" : "NCSA",
			"regex": "|^((\\S*) )*(\\S*) (\\S*) (\\S*) \\[(.*)\\] \"(\\S*) (.*) (\\S*)\" ([0-9]*) (.*)( \"(.*)\" \"(.*)\"( [0-9]*/([0-9]*))*)*$|U",
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
				"URL": ["\/favicon.ico\/", "\/\\.pml\\.php\\.*$\/"],
				"CMD": ["\/OPTIONS\/"]
			}
		}
	}
}
