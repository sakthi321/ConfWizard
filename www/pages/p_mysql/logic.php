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

class P_mysql extends page {



	public function PreProcessor()
	{
		if(defined("_LIVE_")){
			include_once 'core/database_mysql.php';
			$MySQLTunnel  = DatabaseMySQL::singleton();
			global $Enviroment;
			try{
				$MySQLTunnel->connect(
				$Enviroment['mysql']['data']['databasehost'],
				$Enviroment['mysql']['data']['databaseuser'],
				$Enviroment['mysql']['data']['databasepassword']
			);
			}catch(Exception $e){
				throw new AccessException('MySQL nicht erreichbar.');
			}
		}

	}


    public function indexAction()
    {
        $sql 	= new SQL();
    	$Usage 	= new Usage();
    	$MYSQL 	= new M_Mysql();

    	$Data = $sql->Select(array(
    	        'd.name',
    	        'd.state',
    	        'd.comment',
    	        'd.id','u.username' ),false )
    	->From('cw_mysqldbs','d' )
    	->Join('cw_mysqlaccounts','u')
    	->On('d.dbuser_ids','u.id')
    	->Where(array('d.user_id' => User::GetId() ) )->Execute()->fetchArraySet() ;
    	$Data2 = $sql->Select('*' )->From('cw_mysqlaccounts' )->Where(array('user_id' => User::GetId() ) )->Execute()->fetchArraySet() ;

        $MYSQLTable = $MYSQL->GetListView($Data ) ;
    	$MYSQLTable2 = $MYSQL->GetListViewUser($Data2 ) ;


        if ($Usage->IsFree('mysql_databases') ) {
            $this->tpl->set('add_mysql', Link::Button('mysql', 'add', array(), Icon::Create('database--plus' ) . 'Datenbank anlegen' ) ) ;
        } else {
            $this->tpl->set('add_mysql', 'Sie können keine weitere Datenbanken anlegen' ) ;
        }
    	if ($Usage->IsFree('mysql_accounts') ) {
    		$this->tpl->set('add_mysqluser', Link::Button('mysql', 'adduser', array(), Icon::Create('user--plus' ) . 'MySQL Benutzer anlegen' ) ) ;
    	} else {
    		$this->tpl->set('add_mysqluser', 'Sie können keine weiteren Benutzer anlegen' ) ;
    	}

    	$this->tpl->set(
    		array(
    			'current_self'		=>	$Usage->Current('mysql_databases','self'),
    			'current_selfu'		=>	$Usage->Current('mysql_accounts','self'),
    			'max_mysql'			=>  $Usage->Maximal('mysql_databases'),
    			'pgr_mysql'			=>  Progressbar::Create($Usage->Current('mysql_databases'),$Usage->Maximal('mysql_databases')),
    			'pgr_mysqluser'		=>  Progressbar::Create($Usage->Current('mysql_accounts'),$Usage->Maximal('mysql_accounts')),
    			'listview'			=>	$MYSQLTable->GetHtml(),
    			'listviewu'			=>	$MYSQLTable2->GetHtml()
    		)
		);




        return $this->tpl->GetHtml();
    }

    public function editAction()
    {
    	$id = (int)Request::get('id' ) ;

    	$Statement 	= new SQL() ;
    	$Usage 		= new Usage();

    	// tests if the user is the owner of the database
    	if (!$Statement->Exists('cw_mysqldbs', array('id' => $id, 'user_id' => User::GetId() ) ) )
    		throw new AccessException('this is not your database!' ) ;


    	$Model 		= new M_Mysql();

    	$Data 		= $Statement
    					->Select(array('comment', 'name','dbuser_ids' ) )
    					->From('cw_mysqldbs' )
    					->Where(array('id' => $id ) )
    					->Execute()
    					->FetchArray() ;


    	$Formular = $Model->GetEditForm($id ) ;



    	if($Formular->WasSent()){
   			try{
   				$Formular->Validate();
   				$Model->UpdateMySQL($id, Request::Get('comment', true ),Request::Get('dbuser_ids', true ) );
   				$html = Messagebox::Create('Änderungen durchgeführt', 'info' ) . new Link('mysql', 'index', array(), $this->GlobalLocale->_('back'), true ) ;
   			}catch(AppExeption $e){
   				$Formular->Populate();
   				$html =  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
   			}
    	}else{
    		$Formular->Populate($Data) ;
    		$html = $Formular->GetHtml() ;
    	}



    	$this->tpl->set('form', $html ) ;
    	$this->tpl->set('name', $Data['name'] ) ;
    	$this->tpl->set('text', '' ) ;

    	return $this->tpl->GetHtml() ;
    }

