<?php

$paths = array(
	'/var/log/',
	'/var/log/apache/',
	'/var/log/apache2/',
	'/var/log/httpd/',
	'/usr/local/var/log/apache/',
	'/usr/local/var/log/apache2/',
	'/usr/local/var/log/httpd/',
	'/opt/local/apache/logs/',
	'/opt/local/apache2/logs/',
	'/opt/local/httpd/logs/',
	'C:/wamp/logs/',
);

$files = array(
	'error' => array(
		'error.log',
		'error_log',
		'apache_error.log',
	),
	'access' => array(
		'access.log',
		'access_log',
		'apache.log',
		'apache_access.log',
	),
);
