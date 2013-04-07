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

class Database
{
    static protected $DB = null;
    static protected $Type = 'SQLite';
    /**
     * Database::Factory()
     * returns a database adapter
     * @param database $type (SQLite or MySQL)
     * @return
     */
    public static function Factory($type)
    {
        global $config;
        self::$Type = $type;
        switch ($type)
        {
            default:
                throw new Exception();
                break;
            case 'SQLite':
                require_once 'core/database_sqlite.php';
                $classname = 'DatabaseSQLite';
                self::$DB = $classname::Singleton();
                self::$DB->connect($config['SQLite']['Filename']);
                break;
            case 'MySQL':
                require_once 'core/database_mysql.php';
                $classname = 'DatabaseMySQL';
                self::$DB = $classname::Singleton();
                self::$DB->connect($config['MySQL']['DatabaseHost'], $config['MySQL']['DatabaseUser'],
                    $config['MySQL']['DatabasePassword'], $config['MySQL']['DatabaseName']);
                break;
        }

    }

    /**
     * Database::GetType()
     * returns type of database (SQLite or MySQL)
     * @return
     */
    public static function GetType(){
        return self::$Type;
    }

    /**
     * Database::Instance()
     * returns instance of database
     * @return
     */
    public static function Instance()
    {
        return self::$DB;
    }

    /**
     * Database::Query()
     * query a sql statemant and returns record set
     * @param string $query
     * @return Recordset
     */
    public static function Query($query)
    {
        return self::$DB->Query($query);
    }
    /**
     * Database::Close()
     * disconnect from database
     * @return
     */
    public static function Close()
    {
        return self::$DB->Close();
    }

	/**
	 * Database::LastId()
	 * returns id from last statement
	 * @return
	 */
	public static function LastId(){
		return self::$DB->LastId();
	}

    /**
     * Database::Now()
     * returns current time in correct format for databases "Y-m-d H:i:s"
     * @return
     */
    public static function Now(){
        return date("Y-m-d H:i:s");
    }
    /**
     * Database::Time()
     * returns given time in correct format for databases
     * @param mixed $timestamp
     * @return
     */
    public static function Time($timestamp){
        return date("Y-m-d H:i:s",$timestamp);
    }

    /**
     * Database::TestInjection()
     * manual test if Statement contains injections (very pessimist)
     * @param mixed $var
     * @return
     */
    public static function TestInjection($var)
    {
        return strpos($var, "DROP TABLE") || strpos($var, "DROP") || strpos($var,
            "OR 1=1") || (strpos($var, " WHERE ") && strpos($var, " OR ")) || (strpos($var,
            " OR ") && strpos($var, " LIKE ")) || (strpos($var, "\'' ") < strpos($var,
            " LIKE ")) || (strpos($var, "UPDATE ") && (strpos($var, "SET ") < strpos($var,
            "WHERE "))) || (strpos($var, "INSERT INTO ") && strpos($var, "VALUES ")) ||
            strpos($var, "SELECT * FROM");
    }


}
