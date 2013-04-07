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

class M_Tools extends model{

	public function SSLListView($data = array())
	{
		$Table = new Table(array(
		   # 'id' => 'Id',
		   'commonName'=>$this->Locale->_('commonName'),
		   'organizationName' => $this->Locale->_('organizationName'),
		), $data) ;

		$Table->SetActions(array(
		    new Link('ftp', 'changepassword', array('id' => ''), Icon::Create('lock--arrow'), false),
		    new Link('ftp', 'edit', array('id' => ''), Icon::Create('property'), false),
		    new Link('ftp', 'delete', array('id' => ''), Icon::Create('cross-button'), false))) ;

		return $Table ;
	}

	public function CertificateForm($keytype='create'){

		global $config;


		$Formular = new Form();
		$Formular->AddElementsFromArray(
			array(
				array(
					'type' => 'select',
					'name' => 'strength',
					'quicktip' => 'strength',
					'value' => '',
					'label' => $this->Locale->_('keystrength'),
					'items' => array('2048'=>'2048 Bit','4096'=>'4096 Bit'),
					'size' => 40),
				array(
					'type' => 'button',
					'name' => 'btnGenKey',
					'value' => $this->Locale->_('create_key'),
					'label' => $this->Locale->_('create_key'),
					'size' => 40)
			),
			'gen_key',
			'Schlüssel erzeugen'
		);

		$JavascriptRetreivePrivateKey = "\$('#keytext_generated').val('wird geladen...');$.get('index.php?page=ajax&action=privatekey&strength='+\$('#strength').val(),{ }, function(responseText){\$('#keytext_generated').val(responseText);});";
		$Formular->Sections['gen_key']->Elements['btnGenKey']->Events['onclick'][] = $JavascriptRetreivePrivateKey;
		$Formular->Sections['gen_key']->active = false;
		$Formular->AddElementsFromArray(
			array(
				array(
					'type' => 'checkbox',
					'name' => 'keygen',
					'label' => 'Schlüssel erzeugen',
					'items' => array('foo'=>'Dialog anzeigen')
				),
				array(
					'type' => 'textarea',
					'name' => 'keytext_generated',
					//'readonly' => true,
					'cols' => 75,
					'rows'=>12
				),
			),
			'key_raw',
			'Schlüsseldaten'
		);

		$js = "if(this.checked){\$('#".$Formular->Sections['gen_key']->id."').show();}else{\$('#".$Formular->Sections['gen_key']->id."').hide();}";
		$Formular->Sections['key_raw']->Elements['keygen']->Events['onclick'][] = $js;


		$Formular->AddElementsFromArray(
			array(
				array(
				    'type' => 'text',
				    'name' => 'commonName',
				    'quicktip' => 'commonName',
				    'value' => '',
				    'label' => $this->Locale->_('commonName'),
				    'placeholder' => 'www.example.tld',
				    'validators' => array(new EmptyValidator(), new MaxLengthValidator(100)),
				    'size' => 40),
				array(
				    'type' => 'text',
				    'name' => 'organizationName',
				    'quicktip'=>'organizationName',
				    'value' => '',
				    'label' => $this->Locale->_('organizationName'),
				    'placeholder' => 'World Peace',
				    'validators' => array(new EmptyValidator(), new MaxLengthValidator(100)),
				    'size' => 40),
				array(
					'type' => 'text',
					'name' => 'organizationalUnitName',
					'quicktip'=>'organizationalUnitName',
					'value' => '',
					'label' => $this->Locale->_('organizationalUnitName'),
					'placeholder' => 'World Peace / Tierschutz',
					'validators' => array(new MaxLengthValidator(100)),
					'size' => 40),
				array(
					'type' => 'text',
					'name' => 'localityName',
					'quicktip'=>'localityName',
					'value' => '',
					'label' => $this->Locale->_('localityName'),
					'placeholder' => 'Potsdam',
					'validators' => array(new EmptyValidator(), new MaxLengthValidator(100)),
					'size' => 40),
				array(
					'type' => 'select',
					'name' => 'countryName',
					'quicktip'=>'countryName',
					'value' => '',
					'items' => $config['countries'] ,
					'label' => $this->Locale->_('countryName'),),
				array(
					'type' => 'text',
					'name' => 'stateOrProvinceName',
					'quicktip'=>'stateOrProvinceName',
					'value' => '',
					'label' => $this->Locale->_('stateOrProvinceName'),
					'placeholder' => 'Brandenburg',
					'validators' => array(new MaxLengthValidator(100)),
					'size' => 40),
				array(
					'type' => 'text',
					'name' => 'emailAddress',
					'quicktip'=>'emailAddress',
					'value' => '',
					'label' => $this->Locale->_('emailAddress'),
					'placeholder' => 'mail@adress.com',
					'validators' => array(new EmptyValidator(), new MaxLengthValidator(100),new EmailValidator()),
					'size' => 50
				),
				array(
					'type' => 'button',
					'name' => 'btnGenCSR',
					'value' => $this->Locale->_('create_csr'),
					'label' => $this->Locale->_('create_csr'),
					'size' => 40)
			),
			'gen_csr',
			'CSR Request Daten'
		);
		$url = 'index.php?page=ajax&action=csr&';
		foreach($Formular->Sections['gen_csr']->Elements as $k => $el){
			$url .= $k.'=\'+$(\'#'.$k.'\').val()+\'&';
		}






		$JavascriptRetreive = "var s='".$url."';\$('#csrtext_generated').val('wird geladen...');$.get(s,{ }, function(responseText){\$('#csrtext_generated').val(responseText);});";
		$JavascriptRetreive = "
$.post('index.php?page=ajax&action=csr', $('#".$Formular->id."').serialize()).done(function(data) {\$('#csrtext_generated').val(data);});";
		$Formular->Sections['gen_csr']->Elements['btnGenCSR']->Events['onclick'][] = $JavascriptRetreive;

		$Formular->AddElementsFromArray(
			array(
				array(
					'type' => 'checkbox',
					'name' => 'csrgen',
					'label' => 'CSR erzeugen',
					'items' => array('foo'=>'Dialog anzeigen')
				),
				array(
					'type' => 'textarea',
					'name' => 'csrtext_generated',
					//'readonly' => true,
					'cols' => 75,
					'rows'=>12
				),
			),
			'csr_raw',
			'CSR Daten'
		);

		$js = "if(this.checked){\$('#".$Formular->Sections['gen_csr']->id."').show();}else{\$('#".$Formular->Sections['gen_csr']->id."').hide();}";
		$Formular->Sections['csr_raw']->Elements['csrgen']->Events['onclick'][] = $js;
		$Formular->Sections['gen_csr']->active = false;

		return $Formular;

	}

