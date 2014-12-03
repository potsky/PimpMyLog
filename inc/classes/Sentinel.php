<?php

class Sentinel
{

	private static $auth;
	private static $authFile;
	private static $authFileP;
	private static $api_session;
	private static $currentlylocked = false;


	/**
	 * Manage login
	 *
	 * @param   array   $files  the files configuration
	 *
	 * @return [type] [description]
	 */
	public static function attempt( $files = false )
	{

		if ( self::isAuthSet() ) { // authentication is enabled on this instance

			$user = self::getCurrentUsername();

			if ( is_null( $user ) ) { // no logged in user

				if ( isset( $_POST['attempt'] ) ) { // form is posted

					if ( ! csrf_verify() ) {
						$attempt = $_POST['attempt'];
						$error   = 2;
						include_once PML_BASE . '/inc/login.inc.php';
						self::release();
						die();
					}

					$loggedin = self::signIn( $_POST['username'] , $_POST['password'] );

					if ( is_array( $loggedin ) ) { // signed in
						header( "Location: " . $_POST['attempt'] );
						die();
					}

					else { // error while signing in
						$attempt = $_POST['attempt'];
						$error   = 1;
						include_once PML_BASE . '/inc/login.inc.php';
						self::release();
						die();
					}
				}

				else if ( isset( $_GET['signin'] ) ) { // sign in page when anonymous access is enabled

					$attempt = ( isset( $_GET['attempt'] ) ) ? $_GET['attempt'] : $_SERVER['REQUEST_URI'] . '?' . $_SERVER['QUERY_STRING'];
					$error   = 0;
					include_once PML_BASE . '/inc/login.inc.php';
					self::release();
					die();
				}

				else if ( self::isAnonymousEnabled( $files ) ) { // Anonymous access is enabled, simply return to let anonymosu users to parse logs
					return null;
				}

				else { // send form
					$attempt = $_SERVER['REQUEST_URI'] . '?' . $_SERVER['QUERY_STRING'];
					$error   = 0;
					include_once PML_BASE . '/inc/login.inc.php';
					self::release();
					die();
				}

			}

			else {

				if ( isset( $_GET['signout'] ) ) {
					self::signOut();
					self::release();

					if ( self::isAnonymousEnabled( $files ) ) { // Anonymous access, redirect to normal page
						header( 'Location: ' . $_SERVER['PHP_SELF'] );
					}
					else { // No anonymous access, redirect to login page
						$error   = 3;
						$attempt = '?';
						include_once PML_BASE . '/inc/login.inc.php';
					}

					die();
				}

				return $user;
			}
		}

		return null;

	}

