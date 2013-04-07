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

class IntegerDisplayProcessor {
	public function Process($data, $row )
	{
		switch(trim($data)){
			case '-1':
				return '&infin;';
			case '0':
				return '-';
			default:
				return $data;
		}
	}
}

class M_Offers extends Model{

	protected $arr = array();

	public function __construct(){
		parent::__construct();
		$this->arr = array(
			// entry					// element   	//label
			array('mysql_databases',	'databases',	'Datenbanken'),
			array('mysql_accounts',		'mysqlaccount',	'MySQL Benutzer'),
			array('ftp_access',			'ftpaccess',	'FTP Zugänge'),
			array('domains',			'domains',		'interne Domains'),
			array('subdomains',			'subdomains',	'Subdomains'),
			array('domains_extern',		'extdomains',	'externe Domains'),
			array('cronjobs',			'cronjobs',		'Cronjobs'),
			array('webspace_mb',		'webspace',		'Webspace in MB'),
			array('mail_adresses',		'mailaddy',		'Mailadressen'),
			array('mail_boxes',			'mailbox',		'Mailaccounts'),
			array('mail_autoresponder',	'mailauto',		'Autoresponder'),
			array('traffic_m',			'traffic',		'Traffic'),
			array('offers',				'offers',		'Angebotsvorlagen'),
			array('users',				'subusers',		'Unterbenutzer'),
			array('customers',			'customers',	'Kunden')
		);
	}



	public function GetOfferDataByUserId($user_id){
		$Statement = new SQL() ;

		$offerid = $Statement->Select('offer_id')->From('cw_users')->Where(array('id' => $user_id))->Execute()->FetchField('offer_id') ;
		return  $Statement
				->Select('*')
				->From('cw_offers')
				->Where(array('id'=>$offerid))
				->Execute()
				->FetchArray();
	}
	public function GetOfferDataById($id){
		$Statement = new SQL() ;
		return  $Statement
				->Select('*')
				->From('cw_offers')
				->Where(array('id'=>$id))
				->Execute()
				->FetchArray();
	}
	public function Delete($id )
	{
		if (!$this->Stm->Exists('cw_offers', array('id' => $id, 'user_id' => User::GetId() ) ) ) {
			throw new AppException('this is not your offer!' ) ;
		}
		if ($this->Stm->Exists('cw_users', array( 'offer_id' => $id ) ) ) {
			throw new AppException('Diese Angebotsvorlage kann nicht gelöscht werden, da einem Benutzer diese zugeordnet ist', 'error' );
		}

		$Adapter = new DatabaseTable('cw_offers', $id ) ;
		$Adapter->Delete() ;
	}
	public function GetListView($Data){
		$Table = new Table(array(
		    'name' => 'Name',
		    'webspace_mb' => 'Webspace (MB)',
		    'ftp_access' => 'FTP Zugänge',
		    'cronjobs' => 'Cronjobs',
		    'domains' => 'Domains',
		    'mysql_databases' => 'MySQL Datenbanken'
			), $Data);

		$Table->SetProcessor('mysql_databases', new IntegerDisplayProcessor() ) ;
		$Table->SetProcessor('ftp_access', new IntegerDisplayProcessor() ) ;
		$Table->SetProcessor('cronjobs', new IntegerDisplayProcessor() ) ;
		$Table->SetProcessor('subdomains', new IntegerDisplayProcessor() ) ;
		$Table->SetProcessor('domains', new IntegerDisplayProcessor() ) ;

		$Table->SetActions(array(new Link('offers', 'delete', array('id' => ''),
		        Icon::Create('cross-button' ))));
		return $Table;
	}


    public function GetDataById($id=null,$display=false){

        $id = ($id === null) ? User::GetId() : (int) $id;

        $sql = new SQL();

        $offer_id = $sql
                    ->Select('offer_id')
                    ->From('cw_users')
                    ->Where(array('id'=>$id))
                    ->Execute()
                    ->fetchArray();

        $offer_id = $offer_id['offer_id'];
        $sql->Clear();
        $data = $sql
                ->Select('*')
                ->From('cw_offers')
                ->Where(array('id'=>$offer_id))
                ->Execute()
                ->fetchArray();

        if($display){
            foreach($data as $k=>$v){
                if($v=='-1')
                    $data[$k] = 'unendlich';
            }
        }
        return $data;

    }



	public function GetOffers($id,$restrict=false )
	{
		if(!$restrict){
			$Statement = new SQL() ;
			$Result = $Statement
				->Select(array('id','name'))
				->From('cw_offers')
				->Where(array('user_id'=>$id))
				->Execute()
				->FetchArraySet();

			$Return = array();
			foreach($Result as $Entry ) {
				$Return[$Entry['id']] = $Entry['name'];
			}
			return $Return;
		}else{
			$Usage = new Usage();
			// tests if is avaiblable
			$PossibleOffers2 = $this->GetOffers($id,false );
			$PossibleOffers  = array();
			// tests if offer is available due its limits
			foreach($PossibleOffers2 as $k=>$v){

				$Off_Data = $this->GetOfferDataById($k);
				if($Usage->IsFree($Off_Data,$Off_Data['users']))
					$PossibleOffers[$k] = $v;
			}
			return $PossibleOffers;
		}

	}

