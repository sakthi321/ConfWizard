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

class P_login extends page
{
    public function PreProcessor(){
        // change temple into msg template
        global $MASTER_TPL, $config;
        $MASTER_TPL = new Template('template/msg_template.html');

        // check bruteforce
        $now = time();
        $time = $now - $config['security']['max_login_bantime'];

        $sql = new SQL();
        $attemps = $sql ->Select('*')
                        ->From('cw_loginhistory')
                        ->Where(array('ip'=>Request::GetIp(),'result'=>'0'))
                        ->Where('`time` > \''.Database::Time($time).'\'')
                        ->Execute()
                        ->NumberOfRows();

        // send header
        if($attemps>$config['security']['max_login_tries_per_interval']){
            Header::StatusCode(403,true); // send  403 and exit!
        }


    }
    // login page
    public function indexAction()
    {
        $html = '';
        $this->tpl->set('error','');

        $Formular = $this->GetLoginForm();

    	if ($Formular->WasSent()){
    		try{
    			$Formular->Validate();
    			// prepare login history statistics
    			$LH = new DatabaseTable('cw_loginhistory');
    			$LH->username = Request::Get('username');
    			$LH->ip = Request::GetIp();
    			$LH->time = Database::Now();
    			$LH->action = 'login';

    			if (User::ProcessLogin(Request::Get('username', true), Request::Get('password')))
    			{
    				// login allowed
    				$LH->result = '1';
    				$LH->Apply();
    				$this->stop();
    				header('Location: index.php');
    			} else
    			{
    				// wrong access !
    				$LH->result = '0';
    				$LH->Apply();
    				throw new AppException('Login fehlgeschlagen');
    			}
    		}catch(AppException $e){
    			$Formular->Populate();
    			$html =  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
    		}

    	}else{
    		$Formular->Populate();
    		$html = $Formular->GetHtml();
    	}
    	return $html;
    }

    public function logoutAction()
    {
        User::ProcessLogout();
        Header::Location('index.php?page=user&action=index');
    }

	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	protected function GetLoginForm(){
		$Form = new Form();

		$Form->AddElementsFromArray(
			array(
				array(
					'type' => 'text',
					'name' => 'username',
					'value' => '',
					'label' => 'Name',
					'validators' => array(new EmptyValidator(), new MaxLengthValidator(100)),
					'size' => 40
				),
				array(
					'type' => 'password',
					'name' => 'password',
					'value' => '',
					'label' => 'Passwort',
					'validators' => array(new EmptyValidator(), new MaxLengthValidator(100)),
					'size' => 40
				)

			),
			'Zugangsdaten'
		);
		$Form->AddElementsFromArray(
			array(
				array(
					'type' => 'submit',
					'name' => 'login',
				)
			),
			'Aktionen'
		);


		return $Form;
		/*
		$Formular->AddElementsFromArray(array(
		    array(
		        'type' => 'text',
		        'name' => 'username',
		        'value' => '',
		        'label' => 'Name:',
		        'validators' => array(new EmptyValidator(), new MaxLengthValidator(100)),
		        'size' => 40),
		   array(
		        'type' => 'Password',
		        'name' => 'password',
		        'value' => '',
		        'label' => 'Password',
		        'validators' => array(new EmptyValidator(), new MaxLengthValidator(100)),
		        'size' => 40)
		   )
		, 'Zugangsdaten');

		$Formular->AddElementsFromArray(array(array(
		        'type' => 'Submit',
		        'name' => 'login',
		        'value' => 'Login')), 'Aktionen');*/

		return $Formular;
	}




}
