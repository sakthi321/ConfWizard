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

class P_user extends page
{
    public function indexAction()
    {

		$Usage 	= new Usage();
    	$Offers = new M_Offers();
        $Model 	= new M_User();
        $Data 	= $Model->GetUserData(User::getId());

        $this->tpl->set(array(
            'user_name' => $Data['username'],
            'user_email' => $Data['email'],
            'user_group' => $Data['group'],
            'edit_data' => Link::Button('user', 'changeemail', array(), Icon::Create('user--pencil').' bearbeiten'),
            'change_pwd' => Link::Button('user', 'changepassword', array(), Icon::Create('key--pencil').' Passwort ändern')));

    	$Capacity = $Model->GetCapacity(User::GetId());


    	if( $Usage->Maximal('users') == '0' ){
    		// do not display anything
    		$this->tpl->set('subusersection','');
    		$this->tpl->set('subusersection','');
    	}else{
    		// can use subusers

    		$SubData 		= $Model->GetSubuserData(User::getId());
    		$Table 			= $Model->GetListView($SubData);
    		$PossibleOffers = $Offers->GetOffers(User::GetId() ,true);

    		$this->tpl->set(
	    		array(
	    			'subusersection'			=>	$this->Locale->_('subusersection'),
	    			'subuserlistview' 			=>	$Table->GetHtml(),
	    			'numberofpossibleoffers' 	=>	count($PossibleOffers),
	    			'possibleoffers' 			=>	implode(',',$PossibleOffers),
	    			'usercapacity'				=>  Progressbar::Create($Usage->Current('users'),$Usage->Maximal('users')),
	    		)
    		);


    		if ( $Usage->IsFree('users') ) {
    			$this->tpl->set( 'add_user', Link::Button( 'user', 'add', array(), Icon::Create( 'user--plus' ) . $this->Locale->_('Add') ) ) ;
    		} else {
    			$this->tpl->set( 'add_user', $this->Locale->_('CapacityExceeded') ) ;
    		}


    	}



        return $this->tpl->GetHtml();
    }

    public function changepasswordAction()
    {
        $html = '';
        $User = new M_User();

        $Data = $User->GetUserData(User::GetId());
        $Formular = $User->GetPasswordForm();


    	if ($Formular->WasSent()){
    		try{
    			$Formular->Validate();
    			$AC = new DatabaseTable('cw_users', User::GetId());
    			$val = $User->HashPassword(Request::Get('pwd'));
    			$AC->salt = $val[0];
    			$AC->password = $val[1];
    			$AC->Apply();

    			// renew Session
    			User::ProcessLogin(User::GetName(), Request::Get('pwd'));
    			$html = Messagebox::Create('Änderungen durchgeführt').
                        new Link('user', 'index', array(), $this->GlobalLocale->_('back'),true);
    		}catch(AppException $e){
    			$Formular->Populate();
    			$html =  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
    		}

    	}else{
    		$Formular->Populate($Data) ;
    		$html = $Formular->GetHtml() ;
    	}



        $this->tpl->set('form', $html);
        return $this->tpl->GetHtml();

    }
    public function changeemailAction()
    {
        $html = '';
        $User = new M_User();

        $Data = $User->GetUserData(User::GetId());
        $Formular = $User->GetEmailForm();



    	if ($Formular->WasSent()){
    		try{
    			$Formular->Validate();
    			$AC = new DatabaseTable('cw_users', User::GetId());
    			$AC->email = Request::get('email',true);
    			$AC->Apply();
    			$html = Messagebox::Create('Änderungen durchgeführt').
    				new Link('user', 'index', array(), $this->GlobalLocale->_('back'),true);
    		}catch(AppException $e){
    			$Formular->Populate();
    			$html =  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
    		}

    	}else{
    		$Formular->Populate($Data) ;
    		$html = $Formular->GetHtml() ;
    	}



        $this->tpl->set('form', $html);
        return $this->tpl->GetHtml();

    }
	public function deleteAction()
	{

		$id = (int)Request::get('id') ;

		if(!defined("_LIVE_") AND  ( ($id == '1') OR ($id == '24')   )  ){
			return Messagebox::Create('im Demomodus nicht erlaubt','error');
		}

		// test if user can apply changes
		$sql = new SQL() ;
		if (!$sql->Exists('cw_users', array('id' => $id, 'owner_id' => User::GetId())))
		{
			throw new AccessException('this is not your user!') ;
		} else
		{
			$F = new DatabaseTable('cw_users', $id) ;
			$F->state = -1 ;
			$F->Apply() ;
			$html =  Messagebox::Create('Benutzer wurde zum löschen markiert.', 'info') . new Link('user', 'index', array(), $this->GlobalLocale->_('back'), true) ;
		}
	}

	public function addAction(){
		$Model = new M_User() ;

		if(!$Model->CanAddNewUser(User::GetId())){
			throw new AccessException('kann keinen weiteren Benutzer anlegen.');
		}
		$Formular = $Model->GetForm();



		$html = '';


		if ($Formular->WasSent()){
			try{
				$Formular->Validate();
				$Model->AddUser(
						Request::Get('pwd'),
						Request::Get('customer_id',true),
						Request::Get('offer_id',true),
						User::GetId()
						);
				$html =  Messagebox::Create('Benutzer angelegt', 'info') . new Link('user', 'index', array(), $this->GlobalLocale->_('back'), true) ;
			}catch(AppException $e){
				$Formular->Populate() ;
				$html = $Formular->GetHtml() ;
			}

		}else{
			$RandomPassword = User::GetRandomPassword();
			$Data = array('pwd'=>$RandomPassword,'pwd2'=>$RandomPassword);
			$Formular->Populate($Data) ;
			$html = $Formular->GetHtml() ;
		}


		$this->tpl->set('form', $html) ;
		$this->tpl->set('text', '') ;
		return $this->tpl->GetHtml() ;
	}

}
