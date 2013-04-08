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

		$this->tpl->set(
		array(
		#	'current_ftp'	=> $Usage->Current('ftp_access'),
		#	'max_ftp'		=> $Usage->Maximal('ftp_access',true),
		#	'ftpcapacity'	=> Progressbar::Create($Usage->Current('ftp_access'),$Usage->Maximal('ftp_access')),
			'listview'		=> $Table->GetHtml()
		)
		);


		$this->tpl->set( 'csr_request', Link::Button( 'tools', 'certificate', array(), Icon::Create( 'receipt--plus' ) . 'SSL Zertifikat erstellen' ) ) ;
		return $this->tpl->GetHtml();
	}

	public function certificateAction(){
		$Model = new M_Tools();
		$Formular = $Model->CertificateForm();

		$this->tpl->set('form',$Formular->GetHtml());
		return $this->tpl->GetHtml();
	}


}