	public function CSRForm(){
		global $config;
		$Formular = new Form();



		return $Formular;
	}
	public function GenKeyForm(){
		$Formular = new Form();
		$Formular->AddElementsFromArray(array(
		array(
		    'type' => 'Select',
		    'name' => 'strength',
		    'quicktip' => 'strength',
		    'value' => '',
		    'label' => $this->Locale->_('keystrength'),
		    'items' => array('2048'=>'2048 Bit','4096'=>'4096 Bit'),
		    'validators' => array(new SelectValueFilter(), new AllowValueFilter(array('2048','4096'))),
		    'size' => 40)
	)
		, 'Allgemein');


		return $Formular;
	}

	public function InsertKey($strength, $user_id){

		$config = array(
		"private_key_bits" => (int) Request::Get('strength'),
		'digest_alg' => 'md5',
		'x509_extensions' => 'v3_ca',
		'req_extensions'   => 'v3_req',
		'private_key_type' => OPENSSL_KEYTYPE_RSA
	);


		// Create the private and public key
		$res = openssl_pkey_new($config);

		// Extract the private key from $res to $privKey
		openssl_pkey_export($res, $privKey);

		// Extract the public key from $res to $pubKey
		$pubKey = openssl_pkey_get_details($res);
		$pubKey = $pubKey["key"];

		$Adapter = new DatabaseTable('cw_certificate');
		$Adapter->public_key = $pubKey;
		$Adapter->private_key = $privKey;
		$Adapter->user_id = $user_id;
		$Adapter->createtime = Util::Now();
		$Adapter->Apply();

		return $Adapter->id;

	}




}