<?php
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
	$message  = '<br/>';
	$message .= __( 'Pimp my Log is not configured.');
	$message .= '<br/><br/>';
	$message .= '<span class="glyphicon glyphicon-cog"></span> ';
	$message .= sprintf( __( 'You can manually copy <code>cfg/config.example.php</code> to %s in the root directory and change parameters. Then refresh this page.' ) , '<code>' . CONFIG_FILE_NAME . '</code>' );
	$message .= '<br/><br/>';
	$message .= '<span class="glyphicon glyphicon-heart-empty"></span> ';
	$message .= __( 'Or let me try to configure it for you!' );
	$message .= '<br/><br/>';
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
list( $badges , $files ) = config_load();


/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
|
*/
$current_user = Sentinel::attempt();


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
	$link_url = '?signout';
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
	'notification_deny'       => __( 'Notifications are denied for this site. Go to your browser preferences to enable notifications for this site.' ),
	'no_log'                  => __( 'No log has been found.' ),
	'regex_valid'             => __( 'Search was done with RegEx engine' ),
	'regex_invalid'           => __( 'Search was done with regular engine' ),
	'search_no_regular'       => __( 'No log has been found with regular search %s' ),
	'search_no_regex'         => __( 'No log has been found with Reg Ex search %s' ),
	'new_logs'                => __( 'New logs are available' ),
	'new_log'                 => __( '1 new log is available' ),
	'new_nlogs'               => __( '%s new logs are available' ),
	'display_log'             => __( '1 log displayed,' ),
	'display_nlogs'           => __( '%s logs displayed,' ),
	'error'                   => __( 'An error occurs!' ),
	'toggle_column'           => __( 'Toggle column %s' ),
	'date'                    => __( 'Date' ),
	'action'                  => __( 'Action' ),
	'username'                => __( 'User name' ),
	'ip'                      => __( 'IP' ),
	'useragent'               => __( 'User agent' ),
	'signin'                  => __( 'Sign in' ),
	'signinerr'               => __( 'Sign in error' ),
	'signout'                 => __( 'Sign out' ),
	'changepwd'               => __( 'Password changed' ),
	'authlogerror'            => __( 'There is no log to display and your are connected... It seems that global parameter <code>AUTH_LOG_FILE_COUNT</code> is set to 0. Change this parameter to a higher value to display logs.' ),
	'roles'                   => __( 'Roles' ),
	'creationdate'            => __( 'Created at' ),
	'createdby'               => __( 'Created by' ),
	'logincount'              => __( 'Logins' ),
	'lastlogin'               => __( 'Last login' ),
	'system'                  => __( 'System' ),
	'user'                    => __( 'User' ),
	'adduser'                 => __( 'Add user' ),
	'deleteuser'              => __( 'Delete user' ),
	'reallydeleteuser'        => __( 'Confirm' ),
	'users'                   => __( 'Users' ),
	'user_roles'              => __( 'Roles' ),
	'user_logs'               => __( 'Log access' ),
	'user_cd'                 => __( 'Created at' ),
	'user_cb'                 => __( 'Created by' ),
	'user_logincount'         => __( 'Logins' ),
	'user_lastlogin'          => __( 'Last login' ),
	'user_add_ok'             => __( 'User has been successfully saved!' ),
	'user_delete_ok'          => __( 'User has been successfully deleted!' ),
	'form_invalid'            => __( 'Form is invalid:' ),
	'all_access'              => __( 'All accesses granted' ),
	'addadmin'                => __( 'Add admin' ),
	'adduser'                 => __( 'Add user' ),
	'deleteuser'              => __( 'Delete user' ),
	'loadmore'                => __( 'Still %s to load'),
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
?><!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width">
	<meta name="robots" content="none">
	<title><?php echo TITLE;?></title>
	<?php include_once 'inc/favicon.inc.php'; ?>
	<link rel="stylesheet" href="css/pml.min.css">
	<?php if ( file_exists( 'css/config.inc.user.css' ) ) { ?>
	<link rel="stylesheet" href="css/config.inc.user.css">
	<?php } else { ?>
	<link rel="stylesheet" href="css/config.inc.css">
	<?php } ?>
	<script>
		var logs_refresh_default = <?php echo (int)LOGS_REFRESH;?>,
			logs_max_default     = <?php echo (int)LOGS_MAX;?>,
			files                = <?php echo json_encode($files);?>,
			title_file           = <?php echo json_encode( TITLE_FILE ); ?>,
			notification_title   = <?php echo json_encode( NOTIFICATION_TITLE ); ?>,
			badges               = <?php echo json_encode( $badges ); ?>,
			lemma                = <?php echo json_encode( $lemma ); ?>,
			geoip_url            = <?php echo json_encode( GEOIP_URL ); ?>,
			pull_to_refresh      = <?php echo ( PULL_TO_REFRESH === true ) ? 'true' : 'false';?>,
			file_selector        = <?php echo json_encode( FILE_SELECTOR ); ?>,
			csrf_token           = <?php echo json_encode( $csrf ); ?>,
			querystring          = <?php echo json_encode( $_SERVER['QUERY_STRING'] ); ?>,
			currentuser          = <?php echo json_encode( Sentinel::getCurrentUsername() ); ?>,
			notification_default = <?php echo ( NOTIFICATION === true ) ? 'true' : 'false';?>;
	</script>
