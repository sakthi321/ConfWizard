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

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ CONSTANTS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
define("NAMESPACE", "ConfWizard");
include 'configuration.php';
include 'core/exceptions.php';
define('PHP_INT_MIN', ~PHP_INT_MAX);

include_once 'core/form.php';
@header("Content-Type: text/html; charset=utf-8");

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
function in_string($needle, $haystack, $insensitive = 0) {
	if ($insensitive) {
		return (false !== stristr($haystack, $needle)) ? true : false;
	} else {
		return (false !== strpos($haystack, $needle))  ? true : false;
	}
}


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ AUTOLOAD ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
function __autoload($class)
{
	$class = strtolower($class);

	if ($class[0] . $class[1] === 'm_')
	{
		require_once 'models/'.$class . ".php";
	} else
	{
		if (file_exists("core/" . $class . ".php"))
		{
			$file = "core/" . $class . ".php";
		} elseif (file_exists($file = "modul/" . $class . ".php"))
		{
			$file = "modul/" . $class . ".php";
		}else{
			throw new Exception('file for '.$class.' not found');
		}
		require_once $file;
	}


}
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ INCLUDES ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
include 'systembot/settings.php';
include 'systembot/services/'.$Enviroment['current']['webserver']['name'].'.php';
include 'systembot/services/'.$Enviroment['current']['ftpserver']['name'].'.php';

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ INITALIZATION ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Database::Factory($config['DatabaseAdapter']);
User::Initialize();
$GlobalStack = new Stack();
$GlobalStack->javascript = '';
$GlobalLocale = new Locale("pages/");

