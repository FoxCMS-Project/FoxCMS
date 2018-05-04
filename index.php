<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./index.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.8.4 [0.8.4] - Alpha
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */

    /*
     |  DEFINE BASICs
     */
    define("START",     microtime(true));
    define("FOXCMS",    "foxcms");

    define("DS",        DIRECTORY_SEPARATOR);
    define("BASE_DIR",  "." . DS);
    define("BASE_ROOT", realpath(BASE_DIR) . DS);

    /*
     |  INIT
     */
    if(file_exists(BASE_DIR . "config.php")){
        require_once(BASE_DIR . "config.php");
    }
    require_once(BASE_DIR . "defaults.php");
    require_once(BASE_DIR . "init.php");

    /*
     |  EOF HINT
     |  You can move the index.php file to another directory by
     |  simply changing the BASE_DIR constant. :3
     */
