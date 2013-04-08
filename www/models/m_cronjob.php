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

class M_Cronjob extends Model
{

	public function AddCronjob($format,$enabled,$comment,$command,$user_id){

		$Usage = new Usage();
		if (! $Usage->IsFree('cronjobs') )
			throw new AppException($this->Locale->_('CapacityExceeded'));

		$Adapter = new DatabaseTable('cw_cronjobs') ;
		$Adapter->format = (string) $format ;
		$Adapter->enabled = $enabled ;
		$Adapter->comment = (string) $comment ;
		$Adapter->command = (string) $command ;
		$Adapter->user_id = (int) $user_id ;
		$Adapter->state = 1 ;
		$Adapter->Apply() ;
	}
	public function UpdateCronjob($id,$format,$enabled,$comment,$command){
		if (!$this->Stm->Exists('cw_cronjobs', array('id' => $id, 'user_id' => User::GetId())))
			throw new AppException( $this->Locale->_('notown') ) ;

		$Adapter = new DatabaseTable('cw_cronjobs',$id) ;
		$Adapter->format = $format ;
		$Adapter->enabled = $enabled ;
		$Adapter->comment = $comment ;
		$Adapter->command = $command ;
		$Adapter->state = 2 ;
		$Adapter->Apply() ;
	}
	public function DeleteCronjob($id){
		if (!$this->Stm->Exists('cw_cronjobs', array('id' => $id, 'user_id' => User::GetId())))
			throw new AppException($this->Locale->_('notown'));

		$Adapter = new DatabaseTable('cw_cronjobs', $id) ;
		$Adapter->state = -1 ;
		$Adapter->Apply() ;
	}


    public function GetListView($data = array())
    {
        $sql = new SQL() ;
        //


        $Table = new Table(array(
           # 'id' => 'Id',
           'enabled' => 'Aktiviert',
            'format' => 'Format',
            'comment' => 'Kommentar',
           # 'command' => 'Befehl',
            'state' => 'Status'
            ), $data) ;

        $Table->SetActions(array(new Link('cronjob', 'edit', array('id' => ''), Icon::Create('property'), false), new Link('cronjob', 'delete', array('id' => ''), Icon::Create('cross-button'), false))) ;

        $Table->SetProcessor('enabled', new ActiveProcessor()) ;
        $Table->SetProcessor('state', new StateProcessor()) ;

        return $Table ;
    }

    public function GetForm($id=''){
        $Formular = new Form() ;
        $Formular->AddElementsFromArray(array(
         array(
                'type' => 'hidden',
                'name' => 'id',
                'value' => $id,
                'label' => ''),
            array(
                'type' => 'text',
                'name' => 'format',
                'value' => '',
                'label' => 'Zeit'),
            array(
                'type' => 'custom',
                'name' => 'cronjob',
                'text' => '<div id="selector" style="margin-left:205px;margin-bottom:10px;"></div>',
                'label' => ''),
            array(
                'type' => 'text',
                'name' => 'comment',
                'value' => '',
                'label' => 'Kommentar',
                'validators' => array(new MaxLengthValidator(50)),
                'size' => 40),
            array(
                'type' => 'text',
                'name' => 'command',
                'value' => '',
                'label' => 'Befehl',
                'validators' => array(new MaxLengthValidator(100), new EmptyValidator()),
                'size' => 40),
            array(
                'type' => 'radio',
                'name' => 'enabled',
                'value' => '0',
                'items' => array('0' => 'nein', '1' => 'ja'),
                'label' => 'Aktiviert'),




                ), 'Cronjob Einstellungen') ;
		$Formular->Sections['Cronjob Einstellungen']->Elements['format']->readonly = true;
    	$Formular->AddElementsFromArray(
	    	array(
		    	array(
		    		'type' => 'submit',
		    		'name' => 'submit',
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
		    		'link' => new Link('cronjob', 'index', array(), $this->GlobalLocale->_('back')),
		    		'name' => 'back',
		    		'float'	=>true
		    	)
		    ),
	    	'Aktionen'
    	);



        return $Formular ;
    }



}