    public function addAction()
    {

        $Model 	= new M_Mysql() ;
    	$Usage 	= new Usage();

    	if (!$Usage->IsFree('mysql_databases') ){
    		throw new Exception('kann keinen weitere Datenbank anlegen.' );
    	}


        $html = '';

        $Formular = $Model->GetAddForm();


    	if($Formular->WasSent()){
   			try{
				$Formular->Validate();
   				$Model->AddMySQL(User::GetId() , User::GetName(), Request::Get('comment', true ),Request::Get('dbuser_ids', true ) );
   				$html = Messagebox::Create('Datenbank angelegt', 'info' ) . new Link('mysql', 'index', array(), $this->GlobalLocale->_('back'), true ) ;
   			}catch(AppException $e){
   				$Formular->Populate();
   				$html = Messagebox::Create($e->getMessage(), 'error'). new Link('mysql', 'index', array(), $this->GlobalLocale->_('back'), true) ;
   			}
    	}else{
    		$Formular->Populate() ;
    		$html = $Formular->GetHtml() ;
    	}


        $this->tpl->set('form', $html ) ;
        $this->tpl->set('text', '' ) ;

        return $this->tpl->GetHtml() ;
    }

    public function deleteAction()
    {
        $id = (int) Request::get('id' ) ;
       	try{
       		$Model = new M_Mysql() ;
       		$Model->DeleteMySQL($id );
       		return Messagebox::Create('Datenbank wurde gelöscht.', 'info' ) . new Link('mysql', 'index', array(), $this->GlobalLocale->_('back'), true ) ;
       	}catch(AppException $e){
       		return Messagebox::Create($e->getMessage(), 'error'). new Link('mysql', 'index', array(), $this->GlobalLocale->_('back'), true) ;
       	}

    }
	public function deleteuserAction()
	{
		$id = (int) Request::get('id' ) ;
		try{
			$Model = new M_Mysql() ;
			$Model->DeleteUser($id );
			return Messagebox::Create('Benutzer wurde gelöscht.', 'info' ) . new Link('mysql', 'index', array(), $this->GlobalLocale->_('back'), true ) ;
		}catch(AppException $e){
			return Messagebox::Create($e->getMessage(), 'error')
				. new Link('mysql', 'index', array(), $this->GlobalLocale->_('back'), true) ;
		}

	}

	public function adduserAction()
	{

		$Model 	= new M_Mysql() ;
		$Usage 	= new Usage();

		if (!$Usage->IsFree('mysql_accounts') )
			throw new AccessException('Kann keine weiteren Benutzer anlegen.' );

		$html = '';



		$Formular = $Model->GetAddUserForm();


		if ($Formular->WasSent()){
			try{
				$Formular->Validate();
				$Model->AddUser(User::GetId(),User::GetName(), Request::Get('pwd', true ),Request::Get('comment', true ) );
				$html = Messagebox::Create('Benutzer angelegt', 'info' ) . new Link('mysql', 'index', array(), $this->GlobalLocale->_('back'), true ) ;
			}catch(AppException $e){
				$Formular->Populate();
				$html =  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
			}

		}else{
			$Formular->Populate() ;
			$html = $Formular->GetHtml() ;
		}


		$this->tpl->set('form', $html ) ;
		$this->tpl->set('text', '' ) ;

		return $this->tpl->GetHtml() ;
	}
}