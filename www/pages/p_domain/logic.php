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

class P_domain extends page {

	public function indexAction()
	{

		$Model 			= new M_Domain();
		$Usage 			= new Usage();
		$Data 			= $Model->GetDomainData( User::GetId() );

		$DomainTable 	= $Model->GetListViewDomains( $Data ) ;
		$DomainTable->SetJQSettings( 'bFilter', false );

		$SubdomainTable = $Model->GetListViewSubdomains( $Model->GetSubdomains( User::GetId() ) ) ;

		if ( $Usage->IsFree('subdomains') ) {
			$this->tpl->set( 'add_subdomain', Link::Button( 'domain', 'addsubdomain', array(), Icon::Create( 'globe--plus' ) . $this->Locale->_('AddSubdomain') ) ) ;
		} else {
			$this->tpl->set( 'add_subdomain', $this->Locale->_('SubDomainCapacityExceeded') ) ;
		}
		if ( $Usage->IsFree('domains') ) {
			$this->tpl->set( 'add_domain', Link::Button( 'domain', 'add', array(), Icon::Create( 'globe--plus' ) . $this->Locale->_('AddDomain') ) ) ;
		} else {
			$this->tpl->set( 'add_domain', $this->Locale->_('DomainCapacityExceeded') ) ;
		}

		$this->tpl->set(
			array(
				'current_subdomains'	=>	 	$Usage->Current('subdomains'),
				'max_subdomains'		=>	 	$Usage->Maximal('subdomains'),
				'capacity'				=>		Progressbar::Create($Usage->Current('domains'),$Usage->Maximal('domains')),
				'subcapacity'			=>		Progressbar::Create($Usage->Current('subdomains'),$Usage->Maximal('subdomains')),
				'subcapacityself'		=>		$Usage->Current('subdomains','self'),
				'capacityself'			=>		$Usage->Current('domains','self'),
				'subcapacitychilds'		=>		$Usage->Current('subdomains','childs'),
				'capacitychilds'		=>		$Usage->Current('domains','childs'),
				'listview'				=>	 	$DomainTable->GetHtml(),
				'listviewsub'			=> 		$SubdomainTable->GetHtml()
			)
		);

		return $this->tpl->GetHtml();
	}
	public function editAction()
	{

		$id 		= (int)Request::get( 'id' ) ;
		$html 		= '';

		if ( !$this->Stm->Exists( 'cw_domains', array( 'id' => $id, 'owner_id' => User::GetId() ) ) ) {
			throw new AccessException( 'this is not your domain!' ) ;
		}

		$Data 		= $this->Stm->Select('*')
								->From('cw_domains')
								->WHERE(array( 'id' => $id, 'owner_id' => User::GetId() ))
								->Execute()
								->FetchArray();
		$Model 		= new M_Domain();


		$Formular 	= $Model->GetForm($id);
		$Formular->AddDefaultActions('domain');



		if ($Formular->WasSent()){
			try{
				$Formular->Validate();
				$Model->UpdateDomain(
						User::GetId(),
						$id,
						Request::Get( 'user_id', true ),
						Request::Get( 'name', true ),
						Request::Get( 'tld', true ),
						Request::Get('linked_subs',true)
					);
				$html =  Messagebox::Create( $this->Locale->_('DomainWasUpdate'), 'info' ) . new Link( 'domain', 'index', array(), $this->GlobalLocale->_('back'), true);
			}catch(AppException $e){
				$Formular->Populate();
				$html =  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
			}

		}else{
			$Formular->Populate($Data) ;
			$html = $Formular->GetHtml() ;
		}

		$this->tpl->set(
			array(
				'form'	=> $html
			)
		);
		return $this->tpl->GetHtml();
	}
	public function addAction()
	{
		$html 		= '';
		$Model 		= new M_Domain();
		$Formular 	= $Model->GetForm();
		$Formular->Sections['Domain Einstellungen']->DeleteElement('linked_subs');
		$Formular->AddDefaultActions('domain');


		if ($Formular->WasSent()){
			try{
				$Formular->Validate();
				$Model->InsertDomain(
						Request::Get( 'user_id', true ),
						User::GetId(),
						Request::Get( 'name', true ),
						Request::Get( 'tld', true )
					);
				$html =  Messagebox::Create( $this->Locale->_('DomainWasInsert'), 'info' ) . new Link( 'domain', 'index', array(), $this->GlobalLocale->_('back'), true);
			}catch(AppException $e){
				$Formular->Populate();
				$html =  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
			}

		}else{
			$Formular->Populate() ;
			$html = $Formular->GetHtml() ;
		}

		$this->tpl->set(
		array(
			'form'	=> $html
		)
		);
		return $this->tpl->GetHtml();
	}

