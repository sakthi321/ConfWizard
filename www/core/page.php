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


include_once 'core/form.php';


abstract class Page
{
    protected $name;
    protected $tpl;
    protected $link;
    protected $Stack;
    protected $mastertpl;
    protected $fullload = true;
    protected $Locale = null;
	protected $GlobalLocale = null;
	protected $Quicktip = null;
    protected $config=null;
	protected $Stm = null;

    protected $stop_output = false;


    /**
     * page::Stop()
     * do not produce any output but all "echo" for ajax results
     * @return
     */
    protected function Stop()
    {
        $this->fullload = false;
        $this->stop_output = true;
    }

    public function LoadFull(){
        return $this->fullload;
    }


    public function __construct()
    {
        global  $Stack,  $MASTER_TPL, $config;
        $this->Stack = &$Stack;
        $this->name = get_class($this);
        $this->mastertpl = $MASTER_TPL;
        $this->config = $config;
    	$this->Stm = new SQL();

        // load locale
        $LocalePath = "pages/" . strtolower($this->name).'/';
    	#echo $LocalePath;
        $this->Locale = new Locale($LocalePath);
    	$this->Quicktip = new Quicktip($LocalePath);
    	$this->GlobalLocale = new Locale("pages/");
    }

    /**
     * page::setTemplate()
     *
     * @param string $file path to template file
     * @return
     */
    protected function setTemplate($file)
    {
        $file = strtolower($file);
        if (file_exists($file)) {
            $this->tpl = new Template($file);

        	// replace locale
        	$src = $this->tpl->GetSource();
        	preg_match_all('/<locale:(.*?)>/',$src,$matches);
        	for($i=0;$i<count($matches[1]);$i++){
        		$src = str_replace($matches[0][$i],$this->Locale->_($matches[1][$i]),$src);

        	}
        	$this->tpl->SetSource($src);
        }
    }

    /**
     * page::call()
     * execute an given action and return produced output
     * @param mixed $action
     * @return
     */
    public function call($action = null)
    {

    	try{
    		global $config;
    		if ($action == null || $action == "") {
    			$action = "index";
    		}
    		if(User::GetGroup() === 'administrator')
    			return true;

    		if (!User::IsAllowedTo($this->name, $action)) {
    			ob_start();
    			ob_end_clean();
    			throw new AccessException('No access granted for '.$this->name.' and action '.$action.'!');
    		}
    		$name = $action . "Action";
    		if (method_exists($this, $name)) {
    			$this->setTemplate("pages/" . $this->name . "/" . $action .
    			    ".html");
    			$pre = $this->PreProcessor();
    			if ($this->stop_output)
    				return $pre;

    			$txt = $this->$name();


    			$txt = $this->PostProcessor($txt);
    			return $txt;
    		} else {

    			return $this->errorAction();

    		}
    	}catch(AccessException $e){
    		return $e->getMessage();
    	}


    }

    /**
     * page::Load()
     * load source from a given link
     * @param Link $link
     * @return raw source
     */
    public function Load($link){
        global $config;
        $url = $link->GetHref();
        $src = file_get_contents($url);
        return $src;
    }
    /**
     * page::PreProcessor()
     * this code will be executed before all code from the actions
     * @return
     */
    protected function PreProcessor()
    {

    }

	/**
	 * page::PostProcessor()
	 * this methode will be executed after all actions code
	 * @param string $src produced source of methode "___Action"
	 * @return
	 */
	protected function PostProcessor($src){
		// replace quicktips
		preg_match_all('/<qt:(.*?)>/i',$src,$ausgabe, PREG_PATTERN_ORDER);
		for($i=0;$i<count($ausgabe[0]);$i++)
			$src = str_replace($ausgabe[0][$i],$this->Quicktip->_($ausgabe[1][$i]),$src);
		return $src;
	}

    /**
     * page::indexAction()
     * default index action
     * @return
     */
    protected function indexAction()
    {
        return 'default indexAction';
    }

    /**
     * page::errorAction()
     * default error action
     * @return
     */
    protected function errorAction()
    {
        return 'page not found';
    }
}
