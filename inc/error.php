<?php
include_once 'inc/global.inc.php';
?>
<!DOCTYPE html>
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
	<script src="js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
</head>
<body>
	<!--[if lt IE 7]>
	<p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
	<![endif]-->
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="logo"></div>
	</div>

	<div class="jumbotron">
		<div class="container">
			<h1><?php echo $title;?></h1>
			<p><?php echo $message;?></p>
<?php if ( '' != @$link_url ) { ?>
			<p><a class="btn btn-primary btn-lg" href="<?php echo $link_url;?>"><?php echo $link_msg;?> &raquo;</a></p>
<?php } ?>
		</div>
	</div>

	<div class="container">
		<footer><small>&copy; <a href="http://www.potsky.com" target="doc">Potsky</a> 2007-<?php echo date('Y'); ?> - <a href="<?php echo HELP_URL; ?>" target="doc">Pimp my Log</a></small></footer>
	</div>

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
	<script>window.jQuery || document.write('<script src="js/vendor/jquery-1.10.1.min.js"><\/script>')</script>
	<script src="js/vendor/bootstrap.min.js"></script>
</body>
</html>
