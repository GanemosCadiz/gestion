<?php

/**
 * user
 * 
 * @package 26horas Shared
 * @author Pablo Fernandez
 * @copyright 2015 Pablo Fernandez
 * @version 0.01 // 2015-01-09
 * @access public
 */

/**
 * Changelog:
 * 2015-01-09: 	First version.
 */
 
class user {
  
  /*****************************************************************************************
	 ***  Login Class
	 *****************************************************************************************
	 Adaptation from phpBB login system ((C) 2001 The phpBB Group)
	 (c) 2014 Pablo Fernández (pablo.fernandez@26horas.com)
	 v1.20 - (2014-01-31)
	 ----------------------------------------------------------------------------------------
	 
	 ................................................
	 CHANGELOG
	 
	 v1.20 - (2014-01-31) - Changed constructor behaviour (now empty).
	 											- Added required function "userGet", substitute of old constructor.
	 v1.11 - (2014-01-20) - Added password hashing.
	 v1.10 - (2014-01-18) - Added optional encription support.
	 											- Changed fields definitions to parametized array.
	 											- Uses internal mysqli connection (db class no loger required).
	 v1.00 - (2012-01-01) - First version.
	 
	 *****************************************************************************************/
  
  // Config
  private $tables = array(
  	'users' => "users", // Database table where user data is stored
  	'sessions' => "users_sessions", // Database table where session info is stored
  	'sessions_auto' => "users_sessions_auto" // Database table where autologin info is stored
	);
	// Users table fields definition
	private $fields = array(
		'user_id' => array('name' => "user_id", 'type' => "int", 'encryption' => "none"), 
		'type' => array('name' => "type", 'type' => "string", 'encryption' => "none"), 
		'username' => array('name' => "username", 'type' => "string", 'encryption' => "simple"), 
		'password' => array('name' => "password", 'type' => "string", 'encryption' => "hash"), 
		'name' => array('name' => "name", 'type' => "string", 'encryption' => "salted"), 
		'data' => array('name' => "data", 'type' => "json", 'encryption' => "salted"), 
		'permissions' => array('name' => "permissions", 'type' => "json", 'encryption' => "salted"), 
		'login_current' => array('name' => "login_current", 'type' => "int", 'encryption' => "none"), 
		'login_last' => array('name' => "login_last", 'type' => "int", 'encryption' => "none"), 
		'salt' => array('name' => "salt", 'type' => "string", 'encryption' => "none"), 
		'date_creation' => array('name' => "date_creation", 'type' => "datetime", 'encryption' => "none"), 
		'date_modification' => array('name' => "date_modification", 'type' => "datetime", 'encryption' => "none"), 
		'active' => array('name' => "active", 'type' => "int", 'encryption' => "none"), 
		'deleted' => array('name' => "deleted", 'type' => "int", 'encryption' => "none")
	);
  public $session_name = "user"; // Name of session
  public $session_life = 3600; // Session cookie life (in seconds)
  
  // Error messages
  private $errors = array('nosession'				=> "Error al acceder (Código de error: 1)", // Session doesn't exists in database
													'sessionupdate'		=> "Error al acceder (Código de error: 2)", // Session timestamp couldn't be updated
													'nouser'					=> "Error al acceder (Código de error: 3)", // User doesn't exists in database
													'sessionstartko'	=> "Error al acceder (Código de error: 4)", // Session couldn't be started
													'sessionnotvalid'	=> "Error al acceder (Código de error: 5)", // Session is not valid
													'autologin'				=> "Error al acceder (Código de error: 6)", // Error when retrieving autologin
													'userinactive'		=> "Error al acceder (Código de error: 7)", // User is not active
													'sessionko'				=> "Error al acceder (Código de error: 8)", // Error creating session
													'timestamps'			=> "Error al acceder (Código de error: 9)", // Error updating login timestamps
													'autologinkey'		=> "Error al acceder (Código de error: 10)", // Autologin id couldn't be created
													'sessionend'			=> "Error al acceder (Código de error: 11)", // Error ending session
													'autologinend'		=> "Error al acceder (Código de error: 12)", // Error deleting autologin
													'sessionclean'		=> "Error al acceder (Código de error: 13)"); // Error while cleaning old sessions
  
