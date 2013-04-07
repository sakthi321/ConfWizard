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


class M_Mysql extends Model{

	protected $MySQLTunnel = null;

	public function __construct(){
		parent::__construct();
		if(defined("_LIVE_")){
		// create database
		include_once 'core/database_mysql.php';
		$this->MySQLTunnel  = DatabaseMySQL::singleton();
		global $Enviroment;

		$this->MySQLTunnel->connect(
			$Enviroment['mysql']['data']['databasehost'],
			$Enviroment['mysql']['data']['databaseuser'],
			$Enviroment['mysql']['data']['databasepassword']
		);
		}
	}

	public function NextFreeDatabase($id )
	{
		$sql = new SQL();
		$user = new M_User();
		$data = $user->GetUserData($id );

		$rs = $sql->Select('name' )->From('cw_mysqldbs' )->Where(array('user_id' => $id ) )->Execute()->FetchArraySet();

		$free = array();
		foreach($rs as $e ) {
			$free[] = str_replace($data['username'] . '_db_', '', $e['name'] );
		}
		$max_num = @max($free ) + 3;
		$take = - 1;
		for($i = 1;$i < $max_num + 1;$i++ ) {
			if (!in_array($i, $free ) ) {
				$take = $i;
				break;
			}
		}
		return $take;
	}
	public function NextFreeUser($id )
	{
		$sql = new SQL();
		$user = new M_User();
		$data = $user->GetUserData($id );

		$rs = $sql->Select('username' )->From('cw_mysqlaccounts' )->Where(array('user_id' => $id ) )->Execute()->FetchArraySet();

		$free = array();
		foreach($rs as $e ) {
			$free[] = str_replace($data['username'] . 'm', '', $e['username'] );
		}
		$max_num = @max($free ) + 3;
		$take = - 1;
		for($i = 1;$i < $max_num + 1;$i++ ) {
			if (!in_array($i, $free ) ) {
				$take = $i;
				break;
			}
		}
		return $take;
	}


	public function GetListView($data = array() )
	{
		$sql = new SQL() ;

		$Table = new Table(array(
		        // 'id' => 'Id',
		        'name' => 'Name',
		        'comment' => 'Kommentar',
		        'username' => 'zugeordneter Benutzer',
		        'state' => 'Status' ), $data ) ;

		$Table->SetActions(array(
				new Link('mysql', 'edit', array('id' => '' ), Icon::Create('property' ), false ),
		        new Link('mysql', 'delete', array('id' => '' ), Icon::Create('cross-button' ), false )
		        ) ) ;

		$Table->SetProcessor('state', new StateProcessor() ) ;

		return $Table ;
	}

	public function GetEditForm($id='' )
	{
		$usr = $this->GetUsers(User::GetId() );
		$Formular = new Form() ;
		$Formular->AddElementsFromArray(array(array(
		            'type' => 'hidden',
		            'name' => 'id',
		            'value' => $id,
		            'label' => '' ),
		array(
				'type' => 'select',
				'name' => 'dbuser_ids',
				'label' => 'Zugriff durch',
				'items' => $usr,

			)
		, array(
		            'type' => 'text',
		            'name' => 'comment',
		            'value' => '',
		            'label' => 'Kommentar',
		            'validators' => array(new MaxLengthValidator(50 ) ),
		            'size' => 40 ) ), 'MySQL Einstellungen ' ) ;

		$Formular->AddDefaultActions('mysql');

		return $Formular ;
	}
	public function GetAddForm()
	{
		$usr = $this->GetUsers(User::GetId() );
		$Formular = new Form() ;
		$Formular->AddElementsFromArray(array(array(
		            'type' => 'text',
		            'name' => 'comment',
		            'value' => '',
		            'label' => 'Kommentar',
		            'validators' => array(new MaxLengthValidator(50 ) ),
		            'size' => 40 ),
		array(
					'type' => 'select',
					'name' => 'dbuser_ids',
					'value' => '',
					'label' => 'Zugriff durch',
					'items' => $usr,

				)


		 ), 'MySQL Einstellungen ' ) ;

		$Formular->AddDefaultActions('mysql');

		return $Formular ;
	}

