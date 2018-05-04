<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/Controller.php
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

    class Controller{
        protected $layout = false;
        protected $layout_vars = array();

        /*
         |  CORE :: SETS A RESPECTIVE LAYOUT
         |  @since  0.8.4
         |
         |  @param  string  The layout as STRING.
         |
         |  @return void
         */
        public function setLayout($layout){
            $this->layout = $layout;
        }

        /*
         |  CORE :: ASSIGN VAR TO LAYOUT
         |  @since  0.8.4
         |
         |  @param  multi   A single variable name as STRING, some (var => value) pairs as ARRAY.
         |  @param  multi   The respective variable value if $var is STRING.
         |
         |  @return void
         */
        public function assignToLayout($key, $value = NULL){
            if(is_array($key)){
                $this->layout_vars = array_merge($this->layout_vars, $key);
            } else {
                $this->layout_vars[$key] = $value;
            }
        }

        /*
         |  CORE :: EXECUTES A SPECIFIC ACTION / METHOD FOR THIS CONTROLLER
         |  @since  0.8.4
         |
         |  @param  string  The respective action / method name as STRING.
         |  @param  array   The parameters / arguments, which should be passed within an ARRAY.
         |
         |  @return void    Throws an exception on error.
         */
        public function execute($action, $params = array()){
            if(substr($action, 0, 1) == "_" || !method_exists($this, $action)){
                throw new Exception("The Action '{$action}' is not valid!");
            }
            call_user_func_array(array($this, $action), $params);
        }

        /*
         |  OUTPUT :: DISPLAY
         |  @since  0.8.4
         |
         |  @param  string  The name of the view to render as STRING.
         |  @param  array   Some additional arrays for the view class within an ARRAY.
         |  @param  bool    TRUE to exit, FALSE to do it not.
         |
         |  @return void
         */
        public function display($view, $vars = array(), $exit = false){
            print($this->render($view, $vars));
            if($exit){
                die();
            }
        }

        /*
         |  OUTPUT :: RENDER
         |  @since  0.8.4
         |
         |  @param  string  The name of the view to render as STRING.
         |  @param  array   Some additional arrays for the view class within an ARRAY.
         |
         |  @return object  A new VIEW instance.
         */
        public function render($view, $vars = array()){
            if($this->layout){
                $this->layout_vars["content_for_layout"] = new View($view, $vars);
                $view = new View("../layouts/{$this->layout}", $this->layout_vars);
                return $view->render();
            }
            $view = new View($view, $vars);
            return $view->render();
        }

        /*
         |  OUTPUT :: RENDER A JSON ENCODED RESPONSE
         |  @since  0.8.4
         |
         |  @param  multi   The data which should be encoded.
         |  @param  bool    TRUE to use PRETTY_PRINT, FALSE to use it not.
         |
         |  @return string  The JSON encoded string, Throws an exception on failure!
         */
        public function renderJSON($data, $pretty = false){
            if(function_exists("json_encode")){
                return json_encode($data, ($pretty)? JSON_PRETTY_PRINT: 0);
            }
            throw new Exception('No function or class found to render JSON.');
        }
    }