  public $auth = false; // Indicates if user is authorized
  public $sid = ""; // Stores user's session id
  public $type = ""; // Stores user's type
  public $user_id = ""; // Stores user's id
  public $error_text = ""; // Stores error description (if not critical)
  public $data = array(); // Array that stores user's info
  
  // Internal use
  private $db_connection; // Stores database mysqli connection
  private $crypt_key; // Stores database mysqli connection
  
  
  /**
   * user::__construct()
   * 
   * @param mixed $options
   * @return
   */
  public function __construct($options=array()) {
  	
  	// ===========================================================================================
		// CLASS CONSTRUCTOR
		// Has to be invoked in every page that is controlled by authentication.
		// Use:
		// require_once("class.login.php");
		// $user = new loginClass($mysqli_connection);
		// ===========================================================================================
  	
  	$this->db_connection = $this->setVar($options, "conn", "object");
  	$this->crypt_key = $this->setVar($options, "crypt_key", "string");
  	
 	}
 	
 	/**
	 * dbLayer::setVar()
	 * 
	 * @param mixed $options
	 * @param mixed $option
	 * @param string $type
	 * @return void
	 */
	private function setVar($options, $option, $type="string") {
		
		if (is_array($options)) {
			if (isset($options[$option]) && gettype($options[$option]) == $type) {
				return $options[$option];
			}
		}
		
		return $this->$option;
		
	}
  
  /**
   * user::getUser()
   * 
   * @param mixed $name
   * @return
   */
  public function getUser($name) {
   	
   	// ===========================================================================================
	  // GET USER AUTHETICATION
	  // Has to be invoked in every page that is controlled by authentication.
	  // If user is logged and authenticated "auth" var will be set to "true".
	  // Use:
	  // require_once("class.login.php");
	  // $user = new loginClass($mysqli_connection);
	  // $user->getUser("session_name");
	  // If user is authenticated then $user->auth == true;
	  // ===========================================================================================
   	
    $now = time();
		$this->data = array();
		
		if (isset($name)) {
			$this->session_name = $name;
		}
		
		if (isset($_COOKIE[$this->session_name . '_sid']) || isset($_COOKIE[$this->session_name . '_data'])) {
		 	// We have the cookie so we take data from it
			$session_id = (isset($_COOKIE[$this->session_name . '_sid'])) ? $_COOKIE[$this->session_name . '_sid'] : "";
			$session_data = (isset($_COOKIE[$this->session_name . '_data'])) ? unserialize(stripslashes($_COOKIE[$this->session_name . '_data'])) : array();
		} elseif (isset($_SESSION[$this->session_name . '_sid']) || isset($_SESSION[$this->session_name . '_data'])) {
		 	// We have the session vars so we take data from them
		 	$session_id = (isset($_SESSION[$this->session_name . '_sid'])) ? $_SESSION[$this->session_name . '_sid'] : "";
			$session_data = (isset($_SESSION[$this->session_name . '_data'])) ? $_SESSION[$this->session_name . '_data'] : array();
		} else {
		 	// We don't have previous data
			$session_id = "";
			$session_data = array();
		}
		
		if (!preg_match('/^[A-Za-z0-9]*$/', $session_id)) { $session_id = "";	}
		
		if (!empty($session_id)) {
		 	// We continue only if we have a session
		 	
		 	// Select session from database
			$q = "SELECT u.*, s.*	FROM " . $this->tables['sessions'] . " s, " . $this->tables['users'] . " u 
							WHERE s.session_id='" . $session_id . "' 
							AND u." . $this->fields['user_id']['name'] . " = s.session_user_id";
			if (!($r = $this->db_connection->query($q))) {
			 	$this->error_text = $this->errors['nosession'];
			 	return false;
			}
			// Get user data
			$this->data = $this->dataGet($r);
			
			
			if (isset($this->data[$this->fields['user_id']['name']]))	{
			 	// We continue only if session exists in database
				if ($now - $this->data['session_time'] > 60)	{
					// We update session time every 60 seconds
					$q = "UPDATE " . $this->tables['sessions'] . " SET 
											session_time='" . $now . "' 
									WHERE session_id='" . $this->data['session_id'] . "'";
					if (!$this->db_connection->query($q))	{
					 	$this->error_text = $this->errors['sessionupdate'];
					 	return false;
					}
				}
				
				// User is authorized, so we define session data
				$this->sid = $session_id;
				$this->type = $this->data[$this->fields['type']['name']];
				$this->user_id = $this->data[$this->fields['user_id']['name']];
				$this->auth = true;
				
				setcookie($this->session_name . '_sid', $session_id, 0, "/");
				setcookie($this->session_name . '_data', serialize($session_data), $now + $this->session_life, "/");
				
				$_SESSION[$this->session_name . '_sid'] = $session_id;
				$_SESSION[$this->session_name . '_data'] = $session_data;

				// We add autologin (if so is declared)
				if (isset($session_data['autologin_id']) && $session_data['autologin_id'] != "") {
					$this->data['session_key'] = $session_data['autologin_id'];
				}
				
			}
		} else {
		 	// We don't hace session, so we search for autologin
		 	
			if (isset($session_data['autologin_id']) && (string) $session_data['autologin_id'] != "") {
			 	return $this->sessionStart($session_data['user_id'], true);
			}
			
		}
		
	}
  
