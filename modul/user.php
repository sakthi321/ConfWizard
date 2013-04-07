<?php
/**
 *   ____             ____        ___                  _
 *  / ___|___  _ __  / _\ \      / (_)______ _ _ __ __| |
 * | |   / _ \| '_ \| |_ \ \ /\ / /| |_  / _` | '__/ _` |
 * | |__| (_) | | | |  _| \ V  V / | |/ / (_| | | | (_| |
 *  \____\___/|_| |_|_|    \_/\_/  |_/___\__,_|_|  \__,_|
 *
 * This file is part of the Confwizard project.
 * Copyright (c) 2013 Patrick Wieschollek
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://integralstudio.net/license.txt
 *
 * @package    ConfWizard
 * @copyright  2013 (c) Patrick Wieschollek
 * @author     Patrick Wieschollek <wieschoo@gmail.com>
 * @link       http://www.integralstudio.net/
 * @license    GPLv3 http://integralstudio.net/license.txt
 *
 */

class User
{
    protected static $LoginState = false;
    protected static $Permissions = array();
    protected static $Group = 'none';
	protected static $HomeDir = '';

    protected static function StartSession()
    {
        $session_name = 'sec_session_id'; // Set a custom session name
        $secure = false; // Set to true if using https.
        // was true
        $httponly = true; // This stops javascript being able to access the session id.

        ini_set('session.use_only_cookies', 1); // Forces sessions to only use cookies.
        $cookieParams = session_get_cookie_params(); // Gets current cookies params.
        session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"],
            $secure, $httponly);
        session_name($session_name); // Sets the session name to the one set above.
        session_start(); // Start the php session
        session_regenerate_id(true); // regenerated the session, delete the old one.
    }

    public static function Initialize()
    {
        self::StartSession();
        $xml = simplexml_load_file('core/permission.xml');
        for ($i = 0; $i < count($xml->group); $i++)
        {
            if ($xml->group[$i]['name'] == self::GetGroup())
            {
                self::$Permissions = $xml->group[$i];
                break;
            }
        }
    	global $Enviroment;
    	self::$HomeDir = $Enviroment['webserver']['data']['customer_homedirectories'].'/'.self::GetName().'/';

    }
	public static function GetHomeDirectory(){
		return self::$HomeDir;
	}
    public static function ProcessLogin($username, $password)
    {

        $return = self::VerifyLoginData($username, $password);

        if ($return !== false)
        {
            $ip_address = Request::GetIp(); // Get the IP address of the user.
            $user_browser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.

            $user_id = preg_replace("/[^0-9]+/", "", $return['id']); // XSS protection as we might print this value
            $_SESSION['user_id'] = $return['id'];
            $username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $username); // XSS protection as we might print this value
            $_SESSION['username'] = $username;
            $_SESSION['usergroup'] = $return['group'];
            $_SESSION['login_string'] = hash('sha512', $return['password'] . $ip_address . $user_browser);
            self::$Group = $return['group'];
            return true;
        }
        return false;
    }

    public static function ProcessLogout()
    {
        self::$Group = 'none';
        // Unset all session values
        $_SESSION = array();
        // get session parameters
        $params = session_get_cookie_params();
        // Delete the actual cookie.
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]);
        // Destroy session
        session_destroy();
    }

    public static function IsLogin()
    {
        if (isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['login_string']))
        {
            $user_id = $_SESSION['user_id'];
            $login_string = $_SESSION['login_string'];
            $username = $_SESSION['username'];
            $group = $_SESSION['usergroup'];
            $ip_address = Request::GetIp(); // Get the IP address of the user.
            $user_browser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.

            $sql = "SELECT password FROM cw_users WHERE id = '{$user_id}' LIMIT 1";
            $ans = Database::Query($sql);

            if ($ans->NumberOfRows() > 0)
            {
                $data = $ans->fetchArray();
                $login_check = hash('sha512', $data['password'] . $ip_address . $user_browser);
                if ($login_check == $login_string)
                {
                    // Logged In!!!!
                    return true;
                } else
                {
                    // Not logged in
                    return false;
                }

            } else
            {
                self::$Group = 'none';
                // Not logged in
                return false;
            }

        }
    }

    public function VerifyLoginData($Username, $Password_Input)
    {
        $Username = Request::Escape($Username);
        $sql = "SELECT `id`, `username`, `password`, `salt`, `group` FROM `cw_users` WHERE username = '{$Username}'";
        $ans = Database::Query($sql);

        if ($ans->NumberOfRows() === 0)
            return false;

        $ans = $ans->fetchArray();
        $Password = hash('sha512', $Password_Input . $ans['salt']);
        if ($Password === $ans['password'])
        {
            return array(
                'id' => $ans['id'],
                'username' => $ans['username'],
                'group' => $ans['group'],
                'password' => $ans['password']);
        }
        return false;
    }
    public static function IsAllowedTo($page, $action, $modul = null)
    {

        $page = str_replace('P_', '', $page);
        for ($i = 0; $i < count(self::$Permissions); $i++)
        {
            if (self::$Permissions->page[$i]['name'] == $page)
            {
                $perms = explode(',',self::$Permissions->page[$i]['allow']);
                return in_array($action,$perms);
            }
        }
        return false;
    }


    public static function GetGroup()
    {
        if(isset($_SESSION['usergroup']))
            return $_SESSION['usergroup'];
        else
            return 'none';
    }
    public static function GetId()
    {
        return $_SESSION['user_id'];
    }
    public static function GetName()
    {
        return (isset($_SESSION['username'])) ? $_SESSION['username'] : 'none';
    }

	public function GetRandomPassword($length=10){
		$Charset = array();
		# Zahlen
		for($i=48;$i<58;$i++)
			$Charset[] = chr($i);

		# kleine Buchstaben
		for($i=97;$i<122;$i++)
			$Charset[] = chr($i);

		# Großbuchstaben
		for($i=65;$i<90;$i++)
			$Charset[] = chr($i);

		# Sonderzeichen:
		for($i=33;$i<47;$i++)
			$Charset[] = chr($i);
		for($i=59;$i<64;$i++)
			$Charset[] = chr($i);
		for($i=91;$i<96;$i++)
			$Charset[] = chr($i);
		for($i=123;$i<126;$i++)
			$Charset[] = chr($i);

		return substr(implode('',$Charset),0,$length);

	}
}
