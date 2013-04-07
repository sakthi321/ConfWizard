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

class Locale
{
    protected $XMLData = null;
    protected $Language = 'de';
	protected $Loaded = false;
	protected $LanguageData = null;

    public function __construct($folder, $language = 'de')
    {

        $this->GlobalLocaleuage = $language;
        $file = $folder . $this->GlobalLocaleuage . '_lang.xml';

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
            $path = "/language[@id=\"{$this->GlobalLocaleuage}\"]/entry[@id=\"".$txt_id."\"]";
            $res = $this->XMLData->xpath($path);
        	if(isset($res[0]))
            	return (string) $res[0];
        	else
        		return '<span style="color:red; font-weight:bold;">__LOCALE_'.$txt_id.'_NOT_FOUND__</span>'.'';
        } else
        {
            throw new Exception('Text for ' . $txt_id . ' not found');
        }
    }

}
