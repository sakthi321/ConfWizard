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

function ArrayFilterTrue($var)
{
    // returns whether the input integer is odd
    return ($var === true);
}

/**
 * DatabaseTable
 * this class implements the active recordset pattern
 * @package
 * @author wieschoo
 * @copyright Copyright (c) 2013
 * @version $Id$
 * @access public
 */
class DatabaseTable
{
    protected $Tablename = null;
    protected $Identifier = null;

    protected $Fields = array();
    protected $Data = array();
    protected $Dirty = array();
    protected $IsNew = false;

    /**
     * DatabaseTable::__construct()
     * query a record or create a new one (new one is created after Apply)
     * @param string $tablename name of table
     * @param intmixed $id default (null) or id of record
     */

    public function __construct($tablename, $id = null)
    {
        $this->Tablename = $tablename;
        $this->Identifier = $id;

        $this->Fields = Database::Instance()->GetFieldsOfTable($this->Tablename);
        $this->Dirty = array_fill_keys($this->Fields, false);
        $this->Data = array_fill_keys($this->Fields, null);

        if ($id !== null)
        {
            $this->Select($id);
        } else
        {
            $this->IsNew = true;
        }
    }

    /**
     * DatabaseTable::Select()
     * update the record by id
     * @param mixed $id
     * @return
     */
    public function Select($id)
    {
        $id = (int)$id;

    	$Statement = new SQL();


    	if ( !$Statement->Exists($this->Tablename, array( 'id' => $id ) ) ) {
    		throw new Exception('Record with id '.$id.' does not exists in '.$this->Tablename);
    	}

        $this->Data = Database::Query('Select * From ' . $this->Tablename .
            ' WHERE `id` = \'' . $id . '\'')->fetchArray();

    }

    /**
     * DatabaseTable::GetId()
     * returns id of current record
     * @return
     */
    public function GetId()
    {
        return $this->Identifier;
    }


    /**
     * DatabaseTable::__get()
     * returns value of field given by key or null
     * @param string $key
     * @return
     */
    public function __get($key)
    {
        if (in_array($key, $this->Fields))
        {
            return $this->Data[$key];
        } else
        {
            return null;
        }
    }

    /**
     * DatabaseTable::__set()
     * set value for field
     * @param mixed $key name of field
     * @param mixed $value value to set
     * @return
     */
    public function __set($key, $value)
    {
        if (in_array($key, $this->Fields))
        {

        	if($this->Data[$key] != $value){
        		$this->Data[$key] = $value;
        		$this->Dirty[$key] = true;
        	}

        }
    }
	/**
	 * DatabaseTable::Delete()
	 * delete current record immediately
	 * @return
	 */
	public function Delete(){
		Database::Query("DELETE FROM `" . $this->Tablename."` WHERE `id`='".$this->Identifier."'");
	}
	/**
	 * DatabaseTable::GetSQL()
	 * construct SQL Statement for the next query
	 * @return
	 */
	public function GetSQL(){
		if(!in_array(true,$this->Dirty))
			return false;
		$Statement = '';
		if ($this->IsNew)
		{

			$vals = array();

			foreach($this->Data as $k=>$v){
				if($k === 'id')
					$vals[] = 'null';
				else
					$vals[] = '\''.$v.'\'';
			}

			// create complete new entry in table
			$Statement = "INSERT INTO `" . $this->Tablename;
			$Statement .= "` (`" . implode("`, `", array_keys($this->Data)) . "`)";
			$Statement .= " VALUES (" . implode(" , ", $vals) . ") ";
		} else
		{
			// update only changed entries
			$allowed = array_filter($this->Dirty, function ($var)
			{
				return ($var === true); }
			);
			// now all affected fields => value in $tmp
			$tmp = array_intersect_key($this->Data, array_flip(array_keys($allowed)));
			$out = array();
			foreach ($tmp as $key => $value)
			{
				switch ($value)
				{
					default:
						$out[] = "`$key` = '$value'";
						break;
					case 'NOW()':
						$out[] = "`$key` = $value";
						break;
				}
			}


			$Statement = "UPDATE `{$this->Tablename}`" . PHP_EOL . "SET " . PHP_EOL . '    ' .
			    implode(',' . PHP_EOL . '    ', $out) . PHP_EOL;
			$Statement .= "WHERE" . PHP_EOL . "    `id` = '{$this->Identifier}'";
		}
		#echo $Statement;
		return $Statement;

	}
    /**
     * DatabaseTable::Apply()
     * apply changes
     * @return Recordset of query or null
     */
    public function Apply()
    {
		// constructs sql statement
    	$Statement = $this->GetSQL();
    	// if there is nothing to do, do nothing
    	if($Statement === false)
    		return null;
		// otherwise execute query
    	$rs = Database::Query($Statement);
		// record was a new one select it
    	if($this->IsNew){
    		$tmp = Database::LastId();
    		$this->Select($tmp);
    	}

		// at this point no record can be a new one
		$this->IsNew = false;
        return $rs;

    }


}
