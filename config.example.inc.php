<?php

// Remove this if you have PHP Warning: ini_set() has been disabled for security reasons
ini_set( 'max_execution_time' , 10 );

define( 'TITLE'                      , 'Pimp my Log' );
define( 'FOOTER'                     , '&copy; <a href="http://www.potsky.com" target="doc">Potsky</a> ' . date('Y') . ' - <a href="https://github.com/potsky/PHPApacheLogViewer" target="doc">Pimp my Log</a>');
define( 'LOGS_MAX'                   , 10 );
define( 'LOGS_REFRESH'               , 7 );
define( 'NOTIFICATION'               , true );
define( 'NOTIFICATION_TITLE'         , 'Logs [%f]' );
define( 'GOOGLE_ANALYTICS'           , 'UA-XXXXX-X' );
define( 'PULL_TO_REFRESH'            , true );
define( 'SEVERITY_COLOR_ON_ALL_COLS' , false );
define( 'GEOIP_URL'                  , 'http://www.geoiptool.com/en/?IP=%p' );
//define( 'GEOIP_URL'                  , 'http://%p' ); // Use this to open the webserver of the IP address instead of the geoip

$files = array(

	'access' => array(
		'display' => 'Access Apache 2.2',
		'path'    => '/opt/local/apache2/logs/access_log',
		'refresh' => 0,
		'max'     => 20,
		'notify'  => true,
		'format'  => array(
			'regex' => '|^(.*) (.*) (.*) \[(.*)\] "(.*) (.*) (.*)" ([0-9]*) (.*) "(.*)" "(.*)"$|U',
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
			),
			'exclude' => array(
				'URL' => array( '/favicon.ico/' , '/\.pml\.php/' ),
				'CMD' => array( '/OPTIONS/' )
			),
		),
	),

	'error22' => array(
		'display' => 'Errors Apache 2.2',
		'path'    => '/opt/local/apache2/logs/error_log',
		'refresh' => 0,
		'max'     => 20,
		'notify'  => true,
		'format'  => array(
			'regex' => '|^\[(.*)\] \[(.*)\] (\[client (.*)\] )*((?!\[client ).*)(, referer: (.*))*$|U',
			'match' => array(
				'Date'     => 1,
				'IP'       => 4,
				'Log'      => 5,
				'Severity' => 2,
				'Referer'  => 7,
			),
			'exclude' => array(
				'Log' => array( '/PHP Stack trace:/' , '/PHP *[0-9]*\. /' )
			),
		),
	),
/*
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
*/
);

/**
 * Keys are apache severities and values are bootstrap classes for table highlights
 *
 * @var  array
 */
$severities = array(
	'debug'  => 'success',
	'info'   => 'success',
	'notice' => '',
	'warn'   => 'warning',
	'error'  => 'danger',
	'crit'   => 'danger',
	'alert'  => 'danger',
	'emerg'  => 'danger',
);


/**
 * Keys are the first digit of a HTTP code and values are bootstrap classes for labels
 *
 * @var  array
 */
$httpcodes = array(
	'1' => 'info',
	'2' => 'success',
	'3' => 'default',
	'4' => 'warning',
	'5' => 'danger',
);



///////////////////////////////////////////////////////
// These values are auto injected in lang functions. //
// Add here a tricky reference for poeditors         //
///////////////////////////////////////////////////////
// __( 'Date' )
// __( 'IP' )
// __( 'Log' )
// __( 'Severity')
// __( 'Referer' )
// __( 'CMD' )
// __( 'Code' )
// __( 'Date' )
// __( 'IP' )
// __( 'Referer' )
// __( 'Size' )
// __( 'UA' )
// __( 'URL' )
// __( 'User' )
