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
if ( realpath( __FILE__ ) === realpath( $_SERVER[ "SCRIPT_FILENAME" ] ) ) {
	header( $_SERVER[ 'SERVER_PROTOCOL' ] . ' 404 Not Found');
	die();
}

include_once 'inc/global.inc.php';
load_default_constants();
?><!DOCTYPE html><!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]--><!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"><![endif]--><!--[if IE 8]><html class="no-js lt-ie9"><![endif]--><!--[if gt IE 8]><!--><html class="no-js"><!--<![endif]--><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><meta name="description" content=""><meta name="pmltrick" content="Pimp My Log Login Match"><meta name="viewport" content="width=device-width"><title><?php echo TITLE;?></title><?php include_once 'inc/favicon.inc.php'; ?><link rel="stylesheet" href="css/pml.min.css"></head><body><!--[if lt IE 7]><p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p><![endif]--><div class="navbar navbar-inverse navbar-fixed-top"><div class="logo"></div></div><div class="jumbotron"><center><div class="container"><div class="container"><div class="row"><div class="input-group col-sm-6 col-md-4"><?php
					if ( (int)@$error === 1 ) {
						echo '<div class="alert alert-danger">';
						echo __('Your username or password is not correct');
						echo '</div>';
					}
					else if ( (int)@$error === 2 ) {
						echo '<div class="alert alert-warning">';
						echo __('Please try again...');
						echo '</div>';
					}
					else if ( (int)@$error === 3 ) {
						echo '<div class="alert alert-info">';
						echo __('You have been logged out');
						echo '</div>';
					}
					else if ( $_SERVER['SERVER_NAME'] === 'demo.pimpmylog.com' ) {
						echo '<br/>';
						echo '<div class="alert alert-info">';
						echo sprintf( __('You can use %s as the username and %s as the password to test the demo account') , '<code>demo</code>' , '<code>pimpmylog</code>' );
						echo '</div>';
						echo '<br/>';
					}
					?></div></div></div><h2><?php echo __("Please sign in");?></h2><br><form method="POST" action="?"><div class="container"><div class="row"><div class="input-group col-sm-6 col-md-4"><span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span> <?php
								$u  = ( strlen( "" . @$_POST['username'] ) > 0 ) ? htmlentities( $_POST['username'] , ENT_QUOTES , 'UTF-8' ) : '';
								if ( strlen( "" . @$_POST['username'] ) === 0 ) {
									echo '<input type="text" name="username" value="' . $u .'" class="form-control" placeholder="' . __('Username') . '" autofocus="autofocus"/>';
								} else {
									echo '<input type="text" name="username" value="' . $u .'" class="form-control" placeholder="' . __('Username') . '"/>';
								}
							?></div><br></div><div class="row"><div class="input-group col-sm-6 col-md-4"><span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span> <?php
								if ( strlen( "" . @$_POST['username'] ) === 0 ) {
									echo '<input type="password" name="password" class="form-control" placeholder="' . __('Password') . '"/>';
								} else {
									echo '<input type="password" name="password" class="form-control" placeholder="' . __('Password') . '" autofocus="autofocus"/>';
								}
							?></div></div></div><br><br><input type="submit" class="btn btn-primary" value="Pimp my Logs"><input type="hidden" name="csrf_token" value="<?php echo csrf_get(); ?>"><input type="hidden" name="attempt" value="<?php echo htmlentities( $attempt , ENT_QUOTES , 'UTF-8' ); ?>"></form><br><a href="<?php echo FORGOTTEN_YOUR_PASSWORD_URL;?>"><?php _e('Forgotten your password?') ?></a></div></center></div><div class="container"><footer class="text-muted"><small><?php echo FOOTER;?></small></footer></div><script src="js/pml.min.js"></script><script src="js/login.min.js"></script><?php if ( ( 'UA-XXXXX-X' != GOOGLE_ANALYTICS ) && ( '' != GOOGLE_ANALYTICS ) ) { ?><script>var _gaq=[['_setAccount','<?php echo GOOGLE_ANALYTICS;?>'],['_trackPageview']];
		(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
			g.src='//www.google-analytics.com/ga.js';
			s.parentNode.insertBefore(g,s)}(document,'script'));</script><?php } ?></body></html>