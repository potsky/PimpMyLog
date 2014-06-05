<?php
/*! pimpmylog - 1.0.5 - 304e44fae52b81256e7624dbca2a9cb3d005808e*/
/*
 * pimpmylog
 * http://pimpmylog.com
 *
 * Copyright (c) 2014 Potsky, contributors
 * Licensed under the GPLv3 license.
 */
?><?php
include_once 'global.inc.php';
init();


if ( ! file_exists( 'test.REMOVE_UPPERCASE.php') ) {
	die( __('Please touch file <code>inc/test.REMOVE_UPPERCASE.php</code> and reload this page') );
}


function test( $type , $regex , $match , $types , $logs , $headers = true , $multiline = '' ) {
	$r  = '<h4>' . $type . '</h4>';
	$r .= '<pre>';
	$r .= ( $headers === true ) ? '<strong>Regex</strong>: ' . $regex . "\n" : '';
	$r .= ( $headers === true ) ? '<strong>Log  </strong>: ' . $logs . "\n" : '';
	$r .= ( $headers === true ) ? "\n" : '';

	$logs   = array_reverse( explode( "\n" , $logs ) );
	$rank   = 0;
	$size   = count( strval( count($logs) ) ) + 2;
	$blan   = str_pad( '' , $size );
	$buffer = array();

	foreach( $logs as $log ) {

		$tokens = parser( $regex , $match , $log , $types );

		if ( is_array( $tokens ) ) {
			$rank++;
			$disp = ( $headers ) ? '' : str_pad( '#' . $rank , $size );

			$maxlength = 0;
			foreach ( $tokens as $token => $value ) $maxlength = max( $maxlength , strlen( $token ) );

			$r .= ( $headers ) ? '' : '<strong>' . $disp . $log . "</strong>\n";

			foreach ( $tokens as $token => $value ) {
				$r .= $blan . '<strong>' . str_pad( $token , $maxlength ) . '</strong>: ' . $value;

				if ( $token === $multiline ) {
					if ( count( $buffer ) > 0 ) {
						$buffer = array_reverse( $buffer );
						foreach ( $buffer as $append ) {
							$r .= "\n" . $blan . str_pad( '' , $maxlength ) . '  ' . $append;
						}
					}
				}

				$r .= "\n";
			}

			$r .= "\n";
			$buffer = array();
		}

		else {
			$buffer[] = $log;
		}

	}

	$r .= '</pre>';
	return $r;
}


if ( isset( $_POST['s'] ) ) {

	$return    = array();
	$match     = @json_decode( $_POST['m'] , true );
	$types     = @json_decode( $_POST['t'] , true );
	$regex     = $_POST['r'];
	$log       = $_POST['l'];
	$multiline = $_POST['u'];

	if ( ! is_array( $match ) ) {
		$return['err'] = 'inputMatch';
		$return['msg'] = '<div class="alert alert-danger"><strong>' . __('Error') . '</strong> '. __('Match is not a valid associative array') . '</div>';
		echo json_encode( $return );
		die();
	}

	if ( ! is_array( $types ) ) {
		$return['err'] = 'inputTypes';
		$return['msg'] = '<div class="alert alert-danger"><strong>' . __('Error') . '</strong> '. __('Types is not a valid associative array') . '</div>';
		echo json_encode( $return );
		die();
	}

	if ( @preg_match( $regex , 'this is just a test !' ) === false ) {
		$return['err'] = 'inputRegEx';
		$return['msg'] = '<div class="alert alert-danger"><strong>' . __('Error') . '</strong> '. __('RegEx is not a valid PHP PCRE regular expression') . '</div>';
		echo json_encode( $return );
		die();
	}

	header('Content-type: application/json');
	$return['msg'] = test( '' , $regex , $match, $types, $log , false , $multiline );

	echo json_encode( $return );
	die();
}