	public function Rewrite($Data){
		#var_dump($Data);



		foreach($this->arr as $entry){
			$Data[$entry[1].'_num'] = $Data[$entry[0]];
			if($Data[$entry[0]] == '-1')
				$Data[$entry[1]] = $entry[1].'_unlimited';
			elseif($Data[$entry[0]] == '0')
				$Data[$entry[1]] = $entry[1].'_no';
			else{
				$Data[$entry[1]] = $entry[1].'_custom';
			}



		}



		return $Data;
	}

	public function GetForm(){
		$Formular = new Form();
		$Formular->AddElementsFromArray(
			array(
	            array(
	                'type' => 'text',
	                'name' => 'name',
	                'value' => '',
	                'label' => 'Name',
	                'validators' => array(new EmptyValidator(), new MaxLengthValidator(100)),
	                'size' => 40
	            )
	        ),
			'Allgemein'
		);
		$Formular->AddElementsFromArray(
			array(
				array(
					'type' => 'select',
					'name' => 'php',
					'value' => 'php43',
					'items' => array('php43' => 'PHP 4.3', 'php5' => 'PHP 5'),
					'label' => 'PHP',
				),
				array(
					'type' => 'checkbox',
					'name' => 'ssl',
					'items' => array('ssl_use' => 'benutzen', 'ssl_manage' => 'verwalten'),
					'label' => 'SSL'
				),
            	array(
	                'type' => 'checkbox',
	                'name' => 'cgi',
	                'items' => array('cgi_use' => 'ja'),
	                'label' => 'CGI'
                ),
            	array(
	                'type' => 'radio',
	                'name' => 'ssh_access',
	                'value' => '0',
	                'items' => array('0' => 'nein', '1' => 'ja'),
	                'label' => 'SSH Zugriff'
	            )
            ),
			'Grundeinstellungen'
		);

		foreach($this->arr as $entry){

			$Formular->AddElementsFromArray(
				array(
					array(
		                'type' => 'radio',
		                'name' => $entry[1],
		                'value' => $entry[1].'_custom',
		                'items' => array(
		                    $entry[1].'_no' => 'nein',
		                    $entry[1].'_unlimited' => 'unbegrenzt',
		                    $entry[1].'_custom' => 'begrenzt'),
		                'label' => $entry[2]
		            ),
					array(
		                'type' 		=> 'text',
		                'name' 		=> $entry[1].'_num',
		                'value' 	=> '1',
		                'size' 		=> 8,
		                'disabled'	=> false,
		                'float' 	=> true)
				),
				'Verbrauch'
			);

		}


		$Formular->AddElementsFromArray(
			array(
				array(
					'type' => 'submit',
					'name' => 'save',
					'value' => 'speichern'
				),
				array(
					'type' => 'reset',
					'name' => 'reset',
					'value' => 'Reset',
					'float' => true
				)
			),
			'Aktionen'
		);


		$Formular->AddElementsFromArray(
			array(
				array(
					'type' => 'submit',
					'name' => 'save',
					'value' => $this->GlobalLocale->_('create'),
					'label'=>null
				),
				array(
					'type'	=> 'reset',
					'name' 	=> 'reset',
					'value' => $this->GlobalLocale->_('reset'),
					'float'	=> true,
					'label' => null
				),
				array(
					'type' => 'link',
					'link' => new Link('offers', 'index', array(), $this->GlobalLocale->_('back')),
					'name' => 'back',
					'float'	=>true
				)
			),
			'Aktionen'
		);

		return $Formular;
	}


	public function Insert($user_id){

		$Usage 		= new Usage();

		if (!$Usage->IsFree('offers') )
			throw new AppException('kann keinen weitere Angebotsvorlage anlegen.' );



		$Data = array();
		foreach($this->arr as $entry){
			switch(Request::Get($entry[1])){
				case $entry[1].'_unlimited':
					$Data[$entry[1]] = '-1';
					break;
				case $entry[1].'_no':
					$Data[$entry[1]] = '0';
					break;
				default:
					$Data[$entry[1]] = (Request::Exists($entry[1].'_num')) ? (int) Request::Get($entry[1].'_num',true) : '0';
					break;

			}


		}


		$Adapter = new DatabaseTable('cw_offers');
		$Adapter->user_id				=	$user_id;
		$Adapter->name					=	Request::Get('name',true);
		$Adapter->webspace_mb 			=	$Data['webspace'];
		$Adapter->cronjobs 				=	$Data['cronjobs'];
		$Adapter->ftp_access			=	$Data['ftpaccess'];
		$Adapter->mysql_databases		=	$Data['databases'];
		$Adapter->mysql_accounts		=	$Data['mysqlaccount'];
		$Adapter->domains				=	$Data['domains'];
		$Adapter->subdomains			=	$Data['subdomains'];
		$Adapter->domains_extern		=	$Data['extdomains'];
		$Adapter->mail_adresses			=	$Data['mailaddy'];
		$Adapter->mail_boxes			=	$Data['mailbox'];
		$Adapter->mail_autoresponder	=	$Data['mailauto'];
		$Adapter->traffic_mb			=	$Data['traffic'];
		$Adapter->offers				=	$Data['offers'];
		$Adapter->users					=	$Data['subusers'];
		$Adapter->customers				=	$Data['customers'];
		$Adapter->php					=	1;
		$Adapter->ssh					=	(int) Request::Get('ssh_access',true);
		$Adapter->ssl_user				=	Request::Exists('ssl_use') ? 1 : 0;
		$Adapter->ssl_create			=	Request::Exists('ssl_create') ? 1 : 0;
		$Adapter->time					=	Util::Now();
		$Adapter->Apply();

	}
}