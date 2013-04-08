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


class homedirs implements command {
	protected $Env = array();
	public function Execute(Log $log,$Enviroment)
	{
		$this->Env = $Enviroment;
		$Stm = new SQL();
		$Users = $Stm
					->Select('*')
					->From('cw_users')
					->Where(array('state'=>'0'),true,'<>')
					->Execute()
					->FetchArraySet();



		foreach($Users as $User){
			switch($User['state']){
				default:
					$log->Write('unkown state ( '.$User['state'].' ) for '.$User['username']);
					break;
				case '1':
					$log->Write('add home dir for '.$User['username']);
					$this->AddUser($User);
					break;
				case '-1':
					$log->Write('add home dir for '.$User['username']);
					$this->DelUser($User);
					break;

			}
		}


	}

	protected function AddUser($Data){
		if(defined("_LIVE_")){
			global $Enviroment;
			$directory = $Enviroment['webserver']['data']['customer_homedirectories'].'/'.$Data['username'];
			System::MakeDirectory($directory,'www-data');
			foreach($Enviroment['webserver']['data']['default_user_dirs'] as $dir){
				System::MakeDirectory($directory.'/'.$dir,'www-data');
			}
		}
		$Adapter = new DatabaseTable('cw_users',$Data['id']);
		$Adapter->state=0;
		$Adapter->Apply();
	}
	protected function DelUser($Data){
		if(defined("_LIVE_")){
			global $Enviroment;
			$directory = $Enviroment['webserver']['data']['customer_homedirectories'].'/'.$Data['username'];
			System::DeleteDirectory($directory,'www-data');
		}
		$Adapter = new DatabaseTable('cw_users',$Data['id']);
		$Adapter->Delete();
	}


}