  /**
   * user::login()
   * 
   * @param mixed $username
   * @param mixed $password
   * @return
   */
  public function login($username, $password) {
   	
   	// ===========================================================================================
	  // LOGIN FUNCTION
	  // Performs user system login. Has to be invoked every time a login attempt is detected.
		// Login form must use post method.
		// 2 parameters are passed: username and password. If we want to enable autologin we have to 
		// add a post var named "autologin".
		// Returns true in case of correct login, false otherwise.
		// Use:
	  // require_once("class.login.php");
	  // $user = new loginClass("session_name", $mysqli_connection);
	  // if ($user->login($username, $password)) { echo "In!"; } else { echo "Out!"; }
	  // ===========================================================================================
   	
   	if ($this->fields['username']['encryption'] != "none") {
   		// Encrypt username if we have to
   		$username = myCrypt::encrypt($username, $this->crypt_key);
   	}
   	
   	$q = "SELECT * FROM " . $this->tables['users'] . " 
		 						WHERE 
							 		" . $this->fields['username']['name'] . "='" . $username . "' AND 
							 		" . $this->fields['active']['name'] . "='1' AND 
							 		" . $this->fields['deleted']['name'] . "='0' 
								ORDER BY " . $this->fields['user_id']['name'] . " ASC 
								LIMIT 1";
		
		if (!($r = $this->db_connection->query($q))) {
			$this->error_text = $this->errors['nouser'];
			return false;
		}
		
		if ($u = $r->fetch_assoc()) {
			
			switch ($this->fields['password']['encryption']) {
				
				case "hash":
					$ok = myCrypt::hashCompare($password, $u[$this->fields['password']['name']]);
				break;
				
				case "salted":
					$ok = (myCrypt::encrypt($password, $this->crypt_key, $u[$this->fields['salt']['name']]) == $u[$this->fields['password']['name']]);
				break;
				
				case "simple":
					$ok = (myCrypt::encrypt($password, $this->crypt_key) == $u[$this->fields['password']['name']]);
				break;
				
				default:
					$ok = false;
				break;
				
			}
			
			if ($ok) {
				
				$autologin = (isset($_POST['autologin'])) ? true : false;
				
				if (!$this->sessionStart($u[$this->fields['user_id']['name']], $autologin)) {
				 	return false;
				} else {
					return true;
				}
				
			} else {
			 	sleep(2);
			 	$this->error_text = $this->errors['nouser'];
			 	return false;
			}
			
		} else {
		 	sleep(2);
	 		$this->error_text = $this->errors['nouser'];
	 		return false;
		}
		
	}
	
