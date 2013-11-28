<?php
include_once 'functions.inc.php';

function test( $type , $regex , $match , $log , $dateformat='Y/m/d h:i:s' , $separator=' :: ') {
	echo '<br/><h3>' . $type . '</h3>';
	echo '<pre>';
	echo '<strong>Regex</strong>: ' . $regex . "\n";
	echo '<strong>Log  </strong>: ' . $log . "\n";
	echo "\n";
	$tokens = parser( $regex , $match , $log , $dateformat , $separator);
	if ( is_array($tokens) ) {
		$maxlength = 0;
		foreach ( $tokens as $token => $value ) $maxlength = max( $maxlength , strlen( $token ) );
		foreach ( $tokens as $token => $value ) {
			echo '<strong>' . str_pad( $token , $maxlength ) . '</strong>: ' . $value . "\n";
		}
	}
	echo '</pre>';
}
?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title></title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width">

	<link rel="stylesheet" href="../css/bootstrap.min.css">
	<style>
		body {
			padding-top: 50px;
			padding-bottom: 20px;
		}
	</style>
	<link rel="stylesheet" href="../css/bootstrap-theme.min.css">
	<link rel="stylesheet" href="../css/main.css">
	<script src="../js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
</head>
<body>
	<!--[if lt IE 7]>
	<p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
	<![endif]-->
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#">PHP Apache Log Viewer Regex Test</a>
			</div>
		</div>
	</div>
	<div class="container">
<?php

$type  = 'Error Apache 2.2 with referer';
$log   = '[Wed Nov 27 09:30:11 2013] [error] [client 127.0.0.1] PHP   1. {main}() /Users/potsky/Private/Work/GitHub/PHPApacheLogViewer/inc/get_logs.php:0, referer: http://localhost/~potsky/PHPApacheLogViewer/';
$regex = '|^\[(.*)\] \[(.*)\] (\[client (.*)\] )*((?!\[client ).*)(, referer: (.*))*$|U';
$match = array(
	'Date'     => 1,
	'IP'       => 4,
	'Log'      => 5,
	'Severity' => 2,
	'Referer'  => 7,
);
test( $type , $regex , $match , $log );

$type  = 'Error Apache 2.2 server restart';
$log   = '[Thu Nov 28 14:03:10 2013] [notice] SIGHUP received.  Attempting to restart';
test( $type , $regex , $match , $log );

$type  = 'Error Apache 2.2 without referer';
$log   = '[Wed Nov 27 09:30:11 2013] [error] [client 127.0.0.1] PHP   1. {main}() /Users/potsky/Private/Work/GitHub/PHPApacheLogViewer/inc/get_logs.php:0';
test( $type , $regex , $match , $log );

$type  = 'Error Apache 2.4 with referer and without module name';
$log   = "[Fri Oct 11 04:41:06.897613 2013] [:error] [pid 61939] [client 192.168.207.71:44171] script '/usr/local/www/apache24/data/test.php' not found or unable to stat, referer: [localhost]";
$regex = '|^\[(.*) (.*) (.*) (.*):(.*):(.*)\.(.*) (.*)\] \[(.*):(.*)\] \[pid (.*)\] .*\[client (.*):(.*)\] (.*)(, referer: (.*))*$|U';
$match = array(
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
);
test( $type , $regex , $match , $log );

$type  = 'Error Apache 2.4 without referer and without module name';
$log   = "[Fri Oct 11 04:41:06.897613 2013] [:error] [pid 61939] [client 192.168.207.71:44171] script '/usr/local/www/apache24/data/test.php' not found or unable to stat";
test( $type , $regex , $match , $log );

$type  = 'Error Apache 2.4 with referer and with module name';
$log   = "[Sat Nov 24 23:24:18.318257 2012] [authz_core:debug] [pid 21841:tid 140204006696704] mod_authz_core.c(802): [client 80.8.82.242:62269] AH01626: authorization result of Require all granted: granted, referer: http://www.adza.com/";
$match = array(
	'Date'     => array(
		'M' => 2,
		'D' => 3,
		'H' => 4,
		'I' => 5,
		'S' => 6,
		'Y' => 8,
	),
	'IP'       => 12,
	'Log'      => array(9 , 14),
	'Severity' => 10,
	'Referer'  => 16,
);
test( $type , $regex , $match , $log );

$type  = 'Access Apache 2.2 with referer and user agent';
$log   = '127.0.0.1 - - [27/Nov/2013:10:20:40 +0100] "GET /~potsky/PHPApacheLogViewer/inc/get_logs.php?ldv=false&file=access&max=27 HTTP/1.1" 200 33 "http://localhost/~potsky/PHPApacheLogViewer/" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9) AppleWebKit/537.71 (KHTML, like Gecko) Version/7.0 Safari/537.71"';
$regex = '|^(.*) (.*) (.*) \[(.*)\] "(.*) (.*) (.*)" ([0-9]*) (.*) "(.*)" "(.*)"$|U';
$match = array(
	'CMD'     => 5,
	'Code'    => 8,
	'Date'    => 4,
	'IP'      => 1,
	'Referer' => 10,
	'Size'    => 9,
	'UA'      => 11,
	'URL'     => 6,
	'User'    => 3,
);
test( $type , $regex , $match , $log );

$type  = 'Access Apache 2.2 dummy SSL connection';
$log   = '::1 - - [27/Nov/2013:12:02:08 +0100] "OPTIONS * HTTP/1.0" 200 - "-" "Apache/2.2.25 (Unix) mod_ssl/2.2.26 OpenSSL/1.0.1e DAV/2 PHP/5.3.27 (internal dummy connection)"';
$regex = '|^(.*) (.*) (.*) \[(.*)\] "(.*) (.*) (.*)" ([0-9]*) (.*) "(.*)" "(.*)"$|U';
$match = array(
	'CMD'     => 5,
	'Code'    => 8,
	'Date'    => 4,
	'IP'      => 1,
	'Referer' => 10,
	'Size'    => 9,
	'UA'      => 11,
	'URL'     => 6,
	'User'    => 3,
);
test( $type , $regex , $match , $log );

?>
		<hr>
		<footer>
			<p>&copy; <a href="http://www.potsky.com" target="doc">Potsky</a> <?php echo date('Y'); ?> - <a href="https://github.com/potsky/PHPApacheLogViewer" target="doc">PHP Apache Log Viewer</a></p>
		</footer>
	</div>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
	<script>window.jQuery || document.write('<script src="../js/vendor/jquery-1.10.1.min.js"><\/script>')</script>
	<script src="../js/vendor/bootstrap.min.js"></script>
</body>
</html>
