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

class TreeIterator{
	private $Res = array();

	public function Get($Table,$RootId,$ParentColumnName,$ColumnToGet){
		$this->Res = array();
		$this->Sub($Table,$RootId,$ParentColumnName,$ColumnToGet);
		return $this->Res;
	}
	public function Sub($Table,$RootId,$ParentColumnName,$ColumnToGet){
		$Stm = new SQL();

		$Result = $Stm	->Select(array($ColumnToGet,'id',$ParentColumnName))
						->From($Table)
						->Where(array($ParentColumnName=>$RootId))
						->Execute()
						->FetchArraySet();
		foreach($Result as $Item){
			$this->Res[] = $Item[$ColumnToGet];
			$this->Sub($Table,$Item[$ColumnToGet],$ParentColumnName,$ColumnToGet);
		}
	}
}