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

class Template
{
    private $Source = '';
    private $Assignments = array();

    /**
     * template::__construct()
     *
     * @param mixed $file
     */
    public function __construct($file)
    {
        $this->Source = file_get_contents(strtolower($file));
    }
    /**
     * template::render()
     *
     * @return
     */
    public function GetHtml()
    {




        foreach ($this->Assignments as $key => $value) {
            $this->Source = str_replace('{{' . $key . '}}', $value, $this->Source);
        }

        // replace icons

        $suchmuster = '/<icon:(.*?)>/i';
        $ersetzung = '<img src="ficons/$1.png" />';
        $this->Source = preg_replace($suchmuster, $ersetzung, $this->Source);




        return $this->Source;
    }
    /**
     * template::show()
     *
     * @return
     */
    public function Show()
    {
        echo $this->GetHtml();
    }
    /**
     * template::set()
     *
     * @param mixed $key
     * @param mixed $value
     * @return
     */
    public function Set($key, $value=null)
    {


        if ($value instanceof Link){
            $this->Assignments[$key] = $value->render();
        }elseif( ($value === null) && is_array($key) ){
            foreach($key as $k=>$v)
                $this->Set($k,$v);
        } else{
            $this->Assignments[$key] = $value;
        }
    }

	public function GetSource(){
		return $this->Source;
	}
	public function SetSource($src){
		$this->Source = $src;
	}
}
