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

class RecordsetSQLite
{
    private $QueryResult = null;
    protected $FetchArraySet = null;
	protected $InsertId = null;


    /**
     * Recordset::__construct()
     *
     * @param mixed $QueryResult
     * @return
     */
    public function __construct($QueryResult,$id=null)
    {
        $this->QueryResult = $QueryResult;
    	$this->InsertId = $id;
    }

    /**
     * Recordset::__destruct()
     *
     * @return
     */
    public function __destruct()
    {
        $this->QueryResult->finalize();
    }


    /**
     * Recordset::GetError()
     *
     * @return
     */
    public function GetError()
    {
        if (!$this->QueryResult) { // prüfen auf false
            return false;
        }
        return true;
    }

	public function GetInsertId(){
		return $this->InsertId;
	}


    /**
     * Recordset::FetchArray()
     *
     * @param mixed $type
     * @return
     */
    public function FetchArray($type = SQLITE3_ASSOC)
    {
        $qres = $this->QueryResult;
        return $qres->fetchArray($type);
    }


    public function FetchField($field){
        $qres = $this->QueryResult;
        $rs = $qres->fetchArray();
        return $rs[$field];
    }

    /**
     * Recordset::FetchArraySet()
     *
     * @param mixed $type
     * @return
     */
    public function FetchArraySet($type = SQLITE3_ASSOC)
    {
        if ($this->FetchArraySet === null) {
            $qres = $this->QueryResult;
            $this->FetchArraySet = array();
            while ($row = $qres->fetchArray($type)) {
                $this->FetchArraySet[] = $row;
            }
        }
        return $this->FetchArraySet;


    }
	public function FetchFieldSet($field)
	{
		$type = SQLITE3_ASSOC;
		if ($this->FetchArraySet === null) {
			$qres = $this->QueryResult;
			$this->FetchArraySet = array();
			while ($row = $qres->fetchArray($type)) {
				$this->FetchArraySet[] = $row;
			}
		}
		$return = array();
		foreach($this->FetchArraySet as $item){
			$return[] = $item[$field];
		}

		return $return;


	}
    public function JSON(){
        return json_encode($this->FetchArraySet());
    }


    /**
     * Recordset::NumberOfRows()
     *
     * @return
     */
    public function NumberOfRows()
    {
        if($this->FetchArraySet === null)
            $this->FetchArraySet();
        return count($this->FetchArraySet);
    }

    /**
     * Recordset::NumberOfFields()
     *
     * @param integer $query_id
     * @return
     */
    public function NumberOfFields($query_id = 0)
    {
        return $this->QueryResult->field_count;
    }


}
