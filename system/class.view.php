<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/class.view.php
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

    class View{
        /*
         |  INSTANCE VARs
         */
        private $file;
        private $vars = array();
        private $loop = false;

        /*
         |  CONSTRUCTOR
         |  @since  0.8.4
         |
         |  @param  string  The absolute or template-relative path to the file to display.
         |  @param  array   Some additional var => value ARRAY-paired arguments.
         */
        public function __construct($file, $vars = array()){
            $file = trim($file, "/");
            if(!ends_with($file, ".php")){
                $file .= ".php";    // Wolf CMS backward compatibility.
            }

            // Check File
            if(!file_exists($file)){
                while(true){
                    if(file_exists(SYSTEM_DIR . "views" . DS . $file)){
                        $file = SYSTEM_DIR . "views" . DS . $file;
                        break;
                    }

                    // Wolf CMS backward compatibility
                    if(strpos($file, "plugins")){
                        $file = str_replace("/plugins/", "/content/plugins/", $file);
                        if(file_exists(SYSTEM_DIR . "views" . DS . $file)){
                            $file = SYSTEM_DIR . "views" . DS . $file;
                            break;
                        }
                    }

                    // Still not found?
                    if(DEBUG_MODE){
                        throw new Exception("The passed View file '".func_get_arg(0)."' couldn't be found!");
                    }
                    return false;
                }
            }
            $this->file = $file;

            // Add Variables
            if(is_array($vars) && !empty($vars)){
                $this->assign($vars);
            }
        }

        /*
         |  MAGIC METHOD :: CONVERT TO STRING
         |  @since  0.8.4
         */
        public function __toString(){
            return $this->render();
        }

        /*
         |  ASSIGN VARIABLES
         |  @since  0.8.4
         |
         |  @param  multi   A single variable key as STRIN or multiple key => value ARRAY pairs.
         |  @param  multi   The single variable value, if $key is a single STRING too.
         |
         |  @return void
         */
        public function assign($key, $value = NULL){
            if(is_string($key)){
                $this->vars[$key] = $value;
            } else if(is_array($key)){
                $this->vars = array_merge($this->vars, $key);
            }
        }

        /*
         |  RENDER VIEW
         |  @since  0.8.4
         |
         |  @return string  The rendered content.
         */
        public function render(){
            if(empty($this->file)){
                return false;
            }
            $this->loop = true;

            // Render Content
            ob_start();
            extract($this->vars, EXTR_SKIP);
            include($this->file);
            $content = ob_get_contents();
            ob_get_clean();

            // Return
            $this->loop = false;
            return $content;
        }

        /*
         |  DISPLAY VIEW
         |  @since  0.8.4
         */
        public function display(){
            print($this->render());
        }


        /*
         |  LAYOUT :: BUILD TITLE
         |  @since  0.8.4
         |
         |  @param  string  The separator between the menu items.
         |
         |  @return string  The build page title.
         */
        public function getTitle($separator = " / "){
            if(!$this->loop){
                return;
            }

            // Get Pre-Defined Title
            if(isset($this->vars["title"])){
                return $this->vars["title"];
            }

            // Create a Title
            $title = array();
            if(Dispatcher::getController() == "plugin"){
                $title[] = Plugin::$controllers[Dispatcher::getAction()]->label;
            } else {
                $title[] = ucfirst(Dispatcher::getController());
            }

            if(isset($this->vars["content_for_layout"]->vars["action"])){
                $title[] = ucfirst($this->vars["content_for_layout"]->vars["action"]);
                if($title[1] == "Edit" && isset($this->vars["content_for_layout"]->vars["page"])){
                    $title[] = $this->vars["content_for_layout"]->vars["page"]->title;
                }
            }

            // Return Title
            return implode($separator, $title);
        }

        /*
         |  LAYOUT :: BUILD ID
         |  @since  0.8.4
         |
         |  @return string  A unique page id.
         */
        public function getID(){
            if(!$this->loop){
                return;
            }

            if(CMS_BACKEND){
                return Dispatcher::getController() . "-" . Dispatcher::getAction();
            }
        }

        /*
         |  LAYOUT :: BUILD CLASS
         |  @since  0.8.4
         |
         |  @return string  Get Body class names
         */
        public function getClass(){
            if(!$this->loop){
                return;
            }

            // Get
            $class = "";
            if(isset($this->vars["body_class"])){
                $class = $this->vars["body_class"];
            }

            // Build and Return
            if(is_array($class)){
                $class = implode(" ", $class);
            }
            return trim("fox-backend {$class}");
        }

        /*
         |  LAYOUT :: IS CURRENT
         |  @since  0.8.4
         |
         |  @return string  The respective path string to check.
         |  @return bool    TRUE to return as BOOLEAN, FALSE to return as STRING.
         |
         |  @return multi   TRUE / FALSE or the string "current".
         */
        public function isCurrent($path, $bool = false){
            if(!$this->loop){
                return;
            }
            $path = trim($path, "/");
            $return = false;

            // Check Admin
            if(CMS_BACKEND){
                if(starts_with($path, "plugin")){
                    $return = $path == Dispatcher::getController()."/".Dispatcher::getAction();
                } else {
                    $return = $path == Dispatcher::getController();
                }
            }

            // Return
            return ($bool)? $return: ($return)? "current": "";
        }
    }
