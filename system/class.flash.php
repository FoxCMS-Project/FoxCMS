<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/class.flash.php
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

    class Flash{
        /*
         |  GLOBAL VARs
         */
        static private $storage = array();

        /*
         |  INIT CLASS
         |  @since  0.8.4
         */
        static public function init(){
            if(!empty(self::$storage)){
                return;
            }
            
            if(isset($_SESSION[SESSION_KEY . "_flash"]) && is_array($_SESSION[SESSION_KEY . "_flash"])){
                self::$storage = $_SESSION[SESSION_KEY . "_flash"];
            }
            $_SESSION[SESSION_KEY . "_flash"] = array();
        }

        /*
         |  GET DATA FROM FLASH STORAGE
         |  @since  0.8.4
         |
         |  @param  string  The respective flash key.
         |  @param  multi   The default returning value, if the key doesn't exist.
         |
         |  @return multi   The respective flash value, or $default.
         */
        static public function get($key, $default = NULL){
            if(!is_string($key)){
                return $default;
            }
            return array_key_exists($key, self::$storage)? self::$storage[$key]: $default;
        }

        /*
         |  SET DATA TO THE FLASH STORAGE
         |  @since  0.8.4
         |
         |  @param  multi   The single flash key as STRING or a key => value paired ARRAY.
         |  @param  multi   The single flash value if $key is single too.
         |  @param  string  Use "session", "storage" or "both" for the respective storage target.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function set($key, $value = NULL, $type = "session"){
            if(is_string($key)){
                $key = array($key => $value);
            }
            if(!is_array($key)){
                return false;
            }

            // Set Data and return
            if(in_array($type, array("storage", "both"))){
                self::$storage = array_merge(self::$storage, $key);
            }
            if(in_array($type, array("session", "both"))){
                if(!is_array($_SESSION[SESSION_KEY . "_flash"])){
                    $_SESSION[SESSION_KEY . "_flash"] = array();
                }
                $_SESSION[SESSION_KEY . "_flash"] = array_merge($_SESSION[SESSION_KEY . "_flash"], $key);
            }
            return true;
        }
        static public function setNow($key, $value = NULL){
            return self::set($key, $value, "storage");
        }

        /*
         |  CLEAR THE FLASH
         |  @since  0.8.4
         |
         |  @param  bool    TRUE to clear the class storage too.
         */
        static public function clear($storage = true){
            $_SESSION[SESSION_KEY . "_flash"] = array();
            if($storage){
                self::$storage = array();
            }
        }
    }
