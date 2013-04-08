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

class P_index extends page
{


    public function indexAction()
    {
		$width = 200;


    	$U 			= new Usage(User::GetId());
    	$User 		= new M_User();

    	$Domains 	= new M_Domain();
    	$Offers 	= new M_Offers();
    	$Customer 	= new M_Customer();

    	// find all domains
    	$DomainData = $Domains->GetDomainData(User::GetId());
    	$Domains = array();
    	foreach($DomainData as $e){
    		$Domains[] = $e['name'].'.'.$e['tld'];
    	}

    	$OfferData 		= $Offers->GetDataById(User::GetId(),true);
    	$CustomerData 	= $Customer->GetCustomerData($User->GetCustomerId());

    	// Login history data
    	$LoginHistory 	= new SQL();
    	$now = time();
    	$time = $now - $this->config['security']['show_history'];
    	$sql = new SQL();
    	$attemps = $LoginHistory    ->Select('*')
                                    ->From('cw_loginhistory')
                                    ->Where(array('username'=>User::GetName(),'result'=>'0'))
                                    ->Where('`time` > \''.Database::Time($time).'\'')
                                    ->Execute();
    	if($attemps->NumberOfRows()>0){
    		$this->tpl->set('security_issues',Messagebox::Create('Es gab '.$attemps->NumberOfRows().' fehlgeschlagene Logins in den letzten '.($this->config['security']['show_history']/60/60).' Stunden.','warning'));
    	}else{
    		$this->tpl->set('security_issues',Messagebox::Create('Es traten keine Sicherheitsprobleme in den letzten '.($this->config['security']['show_history']/60/60).' Stunden auf.'));
    	}

    	$this->tpl->set(
    	array(
    	    'customer_name' 			=> $CustomerData['prename'].' '.$CustomerData['surname'],
    	    'customer_adress' 			=> $CustomerData['street'].' '.$CustomerData['number'],
    	    'customer_adress2' 			=> $CustomerData['zip'].' '.$CustomerData['city'],
    	    'customer_adress3' 			=> $CustomerData['country'],
    	    'customer_phone' 			=> $CustomerData['phone'],
    	    'customer_email' 			=> $CustomerData['email'],
    	    'customer_id' 				=> $CustomerData['identifier'],
    	    'user_name' 				=> User::GetName(),

    	    'offer_domain' 				=> implode('<br />',$Domains),
    	    'offer_webspace' 			=> $OfferData['webspace_mb'],
    	    'offer_cronjobs' 			=> $OfferData['cronjobs'],
    	    'offer_ftp' 				=> $OfferData['ftp_access'],
    	    'offer_mysqldb'				=> $OfferData['mysql_databases'],
    	    'offer_domains' 			=> $OfferData['domains'],
    	    'offer_subdomains' 			=> $OfferData['subdomains'],
    	    'offer_externdomains' 		=> $OfferData['domains_extern'],
    	    'offer_email_adresses' 		=> $OfferData['mail_adresses'],
    	    'offer_email_boxes' 		=> $OfferData['mail_boxes'],
    	    'offer_email_autoresponder' => $OfferData['mail_autoresponder'],

    	    'domain_capacity'			=>	Progressbar::Create($U->Current('domains'),$U->Maximal('domains'),false,$width),
    	    'subdomain_capacity'		=>	Progressbar::Create($U->Current('subdomains'),$U->Maximal('subdomains'),false,$width),
    	    'mail_adresses_capacity'	=>	Progressbar::Create($U->Current('mail_adresses'),$U->Maximal('mail_adresses'),false,$width),
    	    'cronjob_capacity'			=>	Progressbar::Create($U->Current('cronjobs'),$U->Maximal('cronjobs'),false,$width),
    	    'ftp_capacity'				=>	Progressbar::Create($U->Current('ftp_access'),$U->Maximal('ftp_access'),false,$width),
    	    'mysql_capacity'			=>	Progressbar::Create($U->Current('mysql_databases'),$U->Maximal('mysql_databases'),false,$width),
    	    'mysql_accounts'			=>	Progressbar::Create($U->Current('mysql_accounts'),$U->Maximal('mysql_accounts'),false,$width),
    	    'customers_capacity'		=>	Progressbar::Create($U->Current('customers'),$U->Maximal('customers'),false,$width),
    	    'subuser_capacity'			=>	Progressbar::Create($U->Current('users'),$U->Maximal('users'),false,$width)
    	)
    	);


    	return $this->tpl->GetHtml();
    }



}
