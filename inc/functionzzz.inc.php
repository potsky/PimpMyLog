<?php
/**
 * Simply return a localized text or empty string if the key is empty
 * Useful when localize variable which can be empty
 *
 * @param string  $text the text key
 * @return   string                      the translation
 */
function __( $text ) {
	if ( empty( $text ) )
		return '';
	else
		return gettext( $text );
}


/**
 * Simply echo a localized text
 *
 * @param string  $text the text key
 * @return   void
 */
function _e( $text ) {
	echo __( $text );
}


/**
 * The log parser
 *
 * @param string  $regex      The regex which describes the user log format
 * @param array   $match      An array which links internal tokens to regex matches
 * @param string  $log        The text log
 * @param string  $dateformat The wanted date format
 * @param string  $separator  The wanted seperator between matches when the message is composed
 *
 * @return  mixed             An array where keys are internal tokens and values the corresponding values extracted from the log file. Or false if line is not matchable.
 */
function parser( $regex , $match , $log , $dateformat='Y/m/d H:i:s' , $separator=' :: ' ) {
	$result = array();
	preg_match_all( $regex , $log , $out, PREG_PATTERN_ORDER );
	if ( @count( $out[0] )==0 ) {
		return false;
	}
	foreach ( $match as $token => $key ) {
		if ( $token == 'Date' ) {
			if ( is_array( $key ) ) {
				$newdate = array();
				foreach ( $key as $k => $v ) {
					$newdate[$k] = @$out[ $v ][0];
				}
				$str = $newdate['M'] . ' ' . $newdate['D'] . ' ' . $newdate['H'] . ':' . $newdate['I'] . ':' . $newdate['S'] . ' ' . $newdate['Y'];
			}
			else {
				$str = @$out[ $key ][0];
			}
			if ( ( $timestamp = strtotime( $str ) ) === false ) {
				$date = "ERROR ! Unable to convert this string to date : <code>$str</code>";
			} else {
				$date = date( $dateformat , $timestamp );
			}
			$result[ $token ] = $date;
		}
		else if ( is_array( $key ) ) {
			$r = array();
			foreach ( $key as $k )
				$r[] = @$out[ $k ][0];
			$result[ $token ] = implode( $separator , $r );
		}
		else {
			$result[ $token ] = @$out[ $key ][0];
		}
	}
	return $result;
}


function human_filesize( $bytes, $decimals = 0 ) {
	$sz = __( 'B KBMBGBTBPB' );
	$factor = floor( ( strlen( $bytes ) - 1 ) / 3 );
	return sprintf( "%.{$decimals}f", $bytes / pow( 1024, $factor ) ) . @$sz[$factor*2];
}