?><!DOCTYPE html><!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]--><!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"><![endif]--><!--[if IE 8]><html class="no-js lt-ie9"><![endif]--><!--[if gt IE 8]><!--><html class="no-js"><!--<![endif]--><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><meta name="description" content=""><meta name="viewport" content="width=device-width"><title><?php echo TITLE;?></title><?php $fav = '../' ; include_once 'favicon.inc.php'; ?><?php
?><?php
?><link rel="stylesheet" href="../css/pml.min.css"><?php
?></head><body><!--[if lt IE 7]><p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p><![endif]--><div class="navbar navbar-inverse navbar-fixed-top"><div class="container"><div class="logo"></div><div class="navbar-header"><a class="navbar-brand" href="?<?php echo $_SERVER['QUERY_STRING'];?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Debugger');?></a></div></div></div><div class="container"><br><div class="panel-group" id="accordion"><div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo"><?php _e('Regex tester');?></a></h4></div><div id="collapseTwo" class="panel-collapse collapse in"><div class="panel-body"><form class="form-horizontal" role="form" id="regextest"><div class="form-group" id="GPinputLog"><label for="inputLog" class="col-sm-2 control-label"><?php _e('Log');?></label><div class="col-sm-10"><textarea class="form-control test" id="inputLog" placeholder="Log"><?php
									echo '[27-11-2013:23:20:40 +0100] This is an error
on several lines
[27-11-2013:23:20:41 +0100] Single line is cool too';
									?></textarea></div></div><div class="form-group" id="GPinputRegEx"><label for="inputRegEx" class="col-sm-2 control-label"><?php _e('RegEx');?></label><div class="col-sm-10"><textarea class="form-control test" id="inputRegEx" placeholder="RegEx"><?php
										echo '|^\[(.*)-(.*)-(.*):(.*):(.*):(.*) .*\] (.*)$|U';
									?></textarea></div></div><div class="form-group" id="GPinputMatch"><label for="inputMatch" class="col-sm-2 control-label"><?php _e('Match');?><br><small><?php _e('must be json encoded');?></small></label><div class="col-sm-10"><textarea class="form-control test" id="inputMatch" placeholder="Match" rows="5"><?php
									$match = array(
										'Date'  => array(
											'Y' => 3,
											'm' => 2,
											'd' => 1,
											'H' => 4,
											'i' => 5,
											's' => 6,
										 ),
										'Error' => 7,
									);
									$match = array(
										'Date'  => array( 3 , '/' , 2 , '/' , 1 , ' ' , 4 , ':' , 5, ':' , 6 ),
										'Error' => 7,
									);
									echo json_indent( json_encode($match))
									?></textarea></div></div><div class="form-group" id="GPinputTypes"><label for="inputTypes" class="col-sm-2 control-label"><?php _e('Types');?><br><small><?php _e('must be json encoded');?></small></label><div class="col-sm-10"><textarea class="form-control test" id="inputTypes" placeholder="Types" rows="5"><?php
									$types = array(
										'Date'  => 'date:d/m/Y H:i:s /100',
										'Error' => 'txt',
									);
									echo json_indent( json_encode($types))
									?></textarea></div></div><div class="form-group" id="GPinputMultiline"><label for="inputMultiline" class="col-sm-2 control-label"><?php _e('Multiline');?></label><div class="col-sm-10"><input class="form-control test" id="inputMultiline" placeholder="Multiline" value="Error"></div></div><div class="form-group"><div class="col-sm-offset-2 col-sm-10"><button type="submit" class="btn btn-primary"><?php _e('Test');?></button>&nbsp;<a class="btn btn-success clipboard"><?php _e('Copy to clipboard');?></a></div></div><div id="regexresult"></div></form></div></div></div><div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseThree"><?php _e('Regex samples');?></a></h4></div><div id="collapseThree" class="panel-collapse collapse"><div class="panel-body"><?php
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
echo test( $type , $regex , $match , $types , $log );

$type  = 'Error Apache 2.2 server restart';
$log   = '[Thu Nov 28 14:03:10 2013] [notice] SIGHUP received.  Attempting to restart';
echo test( $type , $regex , $match , $types , $log );

