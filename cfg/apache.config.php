<?php
define( 'TITLE'                      , 'Pimp my Log' );
define( 'NAV_TITLE'                  , '' );
define( 'FOOTER'                     , '&copy; <a href="http://www.potsky.com" target="doc">Potsky</a> 2007-' . @date('Y') . ' - <a href="http://pimpmylog.com" target="doc">Pimp my Log</a>');
define( 'LOGS_MAX'                   , 10 );
define( 'LOGS_REFRESH'               , 7 );
define( 'NOTIFICATION'               , true );
define( 'NOTIFICATION_TITLE'         , 'New logs [%f]' );
define( 'GOOGLE_ANALYTICS'           , 'UA-XXXXX-X' );
define( 'PULL_TO_REFRESH'            , true );
define( 'GEOIP_URL'                  , 'http://www.geoiptool.com/en/?IP=%p' );
define( 'CHECK_UPGRADE'              , true );
define( 'MAX_SEARCH_LOG_TIME'        , 3 );
//define( 'USER_TIME_ZONE'           , 'Europe/Paris' );

// CANNOT USE MATCH/pml but MATCH/Date ok !


$files = array(

	'access24' => array(
		'display' => 'Access Apache 2.4',
		'path'    => '/opt/local/apache2/logs/access24.log',
		'refresh' => 0,
		'max'     => 10,
		'notify'  => true,
		'format'  => array(
			'regex' => '|^(.*) (.*) (.*) \[(.*)\] "(.*) (.*) (.*)" ([0-9]*) (.*) "(.*)" "(.*)"( [0-9]*/([0-9]*))*$|U',
			'match' => array(
				'Date'    => 4,
				'IP'      => 1,
				'CMD'     => 5,
				'URL'     => 6,
				'Code'    => 8,
				'Size'    => 9,
				'Referer' => 10,
				'UA'      => 11,
				'User'    => 3,
				'μs'      => 13,
			),
			'types' => array(
				'Date'    => 'date:H:i:s',
				'IP'      => 'ip:geo',
				'URL'     => 'txt/20',
				'Code'    => 'badge:http',
				'Size'    => 'numeral:0b',
				'Referer' => 'link/-20',
				'UA'      => 'ua:{os.name} {os.version} | {browser.name} {browser.version}/100',
				'μs'      => 'numeral:0,0',
			),
			'exclude' => array(
				'URL' => array( '/favicon.ico/' , '/\.pml\.php\.*$/' ),
				'CMD' => array( '/OPTIONS/' )
			),
		),
	),

	'error24' => array(
		'display' => 'Errors Apache 2.4',
		'path'    => '/opt/local/apache2/logs/error24.log',
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
			'types' => array(
				'Date'     => 'date:H:i:s',
				'IP'       => 'ip:http',
				'Log'      => 'pre',
				'Severity' => 'badge:severity',
				'Referer'  => 'link',
			),
			'exclude' => array(
				'Log' => array( '/PHP Stack trace:/' , '/PHP *[0-9]*\. /' )
			),
		),
	),

	'access22' => array(
		'display' => 'Access Apache 2.2',
		'path'    => '/opt/local/apache2/logs/access_log',
		'refresh' => 0,
		'max'     => 10,
		'notify'  => true,
		'format'  => array(
			'regex' => '|^(.*) (.*) (.*) \[(.*)\] "(.*) (.*) (.*)" ([0-9]*) (.*) "(.*)" "(.*)"( [0-9]*/([0-9]*))*$|U',
			'match' => array(
				'Date'    => 4,
				'IP'      => 1,
				'CMD'     => 5,
				'URL'     => 6,
				'Code'    => 8,
				'Size'    => 9,
				'Referer' => 10,
				'UA'      => 11,
				'User'    => 3,
				'μs'      => 13,
			),
			'types' => array(
				'Date'    => 'date:H:i:s',
				'IP'      => 'ip:geo',
				'Code'    => 'badge:http',
				'Size'    => 'numeral:0b',
				'Referer' => 'link',
				'UA'      => 'ua',
				'μs'      => 'numeral:0,0',
			),
			'exclude' => array(
				'URL' => array( '/favicon.ico/' , '/\.pml\.php\.*$/' ),
				'CMD' => array( '/OPTIONS/' )
			),
		),
	),

	'error22' => array(
		'display' => 'Errors Apache 2.2',
		'path'    => '/opt/local/apache2/logs/error_log.backup',
		'refresh' => 0,
		'max'     => 5,
		'notify'  => true,
		'format'  => array(
			'regex' => '|^\[(.*)\] \[(.*)\] (\[client (.*)\] )*((?!\[client ).*)(, referer: (.*))*$|U',
			'match' => array(
				'Date'     => 1,
				'IP'       => 4,
				'Log'      => array( ' <<<--->>> ' , 5 , 2 ),
				'Severity' => 2,
				'Referer'  => 7,
			),
			'types' => array(
				'Date'     => 'date:H:i:s',
				'IP'       => 'ip:http',
				'Log'      => 'pre',
				'Severity' => 'badge:severity',
				'Referer'  => 'link',
			),
			'exclude' => array(
				'Log' => array( '/PHP Stack trace:/' , '/PHP *[0-9]*\. /' )
			),
		),
	),

);


/**
 * Keys are severities and values are bootstrap classes for table highlights
 *
 * @var  array
 */
$badges = array(
	'severity' => array(
		'debug'  => 'success',
		'info'   => 'success',
		'notice' => '',
		'warn'   => 'warning',
		'error'  => 'danger',
		'crit'   => 'danger',
		'alert'  => 'danger',
		'emerg'  => 'danger',
	),
	'http' => array(
		'1' => 'info',
		'2' => 'success',
		'3' => 'default',
		'4' => 'warning',
		'5' => 'danger',
	),
);