	/**
	 * user::logout()
	 * 
	 * @return
	 */
	public function logout() {
		
		// ===========================================================================================
	  // LOGOUT FUNCTION
	  // Logs out user from the system.
	  // Return "true" if logout finished correctly.
	  // Use:
	  // require_once("class.login.php");
	  // $user = new loginClass();
	  // $user->logout();
	  // ===========================================================================================
		
	 	if ($this->sid == "" || $this->sid != $this->data['session_id']) {
			$this->error_text = $this->errors['sessionnotvalid'];
			return false;
		}

		if ($this->data['session_logged_in']) {
			return $this->sessionEnd($this->data['session_id'], $this->data[$this->fields['user_id']['name']]);
		}
	  
	}
	
	/**
	 * user::sessionStart()
	 * 
	 * @param mixed $user_id
	 * @param integer $autologin
	 * @return
	 */
	private function sessionStart($user_id, $autologin=0) {
		
		// ===========================================================================================
	  // SESSION START
	  // Internal function that starts a session.
	  // This is a modification from the one included in "includes/session.php" of phpBB v.2.0.22.
	  // Returns "true" if session was started correctly, "false" otherwise.
	  // ===========================================================================================
		
		if (isset($_COOKIE[$this->session_name . '_sid']) || isset($_COOKIE[$this->session_name . '_data'])) {
		 	// We have the cookie so we take data from it
			$session_id = (isset($_COOKIE[$this->session_name . '_sid'])) ? $_COOKIE[$this->session_name . '_sid'] : "";
			$session_data = (isset($_COOKIE[$this->session_name . '_data'])) ? unserialize(stripslashes($_COOKIE[$this->session_name . '_data'])) : array();
		} elseif (isset($_SESSION[$this->session_name . '_sid']) || isset($_SESSION[$this->session_name . '_data'])) {
		 	// We have the session vars so we take data from them
		 	$session_id = (isset($_SESSION[$this->session_name . '_sid'])) ? $_SESSION[$this->session_name . '_sid'] : "";
			$session_data = (isset($_SESSION[$this->session_name . '_data'])) ? $_SESSION[$this->session_name . '_data'] : array();
		} else {
		 	// We don't have previous data
			$session_id = "";
			$session_data = array();
		}
		
		// Validation of session var
		if (!preg_match('/^[A-Za-z0-9]*$/', $session_id)) { $session_id = ""; }
		
		$login_last = 0;
		$now = time();
		$login = 0;
		
		$this->data = array();
		
		if (isset($session_data['autologin_id']) && (string) $session_data['autologin_id'] != "" && $user_id) {
		 	// If we got autologin we locate the user based on this
			$q = "SELECT u.* FROM " . $this->tables['users'] . " u, " . $this->tables['sessions_auto'] . " k
							WHERE u." . $this->fields['user_id']['name'] . " = " . (int) $user_id . "
								AND u." . $this->fields['active']['name'] . " = '1' 
								AND u." . $this->fields['deleted']['name'] . " = '0' 
								AND k.user_id = u.user_id 
								AND k.key_id = '" . md5($session_data['autologin_id']) . "'";
			if (!($r = $this->db_connection->query($q))) {
			 	$this->error_text = $this->errors['autologinko'];
			 	return false;
			}
			
			$this->data = $this->dataGet($r);
			
			$autologin = $login = 1;
			
		} else {
		 	// We don't have autologin, so we take data from the user
			$session_data['autologin_id'] = "";
			$session_data['user_id'] = $user_id;

			$q = "SELECT * FROM " . $this->tables['users'] . " 
							WHERE " . $this->fields['user_id']['name'] . "='" . (int) $user_id . "' 
								AND " . $this->fields['active']['name'] . "='1' 
								AND " . $this->fields['deleted']['name'] . "='0'";
			if (!($r = $this->db_connection->query($q))) {
			 	$this->error_text = $this->errors['userinactive'];
			 	return false;
			}

			$this->data = $this->dataGet($r);

			$login = 1;
			
		}
		
		// We update or create the session in the database
		$q = "UPDATE " . $this->tables['sessions'] . " SET 
						session_user_id='" . $user_id . "', 
						session_start='" . $now . "', 
						session_time='" . $now . "', 
						session_logged_in='" . $login . "' 
					WHERE session_id='" . $session_id . "'";
		
		$r = $this->db_connection->query($q);
		
		if (!$r || $this->db_connection->affectedRows() <= 0) {
		 	
			$session_id = $this->keyCreate();
			
			$q = "INSERT INTO " . $this->tables['sessions'] . " SET 
							session_id='" . $session_id . "', 
							session_user_id='" . $user_id . "', 
							session_start='" . $now . "', 
							session_time='" . $now . "', 
							session_logged_in='" . $login . "'";
			
			if (!$this->db_connection->query($q)) {
				$this->error_text = $this->errors['sessionko'];
				return false;
			}
			
		}
		
		// We update session timestamps
		$login_last = ($this->data[$this->fields['login_current']['name']] > 0) ? $this->data[$this->fields['login_current']['name']] : $now;
		
		$q = "UPDATE " . $this->tables['users'] . " SET 
							" . $this->fields['login_current']['name'] . "='" . $now . "', 
							" . $this->fields['login_last']['name'] . "='" . $login_last . "' 
					WHERE " . $this->fields['user_id']['name'] . "='" . $user_id . "'";
		if (!$this->db_connection->query($q)) {
			$this->error_text = $this->errors['timestamps'];
			return false;
		}

		$this->data[$this->fields['login_last']['name']] = $login_last;
		
		if ($autologin) {
		 	// We create the autologin key
			$auto_login_key = $this->keyCreate();
			
			if (isset($session_data['autologin_id']) && (string) $session_data['autologin_id'] != "") {
				$q = "UPDATE " . $this->tables['sessions_auto'] . " SET 
									key_id='" . md5($auto_login_key) . "', 
									last_login='" . $now . "' 
								WHERE key_id='" . md5($session_data['autologin_id']) . "'";
			} else {
				$q = "INSERT INTO " . $this->tables['sessions_auto'] . " SET 
									key_id='" . md5($auto_login_key) . "', 
									user_id='" . $user_id . "', 
									last_login='" . $now . "'";
			}

			if (!$this->db_connection->query($q)) {
			 	$this->error_text = $this->errors['autologinkey'];
			 	return false;
			}
			
			$session_data['autologin_id'] = $auto_login_key;
			unset($auto_login_key);
			
		} else {
		 	
			$session_data['autologin_id'] = "";
			
		}
		
		// Cleaning old sessions
		if (isset($this->data['session_id'])) {
			$this->sessionClean($this->data['session_id']);
		}
		
		$session_data['user_id'] = $user_id;
	
		$this->data['session_id'] = $session_id;
		$this->data['session_user_id'] = $user_id;
		$this->data['session_logged_in'] = $login;
		$this->data['session_start'] = $now;
		$this->data['session_time'] = $now;
		$this->data['session_key'] = $session_data['autologin_id'];
		
		$this->sid = $session_id;
		$this->type = $this->data[$this->fields['type']['name']];
		$this->user_id = $this->data[$this->fields['user_id']['name']];
		$this->auth = true;
		
		setcookie($this->session_name . '_sid', $session_id, 0, "/");
		setcookie($this->session_name . '_data', serialize($session_data), $now + $this->session_life, "/");
		
		$_SESSION[$this->session_name . '_sid'] = $session_id;
		$_SESSION[$this->session_name . '_data'] = $session_data;
	
		return true;
		
	}
	
