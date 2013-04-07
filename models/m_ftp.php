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

class M_FTP extends Model
{

    public function NextFreeAccount($id){
            $user = new M_User();
            $data = $user->GetUserData($id);

            $rs =   $this->Stm->Select('username')
                        ->From('cw_ftp')
                        ->Where(array('user_id'=> $id))
                        ->Execute()
                        ->FetchArraySet();

            $free = array();
            foreach($rs as $e){
                $free[] = str_replace($data['username'].'f','',$e['username']);
            }
            $max_num = max($free)+3;
            $take = -1;
            for($i=1;$i<$max_num+1;$i++){
                if(!in_array($i,$free)){
                    $take = $i;
                    break;
                }
            }
            return $take;
    }


	public function Insert($pass,$homedir,$enabled){
		$Usage = new Usage();
		if(!$Usage->IsFree('ftp_access'))
			throw new AppException('kann keinen weiteren FTP Benutzer anlegen.');

		$Adapter = new DatabaseTable('cw_ftp') ;
		$Adapter->password =Util::crypt($pass);
		$Adapter->homedir = $homedir ;
		$Adapter->user_id = User::GetId() ;
		$Adapter->enabled = $enabled;
		$Adapter->state = '1' ;
		$Adapter->username = User::GetName().'f'.$this->NextFreeAccount(User::GetId());
		$Adapter->Apply() ;
	}
	public function Update($id,$homedir,$enabled){

		if ( !$this->Stm->Exists( 'cw_ftp', array( 'id' => $id, 'user_id' => User::GetId() ) ) )
			throw new AppException( $this->Locale->_('notown') ) ;

		$Adapter = new DatabaseTable('cw_ftp',$id) ;
		$Adapter->homedir = $homedir ;
		$Adapter->enabled = $enabled;
		$Adapter->state = '2' ;
		$Adapter->Apply() ;
	}
	public function Delete($id){
		if ( !$this->Stm->Exists( 'cw_ftp', array( 'id' => $id, 'user_id' => User::GetId() ) ) )
			throw new AppException( $this->Locale->_('WrongFTPId') ) ;

		$Adapter = new DatabaseTable('cw_ftp', $id) ;
		$Adapter->state = -1 ;
		$Adapter->Apply() ;

	}

    public function GetListView($data = array())
    {

        //


        $FTPTable = new Table(array(
           # 'id' => 'Id',
           'enabled' => 'Aktiviert',
            'username' => 'Loginname',
            'homedir' => 'Heimverzeichnis',

            'state' => 'Status'), $data) ;

        $FTPTable->SetActions(array(
            new Link('ftp', 'changepassword', array('id' => ''), Icon::Create('lock--arrow'), false),
            new Link('ftp', 'edit', array('id' => ''), Icon::Create('property'), false),
            new Link('ftp', 'delete', array('id' => ''), Icon::Create('cross-button'), false))) ;

        $FTPTable->SetProcessor('state', new StateProcessor()) ;
        $FTPTable->SetProcessor('enabled', new ActiveProcessor()) ;
        return $FTPTable ;
    }
    public function GetAddForm()
    {
        $Formular = new Form() ;
        $Formular->AddElementsFromArray(array(
            array(
                'type' => 'password',
                'name' => 'pwd',
                'value' => '',
                'label' => 'Password',
                'validators' => array(
                    new EmptyValidator(),
                    new MaxLengthValidator(100),
                    new InputCompareValidator(array('pwd', 'pwd2')
                ),
                'size' => 40),
            ),
	        array(
	            'type' => 'password',
	            'name' => 'pwd2',
	            'value' => '',
	            'label' => 'Password (Wiederholen)',
	            'validators' => array(
	                new EmptyValidator(),
	                new MaxLengthValidator(100),
	                new InputCompareValidator(array('pwd', 'pwd2')),
	            'size' => 40)
	        )
				), 'Zugangsdaten für FTP Account ') ;

        $Formular->AddElementsFromArray(array(
            array(
                'type' => 'text',
                'name' => 'homedir',
                'value' => '',
                'label' => 'Heimverzeichnis',
                'autocomplete'=> Link::CreateHref('ajax', 'directory'),
                'validators' => array( new MaxLengthValidator(100)),
                'size' => 40),
            array(
                'type' => 'radio',
                'name' => 'enabled',
                'value' => 'enabled',
                'items' => array('0' => 'nein', '1' => 'ja'),
                'label' => 'darf sich einloggen'),


            ), 'FTP Einstellungen ') ;

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
	    			'link' => new Link('ftp', 'index', array(), $this->GlobalLocale->_('back')),
	    			'name' => 'back',
	    			'float'	=>true
	    		)
	    	),
	    	'Aktionen'
    	);


        return $Formular ;
    }
    public function GetFTPForm($id)
    {
        $Formular = new Form() ;
        $Formular->AddElementsFromArray(
			array(
				array(
					'type' => 'hidden',
					'name' => 'id',
					'value' => $id,
					'label' => ''
				),
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
					'label' => 'Password',
					'validators' => array(
						new EmptyValidator(),
						new MaxLengthValidator(100),
						new InputCompareValidator(array('pwd', 'pwd2'))
					),
					'size' => 40
				)

			), 'Zugangsdaten für FTP Account ') ;

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
		    			'link' => new Link('ftp', 'index', array(), $this->GlobalLocale->_('back')),
		    			'name' => 'back',
		    			'float'	=>true
		    		)
		    	),
	    	'Aktionen'
    	);


        return $Formular ;
    }
    public function GetEditForm($id)
    {
        $Formular = new Form() ;

    	$Formular->AddElementsFromArray(
			array(
	    		array(
	                'type' => 'hidden',
	                'name' => 'id',
	                'value' => $id,
	                'label' => ''),
	            array(
	                'type' => 'text',
	                'name' => 'homedir',
	                'value' => '',
	                'label' => 'Heimverzeichnis',
	                'autocomplete'=> Link::CreateHref('ajax', 'directory'),
	                'validators' => array( new MaxLengthValidator(100)),
	                'size' => 40),
	            array(
	                'type' => 'radio',
	                'name' => 'enabled',
	                'value' => 'enabled',
	                'items' => array('0' => 'nein', '1' => 'ja'),
	                'label' => 'darf sich einloggen'),
            ),
    		'FTP Einstellungen '
    	) ;



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
		    		'link' => new Link('ftp', 'index', array(), $this->GlobalLocale->_('back')),
		    		'name' => 'back',
		    		'float'	=>true
		    	)
		    ),
	    	'Aktionen'
    	);




        return $Formular ;
    }
}
