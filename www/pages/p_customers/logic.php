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

class P_customers extends page
{
    public function indexAction()
    {
        $Model		= new M_Customer();
    	$Usage 		= new Usage();
        $Data 		= $Model->GetAllSubCustomers(User::GetId());
        $Table 		= $Model->GetListView($Data);


    	if ( $Usage->IsFree('customers') ) {
    		$this->tpl->set( 'add_customer', Link::Button( 'customers', 'add', array(), Icon::Create( 'user--plus' ) . $this->Locale->_('Add') ) ) ;
    	} else {
    		$this->tpl->set( 'add_customer', $this->Locale->_('CapacityExceeded') ) ;
    	}


        $this->tpl->set(
        	array(
        		'heading'			=>	$this->Locale->_('CUSTOMER'),
        		'introtext'			=>	$this->Locale->_('CUSTOMER_LISTVIEW_INTRO'),
        		'table'				=>	$Table->GetHtml(),
        		'customercapacity'	=>	Progressbar::Create($Usage->Current('customers'),$Usage->Maximal('customers')),
        	)
		);

        return $this->tpl->GetHtml();

    }

	public function editAction()
	{
		$html 		= '';
		$id 		= (int) Request::Get('id');

		if ( !$this->Stm->Exists( 'cw_customers', array( 'id' => $id, 'owner_user_id' => User::GetId() ) ) )
			throw new AccessException( $this->Locale->_('notown') ) ;

		$Model 		= new M_Customer();
		$Formular 	= $Model->GetFormular();

		if ($Formular->WasSent())
		{
			try{
				$Statement = new SQL() ;
				$Formular->Validate();
				$Model->UpdateCustomer(
					$id,
					Request::Get('prename',true) ,
					Request::Get('surname',true) ,
					Request::Get('street',true) ,
					Request::Get('number',true) ,
					Request::Get('zip',true) ,
					Request::Get('city',true) ,
					Request::Get('country',true) ,
					Request::Get('phone',true) ,
					Request::Get('mobile',true) ,
					Request::Get('fax',true) ,
					Request::Get('email',true) ,
					Request::Get('website',true)
				);
				$html = Messagebox::Create( 'Kundeninformationen geändert', 'info' ) . new Link( 'customers', 'index', array(), $this->GlobalLocale->_('back'), true ) ;
			}catch(AppException $e){
				$Formular->Populate();
				$html .=  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
			}

		} else
		{
			$Data = Database::Query('Select * From cw_customers WHERE `id` = \'' . Request::Get('id', true) . '\'')->FetchArray();
			$Formular->Populate($Data);
			$html = $Formular->GetHtml();
		}

		$this->tpl->set(
			array(
				'form' 	=> $html,
				'text'	=> ''
			)
		) ;

		return $this->tpl->GetHtml() ;
	}
	public function addAction()
	{
		$html 		= '';
		$Model 		= new M_Customer();
		$Usage 		= new Usage();

		if ( !$Usage->IsFree('customers') )
			throw new AccessException( $this->Locale->_('CapacityExceeded') ) ;

		$Formular = $Model->GetFormular();

		if ($Formular->WasSent())
		{
			try{
				$Formular->Validate();

				$Statement = new SQL() ;
				$Model->InsertCustomer(
					User::GetId(),
					Request::Get('prename',true) ,
					Request::Get('surname',true) ,
					Request::Get('street',true) ,
					Request::Get('number',true) ,
					Request::Get('zip',true) ,
					Request::Get('city',true) ,
					Request::Get('country',true) ,
					Request::Get('phone',true) ,
					Request::Get('mobile',true) ,
					Request::Get('fax',true) ,
					Request::Get('email',true) ,
					Request::Get('website',true)
				);
				$html =  Messagebox::Create( 'Kundeninformationen hinzugefügt', 'info' ) . new Link( 'customers', 'index', array(), $this->GlobalLocale->_('back'), true ) ;
			}catch(Exception $e){
				$Formular->Populate();
				$html =  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
			}
		} else
		{
			$html = $Formular->GetHtml();
		}

		$this->tpl->set(
			array(
				'form' 	=> $html,
				'text'	=> ''
			)
		) ;

		return $this->tpl->GetHtml() ;
	}

	public function deleteAction()
	{

		$id = (int) Request::get('id') ;

		try{
			$Model = new M_Customer();
			$Model->DeleteCustomer($id);
			return Messagebox::Create($this->Locale->_('WillDeleted'), 'info'). new Link('customers', 'index', array(), $this->GlobalLocale->_('back'), true) ;
		}catch(AppException $e){
			return Messagebox::Create($e->getMessage(), 'error'). new Link('customers', 'index', array(), $this->GlobalLocale->_('back'), true) ;
		}

	}



}
