<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/class.dispatcher.php
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

    class Dispatcher{
        /*
         |  GLOBAL VARs
         */
        static private $routes = array();
        static private $params = array();
        static private $status = array();
        static private $requested_url = "";

        /*
         |  INIT FUNCTION
         |  @since  0.8.4
         */
        static public function init(){
            $default = Setting::get("default-tab", "page");
            $default = empty($default)? "page": $default;
            self::addRoute(array(
                "/" . ADMIN_DIR             => $default,
                "/" . ADMIN_DIR . "/"       => $default,
                "/" . ADMIN_DIR . "/:all"   => "$1"
            ));
        }

        /*
         |  HELPER :: SPLIT URL
         |  @since  0.8.4
         |
         |  @param  string  The URL to split</philippe>
         |
         |  @return array   The key => value URL components as ARRAY.
         */
        static public function splitUrl($url){
            return preg_split("/\//", $url, -1, PREG_SPLIT_NO_EMPTY);
        }

        /*
         |  ADD A NEW ROUTE
         |  @since  0.8.4
         |
         |  @param  multi   A single route as STRING, multiple as ARRAY.
         |  @param  string  The destination if $route is a single STRING, NULL otherwise,
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function addRoute($route, $destination = NULL){
            if(is_string($route) && !empty($destination)){
                self::$routes[$route] = $destination;
                return true;
            }
            if(is_array($route)){
                self::$routes = array_merge(self::$routes, $route);
                return true;
            }
            return false;
        }

        /*
         |  HAS ROUTE
         |  @since  0.8.4
         |
         |  @param  string  The path / requested url to route to.
         |
         |  @return bool    TRUE if a route could be found, FALSE if not.
         */
        static public function hasRoute($url){
            if(!self::$routes || empty(self::$routes)){
                return false;
            }

            $url = rtrim($url, "/");
            foreach(self::$routes AS $route => $action){
                if(strpos($route, ":") !== false){
                    $rep = array(":any" => "([^/]+)", ":num" => "([0-9]+)", ":all" => "(.+)");
                    $route = str_replace(array_keys($rep), array_values($rep), $route);
                }

                if(preg_match("#^{$route}$#", $url)){
                    if(strpos($action, "$") !== false && strpos($route, "(") !== false){
                        $action = preg_replace("#^{$route}$#", $action, $url);
                    }
                    self::$params = self::splitUrl($action);
                    return true;
                }
            }
            return false;
        }

        /*
         |  DISPATCH A REQUEST
         |  @since  0.8.4
         |
         |  @param  string  The requests url as STRING.
         |  @param  string  The default URL to access, if no URL was requests.
         |
         |  @return string  The respective response.
         */
        static public function dispatch($url = NULL, $default = NULL){
            Flash::init();

            // Check requested URL
            if(is_null($url)){
                if(($pos = strpos($_SERVER["QUERY_STRING"], "&")) !== false){
                    $url = substr($_SERVER["QUERY_STRING"], 0, $pos);
                } else {
                    $url = $_SERVER["QUERY_STRING"];
                }
            }
            if(empty($url) && !empty($default)){
                $url = $default;
            }
            self::$requested_url = $url = "/" . trim($url, "/");
            self::$status["requested_url"] = $url;
            self::$params = self::splitUrl($url);

            // Handle Action
            if(count(self::$routes) == 0){
                return self::executeAction(self::getController(), self::getAction(), self::getParams());
            }
            if(isset(self::$routes[$url])){
                self::$params = self::splitUrl(self::$routes[$url]);
                return self::executeAction(self::getController(), self::getAction(), self::getParams());
            }

            // Loop through the Routes
            foreach(self::$routes AS $route => $action){
                if(strpos($route, ":") !== false){
                    $rep = array(":any" => "([^/]+)", ":num" => "([0-9]+)", ":all" => "(.+)");
                    $route = str_replace(array_keys($rep), array_values($rep), $route);
                }

                if(preg_match("#^{$route}$#", $url)){
                    if(strpos($action, "$") !== false && strpos($route, "(") !== false){
                        $action = preg_replace("#^{$route}$#", $action, $url);
                    }
                    self::$params = self::splitUrl($action);
                    break;
                }
            }
            return self::executeAction(self::getController(), self::getAction(), self::getParams());
        }

        /*
         |  EXECUTE AN ACTION
         |  @since  0.8.4
         |
         |  @param  string  The controller class to use.
         |  @param  string  The controller action metho to use.
         |  @param  array   Some additional parameters for the action method.
         |
         |  @return multi
         */
        static public function executeAction($controller, $action, $params){
            self::$status["controller"] = $controller;
            self::$status["action"]     = $action;
            self::$status["params"]     = implode(", ", $params);

            // Get Class
            $class = Inflector::camelize($controller) . "Controller";
            if(class_exists($class)){
                $controller = new $class();
            } else {
                throw new Exception("Class '{$class}' does not extends Controller class!");
            }
            return $controller->execute($action, $params);
        }

        /*
         |  GET CURRENT URL
         |  @since  0.8.4
         |
         |  @return string  The current requested URL.
         */
        static public function getCurrentUrl(){
            return self::$requested_url;
        }

        /*
         |  GET CURRENT CONTROLLER
         |  @since  0.8.4
         |
         |  @return string  The controller class name.
         */
        static public function getController(){
            if(isset(self::$params[0]) && self::$params[0] == "plugin"){
                $plugins = Plugin::$plugins;
                if(count(self::$params) < 2){
                    unset(self::$params[0]);
                } else if(isset(self::$params[1]) && !isset($plugins[self::$params[1]])){
                    unset(self::$params[0]);
                    unset(self::$params[1]);
                }
            }
            return isset(self::$params[0]) ? self::$params[0]: DEFAULT_CONTROLLER;
        }

        /*
         |  GET CURRENT ACTION
         |  @since  0.8.4
         |
         |  @return string  The controller method name.
         */
        static public function getAction(){
            return isset(self::$params[1]) ? self::$params[1]: DEFAULT_ACTION;
        }

        /*
         |  GET CURRENT PARAMS
         |  @since  0.8.4
         |
         |  @return array   The current requested parameters.
         */
        static public function getParams(){
            return array_slice(self::$params, 2);
        }

        /*
         |  GET STATUS
         |  @since  0.8.4
         |
         |  @param  string  A single status key as STRING.
         |
         |  @return multi   A single status value or the complete status ARRAY.
         */
        static public function getStatus($key = NULL){
            return is_null($key)? self::$status: (isset(self::$status[$key])? self::$status[$key]: NULL);
        }
    }
