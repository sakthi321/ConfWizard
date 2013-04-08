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

class Link
{
    private $page, $action, $params, $txt;
	public $IsButton;


    static public function Create($page = "", $action = 'index', $params =array(),$txt)
    {
        $t = '';
        foreach ($params as $key => $value)
        {
            $vv = urlencode($value);
            $vv = str_replace('#', 'sharpsharp', $vv);
            $t .= "&{$key}=" . $vv;
        }
        return "<a href=\"index.php?page={$page}&action={$action}{$t}\">{$txt}</a>";
    }
	static public function CreateHref($page = "", $action = 'index', $params =array())
	{
		$t = '';
		foreach ($params as $key => $value)
		{
			$vv = urlencode($value);
			$vv = str_replace('#', 'sharpsharp', $vv);
			$t .= "&{$key}=" . $vv;
		}
		return "index.php?page={$page}&action={$action}{$t}";
	}

    static public function Button($page = "", $action = 'index', $params =array(),$txt)
    {
        $t = '';
        foreach ($params as $key => $value)
        {
            $vv = urlencode($value);
            $vv = str_replace('#', 'sharpsharp', $vv);
            $t .= "&{$key}=" . $vv;
        }
        return "<a class=\"btnLink\" href=\"index.php?page={$page}&action={$action}{$t}\">{$txt}</a>";
    }
    /**
     * Link::__construct()
     *
     * @param mixed $page
     * @param mixed $action
     * @param mixed $params
     * @param mixed $txt
     */
    public function __construct($page, $action, $params=array(), $txt,$button=false)
    {
        $this->page = $page;
        $this->action = $action;
        $this->params = $params;
        $this->txt = $txt;
        $this->IsButton = $button;
    }

    public function __toString(){
        return $this->GetHtml();
    }

    /**
     * Link::GetHtml()
     *
     * @return
     */
    public function GetHtml()
    {
        $t = '';
        foreach ($this->params as $key => $value)
        {
            $t .= "&$key=" . $value;
        }
        $css = '';
        if($this->IsButton)
            $css = 'class=" btnLink "';
        else
            $css = '';
        return "<a {$css} href=\"index.php?page={$this->page}&action={$this->action}{$t}\">{$this->txt}</a>";
    }
    public function GetHref()
    {
        $t = '';
        foreach ($this->params as $key => $value)
        {
            $t .= "&$key=" . $value;
        }
        $css = '';
        return "index.php?page={$this->page}&action={$this->action}{$t}";
    }
    /**
     * Link::GetText()
     *
     * @return
     */
    public function SetButton($state){
        $this->IsButton = $state;
    }

    public function GetText()
    {
        return $this->txt;
    }
    public function SetText($txt)
    {
        $this->txt = $txt;
    }

    public function GetPage(){
        return $this->page;
    }

    public function SetParameterValue($k, $v)
    {
        $this->params[$k] = $v;
    }
    public function GetParameterValue($k)
    {
        return $this->params[$k];
    }
    public function IsParameter($k)
    {
        return in_array($k, array_keys($this->params));
    }
}
