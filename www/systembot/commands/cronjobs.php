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


class cronjobs implements command {

	protected $file = null;
	protected $uid = '';

	protected $Env = array();
	public function Execute(Log $log,$Enviroment)
	{
		$this->Env = $Enviroment;

		$this->file = new File($Enviroment['current']['cronjob']['path'].'cw_cronjobs');


		$Record = Database::Query('SELECT * FROM `cw_cronjobs`')->FetchArraySet();
		foreach($Record as $Data){

			switch($Data['state']){
				default:
					break;
				case '-1':
					$log->Write('delete cronjob '.$Data['command']);
					$this->Delete($Data);
					break;
				case '2':
					$log->Write('delete cronjob '.$Data['command']);
					$this->Update($Data);
					break;
				case '1':
					$log->Write('add cronjob '.$Data['command']);
					$this->Add($Data);
					break;

			}
		}
		$this->file->Store();

	}

	protected function Delete($Data){
		$line_number = $this->file->Find('#id::'.$Data['id']);
		if($line_number !== null)
			$this->file->DelLine($line_number);

		$Adapter = new DatabaseTable('cw_cronjobs',$Data['id']);
		$Adapter->Delete();

	}
	protected function Add($Data){
		$agent = (string) (1000+$Data['user_id']);
		$this->file->AddLine($Data['format']." ".$agent." ".$Data['command'].' #id::'.$Data['id']);

		$Adapter = new DatabaseTable('cw_cronjobs',$Data['id']);
		$Adapter->state = (string) 0;
		$Adapter->Apply();

	}
	protected function Update($Data){
		$line_number = $this->file->Find('#id::'.$Data['id']);
		if($line_number !== null)
			$this->file->DelLine($line_number);

		$this->Add($Data);



	}





}