	/**
	 * user::sessionEnd()
	 * 
	 * @param mixed $session_id
	 * @param mixed $user_id
	 * @return
	 */
	private function sessionEnd($session_id, $user_id) {
	 	
	 	// ===========================================================================================
	  // SESSION END
	  // Internal function that ends a session.
	  // This is a modification from the one included in "includes/session.php" of phpBB v.2.0.22.
	  // Returns "true" if session was ended correctly, otherwise it shows an error message.
	  // ===========================================================================================
	 	
		$now = time();
		
		if (!preg_match('/^[A-Za-z0-9]*$/', $session_id)) { return; }
		
		$q = "DELETE FROM " . $this->tables['sessions'] . " 
						WHERE session_id='" . $session_id . "' AND session_user_id='" . $user_id . "'";
		if (!$this->db_connection->query($q)) {
			$this->error_text = $this->errors['sessionend'];
			return false;
		}
		
		if (isset($this->data['session_key']) && $this->data['session_key'] != "") {
			$autologin_key = md5($this->data['session_key']);
			$sql = "DELETE FROM " . $this->tables['sessions_auto'] . " 
								WHERE user_id='" . (int) $user_id . "' AND key_id='" . $autologin_key . "'";
			if (!$this->db_connection->query($q))	{
				$this->error_text = $this->errors['autologinend'];
				return false;
			}
		}
	
		$this->data = array();
		
		setcookie($this->session_name . '_sid', '', $now - 31536000, "/");
		setcookie($this->session_name . '_data', '', $now - 31536000, "/");
		
		$this->sid = "";
		$this->type = "";
		$this->user_id = "";
		$this->auth = false;
		
		unset($_SESSION[$this->session_name . '_sid']);
		unset($_SESSION[$this->session_name . '_data']);
	
		return true;
		
	}
	
