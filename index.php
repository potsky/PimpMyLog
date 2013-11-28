<?php
include_once 'inc/global.inc.php';

// Check if configured
if ( ! file_exists( 'config.inc.php' ) ) {
	$title    = __( 'Oups!' );
	$message  = __( 'This site is not configured. Please create a <code>config.inc.php</code> file at root directory.' );
	$link_url = HELP_URL;
	$link_msg = __('Learn more');
	include_once 'inc/error.php';
	die();
}
include_once 'config.inc.php';

// Check if v2 compliant
if ( isset( $_GET[ 'v1'] ) ) {
	include_once 'inc/index-v1.php';
	die();
}
else if ( ! defined( 'TITLE' ) ) {
	$title    = __( 'Oups!' );
	$message  = __( 'You was using <em>PHP Apache Log Viewer</em> v1.' ) . '<br/>' . __('You need to update the <code>config.inc.php</code> configuration file at root to upgrade <em>PHP Apache Log Viewer</em> v2.' );
	$link_url = '?v1=1';
	$link_msg = __('Use v1 anyway');
	include_once 'inc/error.php';
	die();
}

include_once 'config.inc.php';

$errors = check_config();
if ( is_array( $errors ) ) {
	$title    = __( 'Oups!' );
	$message  = __( '<code>config.inc.php</code> configuration file is buggy :' ) . '<ul>';
	foreach ( $errors as $error ) {
		$message .= '<li>' . $error . '</li>';
	}
	$message .= '</ul>';
	$link_url = '#';
	$link_msg = __('Retry');
	include_once 'inc/error.php';
	die();
}


?><!DOCTYPE html>
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
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<style>
		body {
			padding-top: 50px;
			padding-bottom: 20px;
		}
	</style>
	<link rel="stylesheet" href="css/bootstrap-theme.min.css">
	<link rel="stylesheet" href="css/main.css">
<?php if ( file_exists( 'config.inc.css' ) ) { ?>
	<link rel="stylesheet" href="config.inc.css">
<?php } else { ?>
	<link rel="stylesheet" href="css/config.inc.css">
<?php } ?>
	<link rel="stylesheet" href="js/vendor/Hook.js/hook.css">
	<script src="js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
	<script>
		var logs_refresh_default = <?php echo (int)LOGS_REFRESH;?>,
			logs_max_default = <?php echo (int)LOGS_MAX;?>,
			files = <?php echo json_encode($files);?>,
			notification_title = "<?php echo NOTIFICATION_TITLE;?>",
			severities = <?php echo json_encode($severities);?>,
			pull_to_refresh = <?php echo ( PULL_TO_REFRESH===true ) ? 'true' : 'false';?>,
			notification_default = <?php echo ( NOTIFICATION===true ) ? 'true' : 'false';?>;
	</script>
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
				<a class="navbar-brand" href="#"><?php echo TITLE;?></a>
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav">
					<li class="dropdown" title="<?php _e( 'Select a log file to display' );?>">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span id="file_selector"></span> <b class="caret"></b></a>
						<ul class="dropdown-menu">
<?php
foreach ( $files as $file_id=>$file ) {
	echo '<li id="file_' . $file_id . '" data-file="' . $file_id . '"><a class="file_menu" href="#">' . $file['display'] . '</a></li>';
}
?>
						</ul>
					</li>
					<li class="loader" style="display:none;"><img src="img/loading.gif" class="loading" /></li>
					<li class="loader"><a href="#" title="<?php _e( 'Click to refresh' );?>"><span id="refresh" class="glyphicon glyphicon-refresh"></span></a></li>
				</ul>
				<form class="navbar-form navbar-right">
					<div class="form-group">
						<select id="autorefresh" class="form-control input-sm" title="<?php _e( 'Select a duration to check for new logs automatically' );?>">
							<option value="0"><?php _e( 'No auto refresh' );?></option>
<?php
foreach ( get_refresh_options() as $r ) {
	echo '<option value="' . $r . '">' . sprintf( __( 'Refresh every %ss' ) , $r ) . '</option>';
}
?>
						</select>
					</div>
					<div class="form-group">
						<select id="max" class="form-control input-sm" title="<?php _e( 'Max count of logs to display' );?>">
<?php
foreach ( get_max_options() as $r ) {
	echo '<option value="' . $r . '">' . sprintf( ( (int)$r>1 ) ? __( '%s logs' ) : __( '%s log' ) , $r ) . '</option>';
}
?>
						</select>
					</div>
					<button style="display:none;" type="button" id="notification" class="btn btn-sm" title="<?php _e( 'Desktop notifications on supported modern browsers' );?>">
					  <span class="glyphicon glyphicon-bell"></span>
					</button>
				</form>
			</div><!--/.navbar-collapse -->
		</div>
	</div>

<?php if ( PULL_TO_REFRESH === true ) { ?>
	<div id="hook" class="hook">
		<div id="loader">
			<div class="spinner"></div>
		</div>
		<span id="hook-text"><?php _e('Reloading...');?></span>
	</div>
<?php } ?>

	<div class="container">
		<div id="error" style="display:none;"><div class="alert alert-danger fade in"><h4>Oups!</h4><p id="errortxt"></p></div></div>
		<div id="result">
			<br/>
			<div class="table-responsive">
				<table id="logs" class="table table-striped table-bordered table-hover table-condensed logs">
					<thead id="logshead"></thead>
					<tbody id="logsbody"></tbody>
				</table>
			</div>
			<small id="compute"></small>
		</div>
		<hr>
		<footer>
			<p><?php echo FOOTER;?></p>
		</footer>
	</div>

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
	<script>window.jQuery || document.write('<script src="js/vendor/jquery-1.10.1.min.js"><\/script>')</script>
	<script src="js/vendor/bootstrap.min.js"></script>
	<script src="js/vendor/ua-parser.min.js"></script>
    <script src="js/vendor/Hook.js/mousewheel.js"></script>
    <script src="js/vendor/Hook.js/hook.min.js"></script>
	<script src="js/main.js"></script>
<?php if ( ( 'UA-XXXXX-X' != GOOGLE_ANALYTICS ) && ( '' != GOOGLE_ANALYTICS ) ) { ?>
	<script>
		var _gaq=[['_setAccount','<?php echo GOOGLE_ANALYTICS;?>'],['_trackPageview']];
		(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
			g.src='//www.google-analytics.com/ga.js';
			s.parentNode.insertBefore(g,s)}(document,'script'));
	</script>
<?php } ?>
</body>
</html>
