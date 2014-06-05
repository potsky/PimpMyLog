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
include_once 'inc/global.inc.php';


///////////////////////////////////
// Check PHP Version             //
///////////////////////////////////
if ( version_compare( PHP_VERSION , PHP_VERSION_REQUIRED ) < 0 ) {
	$title    = __( 'Oups!' );
	$message  = sprintf( __( 'PHP version %s is required but your server run %s.') , PHP_VERSION_REQUIRED , PHP_VERSION );
	$link_url = HELP_URL;
	$link_msg = __('Learn more');
	include_once 'inc/error.inc.php';
	die();
}



/////////////////////////
// Check if configured //
/////////////////////////
if ( ! file_exists( 'config.user.json' ) ) {
	$title    = __( 'Welcome!' );
	$message  = '<br/>';
	$message .= __( 'Pimp my Log is not configured.');
	$message .= '<br/><br/>';
	$message .= '<span class="glyphicon glyphicon-cog"></span> ';
	$message .= __( 'You can manually copy <code>cfg/config.example.json</code> to <code>config.user.json</code> in the root directory and change parameters. Then refresh this page.' );
	$message .= '<br/><br/>';
	$message .= '<span class="glyphicon glyphicon-heart-empty"></span> ';
	$message .= __( 'Or let me try to configure it for you!' );
	$message .= '<br/><br/>';
	$link_url = 'inc/configure.php?' . $_SERVER['QUERY_STRING'];
	$link_msg = __('Configure now');
	include_once 'inc/error.inc.php';
	die();
}


//////////////////////////////
// Load config and defaults //
//////////////////////////////
config_load();
init();


/////////////////////////
// Check configuration //
/////////////////////////
$errors = config_check();
if ( is_array( $errors ) ) {
	$title    = __( 'Oups!' );
	$message  = '<br/>';
	$message .= __( '<code>config.user.json</code> configuration file is buggy:' ) . '<ul>';
	foreach ( $errors as $error ) {
		$message .= '<li>' . $error . '</li>';
	}
	$message .= '</ul>';
	$message .= '<br/>';
	$message .= __( 'If you want me to build the configuration for you, please remove file <code>config.user.json</code> at root and click below.' );
	$message .= '<br/><br/>';
	$link_url = 'inc/configure.php?' . $_SERVER['QUERY_STRING'];
	$link_msg = __('Configure now');
	include_once 'inc/error.inc.php';
	die();
}


//////////////////////
// Javascript Lemma //
//////////////////////
$lemma = array(
	'notification_deny' => __( 'Notifications are denied for this site. Go to your browser preferences to enable notifications for this site.' ),
	'no_log'            => __( 'No log has been found.' ),
	'regex_valid'       => __( 'Search was done with RegEx engine' ),
	'regex_invalid'     => __( 'Search was done with regular engine' ),
	'search_no_regular' => __( 'No log has been found with regular search %s' ),
	'search_no_regex'   => __( 'No log has been found with Reg Ex search %s' ),
	'new_logs'          => __( 'New logs are available' ),
	'new_log'           => __( '1 new log is available' ),
	'new_nlogs'         => __( '%s new logs are available' ),
	'display_log'       => __( '1 log displayed,' ),
	'display_nlogs'     => __( '%s logs displayed,' ),
	'error'             => __( 'An error occurs!' ),
);


///////////////////
// Session tasks //
///////////////////
$csrf = csrf_get();


?><!DOCTYPE html><!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]--><!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"><![endif]--><!--[if IE 8]><html class="no-js lt-ie9"><![endif]--><!--[if gt IE 8]><!--><html class="no-js"><!--<![endif]--><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><meta name="description" content=""><meta name="viewport" content="width=device-width"><meta name="robots" content="none"><title><?php echo TITLE;?></title><?php include_once 'inc/favicon.inc.php'; ?><?php
?><?php
?><link rel="stylesheet" href="css/pml.min.css"><?php
?><?php if ( file_exists( 'css/config.inc.user.css' ) ) { ?><link rel="stylesheet" href="css/config.inc.user.css"><?php } else { ?><link rel="stylesheet" href="css/config.inc.css"><?php } ?><script>var logs_refresh_default       = <?php echo (int)LOGS_REFRESH;?>,
			logs_max_default           = <?php echo (int)LOGS_MAX;?>,
			files                      = <?php echo json_encode($files);?>,
			title_file                 = "<?php echo TITLE_FILE;?>",
			notification_title         = "<?php echo NOTIFICATION_TITLE;?>",
			badges                     = <?php echo json_encode($badges);?>,
			lemma                      = <?php echo json_encode($lemma);?>,
			geoip_url                  = "<?php echo GEOIP_URL;?>",
			pull_to_refresh            = <?php echo ( PULL_TO_REFRESH === true ) ? 'true' : 'false';?>,
			file_selector              = "<?php echo FILE_SELECTOR;?>",
			csrf_token                 = "<?php echo $csrf;?>",
			querystring                = "<?php echo $_SERVER['QUERY_STRING'];?>",
			notification_default       = <?php echo ( NOTIFICATION === true ) ? 'true' : 'false';?>;</script></head><body><!--[if lt IE 8]><p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p><![endif]--><div class="navbar navbar-inverse navbar-fixed-top"><div class="logo" title="<?php _e('Reload the page with default parameters'); ?>"></div><div class="container"><div class="navbar-header"><button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse"><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button><div class="navbar-brand"><span class="loader glyphicon glyphicon-download" style="display:none"></span><span class="loader glyphicon glyphicon-refresh" title="<?php _e( 'Click to refresh or press the R key' );?>" id="refresh"></span><a href="?"><?php echo NAV_TITLE;?></a></div></div><div class="navbar-collapse collapse"><?php
