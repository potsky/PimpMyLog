<?php
/*! pimpmylog - 1.7.14 - 025d83c29c6cf8dbb697aa966c9e9f8713ec92f1*/
/*
 * pimpmylog
 * http://pimpmylog.com
 *
 * Copyright (c) 2017 Potsky, contributors
 * Licensed under the GPLv3 license.
 */
?><?php
include_once 'global.inc.php';

load_default_constants();

$access_file = str_replace(':', '', 'test.PLEASE_REMOVE_ME.access_from_' . get_client_ip() . '_only.php' );

/**
 * Regex Tester
 *
 * @param   string   $type       type
 * @param   string   $regex      regex
 * @param   array    $match      matchers
 * @param   array    $types      typers
 * @param   array    $logs       logs
 * @param   boolean  $headers    display header
 * @param   string   $multiline  multiline field
 *
 * @return  string               html
 */
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

		$tokens = LogParser::parseLine( $regex , $match , $log , $types );

		if ( is_array( $tokens ) ) {
			$rank++;
			$disp = ( $headers ) ? '' : str_pad( '#' . $rank , $size );

			$maxlength = 0;
			foreach ( $tokens as $token => $value ) $maxlength = max( $maxlength , strlen( $token ) );

			$r .= ( $headers ) ? '' : '<strong>' . $disp . $log . "</strong>\n";

			foreach ( $tokens as $token => $value ) {
				if ( substr( $token , 0 , 3 ) === 'pml' ) continue;

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


/**
 * Ajax return for regexp tester
 */
if ( @$_POST['action'] === 'regextest' )
{
	if ( ( ! file_exists( $access_file ) ) && ( ! Sentinel::isAdmin() ) )
	{
		echo json_encode( array( 'msg' => "Authentication error" ) );
		die();
	}

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




//////////////////////
// Javascript Lemma //
//////////////////////
$lemma = array(
	"configuration_copied" => __( "Configuration array has been copied to your clipboard!" ),
);


?><!DOCTYPE html><!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]--><!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"><![endif]--><!--[if IE 8]><html class="no-js lt-ie9"><![endif]--><!--[if gt IE 8]><!--><html class="no-js"><!--<![endif]--><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><meta name="description" content=""><meta name="viewport" content="width=device-width"><title><?php echo TITLE;?></title><?php $fav = '../' ; include_once 'favicon.inc.php'; ?><link rel="stylesheet" href="../css/pml.min.css"><script>var lemma = <?php echo json_encode($lemma);?>;</script></head><body><!--[if lt IE 7]><p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p><![endif]--><div class="navbar navbar-inverse navbar-fixed-top"><div class="container"><div class="logo"></div><div class="navbar-header"><a class="navbar-brand" href="?<?php echo $_SERVER['QUERY_STRING'];?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Debugger');?></a></div></div></div><div class="container"><br><?php
if ( ( ! file_exists( $access_file ) ) && ( ! Sentinel::isAdmin() ) ) {
	echo '<div class="row">';
	echo 	'<div class="col-xs-12"><div class="alert alert-danger">';
	echo 			__( 'This page is protected for security reasons.');
	echo 		'</div>';
	if ( Sentinel::isAuthSet() ) {
		echo sprintf( __('%sSign in%s as an administrator to view this page or follow instructions below.') , '<a href="../index.php?signin&attempt=' . urlencode( 'inc/test.php' ) . '">' , '</a>' ) . '<br/>';
	}
	echo 		'<br/>' . __('To grant access, please create this temporary file on your server:');
	echo 		'<br/><br/>';
	echo 	'</div>';
	echo 	'<div class="col-md-8"><pre class="clipboard2content">touch \'' . dirname( __FILE__ ) . DIRECTORY_SEPARATOR . $access_file . '\'</pre></div>';
	echo 	'<div class="col-md-4"><a class="btn btn-primary clipboard" data-source=".clipboard2content" data-placement="right" data-text="' . h("Command has been copied to your clipboard!") . '">' . __('Copy to clipboard') . '</a></div>';
	echo 	'<div class="col-xs-12">';
	echo 		'<br/>' . __("Then reload this page.") . '<br/><br/>';
	echo 		'<button onclick="document.location.reload();" class="btn btn-primary">' . __("Reload") . '</button>';
	echo 	'</div>';
	echo '</div>';
}
else {
?><ul class="nav nav-tabs" role="tablist"><li class="active"><a href="#quittab" role="tab" data-toggle="tab"><?php _e('Quit');?></a></li><li><a href="#retestertab" role="tab" data-toggle="tab"><?php _e('Regex tester');?></a></li><li><a href="#resamplestab" role="tab" data-toggle="tab"><?php _e('Regex samples');?></a></li><li><a href="#configurationtab" role="tab" data-toggle="tab"><?php _e('Configuration');?></a></li><li><a href="#passwordtab" role="tab" data-toggle="tab"><?php _e('Password recovery');?></a></li><li><a href="#authactivation" role="tab" data-toggle="tab"><?php _e('Authentication');?></a></li></ul><div class="tab-content"><div class="tab-pane active" id="quittab"><br><div class="row"><?php if ( ! Sentinel::isAdmin() ) : ?><div class="col-xs-12"><div class="alert alert-warning"><?php _e('Please remove this temporary file on your server to disable the debugger!'); ?></div></div><br><br><div class="col-md-8"><pre class="clipboard3content"><?php echo 'rm \'' . dirname( __FILE__ ) . DIRECTORY_SEPARATOR . $access_file . '\''; ?></pre></div><?php echo '<div class="col-md-4"><a class="btn btn-primary clipboard" data-source=".clipboard3content" data-placement="right" data-text="' . h( "Command has been copied to your clipboard!" ) . '">' . __('Copy to clipboard') . '</a></div>';?><?php else : ?><div class="col-xs-12"><div class="alert alert-info"><?php _e('You can access the debugger because you are an administrator'); ?></div></div><?php endif; ?></div></div><div class="tab-pane" id="retestertab"><div class="panel-body"><form class="form-horizontal" role="form" id="regextest"><div class="form-group" id="GPinputLog"><label for="inputLog" class="col-sm-2 control-label"><?php _e('Log');?></label><div class="col-sm-10"><textarea class="form-control test" id="inputLog" placeholder="Log"><?php
								echo '[27-11-2013:23:20:40 +0300] This is an error
on several lines with timezone UTC+3
[27-11-2013:23:20:41 +0100] Single line is cool too';
								?></textarea></div></div><div class="form-group" id="GPinputRegEx"><label for="inputRegEx" class="col-sm-2 control-label"><?php _e('RegEx');?></label><div class="col-sm-10"><textarea class="form-control test" id="inputRegEx" placeholder="RegEx"><?php
									echo '|^\[(.*)-(.*)-(.*):(.*):(.*):(.*) (.*)\] (.*)$|U';
								?></textarea></div></div><div class="form-group" id="GPinputMatch"><label for="inputMatch" class="col-sm-2 control-label"><?php _e('Match');?><br><small><?php _e('must be json encoded');?></small></label><div class="col-sm-10"><textarea class="form-control test" id="inputMatch" placeholder="Match" rows="5"><?php
								$match = array(
									'Date'  => array(
										'Y' => 3,
										'm' => 2,
										'd' => 1,
										'H' => 4,
										'i' => 5,
										's' => 6,
										'z' => 7,
									 ),
									'Error' => 8,
								);
								$match = array(
									'Date'  => array( 3 , '/' , 2 , '/' , 1 , ' ' , 4 , ':' , 5, ':' , 6 , ' ' , 7 ),
									'Error' => 8,
								);
								echo json_indent( json_encode($match))
								?></textarea></div></div><div class="form-group" id="GPinputTypes"><label for="inputTypes" class="col-sm-2 control-label"><?php _e('Types');?><br><small><?php _e('must be json encoded');?></small></label><div class="col-sm-10"><textarea class="form-control test" id="inputTypes" placeholder="Types" rows="5"><?php
								$types = array(
									'Date'  => 'date:d/m/Y H:i:s /100',
									'Error' => 'txt',
								);
								echo json_indent( json_encode($types))
								?></textarea></div></div><div class="form-group" id="GPinputMultiline"><label for="inputMultiline" class="col-sm-2 control-label"><?php _e('Multiline');?></label><div class="col-sm-10"><input class="form-control test" id="inputMultiline" placeholder="Multiline" value="Error"></div></div><div class="form-group"><div class="col-sm-offset-2 col-sm-10"><button type="submit" class="btn btn-success" title="<?php _h('Use CTRL-R shortcut instead of clicking on this button');?>" data-loading-text="<?php _h('Loading...');?>" id="regexTestertestBtn"><?php _e('Test');?></button> &nbsp; <a class="btn btn-primary clipboard"><?php _e('Copy to clipboard');?></a></div></div><div id="regexresult"></div></form></div></div><div class="tab-pane" id="resamplestab"><div class="panel-body"><?php
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
?></div></div><div class="tab-pane" id="configurationtab"><div class="panel-body"><div class="panel-group" id="accordion2"><div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion2" href="#collapseOne2">Code <code><?php echo get_config_file_path();?></code></a></h4></div><div id="collapseOne2" class="panel-collapse collapse"><div class="panel-body"><pre><?php if (file_exists( get_config_file_path() )) show_source( get_config_file_path() ); else echo 'configuration file does not exist'; ?></pre></div></div></div><div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo2">Stats <code><?php echo get_config_file_path();?></code></a></h4></div><div id="collapseTwo2" class="panel-collapse collapse"><div class="panel-body"><pre><?php if (file_exists( get_config_file_path() ) ) var_export( @stat( get_config_file_path() ) ); else echo 'configuration file does not exist'; ?></pre></div></div></div><div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion2" href="#collapseFive2"><?php
											$current_user = Sentinel::getCurrentUsername();
											if ( is_null( $current_user ) ) {
												_e('Generated files with includes (no user logged in or no auth)');
											} else {
												echo sprintf( __( 'Generated files with includes for user %s') , '<code>' . $current_user . '</code>' );
											}
										?></a></h4></div><div id="collapseFive2" class="panel-collapse collapse"><div class="panel-body"><pre><?php
										list( $badges , $files , $tz ) = config_load();
										if ( version_compare( PHP_VERSION , '5.4.0' ) >= 0 ) {
											echo json_encode( $files , JSON_PRETTY_PRINT );
										} else {
											echo json_indent( json_encode( $logs ) );
										}
									?></pre></div></div></div><div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion2" href="#collapseFour2"><?php _e('Rights');?></a></h4></div><div id="collapseFour2" class="panel-collapse collapse"><div class="panel-body"><pre><?php
									if (function_exists('posix_getpwuid')) {
										var_dump( @posix_getpwuid(posix_geteuid()) );
									} else {
										_e('No POSIX functions...');
									}
									?></pre><?php
										$paths = array(
											'config' => get_config_file_path(),
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

											set_error_handler( function($errno, $errstr, $errfile, $errline, array $errcontext) {});
											if ( is_readable($file) ) {
												$r  = __('Yes');
												$rc = 'success';
											} else {
												$r  = __('No');
												$rc = 'danger';
											}
											if ( is_writable($file) ) {
												$w  = __('Yes');
												$wc = 'success';
											} else {
												$w  = __('No');
												$wc = 'danger';
											}
											$rp = realpath($file);
											restore_error_handler();

											if ( empty( $rp ) ) {
												$rc = 'default';
												$wc = 'default';
												$rp = __('Not allowed by open_basedir restriction');
											}

											echo '<tr>
											<td><span class="label label-' . $rc . '">'. $r .'</span></td>
											<td><span class="label label-' . $wc . '">'. $w .'</span></td>
											<td>'.$id.'</td>
											<td><code>'.$file.'</code></td>
											<td><code>'.$rp.'</code></td>
											</tr>';
										}
										echo '</tbody>';
										echo '</table></div>';

									?></div></div></div><div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion2" href="#collapseSix2">PHP Modules</a></h4></div><div id="collapseSix2" class="panel-collapse collapse"><div class="panel-body"><?php
										_e('To be optimal, Pimp My Log needs all these modules ok:');
										echo '<ul>';

										echo '<li>';
										if ( MB_SUPPORT === true ) {
											echo 'Multibyte String <span class="label label-success">' . __('Yes') . '</span>';
										} else {
											echo 'Multibyte String <span class="label label-danger">' . __('No') . '</span>';
											echo ' <span class="text-muted">(' . sprintf( __('Follow instructions %shere%s to enable') , '<a href="http://php.net/manual/en/mbstring.installation.php" target="_blank">' , '</a>' ) . ')</span>';
										}
										echo '</li>';

										echo '<li>';
										if ( GETTEXT_SUPPORT === true ) {
											echo 'Gettext <span class="label label-success">' . __('Yes') . '</span>';
										} else {
											echo 'Gettext <span class="label label-danger">' . __('No') . '</span>';
											echo ' <span class="text-muted">(' . sprintf( __('Follow instructions %shere%s to enable') , '<a href="http://php.net/manual/en/gettext.installation.php" target="_blank">' , '</a>' ) . ')</span>';
										}
										echo '</li>';

										echo '</ul>';
									?></div></div></div><div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion2" href="#collapseThree2">PHPInfo</a></h4></div><div id="collapseThree2" class="panel-collapse collapse"><div class="panel-body"><?php
									ob_start();
									phpinfo();
									preg_match ('%<style type="text/css">(.*?)</style>.*?(<body>.*</body>)%s', ob_get_clean(), $matches);
									echo $matches[2];
									?></div></div></div></div></div></div><div class="tab-pane" id="authactivation"><?php
 					$return = '';

					if ( @$_POST['action'] === 'authactivation' ) {
						Sentinel::init();
						Sentinel::create();

						if ( Sentinel::userExists( $_POST['username'] ) ) {
							$return = '<br/><div class="alert alert-danger" role="alert">' . sprintf( __('User %s already exists!') , '<code>' . $_POST['username'] . '</code>' ) . '</div>';
						}
						else if ( $_POST['password'] !== $_POST['password2'] ) {
							$return = '<br/><div class="alert alert-danger" role="alert">' . __( 'Password confirmation is not the same' ) . '</div>';
						}
						else if ( mb_strlen( $_POST['password'] ) < 6 ) {
							$return = '<br/><div class="alert alert-danger" role="alert">' . __( 'Password must contain at least 6 chars' ) . '</div>';
						}
						else {
							Sentinel::setAdmin( $_POST['username'] , $_POST['password'] );
							Sentinel::save();
							$return = '<br/><div class="alert alert-success" role="alert">' . __('Authentication has been enabled and admin account has been created!') . '</div>';
						}
					}
 				?><?php if ( Sentinel::isAuthSet() ) { ?><?php echo $return; ?><br><div class="alert alert-info" role="alert"><?php _e('Authentication is currently enabled'); ?></div><br><div class="row"><div class="col-xs-12"><div class="alert alert-danger"><?php _e('Please remove this file on your server to disable authentication'); ?></div></div><br><br><div class="col-md-8"><pre class="clipboard4content">rm '<?php echo Sentinel::getAuthFilePath(); ?>'</pre></div><?php echo '<div class="col-md-4"><a class="btn btn-primary clipboard" data-source=".clipboard4content" data-placement="right" data-text="' . h( "Command has been copied to your clipboard!" ) . '">' . __('Copy to clipboard') . '</a></div>';?></div><?php } else { ?><?php echo $return; ?><br><div class="alert alert-warning" role="alert"><?php _e('Authentication is currently disabled'); ?></div><h4><?php _e( 'Setup admin account') ?></h4><form id="authsave" autocomplete="off" method="POST" action="?#authactivation"><input type="hidden" name="action" value="authactivation"><div class="container"><div class="row"><div class="input-group col-sm-6 col-md-4" id="usernamegroup" data-toggle="tooltip" data-placement="top" title="<?php _h( 'Username is required' ); ?>"><span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span> <input type="text" id="username" name="username" class="form-control" value="<?php echo h( @$_POST['username'] ); ?>" placeholder="<?php _h('Username') ?>" autofocus="autofocus"></div><br></div><div class="row"><div class="input-group col-sm-6 col-md-4" id="passwordgroup" data-toggle="tooltip" data-placement="bottom" title="<?php _h( 'Password must contain at least 6 chars' ); ?>"><span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span> <input type="password" id="password" name="password" class="form-control" value="<?php echo h( @$_POST['password'] ); ?>" placeholder="<?php _h('Password') ?>"></div><br></div><div class="row"><div class="input-group col-sm-6 col-md-4" id="password2group" data-toggle="tooltip" data-placement="bottom" title="<?php _h( 'Password is not the same' ); ?>"><span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span> <input type="password" id="password2" name="password2" class="form-control" value="<?php echo h( @$_POST['password2'] ); ?>" placeholder="<?php _h('Password Confirmation') ?>"></div></div></div><br><br><input type="submit" class="btn btn-large btn-success" value="<?php _h('Enable authentication') ?>"></form><?php } ?></div><div class="tab-pane" id="passwordtab"><?php if ( Sentinel::isAuthSet() ) { ?><br><h4><?php _e( 'Please fill an existing username and a new password') ?></h4><?php
						if ( @$_POST['action'] === 'passwordtab' ) {
							if ( ! Sentinel::userExists( $_POST['username'] ) ) {
								echo '<div class="alert alert-danger" role="alert">' . sprintf( __('User %s does not exist!') , '<code>' . $_POST['username'] . '</code>' ) . '</div>';
							}
							else if ( $_POST['password'] !== $_POST['password2'] ) {
								echo '<div class="alert alert-danger" role="alert">' . __( 'Password confirmation is not the same' ) . '</div>';
							}
							else if ( mb_strlen( $_POST['password'] ) < 6 ) {
								echo '<div class="alert alert-danger" role="alert">' . __( 'Password must contain at least 6 chars' ) . '</div>';
							}
							else {
								Sentinel::setUser( $_POST['username'] , $_POST['password'] );
								Sentinel::save();
								echo '<div class="alert alert-success" role="alert">' . sprintf( __('Password has been updated for user %s!') , '<code>' . $_POST['username'] . '</code>' ) . '</div>';
							}
						}
	 				?><form id="authsave" autocomplete="off" method="POST" action="?#passwordtab"><input type="hidden" name="action" value="passwordtab"><div class="container"><div class="row"><div class="input-group col-sm-6 col-md-4" id="usernamegroup" data-toggle="tooltip" data-placement="top" title="<?php _h( 'Username is required' ); ?>"><span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span> <input type="text" id="username" name="username" class="form-control" value="<?php echo h( @$_POST['username'] ); ?>" placeholder="<?php _h('Username') ?>" autofocus="autofocus"></div><br></div><div class="row"><div class="input-group col-sm-6 col-md-4" id="passwordgroup" data-toggle="tooltip" data-placement="bottom" title="<?php _h( 'Password must contain at least 6 chars' ); ?>"><span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span> <input type="password" id="password" name="password" class="form-control" value="<?php echo h( @$_POST['password'] ); ?>" placeholder="<?php _h('Password') ?>"></div><br></div><div class="row"><div class="input-group col-sm-6 col-md-4" id="password2group" data-toggle="tooltip" data-placement="bottom" title="<?php _h( 'Password is not the same' ); ?>"><span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span> <input type="password" id="password2" name="password2" class="form-control" value="<?php echo h( @$_POST['password2'] ); ?>" placeholder="<?php _h('Password Confirmation') ?>"></div></div></div><br><br><input type="submit" class="btn btn-large btn-success" value="<?php _h('Reset') ?>"></form><?php } else { ?><br><div class="alert alert-info" role="alert"><?php _e('This feature is only available when authentication is enabled.'); ?></div><?php } ?></div></div><?php } ?><hr><footer class="text-muted"><small><?php echo FOOTER;?></small></footer></div><script src="../js/pml.min.js"></script><script src="../js/test.min.js"></script></body></html>