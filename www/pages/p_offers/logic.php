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


class P_offers extends page{

    public function indexAction(){
		$Model 	= new M_Offers();
    	$Usage 	= new Usage();


		$Data = $this->Stm->Select('*')
    			->From('cw_offers' )
    			->Where(array('user_id' => User::GetId() ) )
    			->Execute()
    			->fetchArraySet() ;

    	$Table = $Model->GetListView($Data);

    	$this->tpl->set(
    		array(
    			'offers_view'	=> $Table->GetHtml(),
    			'current'		=>	$Usage->Current('offers'),
    			'current_o'		=>	$Usage->Current('offers','self'),
    			'max'			=>	$Usage->Maximal('offers',true),
    			'pgr'			=>  Progressbar::Create($Usage->Current('offers'),$Usage->Maximal('offers')),
    		)
    	);
    	if ($Usage->IsFree('offers') ) {
    		$this->tpl->set('add_offer', Link::Button('offers', 'add', array(), Icon::Create('paper-bag--plus' ) . 'Angebot anlegen' ) ) ;
    	} else {
    		$this->tpl->set('add_offer', 'Sie können keine weitere Angebote anlegen' ) ;
    	}

    	return $this->tpl->GetHtml();
    }
	public function deleteAction()
	{


		$id = (int)Request::get('id') ;

		try{
			$Model = new M_Offers();
			$Model->Delete($id );
			return Messagebox::Create('Angebotsvorlage wurde gelöscht', 'info' ) . new Link('offers', 'index', array(), $this->GlobalLocale->_('back'), true ) ;
		}catch(AppException $e){
			return Messagebox::Create($e->getMessage(), 'error'). new Link('offers', 'index', array(), $this->GlobalLocale->_('back'), true) ;
		}

	}

	public function addAction()
	{
		$Usage 		= new Usage();
		if (!$Usage->IsFree('offers') )
			return Messagebox::Create('kann keinen weitere Angebotsvorlage anlegen.','error' ) . new Link('offers', 'index', array(), $this->GlobalLocale->_('back'), true ) ;




		$Model 		= new M_Offers();

		$Formular = $Model->GetForm( ) ;


		if ($Formular->WasSent()){
			try{
				$Formular->Validate();
				$Model->Insert(User::GetId());
				$html = Messagebox::Create('Angebotsvorlage hinzugefügt', 'info' ) . new Link('offers', 'index', array(), $this->GlobalLocale->_('back'), true ) ;
			}catch(AppException $e){
				$Formular->Populate();
				$html =  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
			}

		}else{
			$Formular->Populate() ;
			$html = $Formular->GetHtml() ;
		}

		$this->tpl->set('form', $html ) ;
		$this->tpl->set('name', '' ) ;
		$this->tpl->set('text', '' ) ;

		return $this->tpl->GetHtml() ;
	}

}