if ( FILE_SELECTOR == 'bs' ) {
?><ul class="nav navbar-nav"><li class="dropdown" title="<?php _e( 'Select a log file to display' );?>"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><span id="file_selector"></span> <b class="caret"></b></a><ul class="dropdown-menu"><?php
foreach ( $files as $file_id=>$file ) {
	$selected = ( ( isset( $_GET['i'] ) ) && ( $_GET['i'] == $file_id ) ) ? ' active"' : '';
	echo '<li id="file_' . $file_id . '" data-file="' . $file_id . '" class="file_menup' . $selected . '"><a class="file_menu" href="#">' . $file['display'] . '</a></li>';
}
?></ul></li></ul><?php
} else {
?><form class="navbar-form navbar-left"><div class="form-group"><select id="file_selector_big" class="form-control input-sm" title="<?php _e( 'Select a log file to display' );?>"><?php
foreach ( $files as $file_id=>$file ) {
	$selected = ( ( isset( $_GET['i'] ) ) && ( $_GET['i'] == $file_id ) ) ? ' selected="selected"' : '';
	echo '<option value="' . $file_id . '"' . $selected . '>' . $file['display'] . '</option>';
}
?></select></div>&nbsp;</form><?php
}
?><form class="navbar-form navbar-right"><div class="form-group" id="searchctn"><input type="text" class="form-control input-sm clearable" id="search" value="<?php echo htmlspecialchars(@$_GET['s'],ENT_COMPAT,'UTF-8');?>" placeholder="<?php _e( 'Search in logs' );?>"></div>&nbsp;<div class="form-group"><select id="autorefresh" class="form-control input-sm" title="<?php _e( 'Select a duration to check for new logs automatically' );?>"><option value="0"><?php _e( 'No auto refresh' );?></option><?php
foreach ( get_refresh_options() as $r ) {
	echo '<option value="' . $r . '">' . sprintf( __( 'Refresh every %ss' ) , $r ) . '</option>';
}
?></select></div>&nbsp;<div class="form-group"><select id="max" class="form-control input-sm" title="<?php _e( 'Max count of logs to display' );?>"><?php
foreach ( get_max_options() as $r ) {
	echo '<option value="' . $r . '">' . sprintf( ( (int)$r>1 ) ? __( '%s logs' ) : __( '%s log' ) , $r ) . '</option>';
}
?></select></div>&nbsp;<button style="display:none" type="button" id="notification" class="btn btn-sm" title="<?php _e( 'Desktop notifications on supported modern browsers' );?>"><span class="glyphicon glyphicon-bell"></span></button></form><ul class="nav navbar-nav navbar-right"><li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-cog"></span></a><ul class="dropdown-menu cogmenu" style="padding: 15px"><li><a href="#" id="cog-wide" class="cog" data-cog="wideview" data-value="<?php echo (in_array(@$_GET['w'], array('true','on','1',''))) ? 'on' : 'off' ; ?>"><span class="glyphicon glyphicon-fullscreen"></span>&nbsp;&nbsp;<?php _e('Wide view');?>&nbsp;<span class="cogon" style="<?php echo (in_array(@$_GET['w'], array('true','on','1',''))) ? '' : 'display:none' ; ?>"><?php _e('on')?></span><span class="cogoff" style="<?php echo (in_array(@$_GET['w'], array('false','off','0'))) ? '' : 'display:none' ; ?>"><?php _e('off')?></span></a></li><li><select id="cog-lang" class="form-control input-sm" title="<?php _e( 'Language' );?>"><option value=""><?php _e( 'Change language...' );?></option><?php
									foreach ( $locale_available as $l => $n ) {
										echo '<option value="' . $l . '"';
										if ( $l == $locale ) echo ' selected="selected"';
										echo '>' . $n . '</option>';
									}
									?></select></li><li><select id="cog-tz" class="form-control input-sm" title="<?php _e( 'Timezone' );?>"><option value=""><?php _e( 'Change timezone...' );?></option><?php
									foreach ( $tz_available as $n ) {
										echo '<option value="' . $n . '"';
										if ( $n == $tz ) echo ' selected="selected"';
										echo '>' . $n . '</option>';
									}
									?></select></li></ul></li></ul></div></div></div><?php if ( PULL_TO_REFRESH === true ) { ?><div id="hook" class="hook"><div id="loader" class="hook-loader"><div class="hook-spinner"></div></div><span id="hook-text"></span></div><?php } ?><div class="container"><div id="error" style="display:none"><br><div class="alert alert-danger fade in"><h4>Oups!</h4><p id="errortxt"></p></div></div><div class="result"><br><div id="upgrademessage"></div><div id="singlenotice"></div><div id="notice"></div><div id="nolog" style="display:none" class="alert alert-info fade in"></div></div></div><div class="containerwide result tableresult"><div class="table-responsive"><table id="logs" class="table table-striped table-bordered table-hover table-condensed logs"><thead id="logshead"></thead><tbody id="logsbody"></tbody></table></div></div><div class="container"><div class="result"><small id="footer"></small></div><hr><footer class="text-muted"><small><?php echo FOOTER;?><span id="upgradefooter"></span></small></footer></div><?php
?><?php
?><script src="js/pml.min.js"></script><script src="js/main.min.js"></script><?php
if ( ( 'UA-XXXXX-X' != GOOGLE_ANALYTICS ) && ( '' != GOOGLE_ANALYTICS ) ) { ?><script>var _gaq=[['_setAccount','<?php echo GOOGLE_ANALYTICS;?>'],['_trackPageview']];
		(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
			g.src='//www.google-analytics.com/ga.js';
			s.parentNode.insertBefore(g,s)}(document,'script'));</script><?php
}
?><?php
?></body></html>