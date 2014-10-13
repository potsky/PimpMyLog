<?php
include_once 'global.inc.php';
config_load( '../config.user.json' );
init();

header('Content-type: application/json');
/*
if 	( ! csrf_verify() ) {
	$logs['error'] = __( 'Please refresh the page.' );
	echo json_encode( $logs );
	die();
}
*/
$upgrade = array(
	'footer'     => '',
	'alert'      => '',
	'current'    => '',
	'to'         => '',
	'messages'   => '',
	'messagesto' => '',
);



if ( file_exists( '../version.js' ) ) {
	$JSl_version        = json_decode( clean_json_version( @file_get_contents( '../version.js' ) ) , true );
	$upgrade['current'] = $JSl_version[ 'version' ];
	$default            = sprintf ( __( 'Current version %s' ) , $upgrade['current'] );
	$upgrade['footer']  = $default;
}

else {
	$upgrade['footer'] = '<span class="text-danger">' . __( 'Unable to check your current version!') . '</span>';;
	echo json_encode( $upgrade );
	die();
}

if ( false === CHECK_UPGRADE ) {
	echo json_encode( $upgrade );
	die();
}

/*
|--------------------------------------------------------------------------
| Retrieve remote server upgrade informations
|--------------------------------------------------------------------------
|
*/
try {
	$ctx         = stream_context_create( array( 'http' => array( 'timeout' => 5 ) ) );
	$JSr_version = json_decode( clean_json_version( @file_get_contents( PIMPMYLOG_VERSION_URL . '?' . date("U") , false , $ctx ) ), true );
	if ( is_null( $JSr_version ) ) {
		throw new Exception( 'Unable to fetch remote version' , 1);
	}

	/*
	|--------------------------------------------------------------------------
	| Manage messaging system
	|--------------------------------------------------------------------------
	|
	| We can send a message to all pml users to give them important informations
	| about security features, etc...
	|
	*/
	$local_messages  = @$JSl_version['messages'];
	$remote_messages = @$JSr_version['messages'];

	if ( ! is_array( $local_messages ) ) $local_messages = array();

	if ( is_array( $remote_messages ) ) {

		$local_messages_version  = 0;
		$remote_messages_version = 0;
		foreach ( $local_messages  as $local_messages_version  => $m ) break;
		foreach ( $remote_messages as $remote_messages_version => $m ) break;

		$new_messages          = array();
		$max_messages          = 3;
		$upgrade['messagesto'] = $remote_messages_version;

		// New messages are available,
		//
		foreach ( $remote_messages as $remote_messages_version => $message ) {
			if ( ( (int)$local_messages_version >= (int)$remote_messages_version ) || ( $max_messages === 0 ) ) break;
			$new_messages[ $remote_messages_version ] = $message;
			$max_messages--;
		}

		$message = '<div id="messagedalert" class="alert alert-info alert-dismissable">';
		$message .= '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
		$message .= '<strong>' . __( 'Dev Team Message') . '</strong><br/>';
		foreach ( $new_messages as $date => $content ) {
			$message .= '<div class="row">';
			$message .= '<div class="col-md-2">';
			$message .= $date;
			$message .= '</div>';
			$message .= '<div class="col-md-10">';
			$message .= $content;
			$message .= '</div>';
			$message .= '</div>';
		}
		$message .= '<a href="#" id="messagesstop" data-version="' . $remote_messages_version . '" class="alert-link">' . __("Mark this message as read") . '</a>';
		$message .= '</div>';

		$upgrade['messages'] = $message;

	}

	/*
	|--------------------------------------------------------------------------
	| Manage upgrade now
	|--------------------------------------------------------------------------
	|
	*/
	$upgrade['to'] = $JSr_version[ 'version' ];

	if ( version_compare( $upgrade['current'] , $upgrade['to'] ) < 0 ) {

		$notices = array();
		$html    = '<ul>';

		if ( ! isset( $JSr_version[ 'changelog' ] ) ) {
			$upgrade['footer'] = $default . ' - <a href="'. PIMPMYLOG_VERSION_URL . '" target="check"><span class="text-danger" title="' . sprintf( __( 'Error while fetching URL %s from the server hosting this Pimp my Log instance.' ) , PIMPMYLOG_VERSION_URL ) . '">' . __( 'Remote version broken!') . '</span></a>';
			echo json_encode( $upgrade );
			die();
		}

		if ( ! is_array( $JSr_version[ 'changelog' ] ) ) {
			$upgrade['footer'] = $default . ' - <a href="'. PIMPMYLOG_VERSION_URL . '" target="check"><span class="text-danger" title="' . sprintf( __( 'Error while fetching URL %s from the server hosting this Pimp my Log instance.' ) , PIMPMYLOG_VERSION_URL ) . '">' . __( 'Remote version broken!') . '</span></a>';
			echo json_encode( $upgrade );
			die();
		}

		foreach ( $JSr_version[ 'changelog' ] as $version => $version_details ) {

			if ( version_compare( $upgrade['current'] , $version ) >= 0 ) {
				break;
			}

			if ( ! is_array( $version_details ) ) {
				continue;
			}

			if ( isset( $version_details['notice'] ) ) {
				$notices[ $version ] = $version_details['notice'];
			}

			$html .= '<li>';
			$html .= ( isset( $version_details['released'] ) )
				? sprintf( __( 'Version %s released on %s' ) , '<em>' . $version . '</em>' , '<em>' . $version_details['released'] . '</em>' )
				: sprintf( __( 'Version %s' ) , '<em>' . $version . '</em>' ) ;
			$html .= '<ul>';

			foreach ( array( 'new' => 'New' , 'changed' => 'Changed' , 'fixed' => 'Fixed' ) as $type => $type_display ) {

				if ( isset( $version_details[ $type ] ) ) {

					if ( is_array( $version_details[ $type ] ) ) {

						$html .= '<li>' . $type_display;
						$html .= '<ul>';

						foreach ( $version_details[ $type ] as $issue ) {
							$html .= '<li>' . preg_replace( '/#([0-9]*)/i' , '<a href="' . PIMPMYLOG_ISSUE_LINK . '$1">#$1</a>' , $issue) . '</li>';
						}

						$html .= '</ul>';
						$html .= '</li>';

					}

				}

			}

			$html .= '</ul>';
			$html .= '</li>';

		}

		$html .= '</ul>';

		$severity = ( count( $notices ) > 0 ) ? 'danger' : 'info';

		$upgrade['alert'] .= '<div id="upgradealert" class="alert alert-' . $severity . ' alert-dismissable">';
		$upgrade['alert'] .= '  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
		$upgrade['alert'] .= '<strong>' . __( 'An upgrade is available !') . '</strong> ';
		$upgrade['alert'] .= sprintf( __( 'You have version %s and version %s is available' ) , '<em>' . $upgrade['current'] . '</em>' , '<em>' . $upgrade['to'] . '</em>');
		$upgrade['alert'] .= ' (<a href="#" class="alert-link" data-toggle="collapse" data-target="#changelog">' . __( 'release notes') . '</a>)';
		$upgrade['alert'] .= '<br/>';
		$upgrade['alert'] .= __('Simply <code>git pull</code> in your directory or <a href="http://pimpmylog.com/getting-started/#update" target="doc">follow instructions here</a>');
		$upgrade['alert'] .= '<br/>';
		$upgrade['alert'] .= '<br/>';
		$upgrade['alert'] .= '<a href="#" id="upgradestop" data-version="' . $upgrade['to'] . '" class="alert-link">' . __("Don't bother me again with this upgrade!") . '</a>';
		$upgrade['alert'] .= '<div id="changelog" class="panel-collapse collapse"><div class="panel-body panel panel-default">' . $html . '</div></div>';

		if ( count( $notices ) > 0 ) {

			$upgrade['alert'] .= '<hr/>';
			$upgrade['alert'] .= '<strong>' . __( 'You should upgrade right now:') . '</strong><ul>';

			foreach ( $notices as $version => $notice ) {
				$upgrade['alert'] .=  '<li><em>' . $version . '</em> : ' . $notice . '</li>';
			}

			$upgrade['alert'] .= '</ul>';

		}

		$upgrade['alert'] .= '</div>';
		$upgrade['footer'] = '<span class="text-warning">' . sprintf ( __( 'Your version %s is out of date' ) , $upgrade['current'] ) . '</span>';

	}

	else {
		$upgrade['footer'] = sprintf ( __( 'Your version %s is up to date' ) , $upgrade['current'] );
	}

}

catch ( Exception $e ) {
	$upgrade['footer'] = $default . ' - <a href="'. PIMPMYLOG_VERSION_URL . '" target="check"><span class="text-danger" title="' . sprintf( __( 'Unable to fetch URL %s from the server hosting this Pimp my Log instance.' ) , PIMPMYLOG_VERSION_URL ) . '">' . __( 'Unable to check remote version!') . '</span></a>';
	echo json_encode( $upgrade );
	die();
}

echo json_encode( $upgrade );
die();

?>