$type  = 'Error Apache 2.2 without referer';
$log   = '[Wed Nov 27 09:30:11 2013] [error] [client 127.0.0.1] PHP   1. {main}() /Users/potsky/Private/Work/GitHub/PHPApacheLogViewer/inc/get_logs.php:0';
echo test( $type , $regex , $match , $types , $log );

$type  = 'Error Apache 2.4 with referer and without module name';
$log   = "[Fri Oct 11 04:41:06.897613 2013] [:error] [pid 61939] [client 192.168.207.71:44171] script '/usr/local/www/apache24/data/test.php' not found or unable to stat, referer: [localhost]";
$regex = '|^\[(.*) (.*) (.*) (.*):(.*):(.*)\.(.*) (.*)\] \[(.*):(.*)\] \[pid (.*)\] .*\[client (.*):(.*)\] (.*)(, referer: (.*))*$|U';
$match = array(
	'Date'     => array(
		'M' => 2,
		'd' => 3,
		'H' => 4,
		'i' => 5,
		's' => 6,
		'Y' => 8,
	),
	'IP'       => 12,
	'Log'      => 14,
	'Severity' => 10,
	'Referer'  => 16,
);
echo test( $type , $regex , $match , $types , $log );

$type  = 'Error Apache 2.4 without referer and without module name';
$log   = "[Fri Oct 11 04:41:06.897613 2013] [:error] [pid 61939] [client 192.168.207.71:44171] script '/usr/local/www/apache24/data/test.php' not found or unable to stat";
echo test( $type , $regex , $match , $types , $log );

$type  = 'Error Apache 2.4 with referer and with module name';
$log   = "[Sat Nov 24 23:24:18.318257 2012] [authz_core:debug] [pid 21841:tid 140204006696704] mod_authz_core.c(802): [client 80.8.82.242:62269] AH01626: authorization result of Require all granted: granted, referer: http://www.adza.com/";
$match = array(
	'Date'     => array(
		'M' => 2,
		'd' => 3,
		'H' => 4,
		'i' => 5,
		's' => 6,
		'Y' => 8,
	),
	'IP'       => 12,
	'Log'      => array( ' >>> ' , 9 , 14),
	'Severity' => 10,
	'Referer'  => 16,
);
echo test( $type , $regex , $match , $types , $log );

$type  = 'Access Apache 2.2 with referer and user agent';
$log   = '127.0.0.1 - - [27/Nov/2013:10:20:40 +0100] "GET /~potsky/PHPApacheLogViewer/inc/get_logs.php?ldv=false&file=access&max=27 HTTP/1.1" 200 33 "http://localhost/~potsky/PHPApacheLogViewer/" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9) AppleWebKit/537.71 (KHTML, like Gecko) Version/7.0 Safari/537.71"';
$regex = '|^((\S*) )*(\S*) (\S*) (\S*) \[(.*)\] "(\S*) (.*) (\S*)" ([0-9]*) (.*)( "(.*)" "(.*)"( [0-9]*/([0-9]*))*)*$|U';
$match = array(
	'CMD'     => 7,
	'Code'    => 10,
	'Date'    => 6,
	'IP'      => 3,
	'Referer' => 13,
	'Size'    => 11,
	'UA'      => 14,
	'URL'     => 8,
	'User'    => 5,
	'Time'    => 16,
);
echo test( $type , $regex , $match , $types , $log );

$type  = 'Access Apache 2.2 with virtual host referer and user agent';
$log   = 'potsky.com 62.129.4.154 - rb [19/Dec/2013:16:11:22 +0100] "POST /P1mpmyL0g-dev/inc/getlog.pml.php?1387465882519 HTTP/1.1" 200 7660 "https://home.potsky.com/P1mpmyL0g-dev/" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_1) AppleWebKit/537.73.11 (KHTML, like Gecko) Version/7.0.1 Safari/537.73.11"';
echo test( $type , $regex , $match , $types , $log );

