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


class P_help extends Page{


    public function indexAction(){
    	$Model = new M_Help();
    	$Data = $Model->GetCategories();



    	$Table = $Model->GetListViewCategories($Data);

    	$this->tpl->set('listview',$Table->GetHtml());
    	$this->tpl->set('licenselink',Link::Create('help','license',array(),'Lizenz'));

    	return $this->tpl->GetHtml();

    }
	public function opencategorieAction(){

		$CategorieId = Request::Get('id');

		$Model = new M_Help();
		$QA = $Model->GetQA($CategorieId);

		$html = '<h1>'.$this->Locale->_('Title').'</h1><h2>'.$Model->GetNameById($CategorieId).'</h2>';

		foreach($QA as $Entry){
			$html .= '<h3>'.$Entry['question'].'</h3>';
			$html .= '<div>'.nl2br($Entry['answer']).'</div>';

		}
		return $html;
	}
	public function licenseAction(){
		return '<h1>License</h1>'.nl2br(file_get_contents('license.txt'));
	}

}