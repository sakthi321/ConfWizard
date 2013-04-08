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

// check if script runs cli mode
$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) != 'cli') {
	echo 'only cli!';exit;
}


include 'boot.php';

include 'systembot/log.php';
$Log = new Log();

interface Command{
	public function Execute(Log $Log,$Enviroment);
}



$directory = 'systembot/commands/';
$iterator = new DirectoryIterator($directory);

foreach ($iterator as $fileInfo) {
	if ($fileInfo->isFile()) {
		include $directory.$fileInfo->getFilename();
		$Classname = str_replace('.php','',$fileInfo->getFilename());

		$Log->Entry('command');

		$Command = new $Classname();
		$Command->Execute($Log,$Enviroment);

	}
}

#echo '<pre>';
#echo htmlspecialchars($Log);
#echo '</pre>';
