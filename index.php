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
include_once 'inc/global.inc.php';

/*
|--------------------------------------------------------------------------
| Check PHP Version
|--------------------------------------------------------------------------
|
*/
if ( version_compare( PHP_VERSION , PHP_VERSION_REQUIRED ) < 0 ) {
	$title    = __( 'Oups!' );
	$message  = sprintf( __( 'PHP version %s is required but your server run %s.') , PHP_VERSION_REQUIRED , PHP_VERSION );
	$link_url = HELP_URL;
	$link_msg = __('Learn more');
	include_once 'inc/error.inc.php';
	die();
}


/*
|--------------------------------------------------------------------------
| Check if configured
|--------------------------------------------------------------------------
|
*/
$config_file_name = get_config_file_name();
if ( is_null( $config_file_name ) ) {
	$title    = __( 'Welcome!' );
	$message  = '';
	$message .= '<br/>';
	$message .= __( 'Pimp my Log is not configured.');
	$message .= '<br/><br/>';
	$message .= '<span class="glyphicon glyphicon-cog"></span> ';
	$message .= sprintf( __( 'You can manually copy <code>cfg/config.example.php</code> to %s in the root directory and change parameters. Then refresh this page.' ) , '<code>' . CONFIG_FILE_NAME . '</code>' );
	$message .= '<br/><br/>';
	$message .= '<span class="glyphicon glyphicon-heart-empty"></span> ';
	$message .= __( 'Or let me try to configure it for you!' );
	$message .= '<br/><br/>';
	if ( SUHOSIN_LOADED === true ) {
		$message .= '<div class="alert alert-danger"><strong>';
		$message .= sprintf( __('Suhosin extension is loaded, according to its configuration, Pimp My Log could not run normally... More information %shere%s.') , '<a href="' . SUHOSIN_URL . '">' , '</a>' );
		$message .= '</strong></div>';
		$message .= '<br/><br/>';
	}
	$link_url = 'inc/configure.php?' . $_SERVER['QUERY_STRING'];
	$link_msg = __('Configure now');
	include_once 'inc/error.inc.php';
	die();
}


/*
|--------------------------------------------------------------------------
| Load config and constants
|--------------------------------------------------------------------------
|
*/
list( $badges , $files , $tz ) = config_load();


/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
|
*/
$current_user = Sentinel::attempt( $files );


/*
|--------------------------------------------------------------------------
| Check configuration
|--------------------------------------------------------------------------
|
*/
$errors = config_check( $files );

if ( $errors === false ) {
	$title    = __( 'Oups!' );
	$message  = '<br/>';
	$message .= __( 'Your access is disabled, you cannot view any log file.' );
	$message .= '<br/>';
	$message .= __( 'Please contact your administrator.' );
	$message .= '<br/><br/>';
	$link_url = '?signout&l=' . $locale;
	$link_msg = __('Sign out');
	include_once 'inc/error.inc.php';
	die();
}

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


