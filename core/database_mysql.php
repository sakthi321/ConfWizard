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

class DatabaseMySQL
{
    protected static $AccessData = array(
        'Host' => '',
        'Username' => '',
        'Password' => '',
        'Databasename' => '');
    protected static $Tunnel;
    protected static $query_result;
    protected static $num_queries = 0;


    private static $instance;
    public static function singleton()
    {
        if (!isset(self::$instance)) {
            $className = __class__;
            self::$instance = new $className;
        }
        return self::$instance;
    }
    private function __clone()
    {
    }
    private function __construct()
    {
    }
    private function __wakeup()
    {
    }


    /**
     * Database::$Connect()
     *
     * @param mixed $server
     * @param mixed $user
     * @param mixed $password
     * @param mixed $database
     * @return void
     */
    public function Connect($server, $user, $password, $database=null)
    {
        if ($server == "" && $user == "") {
            throw new Exception('empty access data');
        }
        self::$AccessData['Host'] = $server;
        self::$AccessData['Username'] = $user;
        self::$AccessData['Password'] = $password;
        self::$AccessData['Databasename'] = $database;


    	if($database === null){
    		self::$Tunnel = @new mysqli(
    							self::$AccessData['Host'],
    							self::$AccessData['Username'],
            					self::$AccessData['Password']
            				);
    	}else{
    		self::$Tunnel = @new mysqli(
    							self::$AccessData['Host'],
    							self::$AccessData['Username'],
            					self::$AccessData['Password'],
            					self::$AccessData['Databasename']
            				);
    	}



        if (self::$Tunnel->connect_error) {
            throw new Exception('no access');
        }
        if (!self::$Tunnel->set_charset("utf8")) {
            throw new Exception(self::$Tunnel->error);
        }
    }


    /**
     * Database::$Close()
     *
     * @return
     */
    public function Close()
    {
        if (self::$Tunnel) {
            if (self::$query_result) {
                @mysql_free_result(self::$query_result);
            }
            self::$Tunnel->close();
        } else {
            throw new Exception('error');
        }
    }

    /**
     * Database::$Query()
     *
     * @param string $query
     * @param bool $transaction
     * @return
     */
    public function Query($query = "")
    {
    	#echo $query."<br>";
    	if ($query == "") {
    		throw new Exception('no sql');
    	}else{

    		self::$query_result = self::$Tunnel->query($query);
    		// BUG the following code causes a white site and an exit;
    	/*
    		if (self::$Tunnel->error) {
    			echo "echo tunnel error";
    			throw new Exception(self::$Tunnel->error, self::$Tunnel->errno);
    		} else {
    			$rs = new RecordsetMySQL(self::$query_result);
    			return $rs;
    		}*/

    	}
    }

    public function ListTables(){
        return $this->Query('SHOW TABLES');
    }

    public function GetFieldsOfTable($tablename){

    }



}