</head>
<body>
	<!--[if lt IE 8]>
	<p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
	<![endif]-->
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="logo" title="<?php _e('Reload the page with default parameters'); ?>"></div>
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<div class="navbar-brand">
					<span class="loader glyphicon glyphicon-refresh icon-spin" style="display:none;"></span>
					<span class="loader glyphicon glyphicon-repeat" title="<?php _e( 'Click to refresh or press the R key' );?>" id="refresh"></span>
					<a href="?"><?php echo NAV_TITLE;?></a>
				</div>
			</div>

			<div class="navbar-collapse collapse">
				<?php
				if ( FILE_SELECTOR == 'bs' ) {
				?>
					<ul class="nav navbar-nav">
						<li class="dropdown" title="<?php _e( 'Select a log file to display' );?>">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span id="file_selector"></span> <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<?php
								foreach ( $files as $file_id => $file ) {
									$selected = ( ( isset( $_GET['i'] ) ) && ( $_GET['i'] == $file_id ) ) ? ' active"' : '';
									echo '<li id="file_' . $file_id . '" data-file="' . $file_id . '" class="file_menup' . $selected . '"><a class="file_menu" href="#" title="';
									echo ( isset( $file['included_from'] ) ) ? htmlentities( sprintf( __('Log file #%s defined in %s' ) , $file_id , $file['included_from'] ) ,ENT_QUOTES,'UTF-8') : htmlentities( sprintf( __( 'Log file #%s defined in main configuration file' ) , $file_id ) ,ENT_QUOTES,'UTF-8');
									echo '">' . $file['display'] . '</a></li>';
								}
								?>
							</ul>
						</li>
					</ul>
				<?php
				} else {
				?>
	    			<form class="navbar-form navbar-left">
						<div class="form-group">
							<select id="file_selector_big" class="form-control input-sm" title="<?php _e( 'Select a log file to display' );?>">
								<?php
								foreach ( $files as $file_id=>$file ) {
									$selected = ( ( isset( $_GET['i'] ) ) && ( $_GET['i'] == $file_id ) ) ? ' selected="selected"' : '';
									echo '<option value="' . $file_id . '"' . $selected . '>' . $file['display'] . '</option>';
								}
								?>
							</select>
						</div>&nbsp;
				    </form>
				<?php
				}
				?>

				<form class="navbar-form navbar-right">

					<div class="form-group" id="searchctn">
						<input type="text" class="form-control input-sm clearable" id="search" value="<?php echo htmlentities(@$_GET['s'],ENT_QUOTES,'UTF-8');?>" placeholder="<?php _e( 'Search in logs' );?>">
					</div>&nbsp;

					<div class="form-group">
						<select id="autorefresh" class="form-control input-sm" title="<?php _e( 'Select a duration to check for new logs automatically' );?>">
							<option value="0"><?php _e( 'No refresh' );?></option>
							<?php
							foreach ( get_refresh_options( $files ) as $r ) {
								echo '<option value="' . $r . '">' . sprintf( __( 'Refresh %ss' ) , $r ) . '</option>';
							}
							?>
						</select>
					</div>&nbsp;

					<div class="form-group">
						<select id="max" class="form-control input-sm" title="<?php _e( 'Max count of logs to display' );?>">
							<?php
							foreach ( get_max_options( $files ) as $r ) {
								echo '<option value="' . $r . '">' . sprintf( ( (int)$r>1 ) ? __( '%s logs' ) : __( '%s log' ) , $r ) . '</option>';
							}
							?>
						</select>
					</div>&nbsp;

					<button style="display:none;" type="button" id="notification" class="btn-menu btn-sm" title="<?php _e( 'Desktop notifications on supported modern browsers' );?>">
					  <span class="glyphicon glyphicon-bell"></span>
					</button>

				</form>

				<ul class="nav navbar-nav navbar-right">

					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="thmenuicon glyphicon glyphicon-th"></span></a>
						<ul class="dropdown-menu thmenu" style="padding: 15px;">
							<li>
								<a href="#" class="" title="<?php _e('Displayed columns');?>"><?php _e('Displayed columns');?></a>
							</li>
						</ul>
					</li>

					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-cog"></span></a>
						<ul class="dropdown-menu cogmenu" style="padding: 15px;">

							<li>
								<a href="#" id="cog-wide" class="cog btn btn-default" data-cog="wideview" data-value="<?php echo (in_array(@$_GET['w'], array('true','on','1',''))) ? 'on' : 'off' ; ?>">
									<span class="glyphicon glyphicon-fullscreen"></span>&nbsp;&nbsp;<?php _e('Wide view');?>&nbsp;
									<span class="cogon" style="<?php echo (in_array(@$_GET['w'], array('true','on','1',''))) ? '' : 'display:none' ; ?>"><?php _e('on')?></span>
									<span class="cogoff" style="<?php echo (in_array(@$_GET['w'], array('false','off','0'))) ? '' : 'display:none' ; ?>"><?php _e('off')?></span>
								</a>
							</li>

							<li>
								<a href="#" id="clear-markers" class="btn btn-default" title="<?php _e('Click on a date field to mark a row');?>">
									<span class="glyphicon glyphicon-bookmark"></span>&nbsp;&nbsp;<?php _e('Clear markers');?>
								</a>
							</li>

							<li>
								<select id="cog-lang" class="form-control input-sm" title="<?php _e( 'Language' );?>">
									<option value=""><?php _e( 'Change language...' );?></option>

									<?php

									foreach ( $locale_available as $l => $n ) {
										echo '<option value="' . $l . '"';
										if ( $l == $locale ) echo ' selected="selected"';
										echo '>' . $n . '</option>';
									}

									?>

								</select>
							</li>

							<li>
								<select id="cog-tz" class="form-control input-sm" title="<?php _e( 'Timezone' );?>">
									<option value=""><?php _e( 'Change timezone...' );?></option>

									<?php

									foreach ( $tz_available as $n ) {
										echo '<option value="' . $n . '"';
										if ( $n == $tz ) echo ' selected="selected"';
										echo '>' . $n . '</option>';
									}

									?>
								</select>
							</li>

						</ul>
					</li>

					<?php if ( ! is_null( $current_user ) ) { ?>

						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" title="<?php echo htmlentities(  sprintf( __('You are currently connected as %s') , $current_user ) , ENT_QUOTES , 'UTF-8' ); ?>">
								<span class="glyphicon glyphicon-user"></span>
							</a>
							<ul class="dropdown-menu">
								<?php if ( Sentinel::isAdmin() ) { ?>
									<li><a href="#" title="<?php _h('Click here to manager users'); ?>" data-toggle="modal" data-target="#umModal"><span class="glyphicon glyphicon-flash"></span>&nbsp;&nbsp;<?php echo __('Manage users'); ?></a></li>
								<?php } ?>
								<li><a href="#" title="<?php _h('Click here to change your password'); ?>" data-toggle="modal" data-target="#cpModal"><span class="glyphicon glyphicon-lock"></span>&nbsp;&nbsp;<?php echo __('Change password'); ?></a></li>
								<li><a href="?signout" title="<?php _h('Click here to sign out'); ?>"><span class="glyphicon glyphicon-log-out"></span>&nbsp;&nbsp;<?php echo __('Sign out'); ?></a></li>
							</ul>
						</li>

					<?php } ?>

				</ul>
			</div>
		</div>
	</div>

	<?php if ( PULL_TO_REFRESH === true ) { ?>

		<div id="hook" class="hook">
			<div id="loader" class="hook-loader">
				<div class="hook-spinner"></div>
			</div>
			<span id="hook-text"></span>
		</div>

	<?php } ?>

	<div class="container">
		<div id="error" style="display:none;"><br/><div class="alert alert-danger fade in"><h4>Oups!</h4><p id="errortxt"></p></div></div>
		<div class="result">
			<br/>
			<div id="upgrademessages"></div>
			<div id="upgrademessage"></div>
			<div id="singlenotice"></div>
			<div id="notice"></div>
			<div id="nolog" style="display:none" class="alert alert-info fade in"></div>
		</div>
	</div>

	<div class="containerwide result tableresult">
		<div class="table-responsive">
			<table id="logs" class="table table-striped table-bordered table-hover table-condensed logs">
				<thead id="logshead"></thead>
				<tbody id="logsbody"></tbody>
			</table>
		</div>
		<div class="row">
			<div class="col-xs-8 col-xs-offset-2 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-2 col-lg-offset-5">
				<button style="width:100%" id="loadmore" type="button" class="btn btn-xs btn-primary" data-loading-text="<?php _h('Loading...');?>" data-nomore-text="<?php _h('No more data');?>"><?php _h('Load more');?></button>
			</div>
		</div>
	</div>

	<div class="container">
		<br/>
		<div class="result">
			<small id="footer"></small>
		</div>
		<hr/>
		<footer class="text-muted"><small><?php echo FOOTER;?><span id="upgradefooter"></span></small></footer>
	</div>

	<?php if ( ! is_null( $current_user ) ) { ?>

		<div class="modal fade" id="cpModal" tabindex="-1" role="dialog" aria-labelledby="cpModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<form id="changepassword" autocomplete="off">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php _e('Close');?></span></button>
							<h4 class="modal-title" id="cpModalLabel"><?php _e('Change password');?></h4>
						</div>
						<div class="modal-body">
							<div class="alert alert-danger" role="alert" id="cpErr" style="display:none;">
								<strong><?php _e('Form is invalid:'); ?></strong><ul id="cpErrUl"></ul>
							</div>

							<?php echo sprintf( __('You are currently connected as %s') , '<code>' . $current_user . '</code>' ); ?><br/><br/>

							<div class="container">
								<div class="row">
									<div class="input-group col-sm-6 col-md-4" id="password1group">
										<span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
										<input type="password" id="password1" name="password1" class="form-control" placeholder="<?php _h('Current password') ?>"/>
									</div>
									<br/>
								</div>
								<div class="row">
									<div class="input-group col-sm-6 col-md-4" id="password2group">
										<span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
										<input type="password" id="password2" name="password2" class="form-control" placeholder="<?php _h('New password') ?>"/>
									</div>
									<br/>
								</div>
								<div class="row">
									<div class="input-group col-sm-6 col-md-4" id="password3group">
										<span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
										<input type="password" id="password3" name="password3" class="form-control" placeholder="<?php _h('New password confirmation') ?>"/>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close');?></button>
							<input type="submit" class="btn btn-primary" data-loading-text="<?php _h('Saving...');?>" value="<?php _h('Save');?>" id="cpSave"/>
						</div>
					</div>
				</form>
			</div>
		</div>

		<?php if ( Sentinel::isAdmin() ) { ?>

			<div class="modal fade" id="umModal" tabindex="-1" role="dialog" aria-labelledby="umModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content" id="usermanagement">

						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php _e('Close');?></span></button>
							<ul class="nav nav-pills">
								<li class="active"><a href="#umUsers" role="tab" data-toggle="pill"><?php _e('Users');?></a></li>
								<!--<li><a href="#umLogFiles" role="tab" data-toggle="pill"><?php _e('Log files');?></a></li>-->
								<li><a href="#umAuthLog" role="tab" data-toggle="pill"><?php _e('History');?></a></li>
							</ul>
						</div>

						<div class="tab-content">
							<div class="tab-pane active" id="umUsers">
								<div id="umUsersList">
									<div class="modal-body">
										<div id="umUsersListAlert"></div>
										<div id="umUsersListBody"></div>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close');?></button>
									</div>
								</div>
								<div id="umUsersView" style="display:none;">
									<div class="modal-body">
										<div id="umUsersViewAlert"></div>
										<div id="umUsersViewBody"></div>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-default" onclick="users_list();"><?php _e('Back');?></button>
										<button type="button" class="btn btn-primary" onclick="users_edit(this);" id="umUserEditBtn"><?php _e('Edit');?></button>
									</div>
								</div>
								<div id="umUsersAdd" style="display:none;">
									<form id="umUsersAddForm" autocomplete="off" role="form">
										<div id='umUsersAddLoader'><img src="img/loader.gif"/></div>
										<div class="modal-body form-horizontal" id="umUsersAddBody">
											<div id='umUsersAddAlert'></div>
											<div class="form-group" id="add-username-group">
												<label for="username" class="col-sm-4 control-label"><?php _e('Username'); ?></label>
												<div class="col-sm-8">
													<div class="input-group">
														<span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>
														<input type="text" id="add-username" name="username" class="form-control" placeholder="<?php _h('Username') ?>"/>
													</div>
												</div>
											</div>
											<div class="form-group" id="add-password-group">
												<label for="password" class="col-sm-4 control-label"><?php _e('Password'); ?></label>
												<div class="col-sm-8">
													<div class="input-group">
														<span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
														<input type="password" id="add-password" name="password" class="form-control" placeholder="<?php _h('Password') ?>"/>
													</div>
												</div>
											</div>
											<div class="form-group" id="add-password2-group">
												<label for="password2" class="col-sm-4 control-label"><?php _e('Password Confirmation'); ?></label>
												<div class="col-sm-8">
													<div class="input-group">
														<span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
														<input type="password" id="add-password2" name="password2" class="form-control" placeholder="<?php _h('Password Confirmation') ?>"/>
													</div>
													<span class="help-block" id="umUsersAddPwdHelp"><?php _e('Leave password fields blank if you don\'t want to change it'); ?></span>
												</div>
											</div>
											<div class="form-group">
												<label for="roles" class="col-sm-4 control-label"><?php _e('Roles'); ?></label>
												<div class="col-sm-8">
													<div class="btn-group" data-toggle="buttons">
														<label class="btn btn-primary btn-xs active roles-user" id="add-roles-user">
															<input type="radio" name="roles" value="user" checked="checked" /> <?php _e('User'); ?>
														</label>
														<label class="btn btn-default btn-xs roles-admin" id="add-roles-admin">
															<input type="radio" name="roles" value="admin" /> <?php _e('Admin'); ?>
														</label>
													</div>
												</div>
											</div>
											<div class="logs-selector">
												<div class="form-group">
													<label class="col-sm-4 control-label"></label>
													<div class="col-sm-8">
														<?php _e("Select which log files user can view"); ?>
														(<a href="#" class="logs-selector-toggler"><?php _e('Toggle all log files');?></a>)
													</div>
												</div>
												<?php foreach ( $files as $file_id => $file ) {
													$fid = htmlentities( $file_id , ENT_QUOTES , 'UTF-8' );
												?>
													<div class="form-group" data-fileid="<?php echo $fid ?>">
														<label for="<?php echo $fid ?>" class="col-sm-4 control-label"><?php echo $file['display'] ?></label>
														<div class="col-sm-8">
															<div class="btn-group" data-toggle="buttons">
																<label class="btn btn-success btn-xs active logs-selector-yes">
																	<input type="radio" name="f-<?php echo $fid ?>" id="add-logs-f-<?php echo $fid ?>-true" value="1" checked="checked"/> <?php _e('Yes'); ?>
																</label>
																<label class="btn btn-default btn-xs logs-selector-no">
																	<input type="radio" name="f-<?php echo $fid ?>" id="add-logs-f-<?php echo $fid ?>-false" value="0" /> <?php _e('No'); ?>
																</label>
															</div>
														</div>
													</div>
												<?php } ?>

											</div>

										</div>

										<div class="modal-footer">
											<button type="button" class="btn btn-default" onclick="users_view(this);" id="umUsersViewBtn" ><?php _e('Back');?></button>
											<button type="button" class="btn btn-default" onclick="users_list();"     id="umUsersAddBtn" ><?php _e('Cancel');?></button>
											<input type="submit" class="btn btn-primary" data-loading-text="<?php _h('Saving...');?>" value="<?php _h('Save');?>" id="umUsersAddSave" />
										</div>
										<input type="hidden" name="add-type" id="add-type" value="add"/>
									</form>
								</div>
							</div>
							<div class="tab-pane" id="umLogFiles">
								<div class="modal-body" id="umLogFilesBody"></div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close');?></button>
									<input type="submit" class="btn btn-primary" data-loading-text="<?php _h('Saving...');?>" value="<?php _h('Save2');?>" id="umLogFilesSave"/>
								</div>
							</div>
							<div class="tab-pane" id="umAuthLog">
								<div class="modal-body" id="umAuthLogBody"></div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close');?></button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>
	<?php } ?>

	<script src="js/pml.min.js"></script>
	<script src="js/main.min.js"></script>
	<script>
	numeral.language('<?php echo $localejs;?>');
	</script>

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
