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


class M_Help
{

	protected $XMLData = null;
	protected $Language = 'de';

	public function __construct($language='de'){
		$this->GlobalLocaleuage = $language;
		$file = 'pages/p_help/'.$this->GlobalLocaleuage.'_data.xml';

		if (is_file($file))
			if ($this->XMLData = simplexml_load_file($file))
				return true;
		return false;
	}

	public function GetListViewCategories($data = array() )
	{
		$Table = new Table(array(// 'id' => 'Id',
		        'title' => 'Kategorie'
		        ), $data ) ;

		$Table->SetJQSettings('bFilter',false );
		$Table->SetActions(array(new Link('help', 'opencategorie', array('id' => '' ), Icon::Create('book--arrow' ), false )
		  ) ) ;

		return $Table ;
	}

	public function GetCategories(){

		$path = "/help/categorie";
		$data = $this->XMLData->xpath($path);

		$Return  = array();
		foreach($data as $entry){
			$Return[] = array('id'=>(string)$entry['id'],'title'=>(string)$entry['title']);
		}
		return $Return;

	}

	///////////////////////////////////
	public function GetNameById($cat_id){
		$path = "/help/categorie[@id=\"".$cat_id."\"]/@title";
		$res = $this->XMLData->xpath($path);
		return $res[0];
	}
	public function GetQA($cat_id){

		$path = "/help/categorie[@id=\"".$cat_id."\"]/item";
		$data = $this->XMLData->xpath($path);

		$Return  = array();
		foreach($data as $entry){
			$Return[] = array('question'=>(string)$entry->question,'answer'=>(string)$entry->answer);
		}
		return $Return;


	}

}