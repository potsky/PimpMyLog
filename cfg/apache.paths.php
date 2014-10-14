<?php
/*! pimpmylog - 1.3 - 820fbed3ca50fcf8ffb327f7cda4f88987488a9e*/
/*
 * pimpmylog
 * http://pimpmylog.com
 *
 * Copyright (c) 2014 Potsky, contributors
 * Licensed under the GPLv3 license.
 */
?>
<?php

/**
 * All possible paths where log files could be found
 *
 * @var  array
 */
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


/**
 * All possibles files for each type of log
 *
 * All files will be tried in all possibles paths above
 *
 * The order is important because it will be the order of log files for users.
 * eg: I want error log be the first because most users want to see error and not access logs
 *
 * @var  array
 */
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
