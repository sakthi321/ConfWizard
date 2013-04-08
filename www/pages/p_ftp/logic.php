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

class P_ftp extends page
{

    public function indexAction()
    {
    	$Usage 		= new Usage();
        $Model 		= new M_FTP() ;

    	$Data 		= $this->Stm
    				->Select(array(
			                    'username',
			                    'state',
			                    'homedir',
			                    'enabled',
			                    'id'))
    				->From('cw_ftp')
    				->Where(array('user_id' => User::GetId()))
    				->Execute()
    				->fetchArraySet() ;


        $Table = $Model->GetListView($Data) ;

		$this->tpl->set(
			array(
				'current_ftp'	=> $Usage->Current('ftp_access'),
				'max_ftp'		=> $Usage->Maximal('ftp_access',true),
				'ftpcapacity'	=> Progressbar::Create($Usage->Current('ftp_access'),$Usage->Maximal('ftp_access')),
				'listview'		=> $Table->GetHtml()
			)
		);

        if( $Usage->IsFree('ftp_access')  ){
            $this->tpl->set('add_ftp', Link::Button('ftp', 'add', array(), Icon::Create('folder--plus').'FTP Benutzer anlegen')) ;
        }else{
            $this->tpl->set('add_ftp', 'Sie können keine weitere FTP Benutzer anlegen') ;
        }
        return $this->tpl->GetHtml() ;
    }
    public function addAction()
    {
        $Model 	= new M_FTP() ;
    	$Usage 	= new Usage();

        if(!$Usage->IsFree('ftp_access') )
            throw new AccessException($this->Locale->_('FTPExeed'));


        $Formular = $Model->GetAddForm() ;


    	if($Formular->WasSent()){
   			try{
   				$Formular->Validate();
   				$Model->Insert(
   					Request::Get('pwd'),
    				Request::Get('homedir',true),
   					Request::Get('enabled',true)
				);
   				$html =  Messagebox::Create($this->GlobalLocale->_('changedone'), 'info') . new Link('ftp', 'index', array(), $this->GlobalLocale->_('back'), true) ;
   			}catch(AppException $e){
   				$Formular->Populate();
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
        $id = (int)Request::get('id');
    	try{
    		$Model 	= new M_FTP() ;
    		$Model->Delete($id);
    		return Messagebox::Create($this->Locale->_('FTPWillDeleted'), 'info'). new Link('ftp', 'index', array(), $this->GlobalLocale->_('back'), true) ;
    	}catch(Exception $e){
    		return Messagebox::Create($e->getMessage(), 'error'). new Link('ftp', 'index', array(), $this->GlobalLocale->_('back'), true) ;
    	}
    }

    public function changepasswordAction()
    {

        $id = (int)Request::get('id') ;

        // test if user can apply changes
        $sql = new SQL() ;
        if (!$sql->Exists('cw_ftp', array('id' => $id, 'user_id' => User::GetId())))
        {
            throw new AccessException($this->Locale->_('notown')) ;
        }

        $Data = $sql->Select(array('username'))->From('cw_ftp')->Where(array('id' => $id))->Execute()->FetchArray() ;
        $Model = new M_FTP() ;
        $Formular = $Model->GetFTPForm($id) ;


    	if ($Formular->WasSent()){
    		try{
    			$Formular->Validate();

    			$F = new DatabaseTable('cw_ftp', $id) ;
    			$F->password = Util::crypt(Request::Get('pwd')) ;
    			$F->state = 2 ;
    			$F->Apply() ;

    			$html = Messagebox::Create($this->GlobalLocale->_('changedone'), 'info') . new Link('ftp', 'index', array(), $this->GlobalLocale->_('back'), true) ;
    		}catch(AppException $e){
    			$Formular->Populate();
    			$html =  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
    		}

    	}else{
    		$Formular->Populate($Data) ;
    		$html = $Formular->GetHtml() ;
    	}



        $this->tpl->set('form', $html) ;
        $this->tpl->set('username', $Data['username']) ;
        $this->tpl->set('text', '') ;
        return $this->tpl->GetHtml() ;
    }
    public function editAction()
    {

        $id = (int)Request::get('id') ;

        // test if user can apply changes
        $sql = new SQL() ;
        if (!$sql->Exists('cw_ftp', array('id' => $id, 'user_id' => User::GetId())))
            throw new AccessException($this->Locale->_('notown')) ;

        $Data = $sql->Select(array('homedir', 'enabled','username'))->From('cw_ftp')->Where(array('id' => $id))->Execute()->FetchArray() ;


        $Model = new M_FTP() ;
        $Formular = $Model->GetEditForm($id) ;


    	if($Formular->WasSent()){
   			try{
   				$Formular->Validate();
   				$Model->Update(
   					$id,
    				Request::Get('homedir',true),
   					Request::Get('enabled',true)
				);
   				$html =  Messagebox::Create($this->GlobalLocale->_('changedone'), 'info') . new Link('ftp', 'index', array(), $this->GlobalLocale->_('back'), true) ;
   			}catch(AppException $e){
   				$Formular->Populate();
   				$html =  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
   			}
    	}else{
    		$Formular->Populate($Data) ;
    		$html = $Formular->GetHtml() ;
    	}

        $this->tpl->set('form', $html) ;
        $this->tpl->set('text', '') ;

        $this->tpl->set('username', $Data['username']) ;
        return $this->tpl->GetHtml() ;
    }




}
