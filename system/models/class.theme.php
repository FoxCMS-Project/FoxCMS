<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/class.theme.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.8.4 [0.8.4] - Alpha
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */
    if(!defined("FOXCMS")){ die(); }

    class Theme{
        /*
         |  GLOBAL VARIABLEs
         */
        static public $themes = array();

        /*
         |  HELPER :: FIND ALL THEMES
         |  @since  0.8.4
         |
         |  @return array   An array with all valid theme folders / ids.
         */
        static public function findAll(){
            return self::$themes;
        }

        /*
         |  INIT
         |  @since  0.8.4
         */
        static public function init(){
            $dir = THEMES_ROOT;

            // Fetch Plugin Folder
            $themes = array();
            if($handle = opendir($dir)){
                while(($theme_id = readdir($handle)) !== false){
                    if(in_array($theme_id, array(".", "..")) || is_file($dir . $theme_id)){
                        continue;
                    }
                    $path = $dir . $theme_id . DS;
                    $i18n = $path . "i18n" . DS;

                    // Add
                    $themes[$theme_id] = array(
                        "id"        => $theme_id,
                        "folder"    => $theme_id
                    );
                    if(file_exists($path . "theme.json")){
                        $data = file_get_contents($path . "theme.json");
                        $data = json_decode($data, true);
                        if(is_array($data)){
                            $themes[$theme_id] = array_merge($data, $themes[$theme_id]);
                        }
                        $themes[$theme_id]["type"] = "Fox CMS";
                    } else {
                        $themes[$theme_id]["type"] = "Wolf CMS";
                    }
                    if(!isset($themes[$theme_id]["title"])){
                        $themes[$theme_id]["title"] = $theme_id;
                    }

                    // Get Translations
                    if(is_dir($i18n) && file_exists($i18n . I18n::getLocale() . "-message.php")){
                        $strings = include_once($i18n . I18n::getLocale() . "-message.php");
                        I18n::add($strings);
                    }
                    if(is_dir($i18n) && file_exists($i18n . DEFAULT_LANGUAGE . "-message.php")){
                        $defaults = include_once($i18n . DEFAULT_LANGUAGE . "-message.php");
                        I18n::add($defaults, true);
                    }
                }
                closedir($handle);
            }
            self::$themes = $themes;
        }
    }
