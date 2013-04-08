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

class Quicktip
{
	protected $XMLData = null;
	protected $Language = 'de';
	protected $Loaded = false;
	protected $LanguageData = null;

	public function __construct($folder, $language = 'de')
	{

		$this->GlobalLocaleuage = $language;
		$file = $folder . $this->GlobalLocaleuage . '_quicktip.xml';

		if (is_file($file))
		{
			if ($this->XMLData = simplexml_load_file($file))
			{
				$this->Loaded = true;
				return true;
			}
		} else
		{
			#throw new Exception($file . ' not found');
		}
	}
	public function Display($txt_id)
	{
		echo $this->_($txt_id);
	}
	public function _($txt_id)
	{
		if ($this->XMLData !== null)
		{
			$path = "/quicktips/item[@id=\"{$txt_id}\"]";
			$res = $this->XMLData->xpath($path);
			if(isset($res[0])){
				return '<a href="#" title="'.$res[0].'">'.Icon::Create('question-balloon').'</a>';
			}
			else{
				return '<a href="#" title="QUICKTIP_NOT_FOUND">'.Icon::Create('question-balloon').'</a>';
			}
		} else
		{
			throw new Exception('no xml content in quicktip');
		}
	}

}
