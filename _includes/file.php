<?php
	$title    = __( 'Welcome!' );
	$message  = '<br/>';
	$message .= __( 'Pimp my Log is not configured.');
	$message .= '<br/><br/>';
	$message .= '<span class="glyphicon glyphicon-cog"></span> ';
	$message .= __( 'You can manually copy <code>cfg/config.example.inc.php</code> to <code>config.inc.php</code> in the root directory and change parameters. Then refresh this page.' );
	$message .= '<br/><br/>';
	$message .= '<span class="glyphicon glyphicon-heart-empty"></span> ';
	$message .= __( 'Or let me try to configure it for you!' );
	$message .= '<br/><br/>';
	$link_url = 'inc/configure.php';
	$link_msg = __('Configure now');
	include_once 'inc/error.inc.php';
	die();
?>
