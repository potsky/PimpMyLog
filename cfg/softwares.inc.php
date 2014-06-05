<?php
/*! pimpmylog - 1.0.5 - 304e44fae52b81256e7624dbca2a9cb3d005808e*/
/*
 * pimpmylog
 * http://pimpmylog.com
 *
 * Copyright (c) 2014 Potsky, contributors
 * Licensed under the GPLv3 license.
 */
?>
<?php
$softwares_list = array( 'apache' , 'iis' , 'nginx' , 'php' );
$softwares_all  = array();

foreach ( $softwares_list as $sfw ) {
	$config_file = '../cfg/' . $sfw . '.config.php';
	include_once( $config_file );
	$loader = $sfw . '_load_software';
	if ( function_exists( $loader ) ) {
		$cfg = call_user_func( $loader );
		if ( is_array( $cfg ) ) {
			$softwares_all[ $sfw ] = $cfg;
		}
	}
}


/*
You can add your own softwares in file software.user.inc.php, it will not be erased on update (git pull).
Just add a new software like this :

$softwares_all[ 'my_software' ] = array(
	'name' => __('My Super Software'),
	'desc' => __('My Super Software build with love for you users which are installing Pimp my Log !'),
	'home' => __('http://www.example.com'),
	'note' => __('All versions 2.x are supported but 1.x too in fact.'),
);

Just modify an existing software like this :
$softwares_all[ 'apache' ][ 'name' ] = 'Apache HTTPD';

You have to add these files too :
- my_software.config.user.php (which defines function my_software_get_config)
- my_software.paths.user.php
*/
if ( file_exists( '../cfg/softwares.inc.user.php' ) ) {
	include_once '../cfg/softwares.inc.user.php';
}
