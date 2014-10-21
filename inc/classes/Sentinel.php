<?php

class Sentinel
{
    private static $auth;
    private static $authFile;
    private static $authFileP;
    private static $currentlylocked = false;

    /**
     * Lock the database file
     *
     * @return boolean
     */
    private static function lock()
    {
        if (self::$currentlylocked === false) {
            self::$authFileP = fopen( self::$authFile , "a+" );
            if ( flock( self::$authFileP , LOCK_EX ) ) { // acquière un verrou exclusif
                self::$currentlylocked = true;
            } else {
                throw new Exception( 'Unable to lock file' );
            }
        }

        return true;
    }

    /**
     * Read the database
     *
     * @return string the database content
     */
    private static function read()
    {
        self::lock();
        if ( is_null( self::$authFileP ) ) throw new Exception( 'No lock has been requested' );
        return stream_get_contents( self::$authFileP , -1 , 0 );
    }

    /**
     * Write the database
     *
     * @param string $content the database content
     *
     * @return boolean
     */
    private static function write($content)
    {
        self::lock();
        if ( is_null( self::$authFileP ) ) throw new Exception( 'No lock has been requested' );
        ftruncate( self::$authFileP , 0 );
        fwrite( self::$authFileP , $content );

        return fflush( self::$authFileP );
    }

    private static function sessionRead()
    {
        // Web
        if ( isset( $_SERVER['SERVER_PROTOCOL'] ) ) {
            @session_start();

            return ( isset( $_SESSION['auth'] ) ) ? $_SESSION['auth'] : array();
        }
        // CLI
        else {
            $json = @file_get_contents( '_cli_fake_session' );
            $value = json_decode( $json , true );

            return ( is_array( $value ) ) ? $value : array();
        }
    }

    private static function sessionWrite($value)
    {
        // Web
        if ( isset( $_SERVER['SERVER_PROTOCOL'] ) ) {
            @session_start();
            $_SESSION[ 'auth' ] = $value;
            session_write_close();
        }
        // CLI
        else {
            file_put_contents( '_cli_fake_session' , json_encode( $value ) );
        }
    }

    private static function sessionDestroy()
    {
        // Web
        if ( isset( $_SERVER['SERVER_PROTOCOL'] ) ) {
            @session_start();
            unset( $_SESSION['auth'] );
            $_SESSION['auth'] = array();
            session_write_close();
        }
        // CLI
        else {
            $value = array();
            file_put_contents( '_cli_fake_session' , json_encode( $value ) );
        }
    }

