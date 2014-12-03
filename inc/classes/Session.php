<?php

class Session
{
    const SESSION_STARTED        = true;
    const SESSION_NOT_STARTED    = false;
    private static $sessionState = self::SESSION_NOT_STARTED;


    /**
     * Overwrite the original session start to specify a path at root of the installation
     *
     * If you have several instances of PML on the same server with different paths,
     * authentication has to be managed distinctly
     *
     * @param   string   $path      the path of the cookie
     * @param   integer  $lifetime  the lifetime of the cookie
     *
     * @return bool
     */
    public static function start( $path = '' , $lifetime = 0 ) {

        if ( self::$sessionState === self::SESSION_NOT_STARTED ) {

            if ( empty( $path ) ) {
                $sub  = array( 'inc' );
                $url  = parse_url( get_current_url() );
                // we add a string on bottom to manage urls like these
                // - http://niania/blahblah/pimpmylog/
                // - http://niania/blahblah/pimpmylog/index.php
                // So they become
                // - http://niania/blahblah/pimpmylog/fake
                // - http://niania/blahblah/pimpmylog/index.phpfake
                $path = dirname( $url[ 'path' ] . 'fake' );
                // Now remove all identified subfolders
                if ( in_array( basename( $path ) , $sub ) ) {
                    $path = dirname( $path );
                }
            }

            session_set_cookie_params( $lifetime , $path );

            self::$sessionState = self::SESSION_STARTED;

            return session_start();
        }

        return true;
    }


    /**
     * Write the tmp file and close it
     *
     * @return bool
     */
    public static function write_close() {

        if ( self::$sessionState === self::SESSION_STARTED ) {
            self::$sessionState = self::SESSION_NOT_STARTED;

            return session_write_close();
        }

        return true;
    }


}
