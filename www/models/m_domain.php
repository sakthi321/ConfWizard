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

class LinkProcessor {
    public function Process($data, $row )
    {
    	return ($row['owner_id'] == User::GetId() ) ? $data : '';
    }
}

class DomainProcessor {
	public function Process($data, $row )
	{
		if($data[0]=='.')
			$data = substr($data,1,strlen($data));
		return '<a href="http://' . $data . '" target="_blank">' . $data . '</a>' ;
	}
}
class GlobeProcessor {
	public function Process($data, $row )
	{
		if($data != User::GetId())
			return Icon::Create('globe-share').'Weitergabe';
		return Icon::Create('globe').'Eigengebrauch';
	}
}
include_once 'core/form.php';
class DomainExistsFilter  extends Validator{

	public function Test($Element ){
        $sql = new SQL() ;
    	$r = 0;

    	if(Request::Get('id') === ''){
    		$r = $sql->Select('name','user_id')
    			->From('cw_subdomains')
    			->Where(array('name' => strtolower(Request::Get('name', true ) ), 'user_id' => User::GetId(),'domain_id'=>Request::Get('domain_id',true) ))
    			->Execute()
    			->NumberOfRows();
    	}else{
    		// edit
    		$id = (int)Request::Get('id');
    		$r = $sql->Select('name','user_id')
    		->From('cw_subdomains')
    		->Where(array('name' => strtolower(Request::Get('name', true ) ), 'user_id' => User::GetId(),'domain_id'=>Request::Get('domain_id',true) ))
    		->Where(array('id'=>Request::Get('id')),true,'<>')
    		->Execute()
    		->NumberOfRows();
    	}

		if($r>0){
			$this->ErrorMessage = 'Dieser Präfix ist bereits vorhanden.';
			return false;
		}


        return true;
    }
}

class M_Domain extends Model {

    public function InsertSubdomain($user_id, $domain_id, $Name,$path ,$type,$code )
    {
		$Usage = new Usage();
    	if (! $Usage->IsFree('subdomains') )
    		throw new AppException($this->Locale->_('SubDomainCapacityExceeded'));

        $F = new DatabaseTable('cw_subdomains' ) ;
        $F->user_id 		= (int) $user_id ;
        $F->state 			= 1 ;
        $F->domain_id 		= (int) $domain_id ;
        $F->name 			= (string) $Name ;
        $F->path 			= (string) $path;
    	$F->type			= (int) $type;
    	$F->redirect_code	= (int) $code;
        $F->Apply() ;
    }
	public function DeleteSubdomain($id){

		if ( !$this->Stm->Exists( 'cw_subdomains', array( 'id' => $id, 'user_id' => User::GetId() ) ) )
			throw new AppException( $this->Locale->_('WrongSubDomainId') ) ;

		$F = new DatabaseTable( 'cw_subdomains', $id ) ;
		$F->state = - 1 ;
		$F->Apply() ;
	}
	public function UpdateSubdomain($id,$domain_id,$name,$path,$type,$code){
		if ( !$this->Stm->Exists( 'cw_subdomains', array( 'id' => $id, 'user_id' => User::GetId() ) ) )
			throw new AppException( $this->Locale->_('WrongSubDomainId') ) ;

		if ( !$this->Stm->Exists( 'cw_domains', array( 'id' => $domain_id, 'user_id' => User::GetId() ) ) )
			throw new AppException( $this->Locale->_('WrongDomainId') ) ;



		$F 					= new DatabaseTable( 'cw_subdomains', $id ) ;
		$F->state 			= (int) 2 ;
		$F->domain_id 		= (int) $domain_id ;
		$F->name 			= (string) $name ;
		$F->path 			= (string) $path;
		$F->type			= (int) $type;
		$F->redirect_code	= (int) $code;
		$F->Apply() ;
	}




	public function UpdateDomain($id,$domain_id,$owner_id,$name,$tld,$linked){
		$Statement = new SQL();
		if ( !$Statement->Exists( 'cw_domains', array( 'id' => $id, 'owner_id' => User::GetId() ) ) ) {
			throw new AppException( $this->Locale->_('WrongDomainId') ) ;
		}

		if($linked == 'change'){
			DATABASE::Query("UPDATE `cw_subdomains` SET `state` = '2' WHERE `domain_id` = '".$domain_id."'");
		}
		if($linked == 'delete'){
			DATABASE::Query("UPDATE `cw_subdomains` SET `state` = '-1' WHERE `domain_id` = '".$domain_id."'");
		}


		$F = new DatabaseTable( 'cw_domains', $domain_id ) ;
		$F->state = '2' ;
		$F->user_id = $owner_id;
		$F->tld = $tld ;
		$F->name = $name ;
		$F->Apply() ;
	}
	public function InsertDomain($owner_id,$user_id,$name,$tld)
	{
    	$Usage = new Usage();
		if ( !$Usage->IsFree('domains') )
			throw new AppException($this->Locale->_('DomainCapacityExceeded'));

		$F = new DatabaseTable( 'cw_domains' ) ;
		$F->state = '1' ;
		$F->owner_id = $user_id;    // can delete  <-- this is correct!!!!
		$F->user_id = $owner_id;	// can see and add subdomains <-- this is correct!!!!
		$F->tld = $tld ;
		$F->name = $name ;
		$F->Apply() ;
	}



