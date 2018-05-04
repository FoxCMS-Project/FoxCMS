<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/models/class.setting.php
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

    class Setting extends Record{
        const TABLE = "config";

        /*
         |  DATA VARs
         */
        static public $loaded = false;
        static public $settings = array();

        /*
         |  INIT SETTINGs
         |  @since  0.8.4
         */
        static public function init(){
            if(self::$loaded){
                return true;
            }

            $settings = self::find(array("WHERE type = 'core'"));
            foreach($settings AS $setting){
                self::$settings[$setting->name] = $setting->value;
            }
            self::$loaded = true;
        }

        /*
         |  SET SETTING
         |  @since  0.8.4
         |
         |  @param  string  The settings name as STRING.
         |  @param  multi   The default returning value if the settings doesn't exist.
         |
         |  @return multi   The respective value for the setting, $default on failure.
         */
        static public function get($name, $default = false){
            if(!isset(self::$settings[$name])){
                return $default;
            }
            return unserializer(self::$settings[$name]);
        }

        /*
         |  SAVE NEW SETTINGS
         |  @since  0.8.4
         |
         |  @param  array   The key => value ARRAY paired settings.
         |
         |  @return int     The number of inserted / updated settings.
         */
        static public function saveFromData($data){
            $count = 0;
            foreach($data AS $name => $value){
                $result = Record::update("config", array("value" => ":value"), "name = :name", array(
                    ":value"    => serializer($value),
                    ":name"     => $name
                ));
                if($result){
                    $count++;
                }
            }
            return $count;
        }

        /*
         |  HELPER :: GET LANGUAGES
         |  @since  0.8.4
         */
        static public function getLanguages(){
            use_helper("I18n");
            return I18n::getAvailableLanguages();
        }

        /*
         |  HELPER :: GET THEMES
         |  @since  0.8.4
         */
        static public function getThemes(){
            $path = SYSTEM_DIR . "admin" . DS . "themes" . DS;
            $themes = array();
            if(is_dir($path) && $handle = opendir($path)){
                while(($theme = readdir($handle)) !== false){
                    if(in_array($theme, array(".", "..")) || !is_dir($path . $theme)){
                        continue;
                    }
                    $themes[$theme] = Inflector::humanize($theme);
                }
                closedir($handle);
            }
            return $themes;
        }


        /*
         |  INSTANCE VARs
         */
        public $id;
        public $name;
        public $value;
        public $type;
    }
