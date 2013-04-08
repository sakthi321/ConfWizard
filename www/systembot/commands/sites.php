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




class sites implements command {
    public function Execute(Log $log,$Enviroment)
    {
    	$Stm = new SQL();


    	// find all subdomains to delete

    	$Sites = DATABASE::Query('
SELECT
	s.id sid,
	s.user_id uid,
	s.domain_id did,
    s.path path,
	s.name prefix ,
	d.name domain,
	d.tld tld,
	s.state state,
	u.username username,
	u.email email,
    c.prename prename,
    c.surname surname
FROM          	`cw_subdomains` s
INNER JOIN    	`cw_domains` d
ON            	d.id = s.domain_id
INNER JOIN		`cw_users` u
ON 				u.id = s.user_id
INNER JOIN		`cw_customers` c
ON 				c.id = u.customer_id
WHERE s.`state` <> 0
				')->FetchArraySet();

    	foreach($Sites as $Site){

    		switch($Site['state']){
    			default:
    				break;
    			case '-1':
    				$log->Write('delete '.$this->GenerateName($Site));
    				$this->DeleteSite($Site);
    				break;
    			case '1':
    				$log->Write('add '.$this->GenerateName($Site));
    				$this->PublishSite($Site);
    				break;
    			case '2':
    				$log->Write('update '.$this->GenerateName($Site));
    				$this->PublishSite($Site);
    				break;

    		}
    	}

    }
	protected function GenerateName($Data){
		return 'cw_host_'.$Data['username'].'_'.$Data['prefix'].'_'.$Data['domain'].'_'.$Data['tld'].'.conf';
	}

	protected function DeleteSite($Data){
		if(defined("_LIVE_")){
			global $Enviroment;
			$filename = $Enviroment['webserver']['data']['conf_files'].'/'.$this->GenerateName($Data);
			if(file_exists($filename)){
				unlink($filename);
			}
		}

		$domain = new DatabaseTable('cw_subdomains',$Data['sid']);
		$domain->Delete();

	}
	protected function PublishSite($Data){
		if(defined("_LIVE_")){
			global $Enviroment;
			$name = $this->GenerateName($Data);
			$filename = $Enviroment['webserver']['data']['conf_files'].'/'.$name;

			$vHost = null;
			// redirect ?
		if(in_string('http',$Data['path'])){
			// make redirect
			$vHost = new Template('systembot/services/ws_apache2_vhost_redirect.conf');
		}else{
			$vHost = new Template('systembot/services/ws_apache2_vhost.conf');
		}



			$url  = ($Data['prefix'] == '') ? '' : $Data['prefix'].'.';
			$url .= $Data['domain'].'.'.$Data['tld'];

			$vHost->set(
			array(
				'now'							=>		Util::Now(),
				'name'							=>		$name,
				'prename'						=>		$Data['prename'],
				'surname'						=>		$Data['surname'],
				'username'						=>		$Data['username'],
				'email'							=>		$Data['email'],
				'domainid'						=>		$Data['did'],
				'customer_homedirectories'		=>		$Enviroment['webserver']['data']['customer_homedirectories'],
				'server_ip'						=>		$Enviroment['webserver']['data']['ip'],
				'path'							=>		$Data['path'],
				'url'							=>		$url,
				'tld'							=>		$Data['tld']
			)
			);
			file_put_contents($filename, $vHost->GetHtml());

		}

		$domain = new DatabaseTable('cw_subdomains',$Data['sid']);
		$domain->state=0;
		$domain->Apply();



	}
}