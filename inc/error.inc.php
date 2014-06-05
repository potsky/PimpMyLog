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
if ( realpath( __FILE__ ) === realpath( $_SERVER[ "SCRIPT_FILENAME" ] ) ) {
	header( $_SERVER[ 'SERVER_PROTOCOL' ] . ' 404 Not Found');
	die();
}
include_once 'inc/global.inc.php';
init();
?><!DOCTYPE html><!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]--><!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"><![endif]--><!--[if IE 8]><html class="no-js lt-ie9"><![endif]--><!--[if gt IE 8]><!--><html class="no-js"><!--<![endif]--><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><meta name="description" content=""><meta name="viewport" content="width=device-width"><title><?php echo TITLE;?></title><?php include_once 'inc/favicon.inc.php'; ?><?php
?><?php
?><link rel="stylesheet" href="css/pml.min.css"><?php
?></head><body><!--[if lt IE 7]><p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p><![endif]--><div class="navbar navbar-inverse navbar-fixed-top"><div class="logo"></div></div><div class="jumbotron"><div class="container"><h1><?php echo $title;?></h1><p><?php echo $message;?></p><?php if ( '' != @$link_url ) { ?><p><a class="btn btn-primary btn-lg" href="<?php echo $link_url;?>"><?php echo $link_msg;?>&nbsp;&raquo;</a></p><?php } ?></div></div><div class="container"><footer class="text-muted"><small><?php echo FOOTER;?></small></footer></div><?php
?><?php
?><script src="js/pml.min.js"></script><?php
?></body></html>