/*
|--------------------------------------------------------------------------
| Javascript lemma
|--------------------------------------------------------------------------
|
*/
$lemma = array(
	'action'                  => __( 'Action' ),
	'addadmin'                => __( 'Add admin' ),
	'adduser'                 => __( 'Add user' ),
	'all_access'              => __( 'All accesses granted' ),
	'anonymous_ok'            => __( 'Anonymous access has been successfully saved!' ),
	'authlogerror'            => __( 'There is no log to display and your are connected... It seems that global parameter <code>AUTH_LOG_FILE_COUNT</code> is set to 0. Change this parameter to a higher value to display logs.' ),
	'changepwd'               => __( 'Password changed' ),
	'createdby'               => __( 'Created by' ),
	'creationdate'            => __( 'Created at' ),
	'date'                    => __( 'Date' ),
	'deleteuser'              => __( 'Delete user' ),
	'display_log'             => __( '1 log displayed,' ),
	'display_nlogs'           => __( '%s logs displayed,' ),
	'error'                   => __( 'An error occurs!' ),
	'form_invalid'            => __( 'Form is invalid:' ),
	'ip'                      => __( 'IP' ),
	'lastlogin'               => __( 'Last login' ),
	'loadmore'                => __( 'Still %s to load'),
	'logincount'              => __( 'Logins' ),
	'new_log'                 => __( '1 new log is available' ),
	'new_logs'                => __( 'New logs are available' ),
	'new_nlogs'               => __( '%s new logs are available' ),
	'no_log'                  => __( 'No log has been found.' ),
	'notification_deny'       => __( 'Notifications are denied for this site. Go to your browser preferences to enable notifications for this site.' ),
	'profile_ok'              => __( 'Your profile has been successfully saved!' ),
	'reallydeleteuser'        => __( 'Confirm' ),
	'reallysigninuser'        => __( 'Confirm' ),
	'regex_invalid'           => __( 'Search was done with regular engine' ),
	'regex_valid'             => __( 'Search was done with RegEx engine' ),
	'resultcopied'            => __( 'Result copied!' ),
	'roles'                   => __( 'Roles' ),
	'search_no_regex'         => __( 'No log has been found with Reg Ex search %s' ),
	'search_no_regular'       => __( 'No log has been found with regular search %s' ),
	'signin'                  => __( 'Sign in' ),
	'signinas'                => __( 'Sign in as' ),
	'signinerr'               => __( 'Sign in error' ),
	'signinuser'              => __('Sign in as'),
	'signout'                 => __( 'Sign out' ),
	'system'                  => __( 'System' ),
	'toggle_column'           => __( 'Toggle column %s' ),
	'urlcopied'               => __( 'URL copied!' ),
	'user'                    => __( 'User' ),
	'user_add_ok'             => __( 'User has been successfully saved!' ),
	'user_api_lastlogin'      => __( 'Last API call' ),
	'user_api_logincount'     => __( 'API calls' ),
	'user_at'                 => __( 'Access token' ),
	'user_cb'                 => __( 'Created by' ),
	'user_cd'                 => __( 'Created at' ),
	'user_delete_ok'          => __( 'User has been successfully deleted!' ),
	'user_hp'                 => __( 'Presalt key' ),
	'user_lastlogin'          => __( 'Last login' ),
	'user_logincount'         => __( 'Logins' ),
	'user_logs'               => __( 'Log access' ),
	'user_roles'              => __( 'Roles' ),
	'useragent'               => __( 'User agent' ),
	'username'                => __( 'User name' ),
	'users'                   => __( 'Users' ),
	'youhavebeendisconnected' => __( 'You need to sign in' ),
);


/*
|--------------------------------------------------------------------------
| Session
|--------------------------------------------------------------------------
|
*/
$csrf = csrf_get();


