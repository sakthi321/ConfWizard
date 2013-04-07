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


class Usage{

	protected $AffectedSubIds = array();
	protected $RootId = null;
	protected $AllIds = array();
	protected $RootMaxData = array();
	protected $CurentUsage = array();
	protected $ChildUsage = array();

	protected $Keys = array('offers','cronjobs','ftp_access','mysql_databases','subdomains','domains','domains_extern','mail_adresses','mail_boxes','mail_autoresponder','users','customers','mysql_accounts');



	/**
	 * Usage::__construct()
	 * initial Usagedata
	 * @param mixed $root_id
	 */
	public function __construct($root_id=null){

		if($root_id===null)
			$root_id = User::GetId();

		$this->RootMaxData = array_fill_keys($this->Keys,0);
		$this->CurrentUsage = array_fill_keys($this->Keys,0);
		$this->ChildUsage = array_fill_keys($this->Keys,0);

		// get all id's
		$User = new M_User();
		$this->AffectedSubIds = $User->GetSubUserIds($root_id);
		$this->AllIds = $this->AffectedSubIds;
		$this->RootId = $root_id;
		$this->AllIds[] =$root_id;

		$Model = new M_Offers();
		$this->RootMaxData = $Model->GetOfferDataByUserId($root_id);

		// get Offerids for subids
		$SQL = 'SELECT `offer_id` OfferId ,count(*) Num FROM `cw_users` GROUP BY `offer_id` HAVING `id` IN (\''.implode("','",$this->AffectedSubIds)."')";
		$Result = Database::Query($SQL)->FetchArraySet();
		foreach($Result as $Row){
			$tmp = $Model->GetOfferDataById($Row['OfferId']);
			foreach( $this->Keys as $Key ){
				$cur = ($tmp[$Key] == -1) ? INF : $tmp[$Key];
				$this->ChildUsage[$Key] += $cur*$Row['Num'];
			}
		}

		// get current usage



		foreach( $this->Keys as $Key ){
			$this->CurrentUsage[$Key] = $this->CalculateCurrentUsage($Key,$root_id);
		}


	}


	public function Maximal($ressource,$display=false){
		if($display)
			return ($this->RootMaxData[$ressource] == -1) ? '&infin;' : $this->RootMaxData[$ressource];
		else
			return ($this->RootMaxData[$ressource] == -1) ? 'INF' : $this->RootMaxData[$ressource];
	}


	public function CalculateCurrentUsage($ressource,$id){

		$Stm = new SQL();

		$MAPPING = array(
			// key    			=>  	table
			'cronjobs'			=>		'cw_cronjobs',
			'ftp_access'		=>		'cw_ftp',
			'mysql_databases'	=>		'cw_mysqldbs',
			'subdomains'		=>		'cw_subdomains',
			'mail_adresses'		=>		'cw_mail_adress',
			'domains'			=>		'cw_domains',
			'mysql_accounts'	=>		'cw_mysqlaccounts',
			'offers'			=>		'cw_offers'
		);

		if(array_key_exists($ressource,$MAPPING )){

			return  $Stm->Count()
							->From($MAPPING[$ressource])
							->Where(array('user_id'=>$id))
							->Execute()
							->FetchField('num');
		}elseif($ressource=='users'){
			return  $Stm->Count()
							->From('cw_users')
							->Where(array('owner_id'=>$id))
							->Execute()
							->FetchField('num');
		}elseif($ressource=='customers'){
			return  $Stm->Count()
							->From('cw_customers')
							->Where(array('owner_user_id'=>$id))
							->Execute()
							->FetchField('num');
		}elseif($ressource=='domains_extern'){
			return  $Stm->Count()
							->From('cw_domains')
							->Where(array('owner_id'=>$id))
							->Execute()
							->FetchField('num');
		}else{
			return -100;
		}

	}

	public function Current($ressource,$type='both'){
		if(!in_array($ressource,$this->Keys))
			return -1000000;
		if($type==='both')
			return ($this->CurrentUsage[$ressource]+$this->ChildUsage[$ressource]);
		elseif($type==='childs')
			return ($this->ChildUsage[$ressource]);
		elseif($type==='self')
			return $this->CurrentUsage[$ressource];

		return -100;



	}


	public function ConvertToDisplay($data){

	}
	public function IsFree($ressource,$multi=1){

		if(!is_array($ressource))
			return ( ($this->Current($ressource)*$multi<$this->Maximal($ressource)) OR  ($this->Maximal($ressource) == 'INF') );

		foreach($this->Keys as $k){
			if( ($this->Maximal($k) != 'INF')  &&  ($this->Maximal($k)<$this->Current($k)*$multi) ){
				#echo "problem: ".$k.' denn '.$this->Current($k).'*'.$multi.' '.$this->Maximal($k).'';
				return false;
			}

		}

		return true;

	}

}