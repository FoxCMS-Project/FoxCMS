<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/class.fox.php
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

    class Fox{
        /*
         |  DATA VARs
         */
        public $status;
        public $version;

        /*
         |  CONSTRUCTOR
         |  @since  0.8.4
         */
        public function __construct(){
            $this->status = FOX_STATUS;
            $this->version = FOX_VERSION;

            // Set Error Reporting
            if(defined("DEBUG_MODE") && DEBUG_MODE){
                ini_set("html_errors", true);
                ini_set("track_errors", true);
                ini_set("display_errors", true);
                ini_set("display_startup_errors", true);
                error_reporting(E_ALL | E_STRICT | E_NOTICE);
            } else {
                error_reporting(0);
            }

            // Init Session
            session_start();
            if(!isset($_SESSION["initiated"]) || isset($_SESSION["clear"])){
                session_regenerate_id(true);
                $_SESSION["initiated"] = true;
            }

            // Init DateTime
            if(!empty(c("DEFAULT_TIMEZONE"))){
                ini_set("date.timezone", DEFAULT_TIMEZONE);
                if(function_exists("date_default_timezone_set")){
                    date_default_timezone_set(DEFAULT_TIMEZONE);
                } else {
                    putenv("TZ=" . DEFAULT_TIMEZONE);
                }
            }

            // Init Charset
            if(!empty(c("DEFAULT_CHARSET")) && extension_loaded("mbstring")){
                mb_http_input(DEFAULT_CHARSET);
                mb_http_output(DEFAULT_CHARSET);
                mb_internal_encoding(DEFAULT_CHARSET);
            }

            // XSS Clean
            $_GET     = remove_xss($_GET);
            $_POST    = remove_xss($_POST);
            $_COOKIE  = remove_xss($_COOKIE);
            $_SERVER  = remove_xss($_SERVER);
            $_SESSION = remove_xss($_SESSION);
        }

        /*
         |  INIT BASICs
         |  @since  0.8.4
         */
        public function init(){
            if(defined("FOX_INIT")){
                return true;
            }
            define("FOX_INIT", true);

            // Basic URL
            if(!empty(c("FOX_PUBLIC"))){
                $basic = FOX_PUBLIC;
                if(starts_with($basic, "http")){
                    $basic = explode("://", $basic, 2)[1];
                }
            } else {
                $basic = str_replace("\\", "/", $_SERVER["DOCUMENT_ROOT"]);
                $basic = str_replace($basic, "", str_replace("\\", "/", BASE_ROOT));
                $basic = $_SERVER["SERVER_NAME"] . $basic;
            }
            if(!ends_with($basic, "/")){
                $basic .= "/";
            }

            // Define URL
            $https = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on');
            if(!defined("PUBLIC_URL")){
                if(in_array(c("HTTPS_MODE"), array("always", "frontend"))){
                    define("PUBLIC_URL", "https://" . $basic);
                } else {
                    define("PUBLIC_URL", "http://" . $basic);
                }
            }
            if(!defined("PUBLIC_HTML")){
                if(($path = strpos($basic, "/")) === false){
                    define("PUBLIC_HTML", "/");
                } else {
                    define("PUBLIC_HTML", substr($basic, $path));
                }
            }

            // Current Query
            if(isset($_GET["FOX"])){
                $current = ltrim($_GET["FOX"], "/");
            } else {
                $current = ltrim(urldecode($_SERVER["QUERY_STRING"]), "/");
            }

            // Check 4 Admin
            if(!empty(c("ADMIN_DIR")) && starts_with($current, c("ADMIN_DIR"))){
                $url = trim(PUBLIC_URL, "/") . "/";
                if(c("HTTPS_MODE") == "backend" && !starts_with($url, "https")){
                    $url = str_replace("http://", "https://", $url);
                }

                define("CMS_BACKEND",   true);
                define("BASE_URL",      $url . (c("MOD_REWRITE")? "": "?/") . ADMIN_DIR . "/");
                define("BASE_HTML",     PUBLIC_HTML);
                define("BASE_PATH",     BASE_HTML);
            } else {
                define("CMS_BACKEND",   false);
                define("BASE_URL",      PUBLIC_URL);
                define("BASE_HTML",     PUBLIC_HTML);
                define("BASE_PATH",     BASE_HTML);
            }
            define("CMS_FRONTEND", !CMS_BACKEND);

            // Check Redirect
            if(CMS_FRONTEND && in_array(HTTPS_MODE, array("always", "frontend")) && !$https){
                header("Location: https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
                die();
            }
            if(CMS_BACKEND && in_array(HTTPS_MODE, array("always", "backend")) && !$https){
                header("Location: https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
                die();
            }

            // Define Paths
            $const = array("CONTENT", "I18N", "PLUGINS", "THEMES", "UPLOADS", "INCLUDES", "SYSTEM");
            foreach($const AS $c){
                $path = trim(str_replace(BASE_DIR, "", constant("{$c}_DIR")),"\\/");

                // Define
                if(!defined("{$c}_ROOT")){
                    define("{$c}_ROOT", BASE_ROOT . $path . DS);
                }
                if(!defined("{$c}_HTML")){
                    define("{$c}_HTML", PUBLIC_HTML . str_replace("\\", "/", $path) . "/");
                }
                if(!defined("{$c}_URL")){
                    define("{$c}_URL", PUBLIC_URL . str_replace("\\", "/", $path) . "/");
                }
            }

            // Init and fill AutoLoader
            AutoLoader::init();
            AutoLoader::addFolder(SYSTEM_ROOT . "models");
            AutoLoader::addFolder(SYSTEM_ROOT . "controllers");

            // End __FUNCTION__ and Return
            define_wolf_constants();
            return true;
        }

        /*
         |  CHECK CONFIGURATION AND INSTALLATION
         |  @since  0.8.4
         */
        public function check(){
            if(defined("FOX_CHECK")){
                return true;
            }
            define("FOX_CHECK", true);

            // Check Configuration
            $const = array("DB_TYPE" => true, "DB_NAME" => true, "FOX_ID" => true, "FOX_PUBLIC" => true);
            foreach($const AS $constant => &$value){
                if(defined($constant) && !empty(constant($constant))){
                    $value = false;
                }
            }
            if(count(array_filter($const)) > 0){
                return false;
            }

            // Check Installation
            Record::connect();
            if(!Record::exists("config") || !Record::exists("user")){
                return false;
            }

            // End __FUNCTION__ and Return
            return true;
        }

        /*
         |  LOAD ENVRIONMENT
         |  @since  0.8.4
         */
        public function load(){
            if(defined("FOX_LOAD")){
                return true;
            }
            define("FOX_LOAD", true);
            setlocale(LC_TIME, Setting::get("default-language", DEFAULT_LANGUAGE));
            use_helper("I18n");

            // Init Fox Classes
            Cron::init();
            Setting::init();
            AuthUser::init();
            if(AuthUser::isLoggedIn()){
                I18n::setLocale(AuthUser::getUser()->language);
            } else {
                I18n::setLocale(Setting::get("default-language", DEFAULT_LANGUAGE));
            }
            Plugin::init();
            Flash::init();
            Dispatcher::init();

            // End __FUNCTION__ and Return
            return true;
        }

        /*
         |  RENDER REQUEST
         |  @since  0.8.4
         */
        public function render(){
            if(defined("FOX_RENDER") || FOXCMS != "foxcms"){
                return true;
            }
            define("FOX_RENDER", true);
            ob_start();

            // The main.php file
            if(array_key_exists("FOX", $_GET)){
                $path = trim(urldecode($_GET["FOX"]), "/");
                unset($_GET["FOX"]);
            } else if(!array_key_exists("FOX", $_GET) && MOD_REWRITE){
                $path = "/";
            } else {
                $path = trim(urldecode($_SERVER["QUERY_STRING"]), "/");
                if(strpos($path, "?") !== false){
                    list($path, $vars) = explode("?", $path);

                    $query = array();
                    foreach(explode("&", $vars) AS $get){
                        list($key, $value) = array_pad(explode("=", $get), 2, NULL);
                        $query[$key] = $value;
                    }
                    $_GET = $query;
                } else if(strpos($path, "&") !== false || strpos($path, "=") !== false){
                    $path = "/";
                }
            }
            if(array_key_exists("FOXAJAX", $_GET)){
                $path = "/" . ADMIN_DIR . $_GET["FOXAJAX"];
                unset($_GET["FOXAJAX"]);
            }
            if(!empty($path) && $path[0] !== "/"){
                $path = "/" . ltrim($path, "/");
            }

            // URL SUFFIX
            $suffix = trim(URL_SUFFIX, "\\/");
            if(!empty($suffix)){
                $path = preg_replace("#^(.*)({$suffix})$#i", "$1", $path);
            }

            // Define
            define("CURRENT_URL", PUBLIC_URL . trim($path, "/"));
            define("CURRENT_PATH", trim($path, "/"));

            // Dispatch custom Route
            if(Dispatcher::hasRoute($path)){
                Event::apply("dispatch_route_found", $path);
                Dispatcher::dispatch($path);
                die();
            }
            $path = Event::applyFilter("page_requested", array($path));

            $page = Page::findByPath($path, true);
            if(is_a($page, "Page")){
                if($page->status_id == Page::STATUS_PREVIEW){
                    if(!AuthUser::isLoggedIn() || !AuthUser::hasPermission("page_view")){
                        page404($path);
                    }
                }
                if($page->getLoginNeeded() == Page::LOGIN_REQUIRED){
                    if(!AuthUser::isLoggedIn()){
                        Flash::set("redirect", $page->url());
                        redirect(URL_PUBLIC . ((MOD_REWRITE)? "": "?/") . ADMIN_DIR . "/login");
                        die();
                    }
                }
                Event::apply("page_found", $page);
                $page->_executeLayout();
            } else {
                page404($path);
            }

            // End __FUNCTION__ and Return
            ob_end_flush();
            return true;
        }
    }
