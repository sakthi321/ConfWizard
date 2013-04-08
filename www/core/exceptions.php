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

class myException extends Exception{
    protected $Message = null;
    protected $Reason = '';
    protected $Code = null;
    protected $Title = 'Error';
    protected $Stop = true;

    final public function __construct($Message = null, $Code = null )
    {


        $msg = '<div style="font-size:11px;font-family:Verdana, Arial, Helvetica, sans-serif;width:500px; margin-top:60px;margin:0px auto;border:#999999 solid 1px; ">
<div style="background-color:#999999; color:#FFFFFF; padding:2px;">' . $this->Title . '</div>
<table width="100%" cellspacing="9" cellpadding="9" style="font-size:11px;font-family:Verdana, Arial, Helvetica, sans-serif;border-collapse:collapse;">
  <tr style="border-bottom: 1px solid #999999;">
    <td width="20%"><strong>Reason</strong></td>
    <td>' . $this->Reason . '</td>
  </tr><tr style="border-bottom: 1px solid #999999;">
    <td width="20%"><strong>Message</strong></td>
    <td>' . $Message . '</td>
  </tr>

  <tr style="border-bottom: 1px solid #999999;">
    <td width="20%"><strong>Code</strong></td>
    <td>' . $Code . '</td>
  </tr>
  <tr style="color:#666666; text-align:right;">
    <td>&nbsp;</td>
    <td>(c) 2013 ConfWizard - www.integralstudio.net</td>
  </tr>
</table>
</div>';
        return parent::__construct($msg, $Code);
    }
}

class AccessException extends myException {
    protected $Title = 'Access Exception';
    protected $Reason = 'The script stops because you were not allowed to execute this action or an error occurs';
}

class AppException extends Exception {

}
class CodeException extends Exception {

}

?>