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

class DatabaseSQLite
{
    protected static $AccessData = array('Filename' => '');
    protected static $Tunnel;
    protected static $query_result;
    protected static $num_queries = 0;


    private static $instance;
    public static function singleton()
    {
        if (!isset(self::$instance))
        {
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
     * DatabaseSQLite::Connect()
     * open a sqlite3 database
     * @param mixed $file path to database
     * @return
     */
    public function Connect($file)
    {
        if ($file == "")
        {
            throw new Exception('wrong access data');
        }
        self::$AccessData['Filename'] = $file;
        if (is_file(self::$AccessData['Filename']) === true)
        {
            $sqliteerror = null;
            if (!self::$Tunnel = new SQLite3(self::$AccessData['Filename'], 0666, $sqliteerror))
            {
                throw new Exception($sqliteerror);
            }
        } else
        {
            throw new Exception(self::$AccessData['Filename'] . ' is not a database');
        }


    }

	/**
	 * DatabaseSQLite::LastId()
	 * returns last id
	 * @return
	 */
	public function LastId(){
		return self::$Tunnel->lastInsertRowid();
	}


    /**
     * Database::$Close()
     * disconnect
     * @return
     */
    public function Close()
    {
        if (self::$Tunnel)
        {
            self::$Tunnel->Close();
        } else
        {
            throw new Exception('error');
        }
    }


    /**
     * DatabaseSQLite::Query()
     * query a sql statement
     * @param string $query
     * @return RecordsetSQLite
     */
    public function Query($query = "")
    {
        /*$results = $db->query('SELECT bar FROM foo');
        */
        if ($query == "")
        {
            throw new Exception('no sql');
        } else
        {
        	try{
        		self::$query_result = self::$Tunnel->query($query);
        		return new RecordsetSQLite(self::$query_result);
        	}catch(Exception $e){
        		echo $e->getMessage();
        	}


        }
    }

    /**
     * DatabaseSQLite::ListTables()
     * returns recordset of tables in database
     * @return RecordsetSQLite
     */
    public function ListTables()
    {
        return $this->Query('SELECT name FROM sqlite_master WHERE type = "table"');
    }

    /**
     * DatabaseSQLite::GetFieldsOfTable()
     * returns array of fields of a given table
     * @param mixed $tablename
     * @return array
     */
    public function GetFieldsOfTable($tablename)
    {
        $fields = array();
        $raw = $this->Query("SELECT sql FROM sqlite_master WHERE tbl_name='" . $tablename .
            "'")->fetchArray();
        $raw = $raw['sql'];

        $prefix = explode('(',$raw);
        $raw = str_replace($prefix[0],'',$raw);

        $elements = explode('[',$raw);
        foreach($elements as $element){
            $temp = explode(']',$element);
            $fields[] = $temp[0];
        }
        array_shift($fields);
        return $fields;
    }


}