$type  = 'Access Apache 2.2 with tuning options';
$log   = '62.129.4.154 - - [29/Nov/2013:18:13:22 +0100] "GET /PimpMyLogs/inc/getlog.pml.php?ldv=true&file=access&max=20 HTTP/1.1" 500 96 "http://www.potsky.com/PimpMyLogs/" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9) AppleWebKit/537.71 (KHTML, like Gecko) Version/7.0 Safari/537.71" 10/10003980';
echo test( $type , $regex , $match , $types , $log );

$type  = 'Access Apache 2.2 dummy SSL connection';
$log   = '::1 - - [27/Nov/2013:12:02:08 +0100] "OPTIONS * HTTP/1.0" 200 - "-" "Apache/2.2.25 (Unix) mod_ssl/2.2.26 OpenSSL/1.0.1e DAV/2 PHP/5.3.27 (internal dummy connection)"';
echo test( $type , $regex , $match , $types , $log );
?></div></div></div><div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">Configuration</a></h4></div><div id="collapseOne" class="panel-collapse collapse"><div class="panel-body"><div class="panel-group" id="accordion2"><div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion2" href="#collapseOne2">Code <code>config.user.json</code></a></h4></div><div id="collapseOne2" class="panel-collapse collapse"><div class="panel-body"><pre><?php if (file_exists('../config.user.json')) show_source('../config.user.json'); else echo 'file ../config.user.json does not exist'; ?></pre></div></div></div><div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo2">Stat <code>config.user.json</code></a></h4></div><div id="collapseTwo2" class="panel-collapse collapse"><div class="panel-body"><pre><?php if (file_exists('../config.user.json')) var_export( @stat('../config.user.json') ); else echo 'file ../config.user.json does not exist'; ?></pre></div></div></div><div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion2" href="#collapseFour2"><?php _e('Rights');?></a></h4></div><div id="collapseFour2" class="panel-collapse collapse"><div class="panel-body"><pre><?php
										if (function_exists('posix_getpwuid')) {
											var_dump( @posix_getpwuid(posix_geteuid()) );
										} else {
											_e('No POSIX functions...');
										}
										?></pre><?php
											config_load( '../config.user.json' );
											$paths = array(
												'config' => '../config.user.json',
											);
											if ( is_array( @$files ) ) {
												foreach ( $files as $fileid => $file ) {
													$paths[ '--> ' . $fileid ] = @$file['path'];
													$dir_name = realpath( $file['path'] );
													if ( file_exists( $dir_name ) ) {
														while ( $dir_name != dirname( $dir_name ) ) {
 															$dir_name = dirname( $dir_name );
															$paths[ $dir_name ] = $dir_name;
														}
													}
												}
											}

											echo '<div class="table-responsive"><table>';
											echo '<thead><tr><th>'.__('Read').'</th><th>'.__('Write').'</th><th>ID</th><th>'.__('Path').'</th><th>'.__('Real path').'</th></tr></thead>';
											echo '<tbody>';
											foreach ( $paths as $id => $file ) {
												echo '<tr>
												<td>' . ( is_readable($file) ? '<span class="label label-success">'.__('Yes').'</span>' : '<span class="label label-danger">'.__('No').'</span>'  ) . '</td>
												<td>' . ( is_writable($file) ? '<span class="label label-success">'.__('Yes').'</span>' : '<span class="label label-danger">'.__('No').'</span>'  ) . '</td>
												<td>'.$id.'</td>
												<td><code>'.$file.'</code></td>
												<td><code>'.realpath($file).'</code></td>
												</tr>';
											}
											echo '</tbody>';
											echo '</table></div>';

										?></div></div></div><div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion2" href="#collapseThree2">PHPInfo</a></h4></div><div id="collapseThree2" class="panel-collapse collapse"><div class="panel-body"><?php
										ob_start();
										phpinfo();
										preg_match ('%<style type="text/css">(.*?)</style>.*?(<body>.*</body>)%s', ob_get_clean(), $matches);
										echo $matches[2];
										?></div></div></div></div></div></div></div></div><hr><footer class="text-muted"><small><?php echo FOOTER;?></small></footer></div><?php
?><?php
?><script src="../js/pml.min.js"></script><script src="../js/test.min.js"></script><?php
?></body></html>