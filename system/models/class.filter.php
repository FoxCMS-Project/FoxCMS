<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/models/class.filter.php
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

    class Filter{
        /*
         |  DATA VARs
         */
        static public $filters = array();
        static public $loaded = array();

        /*
         |  ADD A NEW FILTER
         |  @since  0.8.4
         |
         |  @param  string  The filter ID.
         |  @param  string  The relative path to the file starting at the plugin_id.
         |                  The absolute path to the file.
         |
         |  @return bool    TRUE if the filter/file could be added, FALSE if not.
         */
        static public function add($filter_id, $file){
            if(strpos($file, "content/plugins") === false){
                $file = PLUGINS_DIR . $file;
            }
            $file = realpath($file);

            // Check Params
            if(!file_exists($file) || !is_file($file)){
                return false;
            }
            if(array_key_exists($filter_id, self::$filters)){
                return false;
            }

            self::$filters[$filter_id] = $file;
            return true;
        }

        /*
         |  REMOVE AN EXISTING FILTER
         |  @since  0.8.4
         |
         |  @param  string  The filter ID.
         |
         |  @return bool    TRUE if the filter could be removed, FALSe if not.
         */
        static public function remove($filter_id){
            if(!array_key_exists($filter_id, self::$filters)){
                return false;
            }
            unset(self::$filters[$filter_id]);
            return true;
        }

        /*
         |  FIND ALL REGISTERED FILTER
         |  @since  0.8.4
         |
         |  @return array   An array with all filter ids.
         */
        static public function findAll(){
            return array_keys(self::$filters);
        }

        /*
         |  GET A SPECIFIC FILTER
         |  @since  0.8.4
         |
         |  @param  string  The filter ID.
         |
         |  @return multi   The filter class object, FALSE on failure.
         */
        static public function get($filter_id){
            if(!isset(self::$loaded[$filter_id])){
                if(!isset(self::$filters[$filter_id])){
                    return false;
                }
                if(!file_exists(self::$filters[$filter_id])){
                    return false;
                }
                include_once(self::$filters[$filter_id]);

                // Init Filter Class
                $class = Inflector::camelize($filter_id);
                if(!class_exists($class, false)){
                    $class = str_replace("\\", "/", self::$filters[$filter_id]);
                    $class = trim(substr($class, strrpos($class)), "/");

                    $class = Inflector::camelize($class);
                    if(!class_exists($class, false)){
                        return false;
                    }
                }
                self::$loaded[$filter_id] = new $class();
            }
            return self::$loaded[$filter_id];
        }
    }
