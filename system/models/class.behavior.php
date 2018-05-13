<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/models/class.behavior.php
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

    class Behavior{
        /*
         |  GLOBAL VARs
         */
        static private $ordered = false;
        static private $behaviors = array();
        static private $loaded = array();

        /*
         |  ADD A NEW BEHAVIOR
         |  @since  0.8.4
         |
         |  @param  string  The behavior ID.
         |  @param  string  The relative path to the file starting at the plugin_id.
         |                  The absolute path to the file or just NULL.
         |  @param  string  The behavior title of NULL to use the ID.
         |
         |  @return bool    TRUE if the behavior/file could be added, FALSE if not.
         */
        static public function add($behavior_id, $file, $title = NULL){
            if(!is_null($file)){
                if(strpos($file, "content/plugins") === false){
                    $file = PLUGINS_DIR . $file;
                }
                $file = realpath($file);
            }

            // Check Params
            if(!is_null($file) && (!file_exists($file) || !is_file($file))){
                return false;
            }
            if(array_key_exists($behavior_id, self::$behaviors)){
                return false;
            }
            if(empty($title) || !is_string($title)){
                $title = Inflector::humanize($behavior_id);
            }

            // Add Behavior
            self::$behaviors[$behavior_id] = array(
                "file"  => $file,
                "title" => $title
            );
            return true;
        }

        /*
         |  REMOVE AN EXISTING BEHAVIOR
         |  @since  0.8.4
         |
         |  @param  string  The behavior ID.
         |
         |  @return bool    TRUE if the behavior could be removed, FALSe if not.
         */
        static public function remove($behavior_id){
            if(!array_key_exists($behavior_id, self::$behaviors)){
                return false;
            }
            unset(self::$behaviors[$behavior_id]);
            return true;
        }

        /*
         |  FIND ALL REGISTERED BEHAVIORS
         |  @since  0.8.4
         |
         |  @return array   An array with all behavior ids.
         */
        static public function findAll(){
            if(!self::$ordered){
                $order = array();
                foreach(self::$behaviors AS $key => $value){
                    $order[$key] = $value["title"];
                }
                array_multisort($order, SORT_ASC, self::$behaviors);
                self::$ordered = true;
            }
            return self::$behaviors;
        }

        /*
         |  LOAD A SPECIFIC BEHAVIOR
         |  @since  0.8.4
         |
         |  @param  string  The behavior ID as INT.
         |  @param  string  The current page content.
         |  @param  array   Some additional parameters for the behavior.
         |
         |  @return multi   The filter class object, FALSE on failure.
         */
        static public function load($behavior_id, &$page, $params = array()){
            if(!isset(self::$behaviors[$behavior_id])){
                return false;
            }
            $behavior = &self::$behaviors[$behavior_id];

            // Check Behavior
            if($behavior["file"] === NULL){
                return false;
            }

            // Load Behavior
            if(!isset($behavior["class"])){
                if(file_exists($behavior["file"])){
                    include_once($behavior["file"]);
                }

                // Get Class
                $class = Inflector::camelize($behavior_id);
                if(!class_exists($class, false)){
                    $class = str_replace("\\", "/", $behavior["file"]);
                    $class = trim(substr($class, strrpos($class)), "/");

                    $class = Inflector::camelize($class);
                    if(!class_exists($class, false)){
                        return false;
                    }
                }
                $behavior["class"] = $class;
            }
            return new $behavior["class"]($page, $params);
        }

        /*
         |  DO SOME PAGE BEHAVIOR HACKs
         |  @since  0.8.4
         |
         |  @param  string  The behavior ID as INT.
         |  @param  string  The current page content.
         |  @param  array   Some additional parameters for the behavior.
         |
         |  @return multi   The filter class object, FALSE on failure.
         */
        static public function loadPageHack($behavior_id){
            $class = Inflector::camelize("page_{$behavior_id}");

            if(class_exists($class, false)){
                return $class;
            }
            return "Page";
        }
    }