    /**
     * Get the local ip address of the current client according to proxy and more...
     *
     * @return string an ip address
     */
    public static function getClientIp()
    {
        $ip = '';
        if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * Generate a security token
     *
     * @return string the security token
     */
    private static function generateSecurityToken()
    {
        return mt_rand_str( 64 );
    }

    /**
     * Get the security token
     *
     * @return string the security token
     */
    private static function getSecurityToken()
    {
        if ( ! is_array( self::$auth ) ) throw new Exception( 'Authentication not initialized' );
        return self::$auth['security'];
    }

    /**
     * Get the date when authentication file has been ganarated
     *
     * @return integer the generated date
     */
    private static function getGenerated()
    {
        if ( ! is_array( self::$auth ) ) throw new Exception( 'Authentication not initialized' );
        return self::$auth['generated'];
    }

    /**
     * Generate a hash from a username and a password
     *
     * @param string $username username
     * @param string $password password
     *
     * @return string the hash
     */
    private static function getPasswordHash($username , $password)
    {
        return sha1( self::getSecurityToken() . $username . self::getGenerated() . $password );
    }

    /**
     * Release the database file
     *
     * @return boolean
     */
    public static function release()
    {
        if ( is_null( self::$authFileP ) ) return;
        $a = @flock( self::$authFileP , LOCK_UN );
        @fclose( self::$authFileP );
        self::$authFileP       = null;
        self::$currentlylocked = false;
    }

    /**
     * Tell whether a password is valid or not
     *
     * @param string $username the username
     * @param string $password the password
     *
     * @return boolean
     */
    public static function isValidPassword($username , $password)
    {
        if ( ! self::userExists( $username ) ) return false;
        $compute = self::getPasswordHash($username , $password);

        return ( $compute === self::$auth['users'][$username]['pwd'] );
    }

    /**
     * Get the path of the authentication file
     *
     * @return string the fila path
     */
    public static function getAuthFilePath()
    {
        return PML_BASE . DIRECTORY_SEPARATOR . AUTH_CONFIGURATION_FILE;
    }

    /**
     * Just get the auth file and load it in the $auth variable
     *
     * @return boolean success or not
     */
    public static function reload()
    {
        $content = self::read();
        $array   = json_decode( $content , true );
        if ( is_null( $array ) ) {
            return false;
        }
        self::$auth = $array;

        return true;
    }

    /**
     * Set file, etc...
     *
     * @return boolean success or not
     */
    public static function init()
    {
        self::$authFile = self::getAuthFilePath();
        if ( self::isAuthSet() ) {
            self::reload();
        }

        return true;
    }

    /**
     * Create a new authentication file
     *
     * @return boolean true if ok or false if error or if already exists
     */
    public static function create()
    {
        if ( file_exists( self::$authFile ) ) return false;
        if ( touch( self::$authFile ) === false ) return false;

        self::$auth = array(
            'generated' => date('U'),
            'security'  => self::generateSecurityToken(),
            'users'     => array()
            );

        self::save();

        return true;
    }

    /**
     * Tell whether authentication file exists or not
     *
     * @return boolean [description]
     */
    public static function isAuthSet()
    {
        return file_exists( self::$authFile );
    }

    /**
     * Set an admin
     *
     * @param [type] $username [description]
     * @param [type] $password [description]
     * @param array  $logs     [description]
     */
    public static function setAdmin($username , $password = null , $logs = null)
    {
        return self::setUser( $username , $password , $roles = array('admin') , $logs );
    }

    /**
     * Set a user
     *
     * @param string $username username
     * @param string $password password
     * @param array  $roles    an array of global roles
     * @param array  $logs     an array of credentials for log files
     */
    public static function setUser($username , $password = null , $roles = null , $logs = null)
    {
        if ( ! is_array( self::$auth ) ) throw new Exception( 'Authentication not initialized' );

        if ( isset( self::$auth['users'][ $username ] ) ) {
            $user = self::$auth['users'][ $username ];
        } else {
            $user = array(
                'roles' => array('user'),
                'pwd'   => '',
                'logs'  => array()
            );
        }

        if ( ! is_null( $password ) ) $user['pwd']   = self::getPasswordHash( $username , $password );
        if ( is_array( $logs ) )      $user['logs']  = $logs;
        if ( is_array( $roles ) )     $user['roles'] = $roles;

        self::$auth['users'][ $username ] = $user;

        return true;
    }

    public static function setUserAccess($username , $logs)
    {
    }

    /**
     * Return the data of a user
     *
     * @param string $username the username
     *
     * @return array data or null if not exists
     */
    public static function getUser($username = null)
    {
        if ( is_null( $username ) ) $username = self::getCurrentUsername();

        if ( self::userExists( $username ) ) {
            return self::$auth['users'][ $username ];
        }

        return null;
    }

    /**
     * Tell whether user exists or not
     *
     * @param string $username the username
     *
     * @return boolean
     */
    public static function userExists($username)
    {
        if ( ! is_array( self::$auth ) ) throw new Exception( 'Authentication not initialized' );
        return ( isset( self::$auth['users'][ $username ] ) );
    }

    /**
     * Return the array of users
     *
     * @return array the users
     */
    public static function getUsers()
    {
        if ( ! is_array( self::$auth ) ) throw new Exception( 'Authentication not initialized' );

        if ( isset( self::$auth['users'] ) ) {
            return self::$auth['users'];
        } else {
            return array();
        }
    }

    /**
     * Return the users count
     *
     * @return integer the user count
     */
    public static function getUsersCount()
    {
        return count( self::getUsers() );
    }

    /**
     * Delete a user
     *
     * @param string $username the username to delete
     *
     * @return array the deleted user or false if not exists
     */
    public static function deleteUser($username)
    {
        if ( ! self::userExists( $username ) ) return false;

        $deleted_user = self::$auth['users'][ $username ];
        unset( self::$auth['users'][ $username ] );

        return $deleted_user;
    }

    /**
     * Tell if a user has a role or not
     *
     * @param string $username a username or current logged in user if not set
     *
     * @return boolean
     */
    public static function userHasRole($role , $username = null)
    {
        if ( is_null( $username ) ) $username = self::getCurrentUsername();
        if ( is_null( $username ) ) return false;
        if ( ! self::userExists( $username ) ) return false;
        if ( in_array( 'admin' , self::$auth['users'][ $username ]['roles'] ) ) return true;
        return ( in_array( $role , self::$auth['users'][ $username ]['roles'] ) );
    }

    /**
     * Tell if a user is admin or not. Being admin is having role "admin"
     *
     * @param string $username a username or current logged in user if not set
     *
     * @return boolean
     */
    public static function isAdmin($username = null)
    {
        return self::userHasRole( 'admin' , $username );
    }

    /**
     * Sign in user
     *
     * @param string $username the user to sign in
     * @param string $password its password
     *
     * @return array the user informations or false if failed
     */
    public static function signIn($username , $password)
    {
        if ( self::isValidPassword($username , $password) ) {
            self::sessionWrite( array( 'username' => $username ) );

            $user = self::$auth['users'][ $username ];

            self::reload();
            self::$auth['users'][ $username ]['logincount'] = (int) @self::$auth['users'][ $username ]['logincount'] + 1;
            self::$auth['users'][ $username ]['lastlogin'] = array(
                'ip' => self::getClientIp(),
                'ua' => ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? $_SERVER['HTTP_USER_AGENT'] : '',
                'ts' => date("U")
                );
            self::save();

            return $user;
        }

        return false;
    }

    /**
     * Get the current logged in user or null
     *
     * @return string the current username or null
     */
    public static function getCurrentUsername()
    {
        $auth = self::sessionRead();

        return ( isset( $auth['username'] ) ) ? $auth['username'] : null;
    }

    /**
     * Sign out the current user and return its username
     *
     * @return string the logged out username
     */
    public static function signOut()
    {
        $a = self::getCurrentUsername();

        self::sessionDestroy();

        return $a;
    }

    /**
     * Manage login
     *
     * @return [type] [description]
     */
    public static function attempt()
    {
        if ( isset( $_GET['signout'] ) ) {
            self::signOut();
            $error   = 3;
            $attempt = '?';
            include_once PML_BASE . '/inc/login.inc.php';
            self::release();
            die();
        }

        if ( Sentinel::isAuthSet() ) { // authentication is enabled on this instance

            $user = Sentinel::getCurrentUsername();

            if ( is_null( $user ) ) { // no logged in user

                if ( isset( $_POST['attempt'] ) ) { // form is posted

                    if ( ! csrf_verify() ) {
                        $attempt = $_POST['attempt'];
                        $error   = 2;
                        include_once PML_BASE . '/inc/login.inc.php';
                        self::release();
                        die();
                    }

                    $loggedin = Sentinel::signIn( $_POST['username'] , $_POST['password'] );

                    if ( is_array( $loggedin ) ) { // signed in
                    	header( "Location: " . $_POST['attempt'] );
                    	die();

                    } else { // error while signing in
                        $attempt = $_POST['attempt'];
                        $error   = 1;
                        include_once PML_BASE . '/inc/login.inc.php';
                        self::release();
                        die();
                    }

                } else { // send form
                    $attempt = $_SERVER['REQUEST_URI'] . '?' . $_SERVER['QUERY_STRING'];
                    $error   = 0;
                    include_once PML_BASE . '/inc/login.inc.php';
                    self::release();
                    die();
                }
            } else {
                return $user;
            }
        }

        return null;

    }

    /**
     * Save modifications on disk
     *
     * @return boolean
     */
    public static function save()
    {
        self::lock();
        if ( is_null( self::$authFileP ) ) throw new Exception( 'No lock has been requested' );

        self::write( json_encode( self::$auth ) );

        return true;
    }

    /**
     * Destroy the authentication file
     *
     * @return boolean success or not
     */
    public static function destroy()
    {
        self::sessionDestroy();

        if ( self::isAuthSet() ) {
            return unlink( self::$authFile );
        }

        return true;
    }
}

/**
 * On shutdown, release the lock !
 *
 * @return  void
 */
function pml_sentinel_shutdown()
{
    Sentinel::release();
}

register_shutdown_function('pml_sentinel_shutdown');

Sentinel::init();
