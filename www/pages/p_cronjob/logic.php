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

class P_cronjob extends page{


    public function indexAction(){
    	$Usage 			= new Usage();
    	$Model 			= new M_Cronjob();

        $Data = $this->Stm
        		->Select(array(
                    'command',
                    'comment',
                    'enabled',
                    'format',
                    'state',
                    'id'))
        		->From('cw_cronjobs')
        		->Where(array('user_id' => User::GetId()))
        		->Execute()
        		->fetchArraySet() ;


        $Table = $Model->GetListView($Data) ;


        if( $Usage->IsFree('cronjobs')  ){
            $this->tpl->set('add_cronjob', Link::Button('cronjob', 'add', array(), Icon::Create('clock--plus').$this->Locale->_('Add'))) ;
        }else{
            $this->tpl->set('add_cronjob', $this->Locale->_('CapacityExceeded')) ;
        }


    	$this->tpl->set(
    		array(
    			'current_cronjobs'		=>	$Usage->Current('cronjobs','self'),
    			'pgr_cronjobs'			=>	Progressbar::Create($Usage->Current('cronjobs'),$Usage->Maximal('cronjobs')),
    			'listview'				=>	$Table->GetHtml()
    		)
		);



        return $this->tpl->GetHtml();
    }
    public function addAction(){

        $this->tpl->set('init', '0 2 * * *') ;
        $Model 	= new M_Cronjob() ;
    	$Usage 	= new Usage();

        if(!$Usage->IsFree('cronjobs')){
        		return Messagebox::Create($this->Locale->_('CapacityExceeded'),'error')
        			. new Link('cronjob', 'index', array(), $this->GlobalLocale->_('back'), true) ;

        }
        $Formular = $Model->GetForm();

        $html = '';

    	if ($Formular->WasSent()){
    		try{
    			$Formular->Validate();
    			$Model->AddCronjob(
					Request::Get('format',true),
					Request::Get('enabled') ,
					Request::Get('comment',true) ,
					Request::Get('command',true) ,
					User::GetId()
    			);
    			$html = Messagebox::Create('Cronjob angelegt', 'info') . new Link('cronjob', 'index', array(), $this->GlobalLocale->_('back'), true) ;
    		}catch(AppException $e){
    			$Formular->Populate();
    			$this->tpl->set('init', Request::Get('format')."") ;
    			$html =  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
    		}

    	}else{
    		$Formular->Populate() ;
    		$html = $Formular->GetHtml() ;
    	}


        $this->tpl->set('form', $html) ;
        $this->tpl->set('text', '') ;
        return $this->tpl->GetHtml() ;
    }

    public function deleteAction()
    {

        $id = (int)Request::get('id') ;

    	try{
    		$Model = new M_Cronjob() ;
    		$Model->DeleteCronjob($id);
    		return Messagebox::Create($this->Locale->_('WillDeleted'), 'info'). new Link('cronjob', 'index', array(), $this->GlobalLocale->_('back'), true) ;
    	}catch(AppException $e){
    		return Messagebox::Create($e->getMessage(), 'error'). new Link('domain', 'index', array(), $this->GlobalLocale->_('back'), true) ;
    	}

    }

    public function editAction(){

        $html = '';
		$id = (int) Request::get('id') ;

    	$Statement = new SQL() ;

        if (!$Statement->Exists('cw_cronjobs', array('id' => $id, 'user_id' => User::GetId())))
        {
        	return Messagebox::Create($this->Locale->_('notown'),'error')
        		. new Link('cronjob', 'index', array(), $this->GlobalLocale->_('back'), true) ;
        }

    	$Model = new M_Cronjob();


        $Data = $Statement
        		->Select(array('command',
                    'comment',
                    'enabled',
                    'format',
                    'state',
                    'id'))
        		->From('cw_cronjobs')
        		->Where(array('id' => $id))
        		->Execute()
        		->FetchArray() ;



        $this->tpl->set('init', $Data['format']) ;



        $Formular = $Model->GetForm($id);
      	$Formular->Sections['Aktionen']->Elements['submit']->value = 'ändern';


    	if ($Formular->WasSent()){
    		try{
    			$Formular->Validate();
    			$Model->UpdateCronjob(
					$id,
					Request::Get('format',true),
					Request::Get('enabled',true) ,
					Request::Get('comment',true) ,
					Request::Get('command',true)
				);
    			$html = Messagebox::Create($this->GlobalLocale->_('changedone'), 'info'). new Link('cronjob', 'index', array(), $this->GlobalLocale->_('back'), true) ;
    		}catch(AppException $e){
    			$Formular->Populate();
    			$this->tpl->set('init', Request::Get('format')."") ;
    			$html =  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
    		}

    	}else{
    		$Formular->Populate($Data) ;
    		$html = $Formular->GetHtml() ;
    	}


        $this->tpl->set('form', $html) ;
        $this->tpl->set('text', '') ;
        return $this->tpl->GetHtml() ;
    }

}