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


class P_email extends page{


    public function indexAction(){
    	$Model 	= new M_Mail();
        $Data 	= $Model->GetMailAdresses();
        $Usage	= new Usage();
        $Table 	= $Model->GetListView($Data) ;

    	if ( $Usage->IsFree('mail_adresses') ) {
    		$this->tpl->set( 'add_adress', Link::Button( 'email', 'addadress', array(), Icon::Create( 'mail--plus' ) . $this->Locale->_('AddAdress') ) ) ;
    	} else {
    		$this->tpl->set( 'add_adress', $this->Locale->_('AdressCapacityExceeded') ) ;
    	}
        $this->tpl->set('listview', $Table->GetHtml()) ;


        return $this->tpl->GetHtml();
    }
	public function addadressAction(){
		$html 		= '';
		$Model 		= new M_Mail();
		$Formular 	= $Model->GetForm();
		$Formular->AddDefaultActions('email');


		if ($Formular->WasSent()){
			try{
				$Formular->Validate();
				$Model->InsertAdress(
						User::GetId(),
						Request::Get( 'domain_id', true ),
						Request::Get( 'target_mails', true ),
						Request::Get( 'target_accounts', true )
					);
				$html =  Messagebox::Create( $this->Locale->_('AdressWasInsert'), 'info' ) . new Link( 'email', 'index', array(), $this->GlobalLocale->_('back'), true);
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


}