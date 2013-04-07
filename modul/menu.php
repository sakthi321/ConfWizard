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

class Menu
{
    private $links = array();

    /**
     * addLink()
     *
     * @param mixed $link
     * @return
     */
    public function AddLink($link)
    {
        $this->links[] = $link;
    }
    /**
     * render()
     *
     * @return
     */
    public function GetHtml()
    {
        $html = '<ul>';
        $first = true;
        foreach ($this->links as $link) {
            if(($link->GetPage() === REQUEST::Get('page')) OR ( ($link->GetPage() === 'index') && ( REQUEST::Get('page')===null))){
                $link->SetText('<li class="active">' . $link->GetText() . '</li>');
                $first = false;
            } else {
                $link->SetText('<li>' . $link->GetText() . '</li>');
            }

            $html .= $link->GetHtml();
        }
        $html .= '</ul>';
        return $html;
    }
}
