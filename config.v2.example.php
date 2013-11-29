<?php

define( 'TITLE'              , 'Pimp My Logs' );
define( 'FOOTER'             , '&copy; <a href="http://www.potsky.com" target="doc">Potsky</a> ' . date('Y') . ' - <a href="https://github.com/potsky/PHPApacheLogViewer" target="doc">Pimp My Logs</a>');
define( 'LOGS_MAX'           , 10 );
define( 'LOGS_REFRESH'       , 7 );
define( 'NOTIFICATION'       , true );
define( 'NOTIFICATION_TITLE' , 'Logs [%f]' );
define( 'GOOGLE_ANALYTICS'   , 'UA-XXXXX-X' );
define( 'PULL_TO_REFRESH'    , false );


$files = array(

	'error22' => array(
		'display' => 'Errors Apache 2.2',
		'path'    => '/opt/local/apache2/logs/error_log',
		'refresh' => 0,
		'max'     => 50,
		'notify'  => true,
		'format'  => array(
			'regex' => '|^\[(.*)\] \[(.*)\] \[client (.*)\] (.*)(, referer: (.*))*$|U',
			'match' => array(
				'Date'     => 1,
				'IP'       => 3,
				'Log'      => 4,
				'Severity' => 2,
				'Referer'  => 6,
			),
			'exclude' => array(
				'Log' => array( '/PHP Stack trace:/' , '/PHP *[0-9]*\. /' )
			),
		),
	),

	'error24' => array(
		'display' => 'Errors Apache 2.4',
		'path'    => '/opt/local/apache2/logs/error_log',
		'refresh' => 0,
		'max'     => 50,
		'notify'  => true,
		'format'  => array(
			'regex' => '|^\[(.*) (.*) (.*) (.*):(.*):(.*)\.(.*) (.*)\] \[(.*):(.*)\] \[pid (.*)\] .*\[client (.*):(.*)\] (.*)(, referer: (.*))*$|U',
			'match' => array(
				'Date'     => array(
					'M' => 2,
					'D' => 3,
					'H' => 4,
					'I' => 5,
					'S' => 6,
					'Y' => 8,
				),
				'IP'       => 12,
				'Log'      => 14,
				'Severity' => 10,
				'Referer'  => 16,
			),
			'exclude' => array(
				'Log' => array( '/PHP Stack trace:/' , '/PHP *[0-9]*\. /' )
			),
		),
	),

	'access' => array(
		'display' => 'Access Apache 2.2',
		'path'    => '/opt/local/apache2/logs/access_log',
		'refresh' => 10,
		'max'     => 50,
		'notify'  => false,
		'format'  => array(
			'regex' => '|^(.*) (.*) (.*) \[(.*)\] "(.*) (.*) (.*)" ([0-9]*) (.*) "(.*)" "(.*)"$|U',
			'match' => array(
				'CMD'     => 5,
				'Code'    => 8,
				'Date'    => 4,
				'IP'      => 1,
				'Referer' => 10,
				'Size'    => 9,
				'UA'      => 11,
				'URL'     => 6,
				'User'    => 3,
			),
			'exclude' => array(
				'URL' => array( '/favicon.ico/' , '/\/wwwlogs\//' ),
				'CMD' => array( '/OPTIONS/' )
			),
		),
	),
);


