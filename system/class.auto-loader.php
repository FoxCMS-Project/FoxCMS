<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/class.auto-loader.php
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

    /*
     |  The AutoLoader class is an object-orientated hook into PHP's __autoload functionality.
     |
     |  You can use this AutoLoader class to add single and multiple PHP class files as well
     |  as to walk through entire folders (doesn't walk through sub directories!).
     |
     |# EXAMPLE
     |  Single file:
     |      AutoLoader::addFile("MyClassName", "/path/to/MyClassName.php")
     |
     |  Multiple Files:
     |      AutoLoader::addFile(array(
     |          "MyClassName"   => "/path/to/MyClassName.php",
     |          "AnotherClass"  => "/path/to/class.another-class.php"
     |      ));
     |
     |  Whole Folders:
     |      AutoLoader::addFolder("path/to")
     |
     |# IMPORTANT (FOLDER WALK ONLY!)
     |  When you walk through complete folders, each file MUST USE one of the following formats
     |  compared with the contained PHP class (within the file):
     |
     |  The 'known' camelize option:
     |  Filename:   MyAwesomeClass.php
     |  PHP Class:  class MyAwesomeClass{ }
     |
     |  The underscored option:
     |  Filename:   class.my_awesome_class.php
     |  PHP Class:  class MyAwesomeClass{ }
     |
     |  The minuscored option
     |  Filename:   class.my-awesome-class.php
     |  PHP Class:  class MyAwesomeClass{ }
     |
     |  You can use 'abstract.', 'interface.' or 'trait.' instead of 'class.'. Files which starts
     |  with the prefix 'func.' or 'conf.' as well as all files which doesn't ends with '.php'
     |  gets automatically ignored!
     */
    class AutoLoader{
        /*
         |  DATA STORAGE
         */
        static protected $classes = array();
        static protected $folders = array();
        static protected $loaded = array();


        /*
         |  CORE :: INIT AUTOLOADER
         |  @since  0.8.4
         |
         |  @return void
         */
        static public function init(){
            spl_autoload_register(array("AutoLoader", "load"), true, true);
        }
        static public function register(){
            return self::init(); // @deprecated due the new format standards.
        }

        /*
         |  HELPER :: VALIDATE AND FORMAT CLASS NAME
         |  @since  0.8.4
         |
         |  @param  string  The file name (without path) as STRING.
         |  @param  multi   The class name to compare, NULL to don't compare anything.
         |
         |  @return multi   The formatted class name through the file name on success,
         |                  FALSE on failure.
         */
        static private function validate($file, $class = NULL){
            if(!ends_with($file, ".php") || starts_with($file, array("func.", "conf."))){
                return false;
            }

            // Format Filename
            $filename = substr($file, 0, strlen($file)-4);
            if(starts_with($filename, array("class.", "abstract.", "interface.", "trait."))){
                $filename = explode(".", $filename, 2)[1];
                $filename = str_replace(array("_", "-"), " ", $filename);
                $filename = str_replace(" ", "", ucwords($filename));
            }

            // Validate Filename
            $filename = trim($filename);
            if(empty($class) || ($class && ($class == $filename))){
                return $filename;
            }
            return false;
        }

        /*
         |  CORE :: ADD A (SET OF) FILE(s)
         |  @since  0.8.4
         |
         |  @param  multi   A single class name as STRING or multiple 'class_name' => filepath
         |                  ARRAY pairs.
         |  @param  multi   The path/to/the/file.php as STRING, or NULL if $class is an ARRAY:
         |
         |  @return multi   The added class names as ARRAY  (if $class is an ARRAY),
         |                  An empty ARRAY on failure       (if $class is an ARRAY),
         |                  The added class name as STRING  (if $class is a STRING),
         |                  FALSE on failure                (if $class is a STRING)
         */
        static public function addFile($class, $file = NULL){
            if(is_array($class)){
                $return = array();
                foreach($class AS $c => $f){
                    if(self::addFile($c, $f)){
                        $return[] = $c;
                    }
                }
                return $return;
            }

            // Check File
            if(!file_exists($file) || !is_file($file)){
                return false;
            }
            if(in_array($file, self::$classes)){
                return array_search($file, self::$classes);
            }

            // Add File
            self::$classes[$class] = $file;
            return $class;
        }

        /*
         |  CORE :: WALK THROUGH A COMPLETE FOLDER
         |  @since  0.8.4
         |
         |  @param  multi   The path/to/the/folder which contains the PHP class files as STRING,
         |                  multiple PATHs as ARRAY.
         |
         |  @return multi   An array with all added class names on success, an empty ARRAY
         |                  if no valid class could be found, FALSE on failure.
         */
        static public function addFolder($path){
            if(is_array($path)){
                $return = array();
                foreach($path AS $p){
                    $return[$p] = self::addFolder($p);
                }
                return $return;
            }

            // Check Path
            $path = str_replace("\\", "/", trim($path, "/\\"));
            if(!file_exists($path) || !is_dir($path)){
                return false;
            }
            if(array_key_exists($path, self::$folders)){
                return self::$folders[$path];
            }

            //  Walk through
            if($handle = opendir($path)){
                $classes = array();
                while(($file = readdir($handle)) !== false){
                    if(in_array($file, array(".", "..")) || !is_file($path . "/" . $file)){
                        continue;
                    }

                    // Validate File
                    $filename = explode("/", $file);
                    $filename = $filename[count($filename)-1];
                    if(($class = self::validate($filename)) !== false){
                        self::$classes[$class] = $path . "/" . $file;
                        $classes[] = $class;
                    }
                }
                self::$folders[$path] = $classes;
                closedir($handle);
            }
            return $classes;
        }

        /*
         |  CORE :: CHECK IF A CLASS EXISTS
         |  @since  0.8.4
         |
         |  @param  string  The class name as STRING.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function exists($class){
            if(!isset(self::$classes[$class])){
                return false;
            }
            $file = self::$classes[$class];
            if(!file_exists($file)){
                return false;
            }
            return true;
        }

        /*
         |  CORE :: LOADS A REQUESTED CLASS
         |  @since  0.8.4
         |
         |  @param  string  The class name as STRING.
         |
         |  @return void    Throws exception on error!
         */
        static public function load($class){
            if(!isset(self::$classes[$class])){
                throw new Exception("The class '{$class}' hasn't been registered yet!");
            }

            $file = self::$classes[$class];
            if(!file_exists($file)){
                throw new Exception("The file for the class '{$class}' couldn't be found in '{$file}'!");
            }

            require_once($file);
            return true;
        }

        /*
         |  CORE :: CHECK IF ALREADY LOADED
         |  @since  0.8.4
         |
         |  @param  string  The class name or filepath as STRING.
         |
         |  @return bool    TRUE if the class / file has already been loaded through this AutoLoader,
         |                  FALSE if not.
         */
        static public function loaded($class){
            if(ends_with($class, ".php")){
                $class = "/" . str_replace("\\", "/", trim($class, "/\\"));
                return array_key_exists($class, self::$loaded);
            }
            return in_array($class, self::$loaded) && class_exists($class, false);
        }
    }
