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


class P_email extends page{


    public function indexAction(){
          $Model   = new M_Mail();
          $Data   = $Model->GetMailAdresses();
          $Usage  = new Usage();
          $Table   = $Model->GetListView($Data) ;
  
        if ( $Usage->IsFree('mail_adresses') ) {
          $this->tpl->set( 'add_adress', Link::Button( 'email', 'addadress', array(), Icon::Create( 'mail--plus' ) . $this->Locale->_('AddAdress') ) ) ;
        } else {
          $this->tpl->set( 'add_adress', $this->Locale->_('AdressCapacityExceeded') ) ;
        }


        return $this->tpl->GetHtml();
    }
     public function addadressAction(){
        $html     = '';
        $Model     = new M_Mail();
        $Formular   = $Model->GetForm();
        $Formular->AddDefaultActions('email');
        
        
        if ($Formular->WasSent()){
            try{
                $Formular->Validate();
                $Model->InsertAdress(
                User::GetId(),
                Request::Get( 'domain_id', true ),
                Request::Get( 'target_mails', true ),
                Request::Get( 'target_accounts', true )
                );
                $html =  Messagebox::Create( $this->Locale->_('AdressWasInsert'), 'info' ) . new Link( 'email', 'index', array(), $this->GlobalLocale->_('back'), true);
            }catch(AppException $e){
                $Formular->Populate();
                $html =  Messagebox::Create( $e->getMessage(), 'error' ).$Formular->GetHtml();
            }
            
        }else{
            $Formular->Populate() ;
            $html = $Formular->GetHtml() ;
        }
        
        $this->tpl->set(
            array(
                'form'  => $html
            )
        );
        return $this->tpl->GetHtml();
    }
    public function editAction(){



        $id = (int)Request::get('id') ;

        // test if user can apply changes
        $sql = new SQL() ;
        if (!$sql->Exists('cw_cronjobs', array('id' => $id, 'user_id' => User::GetId())))
        {
            throw new AccessException('this is not your cronjob!') ;
        }
        $html = '';

        $Data = $sql->Select(array('command',
                    'comment',
                    'enabled',
                    'format',
                    'state',
                    'id'))->From('cw_cronjobs')->Where(array('id' => $id))->Execute()->FetchArray() ;

        $Model = new M_Cronjob();

        $this->tpl->set('init', $Data['format']) ;

        $Formular = $Model->GetForm($id);

        $Formular->AddElementsFromArray(array(array(
                'type' => 'Submit',
                'name' => 'save',
                'value' => 'ändern')), 'Aktionen') ;



        $BackButton = new FormLink() ;
        $BackButton->SetLink(new Link('cronjob', 'index', array(), $this->GlobalLocale->_('back'))) ;
        $BackButton->SetFloat('yes') ;
        $Formular->AddElement($BackButton, 'Aktionen') ;


        if ($Formular->WasSent() && $Formular->Validate())
        {

            $F = new DatabaseTable('cw_cronjobs',$id) ;
            $F->format = Request::Get('format',true) ;
            $F->enabled = Request::Get('enabled',true) ;
            $F->comment = Request::Get('comment',true) ;
            $F->command = Request::Get('command',true) ;
            $F->state = '2' ;
            $F->Apply() ;

            $html = Messagebox::Create('Änderungen durchgeführt', 'info') . new Link('cronjob', 'index', array(), $this->GlobalLocale->_('back'), true) ;

        } else
        {
            if($Formular->WasSent()){
                $this->tpl->set('init', Request::Get('format')."") ;
                $Formular->Populate() ;
            }


            $Formular->Populate($Data) ;
            $html = $Formular->GetHtml() ;
        }


        $this->tpl->set('form', $html) ;
        $this->tpl->set('text', '') ;
        return $this->tpl->GetHtml() ;
    }

}