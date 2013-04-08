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

class CustomerLinkProcessor {
	public function Process($data, $row )
	{
		return Link::Create('customers','edit',array('id'=>$row['customer_id']),$data);
	}
}

class M_User extends Model
{

	public function GetSubUserIds($root){
		$Iter = new TreeIterator();
		$ids = $Iter->Get('cw_users',$root,'owner_id','id');
		return $ids;
	}

	public function GetCapacity($user_id)
	{

		$sql = new SQL() ;
		$offerid = $sql->Select('offer_id')->From('cw_users')->Where(array('id' => $user_id))->Execute()->FetchField('offer_id') ;
		$max     = $sql->Select('users')->From('cw_offers')->Where(array('id' => $offerid))->Execute()->FetchField('users') ;
		$current = count($this->GetSubuserData($user_id));


		return array($current, $max) ;
	}
	public function CanAddNewUser($id)
	{
		$capacity = $this->GetCapacity($id) ;
		return (($capacity[0] < $capacity[1]) or ($capacity[1] == '-1')) ;
	}

    public function HashPassword($plain)
    {
        $random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
        $pass = hash('sha512', $plain . $random_salt);
        return array($random_salt,$pass);
    }

    public function GetUserData($id)
    {

        $CustInfo = new SQL();
        return $CustInfo->Select('*')->From('cw_users')->Where(array('id' => $id))->
            Execute()->fetchArray();

    }
	public function GetSubuserData($id)
	{

		$CustInfo = new SQL();
		return $CustInfo
				->Select('u.`id`',false,'id')
				->Select('u.`username`',false,'username')
				->Select('u.`customer_id`',false,'customer_id')
				->Select('o.`name`',false,'offer_id')
				->Select('u.`email`',false,'email')
				->Select('u.`state`',false,'state')
				->Concate(array('c.`prename`','" "','c.`surname`'),'name')
				->From('cw_users','u')
				->Join('cw_customers','c')
				->On('u.`customer_id`','c.`id`')
				->Join('cw_offers','o')
				->On('u.`offer_id`','o.`id`')
				->Where(array('u.`owner_id`' => $id),false)
				->Execute()
				->fetchArraySet();

	}
	public function GetUserList($id )
	{
		$Result = DATABASE::Query("SELECT * FROM `cw_users` WHERE `id` = '".$id."' OR owner_id = '".$id."'  ")->FetchArraySet();


			$Return = array();
			foreach($Result as $Entry ) {
				$Return[$Entry['id']] = $Entry['username'];
			}
			return $Return;


	}

    public function GetCustomerId($UserId = null)
    {

        $UserId = ($UserId === null) ? User::GetId() : $UserId;

        $CustId = new SQL();
        $id = $CustId->Select('customer_id')->From('cw_users')->Where(array('id' => $UserId))->
            Execute()->fetchArray();
        return $id['customer_id'];

    }

	public function GetListView($HttpRequestData){
		$Table = new Table(array(
				    #'id' => 'Id',
				    'username' 	=> 'Zugang',
				    'name' 		=> 'Kunde',
				    'offer_id' 	=> 'Plan',
				    'state'		=> 'Status',
				    'email'  	=>  'Email'), $HttpRequestData);
				$Table->SetActions(
				array(
				new Link('user', 'delete', array('id' => '' ), Icon::Create('cross-button' ), false )
			)
		) ;
		$Table->SetProcessor('state', new StateProcessor() ) ;
		$Table->SetProcessor('name', new CustomerLinkProcessor() ) ;
		return $Table;
	}