	/**
	 * user::sessionClean()
	 * 
	 * @param mixed $session_id
	 * @return
	 */
	private function sessionClean($session_id) {
	 	
	 	// ===========================================================================================
	  // SESSION CLEANING
	  // Internal function invoked every time a session is created. It cleans old sessions from the
	  // database.
	  // Returns "true" if sessions were cleaned correctly, otherwise it shows an error message.
	  // ===========================================================================================
	 	
	 	$q = "DELETE FROM " . $this->tables['sessions'] . " 
						WHERE session_time<'" . (time() - $this->session_life) . "' 
							AND session_id<>'" . $session_id . "'";
		if (!$this->db_connection->query($q))	{
		 	$this->error_text = $this->errors['sessionclean'];
		 	return false;
		}
		
		$q = "DELETE FROM " . $this->tables['sessions_auto'] . " 
						WHERE last_login<'" . time() . "'";
		$this->db_connection->query($q);
		
		return true;
		
	}
	
	/**
	 * user::dataGet()
	 * 
	 * @param mixed $r
	 * @return
	 */
	private function dataGet($r) {
		
		// Gets user data and returns it decypted (optional) in an array
		
		$data = array();
		
		if ($r->num_rows != 1) {
			return $data;
		}
		
		$data = $r->fetch_assoc();
		
		// Decrypt data if necessary
		foreach ($this->fields as $field => $field_data) {
			
			if ($field == "password") {
				// Remove password
				
				unset($data[$field_data['name']]);
				
			} else {
				
				// Decrypt
				if ($field_data['encryption'] != "none") {
					$salt = ($field_data['encryption'] == "salted") ? $data[$this->fields['salt']['name']] : "";
					$data[$field_data['name']] = myCrypt::decrypt($data[$field_data['name']], $this->crypt_key, $salt);
				}
				
				// Json to array
				if ($field_data['type'] == "json") {
					$data[$field_data['name']] = json_decode($data[$field_data['name']], true);
				}
				
			}
		}
		
		return $data;
		
	}
	
	/**
	 * user::keyCreate()
	 * 
	 * @return
	 */
	private function keyCreate() {
		// Creates autologin id
		return md5(rand(0, 9999999) . uniqid(""));
	}
	
	/**
	 * user::passwordCreate()
	 * 
	 * @param mixed $string
	 * @return
	 */
	private function passwordCreate($string) {
		// Creates password hash
		return md5(sha1($string));
	}
	
	/**
	 * user::passwordCompare()
	 * 
	 * @param mixed $string
	 * @param mixed $hash
	 * @return
	 */
	private function passwordCompare($string, $hash) {
		// Compares password hashes
		return $this->passwordCreate($string) == $hash;
	}
  
}

?>