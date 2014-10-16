<?php


define( 'TESTBASE'  , realpath( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' ) );
define( 'PML_BASE'  , realpath( TESTBASE . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . PSKBASE ) );
define( 'PHPMOCKUP' , realpath( TESTBASE . '/phpmockup' ) );

echo '--> Working in ' . PSKBASE . " <--\n";

include_once( 'TestCase.php' );

include_once( PML_BASE . '/inc/global.inc.php');
load_default_constants();
