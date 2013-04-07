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


class Log{
	protected $XML = null;
	protected $entry = null;

	public function __construct(){
		$this->XML = simplexml_load_string('<?xml version="1.0" encoding="UTF-8" ?><content></content>');
	}

	public function Entry($name = 'command',$time=null){
		if($time===null)
			$time =Util::Now();
		$this->entry = $this->XML->addChild($name);
		$this->entry['time'] = $time;
	}
	public function Write($msg,$result=1){
		$this->entry->addChild('msg',$msg);
	}
	public function __toString(){
		return $this->XML->asXML();
	}
}