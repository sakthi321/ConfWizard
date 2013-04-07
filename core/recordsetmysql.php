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

class RecordsetMySQL
{
    private $QueryResult = array();


    /**
     * Recordset::__construct()
     *
     * @param mixed $QueryResult
     * @return
     */
    public function __construct($QueryResult)
    {
        $this->QueryResult = $QueryResult;
    }

    /**
     * Recordset::__destruct()
     *
     * @return
     */
    public function __destruct()
    {
        @$this->QueryResult->free();
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

    /**
     * Recordset::FetchField()
     *
     * @return
     */
    public function FetchField()
    {
        return $this->QueryResult->fetch_field();
    }


    /**
     * Recordset::FetchArray()
     *
     * @param mixed $type
     * @return
     */
    public function FetchArray($type = MYSQLI_ASSOC)
    {
        return $this->QueryResult->fetch_array($type);
    }


    /**
     * Recordset::FetchArraySet()
     *
     * @param mixed $type
     * @return
     */
    public function FetchArraySet($type = MYSQLI_ASSOC)
    {
        $qres = $this->QueryResult;
        $Result = array();
        while ($row = $qres->fetch_array($type)) {
            $Result[] = $row;
        }
        return $Result;
    }


    /**
     * Recordset::NumberOfRows()
     *
     * @return
     */
    public function NumberOfRows()
    {
        return $this->QueryResult->num_rows;
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
