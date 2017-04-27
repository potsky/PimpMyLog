<?php
/*! pimpmylog - 1.7.14 - 025d83c29c6cf8dbb697aa966c9e9f8713ec92f1*/
/*
 * pimpmylog
 * http://pimpmylog.com
 *
 * Copyright (c) 2017 Potsky, contributors
 * Licensed under the GPLv3 license.
 */
?><?php
include_once 'global.inc.php';


/*
|--------------------------------------------------------------------------
| Get parameters
|--------------------------------------------------------------------------
|
*/
if ( ! isset( $_GET[ 'f' ] ) )
{
	http404();
}
$file_id = $_GET[ 'f' ];


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
|
*/
$user = null;

if ( ( isset( $_GET[ 't' ] ) ) && ( isset( $_GET[ 'h' ] ) ) )
{

	if ( Sentinel::isAuthSet() )
	{ // perhaps auth has been deactivated since link generation
		$accesstoken = $_GET[ 't' ];
		$username    = Sentinel::getUsernameFromAccessToken( $accesstoken );

		if ( is_null( $username ) )
		{ // User does not exist anymore
			http404();
		}

		// Sign in user
		$user = Sentinel::signInWithAccessToken( $accesstoken );

		// Check the security hash
		if ( ! Sentinel::isSignValid( $_GET[ 'h' ] , array( 'f' => $file_id ) , $username ) )
		{
			http403();
		}
	}

}
else if ( ( ! isset( $_GET[ 't' ] ) ) && ( isset( $_GET[ 'h' ] ) ) )
{
	http404();
}
else if ( ( isset( $_GET[ 't' ] ) ) && ( ! isset( $_GET[ 'h' ] ) ) )
{
	http404();
}


/*
|--------------------------------------------------------------------------
| Load config
|--------------------------------------------------------------------------
|
*/
list( $badges , $files , $tz ) = config_load();

if ( ! isset( $files[ $file_id ] ) )
{
	http403();
}

if ( ( isset( $files[ $file_id ][ 'export' ] ) ) && ( $files[ $file_id ][ 'export' ] === false ) )
{
	http403();
}

if ( ( EXPORT === false ) && ( ! isset( $files[ $file_id ][ 'export' ] ) ) )
{
	http403();
}


/*
|--------------------------------------------------------------------------
| Get logs
|--------------------------------------------------------------------------
|
*/
$search  = ( isset( $_GET[ 'search' ] ) ) ? $_GET[ 'search' ] : '';
$format  = ( isset( $_GET[ 'format' ] ) ) ? $_GET[ 'format' ] : 'JSON';
$count   = ( isset( $_GET[ 'count' ] ) ) ? $_GET[ 'count' ] : ( ( isset( $files[ $file_id ][ 'max' ] ) ) ? $files[ $file_id ][ 'max' ] : LOGS_MAX );
$timeout = ( isset( $_GET[ 'timeout' ] ) ) ? $_GET[ 'timeout' ] : MAX_SEARCH_LOG_TIME;

$regex         = $files[ $file_id ][ 'format' ][ 'regex' ];
$match         = $files[ $file_id ][ 'format' ][ 'match' ];
$types         = $files[ $file_id ][ 'format' ][ 'types' ];
$multiline     = ( isset( $files[ $file_id ][ 'format' ][ 'multiline' ] ) ) ? $files[ $file_id ][ 'format' ][ 'multiline' ] : '';
$exclude       = ( isset( $files[ $file_id ][ 'format' ][ 'exclude' ] ) ) ? $files[ $file_id ][ 'format' ][ 'exclude' ] : array();
$title         = ( isset( $files[ $file_id ][ 'format' ][ 'export_title' ] ) ) ? $files[ $file_id ][ 'format' ][ 'export_title' ] : '';
$file_path     = $files[ $file_id ][ 'path' ];
$start_offset  = 0;
$start_from    = SEEK_END;
$load_more     = false;
$old_lastline  = '';
$data_to_parse = filesize( $file_path );
$full          = true;
$logs          = LogParser::getNewLines( $regex , $match , $types , $tz , $count , $exclude , $file_path , $start_offset , $start_from , $load_more , $old_lastline , $multiline , $search , $data_to_parse , $full , $timeout );

/*
|--------------------------------------------------------------------------
| Error while getting logs
|--------------------------------------------------------------------------
|
*/
if ( ! is_array( $logs ) )
{
	http500();
}

