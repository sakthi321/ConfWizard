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
class SQL
{

    protected $Statement = '';
    protected $whered = false;
    protected $last = null;
	protected $Stack = array();

    public function __construct()
    {

    }

    public function Clear(){
        $this->last = null;
        $this->Statement = '';
    }

    public function Count()
    {

        $this->Statement = 'SELECT count(*) as num ';
        $this->whered = false;
        return $this;
    }
	public function Sum($field)
	{

		$this->Statement = 'SELECT sum('.$field.') as '.$field.' ';
		$this->whered = false;
		return $this;
	}
    public function Select($txt,$ticks=true,$alias=null)
    {
    	$a = '';
    	if($alias !== null)
    		$a = ' as '.$alias.' ';

        if($this->last === null)
            $this->Statement = 'SELECT';
        if($this->last === 'select')
            $this->Statement .= ', ';
        $t = '';
        if($ticks)
            $t = '`';

        if (is_array($txt))
            $this->Statement .= ' '.$t . implode($t.' , '.$t, $txt) . $t;
    	else{
    		$this->Statement .= ' ' . $txt.$a;
    	}

        $this->whered = false;


        $this->last = 'select';
        return $this;

    }

    public function Concate($arr,$alias=null){
    	$a = '';
    	if($alias !== null)
    		$a = ' as '.$alias.' ';

        if($this->last === 'select')
            $this->Statement .= ', ';
        $this->Statement .= ' '.implode(' || ',$arr).' '.$a;
        $this->last = 'concat';
        return $this;

    }
    public function Label($as){
        $this->Statement .= ' AS '.$as;
        $this->last = 'as';
        return $this;
    }
    public function Add($as){
        $this->Statement .=' '.$as.' ';
        return $this;
    }

    public function From($table,$alias=null)
    {
        $a = '';
        if($alias !==null)
            $a = ' as '.$alias.' ';
        $this->Statement .= ' FROM `' . $table . '` '.$a;
        $this->last = 'from';
    	$this->Stack[] = $table;
        return $this;
    }
    public function Join($table,$alias=null,$type='INNER')
    {
        $a = '';
        if($alias !==null)
            $a = ' as '.$alias.' ';
        $this->Statement .= ' '.$type.' JOIN `' . $table . '` '.$a;
        $this->last = 'join';
        return $this;
    }
    public function On($a,$b,$rel='=')
    {
        $this->Statement .= ' ON '.$a.' = ' . $b . ' ';
        $this->last = 'on';
        return $this;
    }

	public function WhereIn($Column,$Array,$ticks='`'){
		if ($this->whered === false)
		{
			$this->Statement .= ' WHERE ';
			$this->whered = true;
		} else
		{
			$this->Statement .= ' AND ';
		}

		$this->Statement .=' '.$ticks.$Column.$ticks.' IN (\''.implode('\',\'',$Array).'\') ';


		$this->last = 'where';
		return $this;
	}

    public function Where($arr, $ticks=false,$rel='=')
    {

        $t = '';
        if ($this->whered === false)
        {
            $this->Statement .= ' WHERE ';
            $this->whered = true;
        } else
        {
            $this->Statement .= ' AND ';
        }
        if (is_array($arr))
        {
            $adds = array();
            foreach ($arr as $k => $v)
            {
                if ($ticks)
                {
                    $adds[] .= $t . $k . $t.' '.$rel.' \'' . Request::Escape($v) . '\'';
                } else
                {
                    $adds[] .= $t . $k . $t.' '.$rel.' \'' . $v . '\'';
                }

            }
            $this->Statement .= implode(' AND ', $adds);

        } else
        {
            $this->Statement .= $arr;
        }

        $this->last = 'where';
        return $this;
    }
    public function __toString()
    {
        return $this->Statement;
    }

    public function Execute()
    {
        $s = $this->Statement;
    	#echo $s.'<br>';
        $this->Clear();
    	try{
    		return Database::Query($s);
    	}catch(Exception $e){
    		echo $e->getMessage().'<br>'.$s;
    	}

    }

    public function Exists($table,$ifs){
        $this->Statement = '';
        return ($this->Select('*')->From($table)->Where($ifs)->Execute()->NumberOfRows()>0);
    }

}
