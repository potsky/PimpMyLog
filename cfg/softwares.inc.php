<?php
$softwares_all = array(
	'apache' => array(
		'name'    => __('Apache'),
		'desc'    => __('Apache Hypertext Transfer Protocol Server'),
		'home'    => __('http://httpd.apache.org'),
		'notes'   => __('All versions 2.x are supported.'),
		'default' => true,
	),
);


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
*/

if ( file_exists( 'software.user.inc.php' ) ) {
	include_once 'software.user.inc.php';
}
