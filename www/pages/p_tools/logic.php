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


class P_tools extends page{
	public function indexAction(){

		$Usage 		= new Usage();
		$Model 		= new M_Tools() ;

		$Data 		= $this->Stm
    				->Select(array('id','commonName','organizationName'))
    				->From('cw_certificate')
    				->Where(array('user_id' => User::GetId()))
    				->Add(' AND commonName IS NOT \'\'')
    				->Execute()
    				->fetchArraySet() ;
		$Table = $Model->SSLListView($Data) ;
        
        $HTACCESS 	= $this->Stm
    				->Select(array('id','path','comment'))
    				->From('cw_path_htaccess')
    				->Where(array('user_id' => User::GetId()))
    				->Execute()
    				->fetchArraySet() ;
        $TableHT = $Model->HtAccessListView($HTACCESS) ;

		$this->tpl->set(
		array(
    			'listview'		    => $Table->GetHtml(),
                'listview_htaccess' => $TableHT->GetHtml(),
                'csr_request'       => Link::Button( 'tools', 'certificate', array(), Icon::Create( 'receipt--plus' ) . 'SSL Zertifikat erstellen' ),
                'add_htaccess'       => Link::Button( 'tools', 'add_htaccess', array(), Icon::Create( 'shield--plus' ) . 'Verzeichnisschutz erstellen' )
    		)
		);


		return $this->tpl->GetHtml();
	}

	public function certificateAction(){
		$Model = new M_Tools();
		$Formular = $Model->CertificateForm();

		$this->tpl->set('form',$Formular->GetHtml());
		return $this->tpl->GetHtml();
	}
    
    public function add_htuserAction(){
        $id = (int) Request::Get('id');
        $hh=(string)$id;
        if ( !$this->Stm->Exists( 'cw_path_htaccess', array( 'id' => $id, 'user_id' => User::GetId() ) ) ) {
			throw new AccessException( $this->Locale->_('wrong_htacessid') ) ;
		}
        
        $Model 	= new M_Tools() ;
        $html = '';
        $Formular = $Model->GetHtUserForm($id);
    	if($Formular->WasSent()){
   			try{
				$Formular->Validate();
   				$id = $Model->AddHtUser($id,Request::Get('username'),Request::Get('password') );
   				$html = Messagebox::Create('Schutz angelegt', 'info' ) . new Link('tools', 'edit_htaccess', array('id'=>$hh), $this->GlobalLocale->_('back'), true ) ;
   			}catch(AppException $e){
   				$Formular->Populate();
				$html =  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
   			}
    	}else{
    		$Formular->Populate() ;
    		$html = $Formular->GetHtml() ;
    	}


        $this->tpl->set('form', $html ) ;
        $this->tpl->set('text', '' ) ;

        return $this->tpl->GetHtml() ;
    }
    

    
    public function edit_htuserAction(){
        $id = (int) Request::Get('ht_id');
        if ( !$this->Stm->Exists( 'cw_path_htaccess', array( 'id' => $id, 'user_id' => User::GetId() ) ) ) {
			throw new AccessException( $this->Locale->_('wrong_htacessid') ) ;
		}
        
        $name = Request::get('id');
        
        $Data = array('username'=>$name);
        
        
        $Model 	= new M_Tools() ;
        $html = '';
        $Formular = $Model->GetHtUserForm($id);
        $Formular->Sections['gen_htaccess']->DeleteElement('name');
    	if($Formular->WasSent()){
   			try{
				$Formular->Validate();
   				$id = $Model->UpdateHtUser($id,Request::Get('username'),Request::Get('password') );
   				$html = Messagebox::Create('Schutz angelegt', 'info' ) . new Link('tools', 'index', array(), $this->GlobalLocale->_('back'), true ) ;
   			}catch(AppException $e){
   				$Formular->Populate();
				$html =  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
   			}
    	}else{
    		$Formular->Populate($Data) ;
    		$html = $Formular->GetHtml() ;
    	}


        $this->tpl->set('form', $html ) ;
        $this->tpl->set('text', '' ) ;
        

        return $this->tpl->GetHtml() ;
    }
    public function delete_htuserAction()
	{
		$ht_id = (int) Request::get( 'ht_id' ) ;
        $username = (string) Request::get( 'id' ) ;
        
        
		try{
			$Model = new M_Tools();
			$Model->DeleteHtUser($ht_id,$username);
			return Messagebox::Create($this->Locale->_('htuserdelete'), 'info'). new Link('tools', 'edit_htaccess', array('id'=>$ht_id), $this->GlobalLocale->_('back'), true) ;
		}catch(Exception $e){
			return Messagebox::Create($e->getMessage(), 'error'). new Link('tools', 'edit_htaccess', array('id'=>$ht_id), $this->GlobalLocale->_('back'), true) ;
		}
	}
    
    public function edit_htaccessAction(){
        if(!Request::Exists('id')){
            throw new AccessException('missed id');
        }
        $id = (int) Request::get( 'id' ) ;
        if ( !$this->Stm->Exists( 'cw_path_htaccess', array( 'id' => $id, 'user_id' => User::GetId() ) ) )
			throw new AppException( $this->Locale->_('htaccess_notown') ) ;
        

        
        $Model 	= new M_Tools() ;
        
        $HTACCESS = $Model->GetDataFromAccessFileById($id);
        $html = '';
        $Table = $Model->HtAccessUserListView($HTACCESS,$id) ;

		$this->tpl->set(
    		array(
        			'user_grid'		 => $Table->GetHtml(),
                    'add_user'       => Link::Button( 'tools', 'add_htuser', array('id'=>$id), Icon::Create( 'user--plus' ) . 'Zugriff erstellen' )
            )
        );



		return $this->tpl->GetHtml();
    }
    
    public function add_htaccessAction(){
        $Model 	= new M_Tools() ;

        $html = '';

        $Formular = $Model->GetAddHtAccessForm();


    	if($Formular->WasSent()){
   			try{
				$Formular->Validate();
   				$id = $Model->AddHtAccess(User::GetHomeDirectory().'public'.Request::Get('path', true ), Request::Get('sectionname', true ),Request::Get('comment', true ) );
   				$html = Messagebox::Create('Schutz angelegt', 'info' ) . new Link('tools', 'add_htuser', array('id'=>$id), $this->Locale->_('htaccess_adduser'), true ) ;
   			}catch(AppException $e){
   				$Formular->Populate();
				$html =  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
   			}
    	}else{
    		$Formular->Populate() ;
    		$html = $Formular->GetHtml() ;
    	}


        $this->tpl->set('form', $html ) ;
        $this->tpl->set('text', '' ) ;

        return $this->tpl->GetHtml() ;
    }
    
    public function delete_htaccessAction(){
        $id = (int)Request::get('id') ;

    	try{
    		$Model = new M_Tools() ;
    		$Model->DeleteHtAccess($id);
    		return Messagebox::Create($this->Locale->_('htdelete'), 'info'). new Link('tools', 'index', array(), $this->GlobalLocale->_('back'), true) ;
    	}catch(AppException $e){
    		return Messagebox::Create($e->getMessage(), 'error'). new Link('tools', 'index', array(), $this->GlobalLocale->_('back'), true) ;
    	}

    }


}