	/**
	 * Change the password with logging
	 *
	 * @param string $username     the username
	 * @param string $new_password the new password
	 *
	 * @return boolean true
	 */
	public static function changePassword($username , $new_password)
	{
		self::setUser( $username , $new_password );
		self::log( 'changepwd' , $username );
		self::save();

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
		if ( @touch( self::$authFile ) === false ) return false;

		self::$auth = array(
			'generated' => date('U'),
			'security'  => self::generateSecurityToken(),
			'anonymous' => array(),
			'users'     => array(),
		);

		self::save();

		return true;
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
	 * Destroy the authentication file
	 *
	 * @return boolean success or not
	 */
	public static function destroy()
	{
		self::sessionDestroy();

		if ( self::isAuthSet() ) {
			return @unlink( self::$authFile );
		}

		return true;
	}

	/**
	 * Get the path of the authentication file
	 *
	 * @return string the fila path
	 */
	public static function getAuthFilePath()
	{
		return PML_CONFIG_BASE . DIRECTORY_SEPARATOR . AUTH_CONFIGURATION_FILE;
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
	 * Get the current logged in user or null
	 *
	 * @return string the current username or null
	 */
	public static function getCurrentUsername()
	{
		if ( is_array( self::$api_session ) ) {
			$auth = self::$api_session;
		} else {
			$auth = self::sessionRead();
		}
		return ( isset( $auth['username'] ) ) ? $auth['username'] : null;
	}

	/**
	 * Return the log array
	 *
	 * @return array data
	 */
	public static function getLogs()
	{
		if ( isset( self::$auth['logs'] ) ) {
			return self::$auth['logs'];
		}

		return array();
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
	 * Find a user from its access token
	 *
	 * @param   string  $accesstoken  the access token
	 *
	 * @return  string                username or null
	 */
	public static function getUsernameFromAccessToken( $accesstoken ) {
		$users = self::getUsers();
		foreach( $users as $username => $user ) {
			if ( $user['at'] === $accesstoken ) {
				return $username;
			}
		}
		return null;
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
	 * Tell if at least one log file is accessible anonymously
	 *
	 * The $files parameter is optional. If given, it will check if all anonymous files still exist.
	 * If they do not exist, they must be erased and they do not count for this check.
	 *
	 * @param   array    $files  The files configuration
	 *
	 * @return  boolean
	 */
	public static function isAnonymousEnabled( $files = false )
	{
		if ( ! isset( self::$auth['anonymous'] ) ) return false;
		if ( ! is_array( self::$auth['anonymous'] ) ) return false;

		if ( $files === false ) {
			return ( count( self::$auth['anonymous'] ) > 0 );
		}

		$found = 0;
		foreach( self::$auth['anonymous'] as $file ) {
			if ( isset( $files[ $file ] ) ) $found++;
		}

		return ( $found > 0 );
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
	 * Tell if a log file is accessible anonymously
	 *
	 * @param   string  $log       the fileid
	 *
	 * @return  boolean
	 */
	public static function isLogAnonymous( $log )
	{
		return ( in_array( $log , self::$auth['anonymous'] ) );
	}

	/**
	 * Tell whether a signature is valid for given values or not
	 *
	 * @param   string   $given_sign  the provided signature
	 * @param   array    $values      an array of values to certify
	 * @param   string   $username    the user who has signed values or null for an instance signature
	 *
	 * @return  boolean               ok or not
	 */
	public static function isSignValid( $given_sign , $values , $username = null ) {
		return ( self::sign( $values , $username ) === $given_sign );
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
	 * Log an action
	 *
	 * @param string $action    the action text
	 * @param string $username  the username or null for the current logged in user
	 * @param int    $timestamp the action timestamp
	 * @param string $ip        the IP address
	 * @param string $useragent the user agent
	 *
	 * @return bool true
	 */
	public static function log($action , $username = null , $timestamp = null , $ip = null , $useragent = null)
	{
		if ( ! is_array( self::$auth ) ) throw new Exception( 'Authentication not initialized' );

		if ( ! isset( self::$auth['logs'] ) ) {
			self::$auth['logs'] = array();
		}

		if ( is_null( $username ) ) $username = self::getCurrentUsername();
		if ( is_null( $timestamp ) ) $timestamp = date("U");
		if ( is_null( $ip ) ) $ip = self::getClientIp();
		if ( is_null( $useragent ) ) $useragent = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? $_SERVER['HTTP_USER_AGENT'] : '';

		self::$auth['logs'] = array_slice( array_merge( array( array( $action , $username , $timestamp , $ip , $useragent ) ) , self::$auth['logs'] ) , 0 , abs( (int) AUTH_LOG_FILE_COUNT ) );

		return true;
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
	 * Just get the auth file and load it in the $auth variable
	 *
	 * @return boolean success or not
	 */
	public static function reload()
	{
		$content = preg_replace('/^.+\n/', '', self::read() );
		$array   = json_decode( $content , true );
		if ( is_null( $array ) ) {
			return false;
		}
		self::$auth = $array;

		return true;
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

		$file = '<?php if (realpath(__FILE__)===realpath($_SERVER["SCRIPT_FILENAME"])) {header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");die();}?>' . "\n";
		$file.= json_encode( self::$auth );

		self::write( $file );

		return true;
	}

	/**
	 * Set an admin
	 *
	 * @param string $username username
	 * @param string $password password
	 * @param array  $logs     an array of credentials for log files
	 *
	 * @return boolean true
	 */
	public static function setAdmin($username , $password = null , $logs = null)
	{
		return self::setUser( $username , $password , $roles = array('admin') , $logs );
	}

	/**
	 * Set a log anonymous or not
	 *
	 * @param   string   $log       the fileid
	 * @param   boolean  $anonymous true or false
	 *
	 * @return boolean true
	 */
	public static function setLogAnonymous( $log , $anonymous )
	{
		if ( ! is_array( self::$auth ) ) throw new Exception( 'Authentication not initialized' );

		$anon = array();

		if ( isset( self::$auth['anonymous'] ) ) {
			$anon = self::$auth['anonymous'];
		}

		if ( $anonymous === true ) {
			$anon[] = $log;
			$anon = array_unique( $anon );
		}
		else {
			$anon = array_diff( $anon, array( $log ) );
		}

		self::$auth['anonymous'] = $anon;

		return true;
	}

	/**
	 * Set a user
	 *
	 * @param string  $username          username
	 * @param string  $password          password
	 * @param array   $roles             an array of global roles
	 * @param array   $logs              an array of credentials for log files
	 * @param boolean $regeneratetokens  whether access tokens should be regenerated or not
	 *
	 * @return boolean true
	 */
	public static function setUser($username , $password = null , $roles = null , $logs = null , $regeneratetokens = false )
	{
		if ( ! is_array( self::$auth ) ) throw new Exception( 'Authentication not initialized' );

		if ( isset( self::$auth['users'][ $username ] ) ) {
			$user = self::$auth['users'][ $username ];
		} else {
			$user = array(
				'roles' => array('user'),
				'pwd'   => '',
				'logs'  => array(),
				'cd'    => date('U'),
				'cb'    => self::getCurrentUsername(),
				'at'    => self::generateSecurityToken(32), // Access token
				'hp'    => self::generateSecurityToken(16), // Presalt for this user, postsalt is the instance security token
			);
		}
		if ( $regeneratetokens === true ) {
			$user['at'] = self::generateSecurityToken(32);
			$user['hp'] = self::generateSecurityToken(16);
		}
		if ( ! is_null( $password ) ) $user['pwd']   = self::getPasswordHash( $username , $password );
		if ( is_array( $logs ) )      $user['logs']  = $logs;
		if ( is_array( $roles ) )     $user['roles'] = $roles;

		self::$auth['users'][ $username ] = $user;

		return true;
	}

	/**
	 * Sign values
	 *
	 * @param   array   $values    an array of key values to sign
	 * @param   string  $username  the user who signs values or null for an instance signature
	 *
	 * @return  string             the signature of false if a problem occurs
	 */
	public static function sign( $values , $username = null )
	{
		if ( ! is_array( self::$auth ) ) throw new Exception( 'Authentication not initialized' );

		$presalt  = self::$auth['security'];
		$values   = json_encode( $values );
		$postsalt = '';

		if ( ! is_null( $username ) ) {
			$user     = self::getUser( $username );
			$postsalt = $user['hp'];
		}

		return sha1( $presalt . $values . $postsalt );
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
		$ip = self::getClientIp();
		$ua = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$ts = date("U");

		if ( self::isValidPassword($username , $password) ) {

			self::sessionWrite( array( 'username' => $username ) );
			self::reload();
			self::$auth['users'][ $username ]['logincount'] = (int) @self::$auth['users'][ $username ]['logincount'] + 1;
			self::$auth['users'][ $username ]['lastlogin'] = array(
				'ip' => $ip,
				'ua' => $ua,
				'ts' => $ts
			);
			self::log( 'signin' , $username , $ts , $ip , $ua );
			self::save();

			return self::$auth['users'][ $username ];
		}

		self::log( 'signinerr' , $username , $ts , $ip , $ua );
		self::save();

		return false;
	}

	/**
	 * Sign in as a user
	 *
	 * @param string $username the user to sign in
	 *
	 * @return array the user informations or false if failed
	 */
	public static function signInAs($username)
	{
		$ip = self::getClientIp();
		$ua = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$ts = date("U");
		$cu = self::getCurrentUsername();

		self::sessionWrite( array( 'username' => $username ) );
		self::reload();

		self::log( 'signinas ' . $username , $cu, $ts , $ip , $ua );
		self::save();

		return self::$auth['users'][ $username ];
	}

	/**
	 * Sign in user with its access token
	 * The signin is only available for this call, no session
	 *
	 * @param string $accesstoken
	 *
	 * @return array the user informations or false if failed
	 */
	public static function signInWithAccessToken( $accesstoken )
	{
		$username = self::getUsernameFromAccessToken( $accesstoken );

		if ( is_null( $username ) ) return false;

		$ip = self::getClientIp();
		$ua = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$ts = date("U");
		$cu = get_current_url( true );

		self::$api_session = array( 'username' => $username );

		self::$auth['users'][ $username ]['api_logincount'] = (int) @self::$auth['users'][ $username ]['api_logincount'] + 1;
		self::$auth['users'][ $username ]['api_lastlogin'] = array(
			'ip' => $ip,
			'ua' => $ua,
			'ts' => $ts
		);
		if ( ! is_null( $cu ) ) self::$auth['users'][ $username ]['api_lastlogin']['ur'] = $cu;
		self::save();

		return self::$auth['users'][ $username ];
	}

	/**
	 * Sign out the current user and return its username
	 *
	 * @return string the logged out username
	 */
	public static function signOut()
	{
		$username = self::getCurrentUsername();

		if ( ! is_null( $username ) ) {
			$ip       = self::getClientIp();
			$ua       = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? $_SERVER['HTTP_USER_AGENT'] : '';
			$ts       = date("U");

			self::log( 'signout' , $username , $ts , $ip , $ua );
			self::save();

			self::sessionDestroy();
		}

		return $username;
	}

	/**
	 * Tell if a user has an access on a log file
	 *
	 * @param   string  $log       the fileid
	 * @param   string  $action    the name of the action
	 * @param   string  $value     the value for this action
	 * @param   string  $username  the username
	 *
	 * @return  boolean
	 */
	public static function userCanOnLogs( $log , $action , $value , $username = null)
	{
		if ( is_null( $username ) ) $username = self::getCurrentUsername();
		if ( is_null( $username ) ) return false;
		if ( ! self::userExists( $username ) ) return false;
		if ( in_array( 'admin' , self::$auth['users'][ $username ]['roles'] ) ) return true;
		if ( ! isset( self::$auth['users'][ $username ]['logs'][ $log ][ $action ] ) ) return false;
		return ( self::$auth['users'][ $username ]['logs'][ $log ][ $action ] === $value );
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
	 * Generate a security token
	 *
	 * @return string the security token
	 */
	private static function generateSecurityToken( $len = 64 )
	{
		return mt_rand_str( $len );
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
	 * Lock the database file
	 *
	 * @return boolean
	 */
	private static function lock()
	{
		if (self::$currentlylocked === false) {
			self::$authFileP = fopen( self::$authFile , "a+" );
			if ( flock( self::$authFileP , LOCK_EX ) ) {
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
	 * Destroy a session
	 *
	 * Need a wrapper to manage session with phpunit
	 *
	 * @return  void
	 */
	private static function sessionDestroy()
	{
		// Web
		if ( isset( $_SERVER['SERVER_PROTOCOL'] ) ) {
			Session::start();
			unset( $_SESSION['auth'] );
			$_SESSION['auth'] = array();
			Session::write_close();
		}
		// CLI
		else {
			$value = array();
			file_put_contents( '_cli_fake_session' , json_encode( $value ) );
		}
	}

	/**
	 * Read a session
	 *
	 * @return  array  the array with all auth informations
	 */
	private static function sessionRead()
	{
		// Web
		if ( isset( $_SERVER['SERVER_PROTOCOL'] ) ) {

			if ( isset( $_SESSION['auth'] ) ) return $_SESSION['auth'];

			Session::start();

			return ( isset( $_SESSION['auth'] ) ) ? $_SESSION['auth'] : array();
		}
		// CLI
		else {
			$json = @file_get_contents( '_cli_fake_session' );
			$value = json_decode( $json , true );

			return ( is_array( $value ) ) ? $value : array();
		}
	}


	/**
	 * Write the session array
	 *
	 * @param   array  $value  the array to store
	 *
	 * @return  void
	 */
	private static function sessionWrite($value)
	{
		// Web
		if ( isset( $_SERVER['SERVER_PROTOCOL'] ) ) {
			Session::start();
			$_SESSION[ 'auth' ] = $value;
			Session::write_close();
		}
		// CLI
		else {
			file_put_contents( '_cli_fake_session' , json_encode( $value ) );
		}
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
