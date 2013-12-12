<?php
include_once 'global.inc.php';
init();

/////////////////////////////////
// Ajax tasks required by body //
/////////////////////////////////
if ( isset( $_POST['s'] ) ) {

	$return = array(
		'reload' => false,
		'next'   => false,
	);

	try {

		switch ( $_POST['s'] ) {

			case 'exist':
				if ( file_exists( '../config.inc.php' ) ) {
					throw new Exception( __( 'File <code>config.inc.php</code> already exists. Please remove it if you want me to create it') );
				}
				break;

			case 'touch':
				if ( ! @touch( '../config.inc.php.tmp' ) ) {
					$return[ 'notice' ] =
						__( 'Unable to create file <code>config.inc.php.tmp</code> at root.')
						. '<br/><br/>'
						. __( 'Please give temporary write access to the root directory :' )
						. '<pre>'
						. 'chmod 777 ' . dirname( dirname( __FILE__ ) )
						. '</pre>';
					$return[ 'reload' ] = true;
				}
				break;

			case 'find':
				$softwares = array( 'apache' );
				$softuser  = array();
				$tried     = array();
				$found     = 0;
				foreach ( $softwares as $software ) {
					$software_paths = '../cfg/' . $software . '.paths.php';
					if ( file_exists( $software_paths ) ) {
						include_once $software_paths;
						$softuser[ $software ] = array();
						foreach ( $paths as $path ) {
							$tried[ $software ][ $path ] = false;
							if ( is_dir( $path ) ) {
								$found = 1;
								$tried[ $software ][ $path ] = true;
								foreach ( $files as $type => $fpaths) {
									$softuser[ $software ][ $type ] = 1;
									foreach ( $fpaths as $file ) {
										if ( is_readable( $path . $file ) ) {
											if ( ! is_array( $tried[ $software ][ $path ] ) )
												$tried[ $software ][ $path ] = array();
											$tried[ $software ][ $path ][ $type ][] = $file;
											$found = 2;
										}
									}
								}
							}
						}
					}
				}
				$return[ 'files' ] = $tried;
				$return[ 'found' ] = $found;

				if ( $found == 0 ) {
					$return[ 'notice' ] = '<div class="alert alert-danger">' . __( 'Unable to find any directory.') . '</div>';
					$return[ 'notice' ] = __( 'Please check in the following list if these directories are readable by the webserver user and refresh this page' );
				}
				else if ( $found == 1 ) {
					$return[ 'notice' ] = '<div class="alert alert-warning">' . __( 'Directories are available but unable to find files inside.') . '</div>';
					$return[ 'notice' ] = __( 'Please check in the following list if these directories contain readable files by the webserver user and refresh this page' );
				}
				else {
					$return[ 'notice' ] = '<div class="alert alert-success">' . __( 'Log files have been found!') . '</div>';
					$return[ 'notice' ].= __( 'Please check in the following list files you want to configure. If files or directories are missing, please verify if there are readable by the webserver user' );
				}

				$user = get_server_user();
				$return[ 'notice' ] .= ( $user == '' )
					? ' (<em>' . __( 'unable to detect web server user') . '</em>) :'
					: ' (<em>' . sprintf( __( 'web server user seems to be <code>%s</code>') , $user ) . '</em>) :';

				$return[ 'notice' ] .= '<br/><br/>';
				$return[ 'notice' ] .= '<div class="table-responsive"><table id="find"></table></div><hr/>';
				$return[ 'notice' ] .= __('You can also enter log files path in the text area below separated by coma :');
				$return[ 'notice' ] .= '<br/><br/>';
				$return[ 'notice' ] .= '<div class="table-responsive"><table class="table table-striped table-bordered table-hover table-condensed"><thead><tr><th>' . __( 'Software' ) . '</th><th>' . __( 'Type' ) . '</th><th>' . __( 'Custom paths' ) . '</th></tr></thead><tbody>';
				foreach( $softuser as $software => $types ) {
					foreach ( $types as $type => $dumb ) {
						$return[ 'notice' ] .= '<tr><td>' . $software . ' </td><td>' . $type . ' </td><td><textarea data-soft="' . $software . '" data-type="' . $type . '" class="form-control" rows="2"></textarea></td></tr>';
					}
				}
				$return[ 'notice' ] .= '</tbody></table></div><hr/>';

				$return[ 'next' ]   = true;
				$return[ 'reload' ] = true;
				break;

			default:
				throw new Exception( __( 'Unknown action, abort.' ) );
				break;
		}

	} catch (Exception $e) {
		$return['error'] = $e->getMessage();
	}

	header( 'Content-type: application/json' );
	echo json_encode( $return );
	die();
}



//////////////////////
// Javascript Lemma //
//////////////////////
$lemma = array(
	'complete'   => __( '%s% Complete' ),
	'error'      => __( 'An error occurs!' ),
	'pleasewait' => __( 'Please wait...' ),
	'software'   => __( 'Software' ),
	'path'       => __( 'Path' ),
	'file'       => __( 'File' ),
	'readable'   => __( 'Readable' ),
	'type'       => __( 'Type' ),
	'no'         => __( 'No' ),
	'yes'        => __( 'Yes' ),
);


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
	<script>
		var lemma = <?php echo json_encode($lemma);?>;
	</script>
	</head>
<body>
	<!--[if lt IE 7]>
	<p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
	<![endif]-->
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="logo"></div>
			<div class="navbar-header">
				<a class="navbar-brand" href="#">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo __('Configurator');?></a>
			</div>
		</div>
	</div>

	<div class="container">
		<br/>

		<div class="progress">
			<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
				<span class="sr-only"></span>
			</div>
		</div>
		<div id="error"></div>
		<div id="user"></div>
		<br/>
		<p>
			<a id="reload" class="btn btn-primary" href="javascript:location.reload();" style="display:none;"><?php _e('Reload');?></a>
			<a id="next" class="btn btn-success" href="#" style="display:none;"><?php _e('Continue');?></a>
		</p>
		<hr>
		<footer class="text-muted"><small><?php echo FOOTER;?></small></footer>
	</div>
	<script src="../js/vendor/jquery-1.10.1.min.js"></script>
	<script src="../js/vendor/bootstrap.min.js"></script>
	<script src="../js/configure.js"></script>
</body>
</html>
