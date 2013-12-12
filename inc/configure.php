<?php
include_once 'global.inc.php';
init();

/////////////////////////////////
// Ajax tasks required by body //
/////////////////////////////////
if ( isset( $_POST['s'] ) ) {
	$return = array();
	try {

	} catch (Exception $e) {

	}

	header( 'Content-type: application/json' );
	echo json_encode( $return , true );
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
	<title></title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width">
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
				<a class="navbar-brand" href="#">Configurator</a>
			</div>
		</div>
	</div>

	<div class="container">
		<br/>
		<hr>
		<footer class="text-muted"><small><?php echo FOOTER;?></small></footer>
	</div>
	<script src="../js/vendor/jquery-1.10.1.min.js"></script>
	<script src="../js/vendor/bootstrap.min.js"></script>
	<script src="../js/test.js"></script>
</body>
</html>