/*
|--------------------------------------------------------------------------
| Return
|--------------------------------------------------------------------------
|
*/
$link = str_replace( 'inc/rss.php' , '' , get_current_url( true ) );

header( "Pragma: public" );
header( "Expires: 0" );
header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );

switch ( $format )
{

	case 'ATOM':
	case 'RSS':
		require( 'classes/Feedcreator.php' );
		define( 'TIME_ZONE' , $tz );
		define( 'FEEDCREATOR_VERSION' , 'Pimp My Log v' . get_current_pml_version() );
		$rss                              = new UniversalFeedCreator();
		$rss->title                       = sprintf( __( "Pimp My Log : %s" ) , $files[ $file_id ][ 'display' ] );
		$rss->description                 = ( empty( $search ) )
			? sprintf( __( "Pimp logs for file %s" ) , $files[ $file_id ][ 'path' ] )
			: sprintf( __( "Pimp logs for file %s with search %s" ) , $files[ $file_id ][ 'path' ] , $search );
		$rss->descriptionTruncSize        = 500;
		$rss->descriptionHtmlSyndicated   = true;
		$rss->link                        = $link;
		$rss->syndicationURL              = get_current_url( true );
		$image                            = new FeedImage();
		$image->title                     = $rss->title;
		$image->url                       = str_replace( 'inc/rss.php' , 'img/icon72.png' , get_current_url() );
		$image->link                      = $link;
		$image->description               = __( "Feed provided by Pimp My Log" );
		$image->descriptionTruncSize      = 500;
		$image->descriptionHtmlSyndicated = true;
		$rss->image                       = $image;
		if ( ( isset( $logs[ 'logs' ] ) ) && ( is_array( $logs[ 'logs' ] ) ) )
		{
			foreach ( array_reverse( $logs[ 'logs' ] ) as $log )
			{
				$item        = new FeedItem();
				$description = '';
				foreach ( $log as $key => $value )
				{
					if ( substr( $key , 0 , 3 ) !== 'pml' )
					{
						$description .= '<strong>' . h( $key ) . '</strong> : ' . h( $value ) . '<br/>';
					}
				}
				$item->description = $description;
				if ( isset( $log[ 'pmld' ] ) )
				{
					$item->date = $log[ 'pmld' ];
				}
				if ( isset( $log[ $title ] ) )
				{
					$item->title = $log[ $title ];
				}
				else
				{
					$item->title = current( $log ) . ' - ' . sha1( serialize( $log ) );
				}
				if ( $format === 'ATOM' )
				{
					$item->author = 'PmL';
				}
				$item->link                      = $link . '&' . $log[ 'pmlo' ];
				$item->guid                      = $link . '&' . $log[ 'pmlo' ];
				$item->descriptionTruncSize      = 500;
				$item->descriptionHtmlSyndicated = true;
				$rss->addItem( $item );
			}
		}
		$rss->outputFeed( $tz , $format );
		break;

	case 'CSV':
		header( "Content-Transfer-Encoding: binary" );
		header( "Content-Disposition: attachment;filename=PimpMyLog_" . get_slug( $file_id ) . "_" . date( "Y-m-d-His" ) . '.csv' );
		header( "Content-type: application/vnd.ms-excel; charset=UTF-16LE" );
		echo chr( 255 ) . chr( 254 );
		if ( ( isset( $logs[ 'logs' ] ) ) && ( is_array( $logs[ 'logs' ] ) ) )
		{
			echo mb_convert_encoding( array2csv( $logs[ 'logs' ] ) , 'UTF-16LE' , 'UTF-8' );
		}
		break;

	case 'XML':
		header( 'Content-type: application/xml' , true );
		$xml = '<?xml version="1.0" encoding="UTF-8" ?>'; $xml .= '<pml>'; $xml .= generate_xml_from_array( $logs , 'log' ); $xml .= '</pml>'; echo $xml; break; case 'JSONPR': header( 'Content-type: application/json' , true ); if ( version_compare( PHP_VERSION , '5.4.0' ) >= 0 ) { echo json_encode( $logs , JSON_PRETTY_PRINT ); } else { echo json_indent( json_encode( $logs ) ); } break; case 'JSONP': header( 'Content-type: application/javascript' , true ); echo ( isset( $_GET[ 'callback' ] ) ) ? $_GET[ 'callback' ] : '?'; echo '('; echo json_encode( $logs ); echo ')'; break; case 'JSON': default: header( 'Content-type: application/json' , true ); echo json_encode( $logs ); break; } ?>