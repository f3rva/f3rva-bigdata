<?php
namespace F3\Service;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(dirname(__FILE__))));
}

/**
 * Service class encapsulating authentication logic.
 *
 * @author bbischoff
 */
class AuthenticationService {
	public function __construct() {
	}
	
	public function login($email, $password) {
		if (strcmp($email, 'bbischoff78@gmail.com') == 0) {
			$_SESSION["username"] = $email;
			$_SESSION["loginIP"] = $_SERVER["REMOTE_ADDR"];
			return true;
		}
		
		return false;
	}
	
	public function logout() {
		unset($_SESSION["username"]);
		unset($_SESSION["loginIP"]);
	}
	
	public function loggedIn( )
	{
		// Check if the user has logged in
		if (isset($_SESSION["username"]) &&
			(isset($_SESSION["loginIP"]) &&
			($_SESSION["loginIP"] == $_SERVER["REMOTE_ADDR"])))
		{
			return true;
		}
		
		return false;
	}
}
?>