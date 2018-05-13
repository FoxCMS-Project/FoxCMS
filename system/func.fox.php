<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/func.fox.php
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

    ##
    ##  GENERAL FUNCTIONS
    ##

    /*
     |  GENERAL :: USE HELPER
     |  @since  0.8.4
     |
     |  @param  string  ... A single helper class name as STRING.
     */
    function use_helper(){
        static $_fox_helpers = array();

        $helpers = func_get_args();
        foreach($helpers AS $helper){
            if(in_array($helper, $_fox_helpers)){
                continue;
            }

            $file = INCLUDES_ROOT . $helper . ".php";
            if(!file_exists($file)){
                if(DEBUG_MODE){
                    throw new Exception("Helper file '{$helper}' not found!");
                }
                continue;
            }
            include_once($file);
            $_fox_helpers[] = $helper;
        }
    }

    /*
     |  GENERAL :: REGISTER HELPER
     |  @since  0.8.4
     |
     |  @param  string  The helper class name as STRING.
     |  @param  string  The relative / absolute path to the helper file.
     |  @param  string  The helper class version number.
     |
     |  @return bool    TRUE on success, FALSE on failure.
     */
    function register_helper($helper, $helper_file, $helper_version){
        //@wip Registered function name for a upcoming update.
    }

    /*
     |  GENERAL :: UNREGISTER HELPER
     |  @since  0.8.4
     |
     |  @param  string  The helper class name or file name as STRING.
     |
     |  @return bool    TRUE on success, FALSE on failure.
     */
    function unregister_helper($helper){
        //@wip Registered function name for a upcoming update.
    }

    /*
     |  GENERAL :: USE MODEL
     |  @since  0.8.4
     |
     |  @param  string  ... A single model class name as STRING.
     */
    function use_model(){
        static $_fox_models = array();

        $models = func_get_args();
        foreach($models AS $model){
            if(in_array($model, $_fox_models)){
                continue;
            }

            $file = SYSTEM_PATH . "models" . DS . $model . ".php";
            if(!file_exists($file)){
                if(DEBUG_MODE){
                    throw new Exception("Model file '{$model}' not found!");
                }
                continue;
            }
            include_once($file);
            $_fox_models[] = $model;
        }
    }

    /*
     |  GENERAL :: REGISTER MODEL
     |  @since  0.8.4
     |
     |  @param  string  The model class name as STRING.
     |  @param  string  The relative / absolute path to the model file.
     |  @param  string  The model class version number.
     |
     |  @return bool    TRUE on success, FALSE on failure.
     */
    function register_model($model, $model_file, $model_version){
        //@wip Registered function name for a upcoming update.
    }

    /*
     |  GENERAL :: UNREGISTER MODEL
     |  @since  0.8.4
     |
     |  @param  string  The model class name or file name as STRING.
     |
     |  @return bool    TRUE on success, FALSE on failure.
     */
    function unregister_model($model){
        //@wip Registered function name for a upcoming update.
    }

    /*
     |  GENERAL :: REDIRECT TO A URL
     |  @since  0.8.4
     |
     |  @param  string  The URL to redirect.
     */
    function redirect($url){
        if(($url = filter_var($url, FILTER_SANITIZE_URL)) === false){
            $url = BASE_URL;
        }
        if(!starts_with($url, PUBLIC_URL) && !starts_with($url, BASE_URL)){
            $url = BASE_URL;
        }
        Flash::set("HTTP_REFERER", html_encode($_SERVER["REQUEST_URI"]));
        header("Location: {$url}");
        die();
    }
    function redirect_to($url){
        deprecated("redirect_to", "redirect");
        return redirect($url);
    }


    ##
    ##  PATH HANDLING FUNCTIONS
    ##

    /*
     |  PATH :: EXPLODES A PATH INTO AN ARRAY
     |  @since  0.8.4
     |
     |  @return string  A respective path as STRING.
     |
     |  @return array   An array of slugs.
     */
    function explode_path($path){
        if(!is_string($path)){
            return array();
        }
        return preg_split("#\/#", $path, -1, PREG_SPLIT_NO_EMPTY);
    }

    /*
     |  PATH :: URL MATCH
     |  @since  0.8.4
     |
     |  @param  string  The url to check.
     |
     |  @return bool    TRUE if the current URL equals $url, FALSE otherwise.
     */
    function url_match(){
        $url = trim(implode("/", func_get_args()), "/");
        return (CURRENT_PATH == $url);
    }

    /*
     |  PATH :: URL STARTS WITH
     |  @since  0.8.4
     |
     |  @param  string  The url to check.
     |
     |  @return bool    TRUE if the current URL starts with $url, FALSE otherwise.
     */
    function url_starts_with(){
        $url = trim(implode("/", func_get_args()), "/");
        return (CURRENT_URL == $url || strpos(CURRENT_URL, $url) === 0);
    }

    /*
     |  PATH :: CONVERT ROOT TO HTML
     |  @since  0.8.4
     |
     |  @param  string  The absolute root path as STRING.
     |  @param  bool    TRUE to include the root folder, FALSE to do it not.
     |
     |  @return string  A HTML string version of the $path.
     */
    function root_to_html($path, $absolute = true){
        $path = str_replace(realpath(BASE_DIR), "", $path);
        $path = str_replace("\\", "/", trim($path, "\\/"));
        return ($absolute)? PUBLIC_HTML . $path: $path;
    }
    function path_to_html($path, $absolute = true){
        return root_to_html($path, $absolute);
    }


    ##
    ##  TEMPLATE FUNCTIONS
    ##

    /*
     |  TEMPLATE :: CREATE A URL
     |  @since  0.8.4
     |
     |  @param  string  ... A respective url path.
     |
     |  @return string  The complete fox URL.
     */
    function get_url($path = ""){
        if(func_num_args() > 1){
            $path = array_map(function($val){ return trim($val, "/"); }, func_get_args());
            $path = implode("/", array_filter($path));
        }
        $path = trim($path, "/");

        // Admin Dir
        if(starts_with($path, ADMIN_DIR) && CMS_BACKEND){
            $path = trim(substr($path, 0, strlen(ADMIN_DIR)), "/");
        }

        // HTTPS
        if(CMS_BACKEND && in_array(HTTPS_MODE, array("always", "backend"))){
            $return = str_replace("http://", "https://", BASE_URL . $path);
        } else if(CMS_FRONTEND && in_array(HTTPS_MODE, array("always", "frontend"))){
            $return = str_replace("http://", "https://", BASE_URL . $path);
        } else {
            $return = str_replace("https://", "http://", BASE_URL . $path);
        }

        // Filter and Return
        //@todo Own Event
        return $return;
    }

    /*
     |  TEMPLATE :: GET TITLE
     |  @since  0.8.4
     |
     |  @param  multi   The content object or NULL for the site-title.
     |
     |  @return string  The content title or the site title as STRING.
     */
    function get_title($content = NULL){
        if(is_a($content, "Content")){
            return $content->title();
        }
        return Setting::get("site-title");
    }

    /*
     |  TEMPLATE :: GET KEYWORDS
     |  @since  0.8.4
     |
     |  @param  multi   The content object or NULL for the site-keywords.
     |
     |  @return string  The content keywords or the site keywords as STRING.
     */
    function get_keywords($content = NULL){
        if(is_a($content, "Content")){
            return $content->keywords();
        }
        return Setting::get("site-keywords");
    }

    function get_layout($content){
        if(!is_a($content, "Content")){
            return "";
        }
        if(($layout = Layout::findById($content->layout_id)) == false){
            return "";
        }
        return $layout->name;
    }

    /*
     |  TEMPLATE :: GET DESCRIPTION
     |  @since  0.8.4
     |
     |  @param  multi   The content object or NULL for the site-description.
     |
     |  @return string  The content description or the site description as STRING.
     */
    function get_description($content = NULL){
        if(is_a($content, "Content")){
            return $content->description();
        }
        return Setting::get("site-description");
    }

    /*
     |  TEMPLATE :: GET THEME PATH
     |  @since  0.8.4
     |
     |  @param  multi   The relative path within the theme folder.
     |
     |  @return string  The full theme path.
     */
    function get_theme_path($path = "", $theme = ""){
        if(CMS_BACKEND){
            $return = SYSTEM_DIR . "admin" . DS . "themes" . DS . Setting::get("backend-theme") . DS;
            if(file_exists(realpath($return . $path))){
                return root_to_html(realpath($return . $path), true);
            }
            return $return . $path;
        }

        $return = THEMES_DIR . $theme . DS;
        if(file_exists(realpath($return . trim($path, "/")))){
            return root_to_html(realpath($return . trim($path, "/")), true);
        }
        return $return . trim($path, "/");
    }

    /*
     |  TEMPLATE :: GET THEME URL
     |  @since  0.8.4
     |
     |  @param  multi   The relative path within the theme folder.
     |
     |  @return string  The full theme url.
     */
    function get_theme_url($path = "", $theme = ""){
        if(CMS_BACKEND){
            $return = str_replace("\\", "/", get_theme_path($path));
            $return = str_replace(str_replace("\\", "/", BASE_ROOT), "", $return);
            return PUBLIC_URL . str_replace(PUBLIC_HTML, "", $return);
        }
        return THEMES_URL . $theme . "/" . trim($url, "/");
    }


    ##
    ##  ADVANCED FUNCTIONs
    ##

    /*
     |  ADVANCED :: LOAD CONTENT FROM URL
     |  @since  0.8.4
     |
     |  @param  string  The respective URL as STRING.
     |  @param  int     The connection timeput on error.
     |  @param  string  An additional user agent as STRING.
     |
     |  @return multi   The respective content on success, FALSE on failure,
     |                  NULL if cURL nor allow_url_fopen are allowed on the server.
     */
    function url_get_contents($url, $timeout = 10, $user_agent = NULL){
        if(empty($user_agent)){
            $user_agent = "PHP/Fox CMS v." . FOX_VERSION;
        }

        // cURL Edition
        if(extension_loaded("curl") && function_exists("curl_init")){
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($curl, CURLOPT_NOSIGNAL, true);
            curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
            $result = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if(starts_with((string) $status, "2")){
                return $result;
            }
            return false;
        }

        //  file_get_contents Edition
        if(ini_get("allow_url_fopen")){
            $steam  = stream_context_create(array(
                "http"  => array(
                    "method"            => "GET",
                    "follow_location"   => 0,
                    "ignore_errors"     => true,
                    "timeout"           => $timeout,
                    "user_agent"        => $user_agent
                )
            ));
            if(($result = @file_get_contents($url, 0, $steam)) !== false){
                return $result;
            }
            return false;
        }
        return NULL;
    }

    /*
     |  ADVANCED :: PAGE NOT FOUND
     |  @since  0.8.4
     |  @todo   ROADMAP #mark
     |
     |  @param  string  The URL, which has triggered this 404.
     |
     |  @return void
     */
    function fox_404($url = NULL){
        Event::apply("page_not_found", $url);

        // Set Header
        header("HTTP/1.0 404 Not Found");
        header("Status: 404 Not Found");

        // Load Page / View
        $page = Page::findByBehavior("page_not_found");
        if(is_a($page, "Page")){
            $page->_executeLayout();
        } else {
            $page = new View("404");
            $page->display();
        }
        die();
    }

    /*
     |  ADVANCED :: EXCEPTION HANDLING
     |  @since  0.8.4
     */
    function fox_exception_handler($error){
        if(!DEBUG_MODE){
            return fox_404();
        }

        ob_start();
        include_once("views/exception.php");
        $content = ob_get_contents();
        ob_end_clean();
        print($content);
        die();
    }
    set_exception_handler("fox_exception_handler");
