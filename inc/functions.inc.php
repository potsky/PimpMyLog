<?php
/*
 * Simply return a localized text or empty string if the key is empty
 * Useful when localize variable which can be empty
 *
 * @param    string       $text          the text key
 * @return   string                      the translation
 */
function __( $text ) {
	if ( empty( $text ) )
		return '';
	else
		return gettext( $text );
}


/*
 * Simply echo a localized text
 *
 * @param    string       $text          the text key
 * @return   void
 */
function _e( $text ) {
	echo __( $text );
}
