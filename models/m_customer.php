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

class M_Customer extends model{

	public function GetCustomers($id )
	{
		$Result = $this->Stm
				->Select(array('id'))
				->Concate(array('`prename`','" "','`surname`','" ("','`identifier`','" , "','`email`','")"'),'name')
				->From('cw_customers')
				->Where(array('owner_user_id'=>$id))
				->Execute()
				->FetchArraySet();

		$Return = array();
		foreach($Result as $Entry ) {
			$Return[$Entry['id']] = $Entry['name'];
		}
		return $Return;
	}
	public function GetAllSubCustomers($id){
		return $this->Stm
        			->Select(array('id','prename','surname','email'))
        			->From('cw_customers')
        			->Where(array('owner_user_id'=>User::GetId()))
        			->Execute()
        			->FetchArraySet();

	}
    public function GetCustomerData($id){
        return $this->Stm
                ->Select('*')
                #->Concat(array('prename',"' '",'surname'),'name')
                ->From('cw_customers')
                ->Where(array('id'=>$id ))
                ->Execute()
                ->fetchArray();

    }

    public function GetListView($HttpRequestData){
        $Table = new Table(array(
            #'id' => 'Id',
            'prename' => 'Vorname',
            'surname' => 'Zuname',
            'email'   =>  'Email'), $HttpRequestData);
        $Table->SetActions(
			array(
        		new Link('customers', 'edit', array('id' => '' ), Icon::Create('property' ), false ),
        		new Link('customers', 'delete', array('id' => '' ), Icon::Create('cross-button' ), false )
        		 )
         ) ;
        return $Table;
    }


	public function InsertCustomer($id,$prename,$surname,$street,$number,$zip,$city,$country,$phone,$mobile,$fax,$email,$website){
		$Usage = new Usage();
		if ( !$Usage->IsFree('customers') )
			throw new AppException( $this->Locale->_('CapacityExceeded') ) ;

		$Adapter = new DatabaseTable('cw_customers');
		$Adapter->owner_user_id = $id;
		$Adapter->prename = $prename;
		$Adapter->surname = $surname;
		$Adapter->street = $street;
		$Adapter->number = $number;
		$Adapter->zip = $zip;
		$Adapter->city = $city;
		$Adapter->country = $country;
		$Adapter->phone = $phone;
		$Adapter->mobile = $mobile;
		$Adapter->fax = $fax;
		$Adapter->email = $email;
		$Adapter->website = $website;
		$Adapter->change = Util::Now();
		$Adapter->Apply();
	}
	public function UpdateCustomer($id,$prename,$surname,$street,$number,$zip,$city,$country,$phone,$mobile,$fax,$email,$website){

		if ( !$this->Stm->Exists( 'cw_customers', array( 'id' => $id, 'owner_user_id' => User::GetId() ) ) )
			throw new AppException( $this->Locale->_('notown') ) ;

		$Adapter = new DatabaseTable('cw_customers',$id);
		$Adapter->prename = $prename;
		$Adapter->surname = $surname;
		$Adapter->street = $street;
		$Adapter->number = $number;
		$Adapter->zip = $zip;
		$Adapter->city = $city;
		$Adapter->country = $country;
		$Adapter->phone = $phone;
		$Adapter->mobile = $mobile;
		$Adapter->fax = $fax;
		$Adapter->email = $email;
		$Adapter->website = $website;
		$Adapter->change = Database::Now();
		$Adapter->Apply();
	}
	public function DeleteCustomer($id){

		if($id== '1')
			throw new AppException($this->Locale->_('protected'));
		if (!$this->Stm->Exists('cw_customers', array('id' => $id, 'owner_user_id' => User::GetId())))
			throw new AppException($this->Locale->_('notown'));
		if ($this->Stm->Count()->From('cw_users')->Where(array('customer_id' => $id))->Execute()->FetchField('num') >0 )
			throw new AppException($this->Locale->_('del_hasusers'));

		$Adapter = new DatabaseTable('cw_customers',$id);
		$Adapter->Delete();


	}

    public function GetFormular(){
    	global $config;
        $Formular = new Form();
        $Formular->AddElementsFromArray(
			array(
				array(
					'type' => 'text',
					'name' => 'prename',
					'value' => '',
					'label' => $this->Locale->_('prename'),
					'placeholder' => 'Neil',
					'validators' => array(new EmptyValidator(), new MaxLengthValidator(100)),
					'size' => 40
				),
				array(
					'type' => 'text',
					'name' => 'surname',
					'value' => '',
					'label' => $this->Locale->_('surname'),
					'placeholder' => 'Armstrong',
					'validators' => array(new EmptyValidator(), new MaxLengthValidator(100)),
					'size' => 40
				)
           ), 'Allgemein');

        $Formular->AddElementsFromArray(array(
            array(
                'type' => 'text',
                'name' => 'street',
                'label' => $this->Locale->_('street'),
                'validators' => array(new EmptyValidator(), new MaxLengthValidator(100)),
                'size' => 40),
           array(
                'type' => 'text',
                'name' => 'number',
                'validators' => array(new EmptyValidator(),new DigitsValidator(), new MaxLengthValidator(100)),
                'size' => 5,
                'float' =>true),
           array(
                'type' => 'text',
                'name' => 'zip',
                'label' => $this->Locale->_('zip'),
                'validators' => array(new EmptyValidator(),new DigitsValidator(), new MaxLengthValidator(100)),
                'size' => 9),
            array(
                'type' => 'text',
                'name' => 'city',
                'validators' => array(new EmptyValidator(), new MaxLengthValidator(100)),
                'size' => 36,
                'float' =>true),
            array(
                'type' => 'select',
                'name' => 'country',
                'items' => $config['countries'] ,
                'label' => 'Land'),
            array(
                'type' => 'text',
                'name' => 'phone',
                'label' => $this->Locale->_('phone'),
                'validators' => array(new DigitsValidator(), new MaxLengthValidator(100)),
                'size' => 20),
            array(
                'type' => 'text',
                'name' => 'mobile',
                'label' => $this->Locale->_('mobile'),
                'validators' => array(new DigitsValidator(), new MaxLengthValidator(100)),
                'size' => 20),
            array(
                'type' => 'text',
                'name' => 'fax',
                'label' => $this->Locale->_('fax'),
                'validators' => array(new DigitsValidator(), new MaxLengthValidator(100)),
                'size' => 20),
            array(
                'type' => 'text',
                'name' => 'email',
                'label' => $this->Locale->_('email'),
                'placeholder' => 'mail@adress.com',
                'validators' => array(new EmptyValidator(), new MaxLengthValidator(100),new EmailValidator()),
                'size' => 50),
            array(
                'type' => 'text',
                'name' => 'website',
                'label' => $this->Locale->_('website'),
                'placeholder' => 'http(s)://www.example.tld ',
                'validators' => array( new MaxLengthValidator(100), new UrlValidator()),
                'size' => 50)
           )
           , 'Anschrift');

    	$Formular->AddElementsFromArray(
	    	array(
		    	array(
		    		'type' => 'submit',
		    		'name' => 'save',
		    		'value' => $this->GlobalLocale->_('save'),
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
		    		'link' => new Link('customers', 'index', array(), $this->GlobalLocale->_('back')),
		    		'name' => 'back',
		    		'float'	=>true
		    	)
		    ),
	    	'Aktionen'
    	);
        return $Formular;
    }




}