	public function deletesubdomainAction()
	{
		$id = (int) Request::get( 'id' ) ;
		try{
			$Model = new M_Domain();
			$Model->DeleteSubdomain($id);
			return Messagebox::Create($this->Locale->_('SubWillDeleted'), 'info'). new Link('domain', 'index', array(), $this->GlobalLocale->_('back'), true) ;
		}catch(Exception $e){
			return Messagebox::Create($e->getMessage(), 'error'). new Link('domain', 'index', array(), $this->GlobalLocale->_('back'), true) ;
		}
	}
	public function editsubdomainAction()
	{

		$html 	= '';
		$id 	= (int) Request::get( 'id' ) ;
		$did 	= (int) Request::get( 'domain_id' ) ;
		$name 	= strtolower( Request::get( 'name', true ) );

		if ( !$this->Stm->Exists( 'cw_subdomains', array( 'id' => $id, 'user_id' => User::GetId() ) ) )
			throw new AccessException( $this->Locale->_('WrongDomainId') ) ;

		$Model 	= new M_Domain();
		$Data 	= $this->Stm->Select( '*' )->From( 'cw_subdomains' )->Where( array( 'id' => $id ) )->Execute()->FetchArray() ;


		$Formular 	= $Model->GetSDForm($id);
		$Formular->AddDefaultActions('domain');

		if ($Formular->WasSent()){
			if($Formular->Input('type') == '1'){
				$Formular->Sections['domain_redirect']->active = true;
				$Formular->Sections['domain_host']->active = false;
			}else{
				$Formular->Sections['domain_redirect']->active = false;
				$Formular->Sections['domain_host']->active = true;
			}

			try{
				$Formular->Validate();

				if(Request::Get( 'type', true ) == '1')
					$path = Request::Get( 'redirect_path', true );
				else
					$path = Request::Get( 'host_path', true );

				$Model->UpdateSubdomain(
						$id,
						$did,
						$name,
						$path,
						Request::Get( 'type', true ),
						Request::Get( 'redirect_code', true )
				);

				$html =  Messagebox::Create( 'Subdomain wurde geändert.', 'info' ) . new Link( 'domain', 'index', array(), $this->GlobalLocale->_('back'), true ) ;
			}catch(AppException $e){
				$Formular->Populate();
				$html =  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
			}

		}else{
			$Formular->Populate( $Data) ;
			if($Data['type'] == '1'){
				$Formular->Sections['domain_redirect']->active = true;
				$Formular->Sections['domain_host']->active = false;
				$Formular->Sections['domain_redirect']->Elements['redirect_path']->value = $Data['path'];
			}else{
				$Formular->Sections['domain_redirect']->active = false;
				$Formular->Sections['domain_host']->active = true;
				$Formular->Sections['domain_host']->Elements['host_path']->value = $Data['path'];
			}


			$html = $Formular->GetHtml() ;
		}
/*

   if($Data['type'] == '1'){
   $Formular->SetVisible($this->Locale->_('domain_redirect'),true);
   $Formular->SetVisible($this->Locale->_('domain_host'),false);
   $Formular->SetValue('redirect_path',$Data['path']);
   }else{
   $Formular->SetVisible($this->Locale->_('domain_redirect'),false);
   $Formular->SetVisible($this->Locale->_('domain_host'),true);
   $Formular->SetValue('host_path',$Data['path']);
   }

   */



		$this->tpl->set( 'form', $html ) ;
		$this->tpl->set( 'text', '' ) ;

		return $this->tpl->GetHtml() ;
	}
	public function addsubdomainAction()
	{
		if ( Request::Exists( 'domain_id' ) AND !$this->Stm->Exists( 'cw_domains', array( 'id' => Request::get( 'domain_id' ), 'user_id' => User::GetId() ) ) ) {
			throw new AccessException( 'this is not your domain!' ) ;
		}

		$Model = new M_Domain();
		$html = '';


		$name = strtolower( Request::get( 'name', true ) );

		$Data = array();

		$did = (int)  Request::get( 'domain_id' );



		$Formular = $Model->GetSDForm();
		$Formular->AddDefaultActions('domain');




		if ($Formular->WasSent()){
			try{
				$Formular->Populate( ) ;
				if($Formular->GetInput('type') == '1'){
					$Formular->Sections['domain_redirect']->active = true;
					$Formular->Sections['domain_host']->active = false;
				}else{
					$Formular->Sections['domain_redirect']->active = false;
					$Formular->Sections['domain_host']->active = true;
				}

				$Formular->Validate();

				if(Request::Get( 'type', true ) == '1')
					$path = Request::Get( 'redirect_path', true );
				else
					$path = Request::Get( 'host_path', true );


				$Model->InsertSubdomain(
						User::GetId(),
						$did,
						$name,
						$path,
						Request::Get( 'type', true ),
						Request::Get( 'redirect_code', true )
						);

				$html =  Messagebox::Create( $this->Locale->_('SubDomainWasUpdate'), 'info' ) . new Link( 'domain', 'index', array(), $this->GlobalLocale->_('back'), true ) ;
			}catch(AppException $e){
				$Formular->Populate();
				$html =  Messagebox::Create( $e->getMessage(), 'error' ). $Formular->GetHtml();
			}

		}else{


			$Formular->Populate( $Data) ;
			$html = $Formular->GetHtml() ;
		}



		$this->tpl->set( 'form', $html ) ;
		$this->tpl->set( 'text', '' ) ;

		return $this->tpl->GetHtml() ;
	}
}

