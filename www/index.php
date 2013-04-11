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

/////////////////////////   UPDATE CHECK (while updating the panel is locked)
$u = explode(';',file_get_contents('update.txt'));
if($u[0]!== '0'){
	echo '<!doctype html><html><head><meta charset="utf-8"><title>ConfWizard - Update</title><meta http-equiv="refresh" content="60">
<style>body {margin:50px 0px; padding:0px;text-align:center;background-color:#eee;font-family:Verdana, Geneva, sans-serif;font-size:12px;}#content {width:500px;margin:0px auto;text-align:left;padding:15px;border:1px solid #ccc;background-color:white;-webkit-border-radius: 15px;-moz-border-radius: 15px;border-radius: 15px;}marquee{border:1px solid #ccc;background-color:white;-webkit-border-radius: 8px;-moz-border-radius: 8px;border-radius: 8px;width:200px;text-align:center;}h1{font-size:18px; font-weight:normal;color:#6B2D69;}</style>
</head><body><div id="content"><h1>ConfWizard ('.date("Y-m-d H:i:s").')</h1><p>Gerade wird ein Update am Panel durchgeführt. Sie wurden ausgeloggt. Diese Seite aktualisiert sich automatisch. Bitte loggen Sie sich nach Freigabe erneut ein und überprüfen Sie ihre letzte Aktion, da diese wohlmöglich nicht ausgeführt wurde.</p><p>We are updating the panel. Your were logged out. This page is refreshing itself. Please login after approval of the panel and verify your last action, it could not be executed.</p><div style=" text-align:center;"><marquee scrollamount="5" scrolldelay="5" direction="right"><span style="width:50px; background-color:#6B2D69; ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></marquee></div><br/><br/>
	<div style="text-align:right; font-size:10px; color:#999;">
	ConfWizard 2013 by <a target="_blank" href="http://www.integralstudio.net">IntegralStudio</a>
	</div></div></body></html>';exit;
}
////////////////////////// boot and load core
include 'boot.php';


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ MENU ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Load main template file
$MASTER_TPL = new Template('template/master_template.txt');

// prepare Menu for navigation
$M_Menu1 = new Menu();
$M_Menu2 = new Menu();
$M_Menu3 = new Menu();

$M_Menu1->addLink(new Link('index', 'index', array(), Icon::Create('home').'Start'));
$M_Menu1->addLink(new Link('user', 'index', array(), Icon::Create('users').'Benutzer'));
#$M_Menu1->addLink(new Link('server', 'index', array(), Icon::Create('server').'Server'));
#$M_Menu1->addLink(new Link('security', 'index', array(), Icon::Create('shield').'Sicherheit'));
$M_Menu1->addLink(new Link('offers', 'index', array(), Icon::Create('store').'Angebotsvorlagen'));
$M_Menu1->addLink(new Link('customers', 'index', array(), Icon::Create('user-silhouette').'Kunden'));

$M_Menu2->addLink(new Link('ftp', 'index', array(), Icon::Create('folder-network').'FTP'));
$M_Menu2->addLink(new Link('mysql', 'index', array(), Icon::Create('database').'MySQL'));
$M_Menu2->addLink(new Link('cronjob', 'index', array(), Icon::Create('clock').'Cronjobs'));
$M_Menu2->addLink(new Link('email', 'index', array(), Icon::Create('mail').'Email'));
$M_Menu2->addLink(new Link('domain', 'index', array(), Icon::Create('globe').'Domains'));

#$M_Menu->addLink(new Link('server', 'index', array(), 'Server'));
#$M_Menu->addLink(new Link('security', 'index', array(), 'Sicherheit'));
#$M_Menu->addLink(new Link('demo', 'index', array(), 'Demo seite'));

$M_Menu3->addLink(new Link('help', 'index', array(), Icon::Create('lifebuoy').'Hilfe'));
$M_Menu3->addLink(new Link('tools', 'index', array(), Icon::Create('toolbox').'Tools'));
$M_Menu3->addLink(new Link('login', 'logout', array(), Icon::Create('door').'Logout'));

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PAGE ROUTING ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$PAGE_REQUEST = Request::GetPage();

if (Request::Exists('action'))
    $ACTION_REQUEST = REQUEST::GET('action');
else
    $ACTION_REQUEST = 'index';

if ($PAGE_REQUEST !== 'login')
{
    if (!User::IsLogin())
    {
        Header::StatusCode(403);
        Header::Location('index.php?page=login');
    }
}

include_once 'core/form.php';
$pathtest = new PathValidator(dirname(__FILE__));
$file = "pages/p_" . $PAGE_REQUEST . "/logic.php";
// prevent directory traversal
if(!$pathtest->Validate('/'.$file))
	throw new AccessException($PAGE_REQUEST.' directory traversal');
// load page
if (file_exists($file))
{
    require $file;
    $name = 'p_' . $PAGE_REQUEST;
    $CurrentPage = new $name();

    if ($ACTION_REQUEST != '')
        $content = $CurrentPage->call($ACTION_REQUEST);
    else
        $content = $CurrentPage->call('index');
    if ($CurrentPage->LoadFull())
    {
        $MASTER_TPL->set('content', $content);
    } else
    {
        echo $content;
        exit;
    }
} else
{
    $MASTER_TPL->set('content', '404 Page not found');
}
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ CREATE DOCUMENT ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

$MASTER_TPL->set('name', "ConfWizard");
$MASTER_TPL->set('javascript', $GlobalStack->javascript);
$MASTER_TPL->set('menu1', $M_Menu1->GetHtml());
$MASTER_TPL->set('menu2', $M_Menu2->GetHtml());
$MASTER_TPL->set('menu3', $M_Menu3->GetHtml());
$MASTER_TPL->set('title', $config['info']['title']);
$MASTER_TPL->show();