/*
|--------------------------------------------------------------------------
| HTML
|--------------------------------------------------------------------------
|
*/
?><!DOCTYPE html><!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]--><!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"><![endif]--><!--[if IE 8]><html class="no-js lt-ie9"><![endif]--><!--[if gt IE 8]><!--><html class="no-js"><!--<![endif]--><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><meta name="description" content=""><meta name="viewport" content="width=device-width"><meta name="robots" content="none"><title><?php echo TITLE;?></title><?php include_once 'inc/favicon.inc.php'; ?><link rel="stylesheet" href="css/pml.min.css"><?php
		// We inject the custom css file instead of loading it because of composer installations
		// In composer installations, the css file is out of the public server scope
		if ( file_exists( PML_CONFIG_BASE . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'config.inc.user.css' ) ) {
			echo '<style>';
			echo file_get_contents( PML_CONFIG_BASE . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'config.inc.user.css' );
			echo '</style>';
		} else {
			echo '<link rel="stylesheet" href="css/config.inc.css">';
		}
	?><script>var logs_refresh_default = <?php echo (int)LOGS_REFRESH;?>,
			logs_max_default     = <?php echo (int)LOGS_MAX;?>,
			files                = <?php echo json_encode($files);?>,
			title_file           = <?php echo json_encode( TITLE_FILE ); ?>,
			notification_title   = <?php echo json_encode( NOTIFICATION_TITLE ); ?>,
			badges               = <?php echo json_encode( $badges ); ?>,
			lemma                = <?php echo json_encode( $lemma ); ?>,
			geoip_url            = <?php echo json_encode( GEOIP_URL ); ?>,
			port_url             = <?php echo json_encode( PORT_URL ); ?>,
			pull_to_refresh      = <?php echo ( PULL_TO_REFRESH === true ) ? 'true' : 'false';?>,
			file_selector        = <?php echo json_encode( FILE_SELECTOR ); ?>,
			csrf_token           = <?php echo json_encode( $csrf ); ?>,
			querystring          = <?php echo json_encode( $_SERVER['QUERY_STRING'] ); ?>,
			currentuser          = <?php echo json_encode( $current_user ); ?>,
			export_default       = <?php echo ( EXPORT === true ) ? 'true' : 'false';?>;
			notification_default = <?php echo ( NOTIFICATION === true ) ? 'true' : 'false';?>;</script></head><body><!--[if lt IE 8]><p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p><![endif]--><div class="navbar navbar-inverse navbar-fixed-top"><div class="logo" title="<?php _e('Reload the page with default parameters'); ?>"></div><div class="container"><div class="navbar-header"><button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse"><span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span></button><div class="navbar-brand"><span class="loader glyphicon glyphicon-refresh icon-spin" style="display:none"></span> <span class="loader glyphicon glyphicon-repeat" title="<?php _h( 'Click to refresh or press the R key' );?>" id="refresh"></span> <a href="?"><?php echo NAV_TITLE;?></a></div></div><div class="navbar-collapse collapse"><?php if ( count( $files ) > 1 ) : ?><?php if ( FILE_SELECTOR == 'bs' ) : ?><ul class="nav navbar-nav"><li class="dropdown" title="<?php _h( 'Select a log file to display' );?>"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><span id="file_selector"></span></a><ul class="dropdown-menu"><?php

									$notagged = '';
									$tagged   = '';
									foreach ( config_extract_tags( $files ) as $tag => $f ) {
										if ( $tag === '_' ) {
											foreach ( $f as $file_id ) {
												$selected  = ( ( isset( $_GET['i'] ) ) && ( $_GET['i'] === $file_id ) ) ? ' active' : '';
												$notagged .= '<li id="file_' . $file_id . '" data-file="' . $file_id . '" class="file_menup' . $selected . '"><a class="file_menu" href="#" title="';
												$notagged .= ( isset( $files[ $file_id ][ 'included_from' ] ) ) ? h( sprintf( __('Log file #%s defined in %s' ) , $file_id , $files[ $file_id ]['included_from'] ) ) : h( sprintf( __( 'Log file #%s defined in main configuration file' ) , $file_id ) );
												$notagged .= " &gt; " . $files[ $file_id ][ 'path' ];
												$notagged .= '">' . $files[ $file_id ]['display'] . '</a></li>';
											}
										} else {
											$tagged .= '<li class="tag-' . get_slug( $tag ) . '"><a href="#">' . h( $tag );
											if ( TAG_DISPLAY_LOG_FILES_COUNT === true ) $tagged .= ' <small class="text-muted">(' . count( $f ) . ')</small>';
											$tagged .= '</a>';
											$tagged .= 	'<ul class="dropdown-menu">';
											foreach ( $f as $file_id ) {
												$selected = ( ( isset( $_GET['i'] ) ) && ( $_GET['i'] === $file_id ) ) ? ' active' : '';
												$tagged  .= '<li id="file_' . $file_id . '" data-file="' . $file_id . '" class="file_menup' . $selected . '"><a class="file_menu" href="#" title="';
												$tagged  .= ( isset( $files[ $file_id ]['included_from'] ) ) ? h( sprintf( __('Log file #%s defined in %s' ) , $file_id , $files[ $file_id ]['included_from'] ) ) : h( sprintf( __( 'Log file #%s defined in main configuration file' ) , $file_id ) );
												$tagged  .= " &gt; " . $files[ $file_id ][ 'path' ];
												$tagged  .= '">' . $files[ $file_id ]['display'] . '</a></li>';
											}
											$tagged .= 	'</ul>';
											$tagged .= '</li>';
										}
									}

									if ( TAG_NOT_TAGGED_FILES_ON_TOP === true ) {
										echo $notagged;
										if ( ( ! empty( $tagged ) ) && ( ! empty( $notagged ) ) ) echo '<li class="divider"></li>';
										echo $tagged;
									} else {
										echo $tagged;
										if ( ( ! empty( $tagged ) ) && ( ! empty( $notagged ) ) ) echo '<li class="divider"></li>';
										echo $notagged;
									}

									?></ul></li></ul><?php else : ?><form class="navbar-form navbar-left"><div class="form-group"><select id="file_selector_big" class="form-control input-sm" title="<?php _h( 'Select a log file to display' );?>"><?php
									foreach ( $files as $file_id=>$file ) {
										$selected = ( ( isset( $_GET['i'] ) ) && ( $_GET['i'] == $file_id ) ) ? ' selected="selected"' : '';
										echo '<option value="' . $file_id . '"' . $selected . '>' . $file['display'] . '</option>';
									}
									?></select></div>&nbsp;</form><?php endif; ?><?php else : ?><?php
					foreach ( $files as $file_id => $file ) {
						$d = $file['display'];
						$i = h( $file_id );
						break;
					}
					?><ul class="nav navbar-nav"><li id="singlelog" data-file="<?php echo $i; ?>"><a href="#"><?php echo $d; ?></a></li></ul><?php endif; ?><form class="navbar-form navbar-right"><?php if ( ( is_null( $current_user ) ) && ( Sentinel::isAnonymousEnabled( $files ) ) ) { ?><div class="form-group"><a href="?signin" class="btn-menu btn-primary btn-sm" title="<?php _h( 'Sign in' );?>"><?php _e('Sign in');?></a></div>&nbsp; <?php } ?><div class="form-group" id="searchctn"><input type="text" class="form-control input-sm clearable" id="search" value="<?php echo h( @$_GET['s'] );?>" placeholder="<?php _h( 'Search in logs' );?>"></div>&nbsp;<div class="form-group"><select id="autorefresh" class="form-control input-sm" title="<?php _h( 'Select a duration to check for new logs automatically' );?>"><option value="0"><?php _e( 'No refresh' );?></option><?php
							foreach ( get_refresh_options( $files ) as $r ) {
								echo '<option value="' . $r . '">' . sprintf( __( 'Refresh %ss' ) , $r ) . '</option>';
							}
							?></select></div>&nbsp;<div class="form-group"><select id="max" class="form-control input-sm" title="<?php _h( 'Max count of logs to display' );?>"><?php
							foreach ( get_max_options( $files ) as $r ) {
								echo '<option value="' . $r . '">' . sprintf( ( (int)$r>1 ) ? __( '%s logs' ) : __( '%s log' ) , $r ) . '</option>';
							}
							?></select></div>&nbsp;<div class="form-group"><button style="display:none" type="button" id="notification" class="btn-menu btn-sm" title="<?php _h( 'Desktop notifications on supported modern browsers' );?>"><span class="glyphicon glyphicon-bell"></span> <span class="visible-xs-* visible-sm-* hidden-md hidden-lg"><?php _e( 'Notifications' ); ?></span></button></div></form><ul class="nav navbar-nav navbar-right"><li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="thmenuicon glyphicon glyphicon-th"></span> <span class="visible-xs-* visible-sm-* hidden-md hidden-lg"><?php _h('Displayed columns');?></span></a><ul class="dropdown-menu thmenu" style="padding: 15px"><li><a href="#" class="visible-lg-* visible-md-* hidden-sm hidden-xs" title="<?php _h('Displayed columns');?>"><?php _h('Displayed columns');?></a></li></ul></li><li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-cog"></span> <span class="visible-xs-* visible-sm-* hidden-md hidden-lg"><?php _h('Settings');?></span></a><ul class="dropdown-menu cogmenu" style="padding: 15px"><li><a href="#" id="cog-wide" class="cog btn btn-default" data-cog="wideview" data-value="<?php echo (in_array(@$_GET['w'], array('true','on','1',''))) ? 'on' : 'off' ; ?>"><span class="glyphicon glyphicon-fullscreen"></span>&nbsp;&nbsp;<?php _e('Wide view');?>&nbsp; <span class="cogon" style="<?php echo (in_array(@$_GET['w'], array('true','on','1',''))) ? '' : 'display:none' ; ?>"><?php _e('on')?></span> <span class="cogoff" style="<?php echo (in_array(@$_GET['w'], array('false','off','0'))) ? '' : 'display:none' ; ?>"><?php _e('off')?></span></a></li><li><a href="#" id="clear-markers" class="btn btn-default" title="<?php _h('Click on a date field to mark a row');?>"><span class="glyphicon glyphicon-bookmark"></span>&nbsp;&nbsp;<?php _e('Clear markers');?></a></li><li><select id="cog-lang" class="form-control input-sm" title="<?php _h( 'Language' );?>"><?php if ( GETTEXT_SUPPORT === true ): ?><option value=""><?php _e( 'Change language...' );?></option><?php
											foreach ( $locale_available as $l => $n ) {
												echo '<option value="' . $l . '"';
												if ( $l == $locale ) echo ' selected="selected"';
												echo '>' . $n . '</option>';
											}
										?><?php else : ?><option value=""><?php _e( 'Language cannot be changed' );?></option><?php endif; ?></select></li><li><select id="cog-tz" class="form-control input-sm" title="<?php _h( 'Timezone' );?>"><option value=""><?php _e( 'Change timezone...' );?></option><?php
										foreach ( DateTimeZone::listIdentifiers() as $n ) {
											echo '<option value="' . $n . '"';
											if ( $n == $tz ) echo ' selected="selected"';
											echo '>' . $n . '</option>';
										}
									?></select></li></ul></li><?php if ( ! is_null( $current_user ) ) { ?><li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" title="<?php echo h(  sprintf( __('You are currently connected as %s') , $current_user ) ); ?>"><span class="glyphicon glyphicon-user"></span> <span class="visible-xs-* visible-sm-* hidden-md hidden-lg"><?php _h('User settings');?></span></a><ul class="dropdown-menu"><?php if ( Sentinel::isAdmin() ) { ?><li><a href="#" title="<?php _h('Click here to manager users'); ?>" data-toggle="modal" data-target="#umModal"><span class="glyphicon glyphicon-flash"></span>&nbsp;&nbsp;<?php _e('Manage users'); ?></a></li><?php } ?><li><a href="#" title="<?php _h('Click here to view your profile'); ?>" data-toggle="modal" data-target="#prModal"><span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp;<?php _e('Profile'); ?></a></li><li><a href="#" title="<?php _h('Click here to change your password'); ?>" data-toggle="modal" data-target="#cpModal"><span class="glyphicon glyphicon-lock"></span>&nbsp;&nbsp;<?php _e('Change password'); ?></a></li><li><a href="?signout&l=<?php echo $locale;?>" title="<?php _h('Click here to sign out'); ?>"><span class="glyphicon glyphicon-log-out"></span>&nbsp;&nbsp;<?php _e('Sign out'); ?></a></li></ul></li><?php } ?></ul></div></div></div><?php if ( PULL_TO_REFRESH === true ) { ?><div id="hook" class="hook"><div id="loader" class="hook-loader"><div class="hook-spinner"></div></div><span id="hook-text"></span></div><?php } ?><div class="container"><?php if ( isset( $_SESSION['upgradegitpullok'] ) ) :
			unset( $_SESSION['upgradegitpullok'] );
			$infos = get_current_pml_version_infos();
			$print = '<strong>' . sprintf( __('Welcome in version %s') , $infos['v'] ) . '</strong>';
			if ( isset( $infos['welcome'] ) ) {
				$print .= '<br/>' . $infos['welcome'] . '<br/>';
			}
			$print .= '<br/>';
			$print .= sprintf( __('The changelog and all informations about this version are available on the %sblog%s.') , '<a href="http://pimpmylog.com/blog/" target="_blank">' , '</a>' );
		?><div><br><div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> <?php echo $print; ?></div></div><?php endif; ?><div id="upgradeerrorctn" style="display:none"><br><div class="alert alert-danger" id="upgradeerror"></div></div><div id="error" style="display:none"><br><div class="alert alert-danger fade in"><h4>Oups!</h4><p id="errortxt"></p></div></div><div class="result"><br><div id="upgrademessages"></div><div id="upgrademessage"></div><div id="singlenotice"></div><div id="notice"></div><div id="nolog" style="display:none" class="alert alert-info fade in"></div></div></div><div class="containerwide result tableresult"><div class="table-responsive"><table id="logs" class="table table-striped table-bordered table-hover table-condensed logs"><thead id="logshead"></thead><tbody id="logsbody"></tbody></table></div><div class="row" id="export" style="display:none"><div class="col-xs-8 col-xs-offset-2 col-sm-5 col-sm-offset-1 col-md-4 col-md-offset-2 col-lg-2 col-lg-offset-4"><button style="width:100%; margin-bottom:1em" type="button" class="loadmore btn btn-xs btn-primary" data-loading-text="<?php _h('Loading...');?>" data-nomore-text="<?php _h('No more data');?>"><?php _e('Load more');?></button></div><div class="col-xs-8 col-xs-offset-2 col-sm-5 col-sm-offset-0 col-md-4 col-md-offset-0 col-lg-2 col-lg-offset-0"><div class="btn-group" style="width:100%"><button style="width:100%" type="button" class="btn btn-xs btn-warning dropdown-toggle" data-toggle="dropdown"><?php _e('Export');?>&nbsp;<span class="caret"></span></button><ul class="dropdown-menu" role="menu"><li><a href="#" onclick="get_rss('ATOM')"><?php _e('ATOM');?></a></li><li><a href="#" onclick="get_rss('CSV')"><?php _e('CSV');?></a></li><li><a href="#" onclick="get_rss('JSON')"><?php _e('JSON');?></a></li><li><a href="#" onclick="get_rss('JSONP')"><?php _e('JSONP (with callback)');?></a></li><li><a href="#" onclick="get_rss('JSONPR')"><?php _e('JSON Pretty Print');?></a></li><li><a href="#" onclick="get_rss('RSS')"><?php _e('RSS');?></a></li><li><a href="#" onclick="get_rss('XML')"><?php _e('XML');?></a></li></ul></div></div></div><div class="row" id="noexport" style="display:none"><div class="col-xs-8 col-xs-offset-2 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-2 col-lg-offset-5"><button style="width:100%; margin-bottom:1em" type="button" class="loadmore btn btn-xs btn-primary" data-loading-text="<?php _h('Loading...');?>" data-nomore-text="<?php _h('No more data');?>"><?php _e('Load more');?></button></div></div></div><div class="container"><br><div class="result"><small id="footer"></small></div><hr><footer class="text-muted"><small><?php echo FOOTER;?>&nbsp;-&nbsp;<a href="inc/test.php"><?php _e('Debugger'); ?></a> &nbsp;-&nbsp;<a href="#" data-toggle="modal" data-target="#changeLogModal"><?php _e('Change log'); ?></a> <span id="upgradefooter"></span></small></footer></div><div class="modal fade" id="exModal" tabindex="-1" role="dialog" aria-labelledby="exModalLabel" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php _e('Close');?></span></button><h4 class="modal-title" id="exModalLabel"><?php _e('Export');?>&nbsp;<code id="exModalFormat"></code></h4></div><div class="modal-body"><h3><?php _e('Webservice URL'); ?></h3><div class="alert alert-info" id="exModalWar"><span class="glyphicon glyphicon-warning-sign"></span>&nbsp;<?php _e('Your URL seems to be local so it won\'t be reachable by external browsers and services' );?></div><pre><code id="exModalUrl"></code></pre><div class="row"><div class="col-xs-8"><a class="btn btn-xs btn-primary clipboardex"><?php _e('Copy to clipboard'); ?></a></div><div class="col-xs-4 text-right"><a class="btn btn-xs btn-primary" id="exModalOpen" target="_blank"><?php _e('Open'); ?></a></div></div><br><blockquote><p><?php _e('Feel free to change these parameters in the URL:'); ?></p><ul><li><code>l</code> : <?php _e('language');?></li><li><code>tz</code> : <?php _e('timezone to convert dates');?></li><li><code>format</code> : <?php _e('available formats are CSV, JSON, JSONPR and XML');?></li><li><code>count</code> : <?php _e('the maximum count of returned log lines');?></li><li><code>timeout</code> : <?php _e('the timeout in seconds to return log lines. Increase this value for big counts or when using search');?></li><li><code>search</code> : <?php _e('search this value in log lines. It can be a regular expression or a regex one');?></li><li><code>callback</code>: <?php _e('this field is optional and is used to specify a callback function when format is JSONP');?></li></ul></blockquote><div id="exModalResultLoading" style="display:none"><img src="img/loader.gif"></div><div id="exModalResult" style="display:none"><hr><h3><?php _e('Current result'); ?></h3><pre class="exportresult"><code id="exModalCtn"></code></pre><div class="row"><div class="col-xs-8" style="margin-top:0"><a class="btn btn-xs btn-primary clipboardexr"><?php _e('Copy to clipboard'); ?></a></div><div class="col-xs-4 text-right"><button class="btn btn-xs btn-primary" id="exModalRefresh" data-loading-text="<?php _h('Loading...');?>" onclick="refresh_rss()"><?php _e('Refresh'); ?></button></div></div></div></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close');?></button></div></div></div></div><div class="modal fade" id="changeLogModal" tabindex="-1" role="dialog" aria-labelledby="exModalLabel" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php _e('Close');?></span></button><h4 class="modal-title"><?php _e('Change Log');?></h4></div><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close');?></button></div></div></div></div><?php if ( ! is_null( $current_user ) ) { ?><div class="modal fade" id="cpModal" tabindex="-1" role="dialog" aria-labelledby="cpModalLabel" aria-hidden="true"><div class="modal-dialog"><form id="changepassword" autocomplete="off"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php _e('Close');?></span></button><h4 class="modal-title" id="cpModalLabel"><?php _e('Change password');?></h4></div><div class="modal-body"><div class="alert alert-danger" role="alert" id="cpErr" style="display:none"><strong><?php _e('Form is invalid:'); ?></strong><ul id="cpErrUl"></ul></div><?php echo sprintf( __('You are currently connected as %s') , '<code>' . $current_user . '</code>' ); ?><br><br><div class="container"><div class="row"><div class="input-group col-sm-6 col-md-4" id="password1group"><span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span><input type="password" id="password1" name="password1" class="form-control" placeholder="<?php _h('Current password') ?>"></div><br></div><div class="row"><div class="input-group col-sm-6 col-md-4" id="password2group"><span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span><input type="password" id="password2" name="password2" class="form-control" placeholder="<?php _h('New password') ?>"></div><br></div><div class="row"><div class="input-group col-sm-6 col-md-4" id="password3group"><span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span><input type="password" id="password3" name="password3" class="form-control" placeholder="<?php _h('New password confirmation') ?>"></div></div></div></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close');?></button><input type="submit" class="btn btn-primary" data-loading-text="<?php _h('Saving...');?>" value="<?php _h('Save');?>" id="cpSave"></div></div></form></div></div><div class="modal fade" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true"><div class="modal-dialog modal-lg"><form id="prForm" autocomplete="off"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php _e('Close');?></span></button><h4 class="modal-title" id="prModalLabel"><?php _e('Profile');?></h4></div><div class="modal-body form-horizontal"><div id="prAlert"></div><div id="prBody"></div></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close');?></button><input type="submit" class="btn btn-primary" data-loading-text="<?php _h('Saving...');?>" value="<?php _h('Save');?>" id="prSave"></div></div></form></div></div><?php if ( Sentinel::isAdmin() ) { ?><div class="modal fade" id="umModal" tabindex="-1" role="dialog" aria-labelledby="umModalLabel" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content" id="usermanagement"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php _e('Close');?></span></button><ul class="nav nav-pills"><li class="active"><a href="#umUsers" role="tab" data-toggle="pill"><?php _e('Users');?></a></li><li><a href="#umAnonymous" role="tab" data-toggle="pill"><?php _e('Anonymous access');?></a></li><li><a href="#umAuthLog" role="tab" data-toggle="pill"><?php _e('History');?></a></li></ul></div><div class="tab-content"><div class="tab-pane active" id="umUsers"><div id="umUsersList"><div class="modal-body"><div id="umUsersListAlert"></div><div id="umUsersListBody"></div></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close');?></button></div></div><div id="umUsersView" style="display:none"><div class="modal-body"><div id="umUsersViewAlert"></div><div id="umUsersViewBody"></div></div><div class="modal-footer"><button type="button" class="btn btn-default" onclick="users_list()"><?php _e('« Back');?></button> <button type="button" class="btn btn-primary" onclick="users_edit(this)" id="umUserEditBtn"><?php _e('Edit');?></button></div></div><div id="umUsersAdd" style="display:none"><form id="umUsersAddForm" autocomplete="off" role="form"><div id="umUsersAddLoader"><img src="img/loader.gif"></div><div class="modal-body form-horizontal" id="umUsersAddBody"><div id="umUsersAddAlert"></div><div class="form-group" id="add-username-group"><label for="username" class="col-sm-4 control-label"><?php _e('Username'); ?></label><div class="col-sm-8"><div class="input-group"><span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span><input type="text" id="add-username" name="username" class="form-control" placeholder="<?php _h('Username') ?>"></div></div></div><div class="form-group" id="add-password-group"><label for="password" class="col-sm-4 control-label"><?php _e('Password'); ?></label><div class="col-sm-8"><div class="input-group"><span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span><input type="password" id="add-password" name="password" class="form-control" placeholder="<?php _h('Password') ?>"></div></div></div><div class="form-group" id="add-password2-group"><label for="password2" class="col-sm-4 control-label"><?php _e('Password Confirmation'); ?></label><div class="col-sm-8"><div class="input-group"><span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span><input type="password" id="add-password2" name="password2" class="form-control" placeholder="<?php _h('Password Confirmation') ?>"></div><span class="help-block" id="umUsersAddPwdHelp"><?php _e('Leave password fields blank if you don\'t want to change it'); ?></span></div></div><div class="form-group"><label for="roles" class="col-sm-4 control-label"><?php _e('Roles'); ?></label><div class="col-sm-8"><div class="btn-group" data-toggle="buttons"><label class="btn btn-primary btn-xs active roles-user" id="add-roles-user"><input type="radio" name="roles" value="user" checked="checked"><?php _e('User'); ?></label><label class="btn btn-default btn-xs roles-admin" id="add-roles-admin"><input type="radio" name="roles" value="admin"><?php _e('Admin'); ?></label></div></div></div><div class="logs-selector"><div class="form-group"><label class="col-sm-4 control-label"></label><div class="col-sm-8"><?php _e("Select which log files user can view"); ?>(<a href="#" class="logs-selector-toggler"><?php _e('Toggle all log files');?></a>)</div></div><?php foreach ( $files as $file_id => $file ) {
													$fid     = h( $file_id );
													$display = $files[ $file_id ][ 'display' ];
													$paths   = $files[ $file_id ][ 'path' ];
													$color   = 'default';

													if ( isset( $files[ $file_id ][ 'oid' ] ) ) {
														if ( $files[ $file_id ][ 'oid' ] !== $file_id ) continue;
														$display = $files[ $file_id ][ 'odisplay' ];
														if ( isset( $files[ $file_id ][ 'count' ] ) ) {
															$remain = (int)$files[ $file_id ][ 'count' ] - 1;
															if ( $remain === 1 ) {
																$paths .= ' ' . __( 'and an other file defined by glob pattern' );
															}
															else if ( $remain > 1 ) {
																$paths .= ' ' . sprintf( __( 'and %s other possible files defined by glob pattern' ) , $remain );
															}
														}
														$color = 'warning';
													}
												?><div class="form-group" data-fileid="<?php echo $fid ?>"><label for="<?php echo $fid ?>" class="col-sm-4 control-label text-<?php echo $color; ?>"><?php echo $display; ?></label><div class="col-sm-8"><div class="btn-group" data-toggle="buttons"><label class="btn btn-success btn-xs active logs-selector-yes"><input type="radio" name="f-<?php echo $fid ?>" id="add-logs-f-<?php echo $fid ?>-true" value="1" checked="checked"><?php _e('Yes'); ?></label><label class="btn btn-default btn-xs logs-selector-no"><input type="radio" name="f-<?php echo $fid ?>" id="add-logs-f-<?php echo $fid ?>-false" value="0"><?php _e('No'); ?></label></div><span class="glyphicon glyphicon-question-sign text-muted" data-toggle="tooltip" data-placement="right" data-html="true" title="<div class='hyphen'><?php echo h( $paths ); ?></div>"></span></div></div><?php } ?></div></div><div class="modal-footer"><button type="button" class="btn btn-default" onclick="users_view(this)" id="umUsersViewBtn"><?php _e('« Back');?></button> <button type="button" class="btn btn-default" onclick="users_list()" id="umUsersAddBtn"><?php _e('Cancel');?></button><input type="submit" class="btn btn-primary" data-loading-text="<?php _h('Saving...');?>" value="<?php _h('Save');?>" id="umUsersAddSave"></div><input type="hidden" name="add-type" id="add-type" value="add"></form></div></div><div class="tab-pane" id="umAnonymous"><form id="umAnonymousForm" autocomplete="off" role="form"><div class="modal-body form-horizontal"><div id="umAnonymousAlert"></div><div id="umAnonymousBody" class="logs-selector"></div></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close');?></button><input type="submit" class="btn btn-primary" data-loading-text="<?php _h('Saving...');?>" value="<?php _h('Save');?>" id="umAnonymousSave"></div></form></div><div class="tab-pane" id="umAuthLog"><div class="modal-body" id="umAuthLogBody"></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close');?></button></div></div></div></div></div></div><?php } ?><?php } ?><script src="js/pml.min.js"></script><script src="js/main.min.js"></script><script>numeral.language('<?php echo $localejs;?>');</script><?php if ( ( 'UA-XXXXX-X' != GOOGLE_ANALYTICS ) && ( '' != GOOGLE_ANALYTICS ) ) { ?><script>var _gaq=[['_setAccount','<?php echo GOOGLE_ANALYTICS;?>'],['_trackPageview']];
			(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
				g.src='//www.google-analytics.com/ga.js';
				s.parentNode.insertBefore(g,s)}(document,'script'));</script><?php } ?></body></html>