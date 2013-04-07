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

class Request
{
    protected $Requests = array();
	static protected $Pages = null;

    public static function Get($key, $filter = false)
    {
        if (isset($_POST[$key]))
            return ($filter) ? self::Escape(trim($_POST[$key])) : trim($_POST[$key]);
        if (isset($_GET[$key]))
            return ($filter) ? self::Escape(trim($_GET[$key])) : trim($_GET[$key]);
        return null;
    }
	public static function Exists($key)
	{
		if (isset($_POST[$key]))
			return true;
		if (isset($_GET[$key]))
			return true;
		return false;
	}

	public static function GetPage(){

		if(self::$Pages === null){
			$it = new DirectoryIterator('pages');
			foreach ($it as $fileInfo) {
				if($fileInfo->isDot() || !$fileInfo->IsDir()) continue;
				self::$Pages[] = str_replace('p_','',$fileInfo->getFilename());
			}
		}

		if(self::Exists('page') && in_array(self::Get('page'),self::$Pages))
			return self::Get('page');
		else
			return 'index';

	}

    public static function Escape($value)
    {
    	if(is_int($value))
    		return $value;

        if (Database::GetType() === 'SQLite')
        {

            $return = '';
            for ($i = 0; $i < strlen($value); ++$i)
            {
                $char = $value[$i];
                $ord = ord($char);
                if ($char !== "'" && $char !== "\"" && $char !== '\\' && $ord >= 32 && $ord <=
                    126)
                    $return .= $char;
                else
                    $return .= '\\x' . dechex($ord);
            }
            return $return;
        } else
        {

            return mysql_escape_string($value);
        }
    }

    public static function GetIp($AsInt=false){
        if($AsInt)
            return ip2long($_SERVER['REMOTE_ADDR']);
        else
            return $_SERVER['REMOTE_ADDR'];
    }
}
