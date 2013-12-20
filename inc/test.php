<?php
include_once 'global.inc.php';
init();

if ( basename( __FILE__ ) !== 'test.REMOVE_UPPERCASE.php' ) {
	die( __('Please copy <code>inc/test.php</code> to <code>inc/test.REMOVE_UPPERCASE.php</code> and load <code>inc/test.REMOVE_UPPERCASE.php</code> in your browser') );
}

function test( $type , $regex , $match , $types , $log ) {
	$r  = '<h4>' . $type . '</h4>';
	$r .= '<pre>';
	$r .= '<strong>Regex</strong>: ' . $regex . "\n";
	$r .= '<strong>Log  </strong>: ' . $log . "\n";
	$r .= "\n";
	$tokens = parser( $regex , $match , $log , $types );
	if ( is_array($tokens) ) {
		$maxlength = 0;
		foreach ( $tokens as $token => $value ) $maxlength = max( $maxlength , strlen( $token ) );
		foreach ( $tokens as $token => $value ) {
			$r .= '<strong>' . str_pad( $token , $maxlength ) . '</strong>: ' . $value . "\n";
		}
	}
	$r .= '</pre>';
	return $r;
}

$types = array(
	'Date'    => 'date:Y/m/d H:i:s',
);


if ( isset( $_POST['s'] ) ) {

	$return = array();
	$match  = @json_decode( $_POST['m'] , true );
	$regex  = $_POST['r'];
	$log    = $_POST['l'];

	if ( ! is_array( $match ) ) {
		$return['err'] = 'inputMatch';
		$return['msg'] = '<div class="alert alert-danger"><strong>' . __('Error') . '</strong> '. __('Match is not a valid associative array') . '</div>';
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
	$return['msg'] = test( '' , $regex , $match, $types, $log );

	echo json_encode( $return );
	die();
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
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width">
	<title><?php echo TITLE;?></title>
	<?php include_once 'favicon.inc.php'; ?>
	<link rel="stylesheet" href="../css/bootstrap.min.css">
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
			<div class="logo"></div>
			<div class="navbar-header">
				<a class="navbar-brand" href="#">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Debugger');?></a>
			</div>
		</div>
	</div>

	<div class="container">
		<br/>
		<div class="panel-group" id="accordion">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
							<?php _e('Regex tester');?>
						</a>
					</h4>
				</div>
				<div id="collapseTwo" class="panel-collapse collapse in">
					<div class="panel-body">
						<form class="form-horizontal" role="form" id="regextest">
							<div class="form-group" id="GPinputLog">
								<label for="inputLog3" class="col-sm-2 control-label"><?php _e('Log');?></label>
								<div class="col-sm-10">
									<textarea class="form-control test" id="inputLog" placeholder="Log"><?php
									echo '127.0.0.1 - - [27/Nov/2013:10:20:40 +0100] "GET /~potsky/PHPApacheLogViewer/inc/get_logs.php?ldv=false&file=access&max=27 HTTP/1.1" 200 33 "http://localhost/~potsky/PHPApacheLogViewer/" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9) AppleWebKit/537.71 (KHTML, like Gecko) Version/7.0 Safari/537.71"';
									?></textarea>
								</div>
							</div>
							<div class="form-group" id="GPinputRegEx">
								<label for="inputRegEx3" class="col-sm-2 control-label"><?php _e('RegEx');?></label>
								<div class="col-sm-10">
									<textarea class="form-control test" id="inputRegEx" placeholder="RegEx"><?php
										echo '|^(.*) (.*) (.*) \[(.*)\] "(.*) (.*) (.*)" ([0-9]*) (.*) "(.*)" "(.*)"( [0-9]*/([0-9]*))*$|U';
									?></textarea>
								</div>
							</div>
							<div class="form-group" id="GPinputMatch">
								<label for="inputMatch3" class="col-sm-2 control-label"><?php _e('Match');?></label>
								<div class="col-sm-10">
									<textarea class="form-control test" id="inputMatch" placeholder="Match" rows="5"><?php
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
										'Time'    => 13,
									);
									echo json_indent( json_encode($match))
									?></textarea>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-2 col-sm-10">
									<button type="submit" class="btn btn-primary"><?php _e('Test');?></button>
								</div>
							</div>
							<div id="regexresult"></div>
						</form>
					</div>
				</div>
			</div>

			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">
							<?php _e('Regex samples');?>
						</a>
					</h4>
				</div>
				<div id="collapseThree" class="panel-collapse collapse">
					<div class="panel-body">
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
echo test( $type , $regex , $match , $types , $log );

$type  = 'Error Apache 2.4 without referer and without module name';
$log   = "[Fri Oct 11 04:41:06.897613 2013] [:error] [pid 61939] [client 192.168.207.71:44171] script '/usr/local/www/apache24/data/test.php' not found or unable to stat";
echo test( $type , $regex , $match , $types , $log );

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
?>
					</div>
				</div>
			</div>

			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
							Configuration
						</a>
					</h4>
				</div>

				<div id="collapseOne" class="panel-collapse collapse">
					<div class="panel-body">

						<div class="panel-group" id="accordion2">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title">
										<a data-toggle="collapse" data-parent="#accordion2" href="#collapseOne2">
											Code <code>config.json</code>
										</a>
									</h4>
								</div>
								<div id="collapseOne2" class="panel-collapse collapse">
									<div class="panel-body">
										<pre><?php show_source('../config.json'); ?></pre>
									</div>
								</div>
							</div>
							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title">
										<a data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo2">
											Stat <code>config.json</code>
										</a>
									</h4>
								</div>
								<div id="collapseTwo2" class="panel-collapse collapse">
									<div class="panel-body">
										<pre><?php var_export( stat('../config.json') ); ?></pre>
									</div>
								</div>
							</div>
							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title">
										<a data-toggle="collapse" data-parent="#accordion2" href="#collapseFour2">
											<?php _e('Rights');?>
										</a>
									</h4>
								</div>
								<div id="collapseFour2" class="panel-collapse collapse">
									<div class="panel-body">
										<pre><?php
										if (function_exists('posix_getpwuid')) {
											var_dump( @posix_getpwuid(posix_geteuid()) );
										} else {
											_e('No POSIX functions...');
										}
										?></pre>
										<?php
											config_load( '../config.json' );
											$paths = array(
												'config' => '../config.json',
											);
											if ( is_array( @$files ) ) {
												foreach ( $files as $fileid => $file ) {
													$paths[ '--> ' . $fileid ] = @$file['path'];
													$dir_name = realpath( $file['path'] );
													if (file_exists($dir_name)) {
														while ( ( $dir_name = dirname( $dir_name ) ) != '/' ) {
															$paths[ $dir_name ] = $dir_name;
														}
													}
												}
											}

											echo '<table>';
											echo '<thead><tr><th>ID</th><th>'.__('Path').'</th><th>'.__('Real path').'</th><th>'.__('Read').'</th><th>'.__('Write').'</th></tr></thead>';
											echo '<tbody>';
											foreach ($paths as $id=>$file) {
												echo '<tr>
												<td>'.$id.'</td>
												<td><code>'.$file.'</code></td>
												<td><code>'.realpath($file).'</code></td>
												<td>' . ( is_readable($file) ? '<span class="label label-success">'.__('Yes').'</span>' : '<span class="label label-danger">'.__('No').'</span>'  ) . '</td>
												<td>' . ( is_writable($file) ? '<span class="label label-success">'.__('Yes').'</span>' : '<span class="label label-danger">'.__('No').'</span>'  ) . '</td>
												</tr>';
											}
											echo '</tbody>';
											echo '</table>';
										?>
									</div>
								</div>
							</div>
							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title">
										<a data-toggle="collapse" data-parent="#accordion2" href="#collapseThree2">
											PHPInfo
										</a>
									</h4>
								</div>
								<div id="collapseThree2" class="panel-collapse collapse">
									<div class="panel-body">
										<?php
										ob_start();
										phpinfo();
										preg_match ('%<style type="text/css">(.*?)</style>.*?(<body>.*</body>)%s', ob_get_clean(), $matches);
										echo $matches[2];
										?>
									</div>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
		<hr>
		<footer class="text-muted"><small><?php echo FOOTER;?></small></footer>
	</div>
	<script src="../js/vendor/jquery-1.10.1.min.js"></script>
	<script src="../js/vendor/bootstrap.min.js"></script>
	<script src="../js/test.js"></script>
</body>
</html>
