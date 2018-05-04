<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./init.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.8.4 [0.8.4] - Alpha
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */
    if(!defined("FOXCMS") || !defined("BASE_DIR") || !defined("SYSTEM_DIR")){ die(); }

    /*
     |  DEFINE BASICs
     */
    define("FOX_VERSION",   "0.8.4");
    define("FOX_STATUS",    "Alpha");
    define("FOX_CODE",      "FATW_0.8.4-alpha#00000001"); // <code_version-status#build>
    define("CMS_VERSION",   FOX_VERSION . "-" . strtolower(FOX_STATUS)); // semantic-version

    // Public Globals
    global  $Fox,                   // The main Fox CMS Class.
            $FoxDB,                 // The main Fox CMS DB Class (aka `Record`).
            $FoxPage;

    // Pseudo 'Protected' Globals
    global  $_fox_deprecated;       // Stores each deprecated function-call.

    /*
     |  LOAD BASICs
     */
    require_once(SYSTEM_DIR . "func.general.php");          // General Functions
    require_once(SYSTEM_DIR . "func.fox.php");              // FoxCMS Functions
    require_once(SYSTEM_DIR . "func.deprecated.php");       // WolfCMS functions

    // Basic Classes
    require_once(SYSTEM_DIR . "class.auto-loader.php");     // Core :: AutoLoader Script
    require_once(SYSTEM_DIR . "class.fox.php");             // Core :: Main CMS Class
    require_once(SYSTEM_DIR . "class.record.php");          // Core :: Database Class
    require_once(SYSTEM_DIR . "class.event.php");           // Core :: Event Handler (replaces Observer)
    require_once(SYSTEM_DIR . "class.flash.php");           // Core :: Status Handler
    require_once(SYSTEM_DIR . "class.finder.php");          // Helper :: Finder Class
    require_once(SYSTEM_DIR . "class.inflector.php");       // Helper :: Inflector Class
    require_once(SYSTEM_DIR . "class.dispatcher.php");      // MVC :: Router
    require_once(SYSTEM_DIR . "class.controller.php");      // MVC :: Controller
    require_once(SYSTEM_DIR . "class.view.php");            // MVC :: View

    /*
     |  INIT FOX CMS
     */
    $Fox = new Fox();
    $Fox->init();

    // Check Configuration
    if(!$Fox->check()){
        if(FOXCMS == "wizard"){
            return;
        }
        header("Location: ./system/wizard/");
        die();
    }

    // Load Fox CMS
    $Fox->load();
    $Fox->render();