    public function GetEmailForm()
    {

        $Formular = new Form();
    	$Formular->AddElementsFromArray(
	    	array(
			    	array(
			    		'type' => 'text',
			    		'name' => 'email',
			    		'value' => '',
			    		'label' => 'Email',
			    		'validators' => array(
			    		    new EmptyValidator(),
			    		    new MaxLengthValidator(100),
			    		    new EmailValidator()
			    		),
			    		'quicktip' => 'userdata',
			    		'size' => 40)

			    ),
    		'email',
	    	'Email'
    	);
    	$Formular->AddDefaultActions('user');
    	$Formular->Sections['actions']->Elements['submit']->value = $this->Locale->_('changemail');





        return $Formular;
    }
    public function GetPasswordForm()
    {
        $Formular = new Form();


		$Formular->AddElementsFromArray(
			array(
				array(
					'type' => 'password',
					'name' => 'pwd',
					'value' => '',
					'label' => 'Password',
					'validators' => array(
						new EmptyValidator(),
						new MaxLengthValidator(100),
						new InputCompareValidator(array('pwd', 'pwd2'))
					),
					'size' => 40
				),
				array(
					'type' => 'password',
					'name' => 'pwd2',
					'value' => '',
					'label' => 'Password (wiederholen)',
					'validators' => array(
						new EmptyValidator(),
						new MaxLengthValidator(100),
						new InputCompareValidator(array('pwd', 'pwd2'))
					),
					'size' => 40
				),
			),
			'Zugangsdaten'
		);


    	$Formular->AddDefaultActions('user');
    	$Formular->Sections['actions']->Elements['submit']->value = $this->Locale->_('changepassword');


        return $Formular;
    }

	public function GetForm($id=''){
		$Offers = new M_Offers();
		$Usage 	= new Usage();

		$PossibleOffers = $Offers->GetOffers(User::GetId() ,true);




		$Customers = new M_Customer();
		$PossibleCustomers = $Customers->GetCustomers(User::GetId() );


		$Formular = new Form() ;
		$Formular->AddElementsFromArray(
			array(
				array(
					'type' => 'hidden',
					'name' => 'id',
					'value' => $id,
					'label' => ''),
				array(
					'type' => 'select',
					'name' => 'offer_id',
					'items' => $PossibleOffers,
					'label' => 'Plan',),
				array(
					'type' => 'select',
					'name' => 'customer_id',
					'items' => $PossibleCustomers,
					'label' => 'Kunde',
				)
			),
			'userinfo',
			'Benutzerinformationen'
		);

		$Formular->AddElementsFromArray(
			array(
				array(
					'type' => 'password',
					'name' => 'pwd',
					'value' => '',
					'label' => 'Password',
					'validators' => array(
						new EmptyValidator(),
						new MaxLengthValidator(100),
						new InputCompareValidator(array('pwd', 'pwd2'))
					),
					'size' => 40,
					'InputHint' => $this->Locale->_('passwordstrength')
				),
				array(
					'type' => 'password',
					'name' => 'pwd2',
					'value' => '',
					'label' => 'Password (Wiederholen)',
					'validators' => array(
						new EmptyValidator(),
						new MaxLengthValidator(100),
						new InputCompareValidator(array('pwd', 'pwd2'))
					),
					'size' => 40,
					'InputHint' => $this->Locale->_('passwordmatch')
				),
			),
			'accessdata',
			'Zugangsdaten'
		);


		$Formular->AddDefaultActions('user');
		$Formular->Sections['actions']->Elements['submit']->value = $this->GlobalLocale->_('create');


		return $Formular ;
	}



	public function AddUser($pass,$cust_id,$offer_id,$owner_id){

		$val = $this->HashPassword($pass);
		$hash = Util::GetRandomHash();

		$Adapter = new DatabaseTable('cw_users');
		$Adapter->username = $hash;
		$Adapter->password = $val[1];
		$Adapter->salt = $val[0];

		$Adapter->group = 'registered';
		$Adapter->customer_id = (int) $cust_id;
		$Adapter->offer_id =(int) $offer_id;
		$Adapter->owner_id = (int) $owner_id;
		$Adapter->state = 1;
		$Adapter->Apply();


		// update unique username and set email
		$Stm = new SQL();
		$id = $Stm->Select('id')->From('cw_users')->Where(array('username'=>$hash))->Execute()->FetchField('id');
		$email = $Stm->Select('email')->From('cw_customers')->Where(array('id'=>$cust_id))->Execute()->FetchField('email');
		$Adapter = new DatabaseTable('cw_users',$id);
		$Adapter->username = 'web'.(1000 + $id);
		$Adapter->email = $email;
		$Adapter->Apply();
	}


}