    public function GetDomainData($id )
    {
    	return Database::Query("SELECT * FROM `cw_domains` WHERE `user_id` = '{$id}' OR `owner_id` = '{$id}'")->FetchArraySet();
    }
    public function GetSubDomainData($id )
    {
        $Statement = new SQL() ;
        return $Statement->Select('*' )->From('cw_subdomains' )->Where(array('user_id' => $id ) )->Execute()->fetchArraySet() ;
    }
    public function GetDomains($id )
    {
        $Statement = new SQL() ;
        $Result = $Statement
        		->Select(array(
	                'd.`id` as id',
	                ' d.`name` as prefix',
	                'd.`user_id` as user_id', ), false )
        		->Concate(array(
	                ' d.`name` ',
	                ' \'.\' ',
	                ' d.`tld`' ) )
        		->Add('as domain' )
        		->From('cw_domains', 'd' )
        		->Where(array('d.`user_id`' => $id ), false )
        		->Execute()
        		->FetchArraySet() ;

        $Return = array();
        foreach($Result as $Entry ) {
            $Return[$Entry['id']] = $Entry['domain'];
        }
        return $Return;
    }

    public function GetListViewDomains($data = array() )
    {
        $Table = new Table(array(
                // 'id' => 'Id',
                'name' => 'Name',
                'tld' => 'Endung',
                'user_id' => 'Typ',
                // 'command' => 'Befehl',
                'state' => 'Status' ), $data ) ;


        $Table->SetProcessor('state', new StateProcessor() ) ;
    	$Table->SetProcessor('user_id', new GlobeProcessor() ) ;
        $Table->SetActionProcessor(new LinkProcessor() ) ;

        $Table->SetActions(array(new Link('domain', 'edit', array('id' => '' ), Icon::Create('property' ), false ), new Link('domain', 'delete', array('id' => '' ), Icon::Create('cross-button' ), false ) ) ) ;

        return $Table ;
    }
    public function GetListViewSubdomains($data = array() )
    {
        $Table = new Table(array(// 'id' => 'Id',
                'domain' => 'Domain', // 'command' => 'Befehl',
                'path' => 'Ziel',
                'state' => 'Status' ), $data ) ;

        $Table->SetProcessor('state', new StateProcessor() ) ;
        $Table->SetProcessor('domain', new DomainProcessor() ) ;

        $Table->SetActions(array(new Link('domain', 'editsubdomain', array('id' => '' ), Icon::Create('property' ), false ), new Link('domain', 'deletesubdomain', array('id' => '' ), Icon::Create('cross-button' ), false ) ) ) ;

        return $Table ;
    }
    public function GetSubdomains($id )
    {
        /*$sql = 'SELECT s.`name` || \'.\' || d.`name` || \'.\' || d.`tld` as domain, s.`id` as id, s.`path` as path,
        s.`user_id` as user_id,s.`state` FROM `cw_subdomains` s INNER JOIN `cw_domains` d ON s.`domain_id` = d.`id`
        WHERE s.`user_id` = \''.$id.'\'';*/

        $Stm = new SQL() ;
        return $Stm->Select(array(
                's.`id` as id',
                ' s.`path` as path',
                's.`user_id` as user_id',
                's.`state`' ), false )->Concate(array(
                's.`name` ',
                ' \'.\' ',
                ' d.`name` ',
                ' \'.\' ',
                ' d.`tld`' ) )->Add('as domain' )->From('cw_subdomains', 's' )->Join('cw_domains', 'd' )->On('s.`domain_id`', 'd.`id`' )->Where(array('s.`user_id`' => $id ), false )->Execute()->FetchArraySet() ;
    }
    public function GetSDForm($id = '' , $type='hosting')
    {

    	$visible = array(true,false);
    	if($type === 'hosting')
    		$visible = array(false,true);



        $domains = $this->GetDomains(User::GetId() );

        $Formular = new Form() ;
        $Formular->AddElementsFromArray(
			array(
				array(
					'type' => 'hidden',
					'name' => 'id',
					'value' => $id,
					'format' => 'int',
					'label' => ''
				),
				array(
					'type' => 'hidden',
					'name' => 'previewh',
					'value' => '',
					'label' => ''
				),
				array(
					'type' => 'select',
					'name' => 'domain_id',
					'value' => '',
					'label' => 'Domain',
					'items' => $domains
				),
				array(
					'type' => 'text',
					'name' => 'name',
					'value' => '',
					'label' => 'Prefix',
					'quicktip' => 'prefix',
					'validators' => array(new MaxLengthValidator(50 ), new DomainExistsFilter() ),
					'size' => 40
				),

			),
        	'domainname',
        	'Domainname'
        ) ;
    	$Formular->AddElementsFromArray(
    		array(
	    		array(
	    			'type' => 'radio',
	    			'name' => 'type',
	    			'value' => '',
	    			'label' => 'Type',
	    			'value' =>'0',
	    			'format' => 'int',
	    			'items' => array('0'=>'Webseiten Hosting','1'=>'Weiterleitung auf andere Seite'),
	    			)

    		),
    		'domaintype',
			'Domaintyp'
		);
    	$Formular->AddElementsFromArray(
    	array(
	    	array(
	    		'type' => 'text',
	    		'name' => 'host_path',
	    		'value' => '',
	    		'label' => 'Ziel',
	    		'quicktip' => 'host_path',
	    		'placeholder' => '/firstfolder/test',
	    		'autocomplete'=> Link::CreateHref('ajax', 'directory'),
	    		'validators' => array(
	    			new EmptyValidator(false),
	    			new MaxLengthValidator(100 ),
	    			new PathValidator(User::GetHomeDirectory().'public'),
	    			new DirectoryValidator(User::GetHomeDirectory().'public')
	    		),
	    		'size' => 40 )

    	),
    	'domain_host',
		$this->Locale->_('domain_host')
    	);
    	$Formular->AddElementsFromArray(
    	array(
	    	array(
	    		'type' => 'text',
	    		'name' => 'redirect_path',
	    		'value' => '',
	    		'label' => 'Ziel',
	    		'quicktip' => 'redirect_path',
	    		'placeholder' => 'http://www.example.com',
	    		'validators' => array(new MaxLengthValidator(100 ),new UrlValidator() ),
	    		'size' => 40 ),
	    	array(
	    		'type' => 'select',
	    		'name' => 'redirect_code',
	    		'label' => 'Weiterleitungstyp',
	    		'quicktip' => 'redirect_code',
	    		'value' => '301',
	    		'items' => array( '301'=>  '301 Permanent','302'=>'302 Temporär'),
	    		'size' => 40 )


    	),
    	'domain_redirect',
		$this->Locale->_('domain_redirect')
    	);

    	$js="
	if($(this).val() == '0'){
		$('#".$Formular->Sections['domain_redirect']->id."').hide();$('#".$Formular->Sections['domain_host']->id."').show();
	}else{
		$('#".$Formular->Sections['domain_redirect']->id."').show();$('#".$Formular->Sections['domain_host']->id."').hide();
	}

";

    	// change onclick
    	$Formular->Sections['domaintype']->Elements['type']->Events['onclick'][] = $js;
    	$Formular->Sections['domain_redirect']->active = $visible[0];
    	$Formular->Sections['domain_host']->active = $visible[1];




        return $Formular ;
    }
	public function GetForm($id = '' )
	{
		$Model = new M_User();
		$Formular = new Form() ;
		$Formular->AddElementsFromArray(
			array(
		        array(
		            'type' => 'hidden',
		            'name' => 'id',
		            'value' => $id,
		        ),
		        array(
		            'type' => 'text',
		            'name' => 'name',
		            'label' => 'Name',
		            'placeholder' => 'example',
		            'validators' => array(new MaxLengthValidator(100 ) ),
		            'size' => 40
		        ),
				array(
				    'type' => 'text',
				    'name' => 'tld',
				    'value' => '',
				    'float'	=> true,
				    'quicktip'=>'form_tld',
				    'placeholder' => 'com',
				    'validators' => array(new MaxLengthValidator(10 ) ),
				    'size' => 5
				),
				array(
					'type' => 'radio',
					'name' => 'linked_subs',
					'value' => 'change',
					'items' => array('delete' => 'vorhandene Subdomains löschen', 'change' => 'vorhandene Subdomains aktualisieren'),
					'label' => 'Auswirkungen'
				),
				array(
					'type' => 'select',
					'name' => 'user_id',
					'items' => $Model->GetUserList(User::GetId()),
					'label' => 'Besitzer'
				)
			),
			'Domain Einstellungen'
		) ;

		return $Formular ;
	}
}