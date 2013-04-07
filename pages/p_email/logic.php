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
        $sql = new SQL();

        $Data = $sql->Select(array(
                    'command',
                    'comment',
                    'enabled',
                    'format',
                    'state',
                    'id'))->From('cw_cronjobs')->Where(array('user_id' => User::GetId()))->Execute()->fetchArraySet() ;

        $Model = new M_Cronjob();

        $capacity = $Model->GetCapacity(User::GetId()) ;

        //////////////// Ajax

        $Table = $Model->GetListView($Data) ;

        $current_cronjob = $capacity[0];
        $max_cronjob = ($capacity[1] == '-1') ? '&infin;' : $capacity[1] ;


        if( ($capacity[0]<$capacity[1]) OR ($capacity[1] == '-1')  ){
            $this->tpl->set('add_cronjob', Link::Button('cronjob', 'add', array(), Icon::Create('clock--plus').'Cronjob anlegen')) ;
        }else{
            $this->tpl->set('add_cronjob', 'Sie können keine weitere Cronjobs anlegen') ;
        }

        $this->tpl->set('current_cronjobs', $current_cronjob) ;
        $this->tpl->set('max_cronjobs', $max_cronjob) ;
        $this->tpl->set('listview', $Table->GetHtml()) ;


        return $this->tpl->GetHtml();
    }
    public function addAction(){

        $this->tpl->set('init', '0 2 * * *') ;
        $Model = new M_Cronjob() ;

        if(!$Model->CanAddNewAccount(User::GetId())){
                throw new AccessException('kann keinen weitere Datenbank anlegen.');
            }
        $Formular = $Model->GetForm();


        $Formular->AddElementsFromArray(array(array(
                'type' => 'Submit',
                'name' => 'save',
                'value' => 'anlegen')), 'Aktionen') ;

        $BackButton = new FormLink() ;
        $BackButton->SetLink(new Link('cronjob', 'index', array(), $this->GlobalLocale->_('back'))) ;
        $BackButton->SetFloat('yes') ;
        $Formular->AddElement($BackButton, 'Aktionen') ;
        $html = '';
        if ($Formular->WasSent() && $Formular->Validate())
        {
            if(!$Model->CanAddNewAccount(User::GetId())){
                throw new AccessException('kann keinen weiteren Cronjob anlegen.');
            }


            $F = new DatabaseTable('cw_cronjobs') ;
            $F->format = Request::Get('format',true) ;
            $F->enabled = Request::Get('enabled',true) ;
            $F->comment = Request::Get('comment',true) ;
            $F->command = Request::Get('command',true) ;
            $F->user_id = User::GetId().'' ;
            $F->state = '1' ;
            $F->Apply() ;

            $html = Messagebox::Create('Cronjob angelegt', 'info') . new Link('cronjob', 'index', array(), $this->GlobalLocale->_('back'), true) ;

        } else
        {
            if($Formular->WasSent())
                $this->tpl->set('init', Request::Get('format')."") ;

            $Formular->Populate() ;
            $html = $Formular->GetHtml() ;
        }



        $this->tpl->set('form', $html) ;
        $this->tpl->set('text', '') ;
        return $this->tpl->GetHtml() ;
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