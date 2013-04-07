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


class ftpaccounts implements command {

	protected $file = null;
	protected $uid = '';

	protected $Env = array();
	public function Execute(Log $log,$Enviroment)
	{
		$this->Env = $Enviroment;


		$this->file = new File($Enviroment['ftpserver']['data']['ftpasswd']);
		$this->uid = System::Execute('id -u www-data');

		$Stm = new SQL();
		$Data = $Stm
				->Select('*')
				->From('cw_ftp')
				->Where(array('state'=>'0'),true,'<>')
				->Execute()
				->FetchArraySet();



		foreach($Data as $Entry){
			switch($Entry['state']){
				default:
					$log->Write('unkown state ( '.$Entry['state'].' ) for ftp account '.$Entry['username']);
					break;
				case '1':
					$log->Write('add ftp user '.$Entry['username']);
					$this->AddFtp($Entry);
					break;
				case '-1':
					$log->Write('del ftp user '.$Entry['username']);
					$this->DelFtp($Entry);
					break;
				case '2':
					$log->Write('update ftp user '.$Entry['username']);
					$this->UpdFtp($Entry);
					break;

			}
		}

		$this->file->Store();


	}

	protected function AddFtp($Data){
		// ftptest2:$1$zcNyJote$k8JwRo0o1L3gQ/C4sSOQk0:33:33::/var/www/confwizard/template:/bin/false

		if($Data['enabled'] != '0'){
			$username 	= explode('f',$Data['username']);
			$username 	= $username[0];
			$shell 		= '/bin/false';

			$agent = (string) (1000+$Data['user_id']);

			$line = implode(':',array(
				$Data['username'],
				$Data['password'],
				$agent,
				$agent,
				'',
				$this->Env['webserver']['data']['customer_homedirectories'].'/'.$username.'/public'.$Data['homedir'],
				$shell
			));

			$this->file->AddLine($line);
		}





		$Adapter = new DatabaseTable('cw_ftp',$Data['id']);
		$Adapter->state = 0;
		$Adapter->Apply();

	}
	protected function DelFtp($Data){
		// ftptest2:$1$zcNyJote$k8JwRo0o1L3gQ/C4sSOQk0:33:33::/var/www/confwizard/template:/bin/
		$line_number = $this->file->Find($Data['username']);
		if($line_number !== null)
			$this->file->DelLine($line_number);
		$Adapter = new DatabaseTable('cw_ftp',$Data['id']);
		$Adapter->Delete();

	}
	protected function UpdFtp($Data){
		// ftptest2:$1$zcNyJote$k8JwRo0o1L3gQ/C4sSOQk0:33:33::/var/www/confwizard/template:/bin/
		$line_number = $this->file->Find($Data['username']);
		if($line_number !== null)
			$this->file->DelLine($line_number);
		$this->AddFtp($Data);

	}




}