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

class file{

	protected $Filename = null, $Content=array();

	/**
	 * file::__construct()
	 * open a text file
	 * @param mixed $pathtofile
	 */
	public function __construct($pathtofile){
		$this->Open($pathtofile);
	}
    
    public function Exists(){
        return file_exists($this->Filename);
    }

	public function Open($pathtofile){
		if(is_file($pathtofile) ){
			$this->SetContent(file_get_contents($pathtofile));
		}
		$this->Filename = $pathtofile;
	}

	/**
	 * file::Store()
	 * update the file
	 * @return
	 */
	public function Store(){
		file_put_contents($this->Filename,$this->GetContent());
	}

	/**
	 * file::GetContent()
	 * get temp content from file
	 * @return
	 */
	public function GetContent(){
		return implode(PHP_EOL,array_diff(array_map('trim', $this->Content), array('')));
	}

	/**
	 * file::SetContent()
	 * manipulate tmp content of file
	 * @param mixed $content
	 * @return
	 */
	public function SetContent($content){
		$this->Content = explode(PHP_EOL,$content);
	}

	/**
	 * file::GetLine()
	 * returns content of line with line number ___ or null
	 * @param mixed $line
	 * @return
	 */
	public function GetLine($line){
		if(isset($this->Content[$line]))
			return $this->Content[$line];
		return null;
	}

	/**
	 * file::SetLine()
	 * update the line in the temp content
	 * @param mixed $line
	 * @param mixed $content
	 * @return
	 */
	public function SetLine($line,$content){
		$this->Content[$line] = $content;
	}

	/**
	 * file::AddLine()
	 * add a line to the temp content
	 * @param mixed $content
	 * @return
	 */
	public function AddLine($content){
		$this->Content[] = $content;
	}
	/**
	 * file::DelLine()
	 * set a line empty in temp content
	 * @param mixed $line
	 * @return
	 */
	public function DelLine($line=null){
		if($line!==null)
			$this->Content[$line] = '';
	}


	/**
	 * file::Find()
	 * tries to find a line by given string and stop by first found or search all ($all=true)
	 * @param string $needle
	 * @param boolean $all
	 * @return linenumber or array of linenumbers, or null if not found
	 */
	public function Find($needle,$all=false){

		if($all){
			$result = array();
			for($i=0;$i<count($this->Content);$i++)
				if($this->in_string($needle,$this->Content[$i]))
					$result[] = $i;
			return $result;
		}else{
			for($i=0;$i<count($this->Content);$i++)
				if($this->in_string($needle,$this->Content[$i]))
					return $i;
		}
		return null;
	}

	/**
	 * file::in_string()
	 * tests if needle is in string
	 * @param mixed $needle
	 * @param mixed $haystack
	 * @param integer $insensitive
	 * @return
	 */
	protected function in_string($needle, $haystack, $insensitive = 0) {
		if ($insensitive) {
			return (false !== stristr($haystack, $needle)) ? true : false;
		} else {
			return (false !== strpos($haystack, $needle))  ? true : false;
		}
	}


}