	public function AddMySQL($user_id, $name, $comment,$access_user )
	{
		// Plausibilitätstests
		$Usage 		= new Usage();
		if (!$Usage->IsFree('mysql_databases') ){
			throw new AppException('kann keinen weitere Datenbank anlegen.' );
		}

		if (!$this->Stm->Exists( 'cw_mysqlaccounts', array( 'id' => $access_user, 'user_id' => User::GetId() ) ) ){
			throw new AppException( 'dieser MySQL Benutzer gehört nicht Ihnen!' ) ;
		}




		$dbname  = $name . '_db_' . $this->NextFreeDatabase($user_id );
		$Adapter = new DatabaseTable('cw_mysqldbs' ) ;
		$Adapter->name = $dbname;
		$Adapter->comment = $comment ;
		$Adapter->user_id = $user_id ;
		$Adapter->dbuser_ids = $access_user ;
		$Adapter->state = (string) 0 ;
		$Adapter->Apply() ;


		if(defined("_LIVE_")){
			$User = new DatabaseTable('cw_mysqlaccounts',$access_user);
			// create Database
			$this->MySQLTunnel->Query("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8 COLLATE utf8_bin");
			// add privilegs to user
			$this->MySQLTunnel->Query("GRANT ALL PRIVILEGES ON `{$dbname}`.* TO '{$User->username}'@'localhost'");
			$this->MySQLTunnel->Query("FLUSH PRIVILEGES");
		}
	}
	public function DeleteMySQL($id )
	{
		if (!$this->Stm->Exists('cw_mysqldbs', array('id' => $id, 'user_id' => User::GetId() ) ) )
			throw new AppException('this is not your database!' ) ;

		$Adapter = new DatabaseTable('cw_mysqldbs', $id ) ;
		$Adapter->state = - 1 ;
		$Adapter->Delete() ;

		if(defined("_LIVE_")){
			$this->MySQLTunnel->Query("DROP DATABASE ".$Adapter->name);
		}

	}
	public function DeleteUser($id )
	{

		if (!$this->Stm->Exists('cw_mysqlaccounts', array('id' => $id, 'user_id' => User::GetId() ) ) )
			throw new AppException('this is not your user!' ) ;
		if ($this->Stm->Exists('cw_mysqldbs', array('dbuser_ids' => $id, 'user_id' => User::GetId() ) ) )
			throw new AppException('this user has access to a database. please cut the connection between user and db.' ) ;

		$Adapter = new DatabaseTable('cw_mysqlaccounts', $id ) ;
		$Adapter->state = - 1 ;
		$Adapter->Delete() ;
		if(defined("_LIVE_")){
			$this->MySQLTunnel->Query("DROP USER ".$Adapter->username);
		}
	}
	public function UpdateMySQL($id, $comment,$access_user )
	{
		if (!$this->Stm->Exists( 'cw_mysqlaccounts', array( 'id' => $access_user, 'user_id' => User::GetId() ) ) ){
			throw new AppException( 'dieser MySQL Benutzer gehört nicht Ihnen!' ) ;
		}
		if (!$this->Stm->Exists('cw_mysqldbs', array('id' => $id, 'user_id' => User::GetId() ) ) )
			throw new AppException('this is not your database!' ) ;

		$Adapter = new DatabaseTable('cw_mysqldbs', $id ) ;
		$Adapter->comment = $comment ;
		$Adapter->state = 2 ;
		$Adapter->dbuser_ids = $access_user ;
		$Adapter->Apply() ;
	}

	public function GetDatabases( $id )
	{
		$Statement = new SQL() ;
		$Result = $Statement
			->Select(array('id','user_id','name' ), false )
			->From('cw_mysqldbs' )
			->Where(array('user_id' => $id ))
			->Execute()
			->FetchArraySet() ;

		$Return = array();
		foreach($Result as $Entry ) {
			$Return[$Entry['id']] = $Entry['name'];
		}
		return $Return;
	}
	public function GetUsers( $id )
	{
		$Statement = new SQL() ;
		$Result = $Statement
			->Select('*' )
			->From('cw_mysqlaccounts' )
			->Where(array('user_id' => $id ))
			->Execute()
			->FetchArraySet() ;

		$Return = array();
		foreach($Result as $Entry ) {
			$Return[$Entry['id']] = $Entry['username'];
		}
		return $Return;
	}

	// user ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	public function GetListViewUser($data = array() )
	{
		$sql = new SQL() ;

		$Table = new Table(array(
		        // 'id' => 'Id',
		        'username' => 'Name',
		        'state' => 'Status' ), $data ) ;

		$Table->SetActions(array(
				new Link('mysql', 'edituser', array('id' => '' ), Icon::Create('property' ), false ),
		        new Link('mysql', 'deleteuser', array('id' => '' ), Icon::Create('cross-button' ), false )
		        ) ) ;

		$Table->SetProcessor('state', new StateProcessor() ) ;

		return $Table ;
	}
	public function GetAddUserForm()
	{

		$Formular = new Form() ;
		$Formular->AddElementsFromArray(array(
				array(
		            'type' => 'text',
		            'name' => 'comment',
		            'value' => '',
		            'label' => 'Kommentar',
		            'validators' => array(new MaxLengthValidator(50 ) ),
		            'size' => 40 )


		 ), 'MySQL Einstellungen ' ) ;
		$Formular->AddElementsFromArray(array(
		array(
		    'type' => 'password',
		    'name' => 'pwd',
		    'value' => '',
		    'label' => 'Password',
		    'validators' => array(
		        new EmptyValidator(),
		        new MaxLengthValidator(100),
		        new InputComparevalidator(array('pwd', 'pwd2'))),
		    'size' => 40),
		array(
		    'type' => 'password',
		    'name' => 'pwd2',
		    'value' => '',
		    'label' => 'Password (wiederholen)',
		    'validators' => array(
		        new EmptyValidator(),
		        new MaxLengthValidator(100),
		        new InputComparevalidator(array('pwd', 'pwd2'))),
		    'size' => 40)), 'Zugangsdaten');

		$Formular->AddDefaultActions('mysql');

		return $Formular ;
	}
	public function AddUser($user_id,$uname, $password, $comment )
	{
		$uname  = $uname . 'm' . $this->NextFreeUser($user_id );
		$Adapter = new DatabaseTable('cw_mysqlaccounts' ) ;
		$Adapter->user_id = $user_id ;
		$Adapter->username = $uname;
		$Adapter->password = $password;
		$Adapter->comment = $comment ;
		$Adapter->state = '0' ;
		$Adapter->Apply() ;

		if(defined("_LIVE_")){
			// create Database
			$this->MySQLTunnel->Query("CREATE USER '".$uname."'@'localhost' IDENTIFIED BY '".$password."';");
			$this->MySQLTunnel->Query("GRANT USAGE ON *.* TO '".$uname."'@'localhost' IDENTIFIED BY '".$password."' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;");
		}




	}
}