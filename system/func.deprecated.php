<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/func.deprecated.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.8.4 [0.8.4] - Alpha
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */
    global $deprecated, $deprecated_files;

    // Init Globals
    if(!is_array($deprecated)){
        $deprecated = array();
    }
    if(!is_array($deprecated_files)){
        $deprecated_files = array();
    }

    /*
     |  CORE :: DEPRECATE FUNCTION
     |  @since  0.8.4
     |
     |  @param  string  The old function name as STRING.
     |  @param  multi   The new function name as STRING, if there is any.
     |
     |  @return void
     */
    function deprecated($old_func, $new_func = NULL){
        global $deprecated;

        // Add Function
        $deprecated[] = $old_func;

        // Trigger Error
        if(DEBUG_MODE){
            $error = "The function <b>'{$old_func}'</b> is deprecated and will be removed in the future!";
            if(!empty($new_func)){
                $error .= " Please use the function <b>'{$new_func}'</b> instead.";
            }

            $code = debug_backtrace();
            if(is_array($code) && !empty($code)){
                if(strpos($old_func, "::") !== false){
                    list($class, $func) = explode("::", $old_func);
                } else {
                    $func = trim($old_func, "()");
                }

                foreach($code AS $line){
                    if(isset($class) && isset($line["class"])){
                        if($line["class"] != "self" && $line["class"] != $class){
                            continue;
                        }
                    }
                    if(!isset($line["function"]) || $line["function"] != $func){
                        continue;
                    }

                    $file = str_replace(BASE_ROOT, "", $line["file"]);
                    $file = "./" . trim(str_replace("\\", "/", $file), "\\/");
                    $error .= " Occured in <b>{$file}</b> on line <b>{$line["line"]}</b>!";
                    break;
                }
            }
            trigger_error($error, E_USER_DEPRECATED);
        }
    }
    set_error_handler(function($errno, $errstr){
        print("<p><b>Deprecated:</b> {$errstr}</p>");
    }, E_USER_DEPRECATED);

    ##
    ##  DEPRECATED CONSTANTs
    ##
    function define_wolf_constants(){
        if(!defined("IN_CMS")){
            define("IN_CMS", true);
        }
        if(!defined("URL_PUBLIC")){
            define("URL_PUBLIC", PUBLIC_URL);
        }
        if(!defined("PATH_PUBLIC")){
            define("PATH_PUBLIC", PUBLIC_HTML);
        }
        if(!defined("DEFAULT_LOCALE")){
            define("DEFAULT_LOCALE", DEFAULT_LANGUAGE);
        }
        if(!defined("SESSION_LIFETIME")){
            define("SESSION_LIFETIME", SESSION_LIFE);
        }
        if(!defined("REMEMBER_LOGIN_LIFETIME")){
            define("REMEMBER_LOGIN_LIFETIME", LOGIN_LIFE);
        }
        if(!defined("GLOBAL_XSS_FILTERING")){
            define("GLOBAL_XSS_FILTERING", GLOBAL_XSS_FILTER);
        }
        if(!defined("FRAMEWORK_STARTING_MICROTIME")){
            define("FRAMEWORK_STARTING_MICROTIME", START);
        }

        // Config
        if(!defined("DEBUG")){
            define("DEBUG", DEBUG_MODE);
        }
        if(!defined("URL_PUBLIC")){
            define("URL_PUBLIC", FOX_PUBLIC);
        }
        if(!defined("TABLE_PREFIX")){
            define("TABLE_PREFIX", DB_PREFIX);
        }
        if(!defined("CHECK_UPDATES")){
            define("CHECK_UPDATES", UPDATER);
        }
        if(!defined("CHECK_TIMEOUT")){
            define("CHECK_TIMEOUT", UPDATER_TIMEOUT);
        }
        if(!defined("USE_HTTPS")){
            define("USE_HTTPS", HTTPS_MODE);
        }
        if(!defined("COOKIE_HTTP_ONLY")){
            define("COOKIE_HTTP_ONLY", COOKIE_HTTP);
        }
        if(!defined("USE_MOD_REWRITE")){
            define("USE_MOD_REWRITE", MOD_REWRITE);
        }
        if(!defined("USE_POORMANSCRON")){
            define("USE_POORMANSCRON", POORMANSCRON);
        }
        if(!defined("ALLOW_LOGIN_WITH_EMAIL")){
            define("ALLOW_LOGIN_WITH_EMAIL", true);
        }
        if(!defined("DELAY_ON_INVALID_LOGIN")){
            define("DELAY_ON_INVALID_LOGIN", LOGIN_PROTECTION);
        }
        if(!defined("DELAY_ONCE_EVERY")){
            define("DELAY_ONCE_EVERY", LOGIN_PROTECTION_TIME);
        }
        if(!defined("DELAY_FIRST_AFTER")){
            define("DELAY_FIRST_AFTER", LOGIN_PROTECTION_ATTEMPTS);
        }
        if(!defined("SECURE_TOKEN_EXPIRY")){
            define("SECURE_TOKEN_EXPIRY", TOKEN_LIFE);
        }

        // Paths
        if(!defined("CMS_ROOT")){
            define("CMS_ROOT", BASE_ROOT);
        }
        if(!defined("CORE_ROOT")){
            define("CORE_ROOT", SYSTEM_ROOT);
        }
        if(!defined("APP_PATH")){
            define("APP_PATH", SYSTEM_ROOT);
        }
        if(!defined("ICONS_PATH")){
            define("ICONS_PATH", INCLUDES_URL . "icons/");
        }
        if(!defined("HELPER_PATH")){
            define("HELPER_PATH", INCLUDES_ROOT);
        }
        if(!defined("THEMES_PATH")){
            define("THEMES_PATH", THEMES_HTML);
        }
    }


    ##
    ##  DEPRECATED FUNCTIONs
    ##

    /*
     |  DEPRECATED :: TEXT STARTS WITH
     |  @deprecated     0.8.4
     |  @removed        1.0.0
     |  @notice         Deprecated due the new format standards.
     |
     |  @param  string  The haystack.
     |  @param  multi   A single needle as STRING, multiple as ARRAY.
     |
     |
     |  @return bool    TRUE if the haystack starts with the neddle, FALSE if not.
     */
    function startsWith($haystack, $needle){
        deprecated("startsWith", "starts_with");
        return starts_with($haystack, $needle, true);
    }

    /*
     |  DEPRECATED :: TEXT ENDS WITH
     |  @deprecated     0.8.4
     |  @removed        1.0.0
     |  @notice         Deprecated due the new format standards.
     |
     |  @param  string  The haystack.
     |  @param  multi   A single needle as STRING, multiple as ARRAY.
     |
     |
     |  @return bool    TRUE if the haystack ends with the neddle, FALSE if not.
     */
    function endsWith($haystack, $needle){
        deprecated("endsWith", "ends_with");
        return ends_with($haystack, $needle, true);
    }

    /*
     |  DEPRECATED :: TESTS IF A FILE IS WRITABLE
     |  @deprecated     0.8.4
     |  @removed        1.0.0
     |  @notice         Deprecated due the new format standards.
     |
     |  @param  string  The complete path to the file.
     |
     |  @return bool    TRUE if the file is writable, FALSE if not.
     */
    function isWritable($file){
        deprecated("isWritable", "writable");
        return writable($file);
    }

    /*
     |  DEPRECATED :: TESTS IF A FILE IS WRITABLE
     |  @deprecated     0.8.4
     |  @removed        1.0.0
     |  @notice         Deprecated due the new format standards.
     |
     |  @param  string  The complete path to the file.
     |
     |  @return bool    TRUE if the file is writable, FALSE if not.
     */
    function get_request_method(){
        deprecated("get_request_method", "request_method");
        return request_method();
    }

    /*
     |  DEPRECATED :: TESTS IF A FILE IS WRITABLE
     |  @deprecated     0.8.4
     |  @removed        1.0.0
     |  @notice         Deprecated due the new format standards.
     |
     |  @param  string  The complete path to the file.
     |
     |  @return bool    TRUE if the file is writable, FALSE if not.
     */
    function get_microtime(){
        deprecated("get_microtime", "microtime(true)");
        return microtime(true);
    }

    /*
     |  DEPRECATED :: XSS-CLEAN STRING ARRAYS
     |  @deprecated     0.8.4
     |  @removed        1.0.0
     |  @notice         Deprecated due the new format standards.
     |
     |  @param  array   An array with strings to clean.
     |
     |  @return array   A XSS-cleaned array.
     */
    function cleanArrayXSS($array){
        deprecated("cleanArrayXSS()", "remove_xss()");
        return remove_xss($array);
    }

    /*
     |  DEPRECATED :: XSS-CLEAN SUPERGLOBALS
     |  @deprecated     0.8.4
     |  @removed        1.0.0
     */
    function cleanXSS(){
        deprecated("cleanXSS()");
    }

    /*
     |  DEPRECATED :: BASIC STRING ESCAPING
     |  @deprecated     0.8.4
     |  @removed        1.0.0
     |  @notice         Deprecated due the new format standards.
     |
     |  @param  string  The string to escape.
     |  @param  string  The type of escaping.
     |
     |  @return string  The escapred string.
     */
    function xssClean($string, $type = "html"){
        deprecated("xssClean()", "escape()");
        return escape($string, $type);
    }

    /*
     |  DEPRECATED :: XSS-CLEAN SUPERGLOBALS
     |  @deprecated     0.8.4
     |  @removed        1.0.0
     |  @notice         Deprecated due the new format standards.
     |
     |  @param  string  The javascript string to escape.
     |
     |  @return string  The escapred javascriptstring.
     */
    function jsEscape($string){
        deprecated("jsEscape()", "escape()");
        return escape($string, "javascript");
    }

    /*
     |  DEPRECATED :: PAGE NOT FOUND
     |  @deprecated     0.8.4
     |  @removed        1.0.0
     |  @notice         Deprecated due the new format standards.
     |
     |  @param  string  The URL, which has triggered this 404.
     |
     |  @return void
     */
    function pageNotFound($url = NULL){
        return page404($url);
    }
    function page_not_found($url = NULL){
        return page404($url);
    }

    /*
     |  DEPRECATED :: LOAD CONTENT FROM URL
     |  @deprecated     0.8.4
     |  @removed        1.0.0
     |  @notice         Deprecated due the new format standards.
     |
     |  @param  string  The respective URL as STRING.
     |  @param          deprecated
     |  @param          deprecated
     |
     |  @return multi   The respective content on success, FALSE on failure,
     |                  NULL if cURL nor allow_url_fopen are allowed on the server.
     */
    function getContentFromUrl($url, $flags = 0, $context = false){
        deprecated("getContentFromUrl", "url_get_contents");
        return url_get_contents($url);
    }

    /*
     |  DEPRECATED :: STRIP SLASHES ON MAGIC QUOTES
     |  @deprecated     0.8.4
     |  @removed        1.0.0
     */
    function fix_input_quotes(){
        deprecated("fix_input_quotes");
    }

    /*
     |  DEPRECATED :: EXPLODES A PATH INTO AN ARRAY
     |  @deprecated     0.8.4
     |  @removed        1.0.0
     |  @notice         Deprecated due the new format standards.
     |
     |  @return string  A respective path as STRING.
     |
     |  @return array   An array of slugs.
     */
    function explode_uri($path){
        deprecated("explode_uri", "explode_path");
        return explode_path($path);
    }

    /*
     |  DEPRECATED :: FIND PAGE BY SLUG
     |  @deprecated     0.8.4
     |  @removed        1.0.0
     |  @notice         Deprecated due the new format standards.
     |
     |  @param  string  The respective slug.
     |  @param  object  The page Page object.
     |  @param  bool    TRUE to get all statusses, FALSE otherwise.
     |
     |  @return string  The Page object or FALSE on failure.
     */
    function find_page_by_slug($slug, &$parent, $all = false){
        deprecated("find_page_by_slug", "Page::findBySlug()");
        return Page::findBySlug($slug, $parent, $all);
    }


    ##
    ##  OBSERVER CLASS
    ##
    final class Observer{
        static public function observe($event, $callback){
            deprecated("Observer::observe", "Event::add");
            return Event::add($event, $callback);
        }
        static public function stopObserving($event, $callback){
            deprecated("Observer::stopObserving", "Event::remove");
            return Event::remove($event, $callback);
        }
        static public function clearObservers($event){
            deprecated("Observer::clearObservers", "Event::clear");
            return Event::clear($event);
        }
        static public function getObserverList($event){
            deprecated("Observer::getObserverList", "Event::get");
            return Event::get($event);
        }
        static public function notify($event){
            deprecated("Observer::notify", "Event::apply");
            return Event::apply($event, array_slice(func_get_args(), 1));
        }
    }
