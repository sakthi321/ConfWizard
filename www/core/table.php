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

interface CellProcessor
{
    public function Process($data,$row) ;
}
class StateProcessor implements CellProcessor
{
    public function Process($data,$row)
    {
        switch ($data)
        {
            case '2':
                return Icon::Create('arrow-retweet').' wird aktualisert' ;
                break ;
            case '1':
                return Icon::Create('asterisk').' wird angelegt' ;
                break ;
            case '0':
                return Icon::Create('tick').' aktuell' ;
                break ;
            case '-1':
                return Icon::Create('cross').' wird gelöscht' ;
                break ;
        }
    }
}
class ActiveProcessor implements CellProcessor
{
    public function Process($data,$row)
    {
        switch ($data)
        {
            case '0':
                return Icon::Create('application').' nein' ;
                break ;
            case '1':
                return Icon::Create('application-blue').' ja' ;
                break ;
        }
    }
}

class EmptyProcessor implements CellProcessor
{
    public function Process($data,$row)
    {
        return $data ;
    }
}

class Table
{

    protected $DataArray = null ;
    protected $CellProcessors = null ;
    protected $ActionProcessor = null;
    protected $Head = null ;
    protected $Keys = null ;
    protected $Actions = null ;
    protected $JQSettings = array(
        'bPaginate' => false,
        'bLengthChange' => false,
        'bFilter' => true,
        'bSort' => true,
        'bInfo' => false,
        'bAutoWidth' => false,
        "bProcessing" => false,
        "sAjaxSource" => '') ;

	protected $confirm = array();

    public function SetProcessor($k, $p)
    {
        $this->CellProcessors[$k] = $p ;
    }
    public function SetActionProcessor($p)
    {
        $this->ActionProcessor = $p ;
    }
    public function SetJQSettings($k, $v)
    {
        $this->JQSettings[$k] = $v ;
    }
    public function SetAjaxSource($src)
    {
        $this->SetJQSettings('bProcessing', 'true') ;
        $this->SetJQSettings('sAjaxSource', $src) ;
    }

    public function __construct($head = array(), $data = null)
    {
        $this->ActionProcessor = new EmptyProcessor();
        if ($data !== null)
        {
            $this->DataArray = $data ;
            $this->Keys = array_keys($this->DataArray) ;
        }
        if ($head !== array()) $this->SetHead($head) ;

    }
    public function SetHead($head)
    {
        $this->Keys = array_keys($head) ;
        $this->Head = array_values($head) ;
        $this->CellProcessors = array_fill_keys($this->Keys, new EmptyProcessor()) ;
    }

	public function SetConfirm($num,$val=true){
		$this->confirm[$num] = $val;
	}

    public function SetActions($arr)
    {
        $this->Actions = $arr ;
    }

    public function JSON()
    {

        $data = '{
    "sEcho": 3,
    "iTotalRecords": 57,
    "iTotalDisplayRecords": 57,
    "aaData": [
        ' ;


        $d = array();

        if ($this->DataArray !== null)
        {
            $counter = 0;
            foreach ($this->DataArray as $entry)
            {
                $item = array('"DT_RowId" : "row_'.$counter.'"','"DT_RowClass" : "gradeA"');
                $counterc = 0;
                foreach($this->Keys as $k){
                    $display = $this->CellProcessors[$k]->Process($entry[$k],$entry) ;
                    $item[] = '"'.$counterc.'" : "'.addslashes($display).'"';
                    $counterc++;
                }
                $actions = '';
                if ($this->Actions !== null)
                {
                    $ac = $this->Actions ;
                    foreach ($ac as $a)
                    {
                        $a->SetParameterValue('id', $entry['id']) ;

                        $actions .= $a->GetHtml() . ' ' ;

                    }



                }


                $item[] = '"'.$counterc.'" : '.'"'.addslashes($actions).'"';
                $counter++;
                $d[] = '{   '.implode(','.PHP_EOL.'            ',$item).PHP_EOL.'        }';
            }
        }
        $data .= implode(','.PHP_EOL.'        ',$d);


        $data .= '
    ]
}' ;
return $data;
    }


    public function GetHtml()
    {
        $id = md5(uniqid(mt_rand(1, 20))) ;
        $html = '' ;
        $html .= '<table id="' . $id . '"  cellpadding="0" cellspacing="0">
  <thead>
    <tr>
      <th>' ;
        $html .= implode('</th>
      <th>', $this->Head) ;
        if (count($this->Actions) > 0) $html .= '<th>Aktionen</th>' ;
        #$html .= str_repeat('<th>&nbsp;</th>',count($this->Actions));

        $html .= '</tr>
  </thead>' ;

        if ($this->DataArray !== null)
        {
            $html .= '
  <tbody>' ;
            foreach ($this->DataArray as $entry)
            {
                $html .= '<tr>' ;


                foreach ($this->Keys as $k)
                {
                    $display = $this->CellProcessors[$k]->Process($entry[$k],$entry) ;
                    $html .= '<td>' . $display . '</td>' ;
                }
                /*$html .= '<tr><td>';
                $e = array_intersect_key($entry, array_flip($this->Keys));
                $html .= implode('</td>
                <td>', $e);
                $html .= '</td>';*/

                if ($this->Actions !== null)
                {
                    $html .= '<td>' ;
                    $ac = $this->Actions ;
                    foreach ($ac as $a)
                    {
                        $a->SetParameterValue('id', $entry['id']) ;

                        $html .= $this->ActionProcessor->Process($a->GetHtml(),$entry). ' ' ;

                    }
                    $html .= '</td>' ;

                }

                $html .= '</tr>' ;

            }
            $html .= ' </tbody>' ;

        }
        $html .= '
</table>' ;

        $html .= '<script type="text/javascript" charset="utf-8">
		$(document).ready(function() {
    $(\'#' . $id . '\').dataTable(' ;

        $data = $this->JQSettings ;
        if ($data['bProcessing'] === false)
        {
            unset($data['bProcessing']) ;
            unset($data['sAjaxSource']) ;
        }

        $html .= json_encode($data) ;
        $html .= ');
} );
		</script>' ;


        return $html ;
    }
}
