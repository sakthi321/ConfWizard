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

class Progressbar{


    public static function Create($val,$max,$rel=false,$width=300){


        $width .= 'px';
        if($max=='INF'){

            $p='0';
            $text = ($rel===true) ? round($p).'%' : $val.' / unbegrenzt '.$rel;
        }else{

        	if($max==null)
        		return '<div class="progress-bar" style="width: '.$width.'; text-align:center;">
  -
</div>';

        	$p=0;
        	if($val!= 0)
            	$p = ($val/$max*100);
            $text = ($rel===true) ? round($p).'%' : $val.' / '.$max.$rel;
        }




        return '<div class="progress-bar" style="width: '.$width.';">
  <div class="label" style="width: '.$width.';">'.$text.'</div>
  <div class="fill" style="   width: '.$p.'%;">
     <div class="label" style="width: '.$width.';">'.$text.'</div>
  </div>
</div>';

        #return '<span class="progressbar" ><span>'.$text.'</span><strong style="width: '.$p.'%;"><em>'.$text.'</em></strong></span>';

    }

}