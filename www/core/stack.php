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

class Stack
{
    private $data = array();

    /**
     * Stack::__construct()
     *
     */
    public function __construct()
    {
    }
    /**
     * Stack::set()
     *
     * @param mixed $key
     * @param mixed $value
     * @return
     */
    public function Set($key, $value)
    {
        $this->data[$key] = $value;
    }
    /**
     * Stack::get()
     *
     * @param mixed $key
     * @return
     */
    public function Get($key)
    {
        return $this->data[$key];
    }
    /**
     * Stack::__set()
     *
     * @param mixed $key
     * @param mixed $value
     * @return
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }
    /**
     * Stack::__get()
     *
     * @param mixed $key
     * @return
     */
    public function __get($key)
    {
        return $this->data[$key];
    }

    /**
     * Stack::push()
     *
     * @param mixed $var
     * @param mixed $data
     * @return
     */
    public function Push($var, $data)
    {

        if (!isset($this->data[$var])) {
            $this->data[$var] = array();
        }

        $z = $this->data[$var];
        $z[] = $data;
        $this->data[$var] = $z;
    }
}
