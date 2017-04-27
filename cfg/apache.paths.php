<?php
/*! pimpmylog - 1.7.14 - 025d83c29c6cf8dbb697aa966c9e9f8713ec92f1*/
/*
 * pimpmylog
 * http://pimpmylog.com
 *
 * Copyright (c) 2017 Potsky, contributors
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
	'/var/log/' ,
	'/var/log/apache/' ,
	'/var/log/apache2/' ,
	'/var/log/httpd/' ,
	'/usr/local/var/log/apache/' ,
	'/usr/local/var/log/apache2/' ,
	'/usr/local/var/log/httpd/' ,
	'/opt/local/apache/logs/' ,
	'/opt/local/apache2/logs/' ,
	'/opt/local/httpd/logs/' ,
	'C:/wamp/logs/' ,
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
	'error'  => array(
		'error.log' ,
		'error_log' ,
		'apache_error.log' ,
	) ,
	'access' => array(
		'access.log' ,
		'access_log' ,
		'apache.log' ,
		'apache_access.log' ,
	) ,
);

/**
 * Add sub-directories within specified paths
 * helps with multiple site environments
 */
foreach ( $paths as $path )
{
	if ( is_dir( $path ) )
	{
		try
		{
			$directory = new RecursiveDirectoryIterator( $path );
			$iterator  = new RecursiveIteratorIterator( $directory );
			/** @var DirectoryIterator $file */
			foreach ( $iterator as $file )
			{
				foreach ( $files as $type )
				{
					foreach ( $type as $filename )
					{
						if ( $file->getFilename() === $filename )
						{
							$paths[] = $file->getPath();
							continue 3;
						}
					}
				}
			}
		}
		catch ( Exception $e )
		{
		}
	}
}

sort( $paths );
