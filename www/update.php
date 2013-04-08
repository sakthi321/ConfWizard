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


function ServerOnline($site)
{
	$fp = @fsockopen($site, 80, $errno, $errstr, 2);
	return (!$fp);
}


$update_server = 'http://update.wieschoo.info';
$server_online = ServerOnline($update_server);

$current_version 	= explode(';',file_get_contents('update.txt')); $current_version = $current_version[1];
if($server_online){
	$latest_version 	= file_get_contents($update_server.'/version.txt');
	$new_version 		= version_compare($current_version, $latest_version, '<');
}else{
	$latest_version 	= '---';
	$new_version 		= false;
}

/* Usage:
   $status =  GetServerStatus('http://domain.com',80)
   or
   $status =  GetServerStatus('IPAddress',80)
*/





?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>ConfWizard - Update</title>
<meta http-equiv="refresh" content="60">
<style>
body {margin:50px 0px; padding:0px;text-align:center;background-color:#eee;font-family:Verdana, Geneva, sans-serif;font-size:12px;}
#content {width:500px;margin:0px auto;text-align:left;padding:15px;border:1px solid #ccc;background-color:white;-webkit-border-radius: 15px;-moz-border-radius: 15px;border-radius: 15px;}
marquee{border:1px solid #ccc;background-color:white;-webkit-border-radius: 8px;-moz-border-radius: 8px;border-radius: 8px;width:200px;text-align:center;}h1{font-size:18px; font-weight:normal;color:#6B2D69;}
.btnLink {
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #ffffff), color-stop(1, #cccccc) );
	background:-moz-linear-gradient( center top, #ffffff 5%, #cccccc 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#cccccc');
	background-color:#ffffff;
	-moz-border-radius:6px;
	-webkit-border-radius:6px;
	border-radius:6px;
	border-radius:3px;
	border:1px solid #787878;
	display:inline-block;
	color:#000000;
	font-family:arial;
	font-size:12px;
	font-weight:normal;
	padding:3px 12px;
	text-decoration:none;
}
.btnLink:hover {
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #cccccc), color-stop(1, #ffffff) );
	background:-moz-linear-gradient( center top, #cccccc 5%, #ffffff 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#cccccc', endColorstr='#ffffff');
	background-color:#cccccc;
	cursor: pointer;
	text-decoration:none;
}
</style>
</head>
<body>
<div id="content">
	<h1>ConfWizard - Update Assistent</h1>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr>
	    <td width="50%">UpdateServer:</td>
	    <td><?php echo $update_server; ?></td>
      </tr>
	  <tr>
	    <td>Status Updateserver:</td>
	    <td><?php echo ($server_online) ? 'Server ist online' : 'Server ist offline' ?></td>
      </tr>
	  <tr>
	    <td>your version:</td>
	    <td><?php
	if($new_version)
		echo '<span style="color:red; font-weight:bold;">'.$current_version.' update recommended!</span>';
	else
		echo '<span style="color:black;">'.$current_version.' no update possible.</span>';
	?></td>
      </tr>
	  <tr>
	    <td>latest version:</td>
	    <td><?php
echo $latest_version;
?></td>
      </tr>
	  <tr>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
      </tr>
	  <tr>
	    <td>&nbsp;</td>
	    <td></td>
      </tr>
  </table>
  <?php

if($new_version)
	echo '<a href="#" class="btnLink">start updateand get version '.$latest_version.' now</a>';

?>
  <br/><br/><br/><br/><br/>
	<div style=" text-align:center;">
		<marquee scrollamount="5" scrolldelay="5" direction="right"><span style="width:50px; background-color:#6B2D69; ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></marquee>
	</div><br/><br/>
	<div style="text-align:right; font-size:10px; color:#999;">
	ConfWizard 2013 by <a target="_blank" href="http://www.integralstudio.net">IntegralStudio</a>
	</div>
</div>
</body>
</html>