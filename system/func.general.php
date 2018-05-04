<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/func.general.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.8.4 [0.8.4] - Alpha
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */

    /*
     |  SHORTCODE :: GET CONSTANT
     |  @since  0.1.0
     |
     |  @param  string  The constant string.
     |  @param  string  The respective class for class constant.
     |
     |  @return string  The constant value or NULL if undefined.
     */
    function c($constant, $class = NULL){
        if(empty($class) && defined("$constant")){
            return constant("$constant");
        } else if(!empty($class) && defined("$class::$constant")){
            return constant("$class::$constant");
        }
        return NULL;
    }

    /*
     |  GENERAL :: TRY TO SERIALIZE DATA
     |  @since  0.8.4
     |
     |  @param  multi   Array or Object, which you want to serialize.
     |  @param  bool    True: Serialize it, even if the data is not an array or an object.
     |                  False: Serialize only, if the data is an array or an object.
     |
     |  @return multi   The serialized data, or the given data.
     */
    function serializer($data, $force = false){
        if($force === true || (is_array($data) || is_object($data))){
            return serialize($data);
        }
        return $data;
    }

    /*
     |  GENERAL :: TRY TO UNSERIALIZE A SERIALIZED STRING
     |  @since  0.8.4
     |
     |  @param  string  The serialized string.
     |
     |  @return multi   The unserialized data, or the given data on failure.
     */
    function unserializer($data){
        if(is_string($data)){
            $copy = trim($data);
            if(($serial = @unserialize($copy)) !== false){
                return $serial;
            }
        }
        return $data;
    }

    /*
     |  GENERAL :: TESTS IF A FILE IS READABLE
     |  @since  0.8.4
     |
     |  @param  string  The complete path to the file.
     |
     |  @return bool    TRUE if the file is readable, FALSE if not.
     */
    function readable($file){
        $file = realpath($file);
        if($file === false || !file_exists($file)){
            return false;
        }
        if(!is_readable($file)){
            return false;
        }

        $perms = fileperms($file);
        if(($perms & 0x0100) || ($perms & 0x0020) || ($perms & 0x0004)){
            return true;
        }
        return false;
    }

    /*
     |  GENERAL :: TESTS IF A FILE IS WRITABLE
     |  @since  0.8.4
     |
     |  @param  string  The complete path to the file.
     |
     |  @return bool    TRUE if the file is writable, FALSE if not.
     */
    function writable($file){
        $file = realpath($file);
        if($file === false || !file_exists($file)){
            return false;
        }
        if(!is_writable($file)){
            return false;
        }

        $perms = fileperms($file);
        if(($perms & 0x0080) || ($perms & 0x0010) || ($perms & 0x0002)){
            return true;
        }
        return false;
    }

    /*
     |  GENERAL :: RECURSIVE COPY
     |  @since  0.8.4
     |
     |  @param  string  The source folder to copy / clone.
     |  @param  string  The destination folder for the copy / clone.
     |
     |  @return bool    TRUE on success, FALSE on failure.
     */
    function copy_recursive($source, $dest){
        if(!file_exists($source) || !is_dir($source)){
            return false;
        }
        if(!file_exists($dest)){
            if(@mkdir($dest) === false){
                return false;
            }
        }
        $source = realpath($source) . DIRECTORY_SEPARATOR;
        $dest   = realpath($dest) . DIRECTORY_SEPARATOR;

        // Loop
        $handle = opendir($source);
        while(($file = readdir($handle)) !== false){
            if(in_array($file, array(".", ".."))){
                continue;
            }
            if(is_dir($source . $file)){
                copy_recursive($source . $file . DIRECTORY_SEPARATOR, $dest . $file . DIRECTORY_SEPARATOR);
            } else if(is_file($source . $file)){
                copy($source . $file, $dest . $file);
            }
        }
        closedir($handle);
        return true;
    }

    /*
     |  GENERAL :: RECURSIVE UNLINK
     |  @since  0.8.4
     |
     |  @param  string  The path to delete.
     |
     |  @return bool    TRUE on success, FALSE on failure.
     */
    function unlink_recursive($path){
        if(!file_exists($path) || !is_dir($path)){
            return false;
        }
        $path = realpath($path) . DIRECTORY_SEPARATOR;

        // Loop
        $handle = opendir($path);
        while(($file = readdir($handle)) !== false){
            if(in_array($file, array(".", ".."))){
                continue;
            }
            if(is_dir($path . $file)){
                unlink_recursive($path . $file . DIRECTORY_SEPARATOR);
            } else if(is_file($path . $file)){
                @unlink($path . $file);
            }
        }
        closedir($handle);
        return @rmdir($path);
    }

    /*
     |	GENERAL :: REQUEST METHOD
     |	@since	0.1.0
     |
     |	@return	string	"GET", "POST" or "AJAX" depending on the REQUEST METHOD.
     */
    function request_method(){
        if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest"){
            return "AJAX";
        }
        if($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST)){
            return "POST";
        }
        return "GET";
    }

    /*
     |  GENERAL :: CONVERT SIZE
     |  @since  0.8.4
     |  @author Philippe Archambault AND Martijn van der Kleijn
     |  @source https://github.com/wolfcms/wolfcms/edit/master/wolf/Framework.php#L2147
     |
     |  @param  int     The respective bytes.
     |
     |  @return string  The converted size.
     */
    function convert_size($bytes){
       if($bytes >= 1073741824){
           $bytes = round($bytes / 1073741824 * 100) / 100 . " GB";
       } else if($bytes >= 1048576){
           $bytes = round($bytes / 1048576 * 100) / 100 . " MB";
       } else if($bytes >= 1024){
           $bytes = round($bytes / 1024 * 100) / 100 . " KB";
       } else {
           $bytes = "{$bytes} B";
       }
       return $bytes;
    }

    /*
     |  GENERAL :: PARSE SIZE
     |  @since  0.8.4
     |
     |  @param  string  The size value.
     |
     |  @return int     The parsed size.
     */
    function parse_size($value){
        $bytes = preg_replace("#[^0-9\.]#", "", $value);
        if(ends_with($value, array("GB", "G"))){
            $bytes = (int) round($bytes * 1073741824);
        } else if(ends_with($value, array("MB", "M"))){
            $bytes = (int) round($bytes * 1048576);
        } else if(ends_with($value, array("KB", "K"))){
            $bytes = (int) round($bytes * 1024);
        } else {
            $bytes = (int) round($bytes);
        }
        return $bytes;
    }

    /*
     |  GENERAL :: SAVE STRING COMPARISON AGAINST TIME-ATTACKs
     |  @since  0.8.4
     |  @author asphp@dsgml.com AND Markus P.N.
     |  @source http://php.net/manual/function.hash-equals.php#115635
     |          http://php.net/manual/function.hash-equals.php#115664
     |
     |  @param  string  The known string / hash.
     |  @param  string  The user input.
     |
     |  @return bool    TRUE if the strings are equal FALSE if not.
     */
    function compare($known, $user){
        if(strlen($known) != strlen($user)){
            $return = 1;
        } else {
            $return = 0;
        }

        $result = $known ^ $user;
        for($i = 0; $i < strlen($result); $i++){
            $return |= ord($result[$i]);
        }
        return !$return;
    }

    /*
     |  GENERAL :: GET MAX-UPLOAD DATA
     |  @since  0.8.4
     |
     |  @reutrn string  The max upload data size per request.
     */
    function upload_size(){
        $post_size = parse_size(ini_get("post_max_size"));
        if($post_size > 0){
            $size = $post_size;
        }

        $max_size  = parse_size(ini_get("upload_max_filesize"));
        if($max_size > 0 && $max_size < $post_size){
            $size = $post_size;
        }
        return convert_size(isset($size)? $size: 0);
    }

    /*
     |  GENERAL :: GET MAX-UPLOAD NUMBER
     |  @since  0.8.4
     |
     |  @return int     The number of files per upload requets.
     */
    function upload_number(){
        return ini_get("max_file_uploads");
    }

    /*
     |  GENERAL :: GET EXECUTION TIME
     |  @since  0.8.4
     |
     |  @return string  The execution time as STRING.
     */
    function execution_time(){
        return sprintf("%01.4f", microtime(true) - START);
    }

    /*
     |  GENERAL :: GET MEMORY USAGE
     |  @since  0.8.4
     |
     |  @param  bool    TRUE to get the total usage, FALSE to get the 'current' used one.
     |
     |  @return string  The formatted amound of memory as STRING.
     */
    function memory_usage($real = false){
        return convert_size(memory_get_usage(!!$real));
    }

    /*
     |  GENERAL :: MAKE TIMESTAMP FROM DATETIME
     |  @since  0.1.0
     |
     |  @param  string  The datetime string depending on $type.
     |  @param  string  Use "datetime" for "Y-m-d H:i:s", "date" for "Y-m-d" and "time" for "H:i:s".
     |
     |  @return int     The timestamp as INT.
     */
    function mk_time($string, $type = "datetime"){
        if($type == "datetime"){
            list($date, $time) = array_pad(explode(" ", $string), 2, "");
        } else if($type == "date"){
            $date = $string;
        } else if($type == "time"){
            $time = $string;
        }

        // Convert
        if(isset($date) && preg_match("#[0-9]{4}\-[0-9]{2}\-[0-9]{2}#", $date)){
            list($Y, $M, $D) = explode("-", $date);
        }
        if(isset($time) && preg_match("#[0-9]{2}\:[0-9]{2}\:[0-9]{2}#", $time)){
            list($h, $m, $s) = explode(":", $time);
        }

        // Return
        return mktime(
            (isset($h)? $h: "00"), (isset($m)? $m: "00"), (isset($s)? $s: "00"),
            (isset($M)? $M: "01"), (isset($D)? $D: "01"), (isset($Y)? $Y: "1970")
        );
    }

    /*
     |  STRING :: STARTS WITH
     |  @since  0.8.4
     |
     |  @param  string  The haystack.
     |  @param  multi   A single needle as STRING, multiple as ARRAY.
     |  @param  bool    TRUE to check case-sensitive, FALSE to do it not.
     |
     |  @return bool    TRUE if the haystack starts with the neddle, FALSE if not.
     */
    function starts_with($haystack, $needle, $strict = true){
        if(is_string($needle)){
            $needle = array($needle);
        }
        foreach($needle AS $n){
            if($strict && strpos($haystack, $n) === 0){
                return true;
            } else if(!$strict && stripos($haystack, $n) === 0){
                return true;
            }
        }
        return false;
    }

    /*
     |  STRING :: ENDS WIDTH
     |  @since  0.8.4
     |
     |  @param  string  The haystack.
     |  @param  multi   A single needle as STRING, multiple as ARRAY.
     |  @param  bool    TRUE to check case-sensitive, FALSE to do it not.
     |
     |  @return bool    TRUE if the haystack ends with the neddle, FALSE if not.
     */
    function ends_with($haystack, $needle, $strict = true){
        if(is_string($needle)){
            $needle = array($needle);
        }
        foreach($needle AS $n){
            if($strict && strrpos($haystack, $n) === (strlen($haystack)-strlen($n))){
                return true;
            } else if(!$strict && strripos($haystack, $n) === (strlen($haystack)-strlen($n))){
                return true;
            }
        }
        return false;
    }

    /*
     |  HELPER :: GET THE VALUE FROM AN ARRAY
     |  @since  0.8.4
     |
     |  @param  string  The key inside the array.
     |  @param  array   The respective array.
     |  @param  bool    TRUE to print the value, FALSE to return it as string.
     |
     |  @return string  The value from the array or a empty string.
     */
    function value($key, $array, $print = true){
        $value = "";
        if(array_key_exists($key, $array)){
            if(is_string($array[$key]) || is_numeric($array[$key])){
                $value = (string) $array[$key];
            }
        }

        if($print === false){
            return $value;
        }
        print($value);
    }

    /*
     |  HELPER :: IS CHECKED
     |  @since  0.8.4
     |
     |  @param  string  The option field value.
     |  @param  multi   The checked option(s) as string or as array.
     |  @param  multi   TRUE to print "checked", FALSE to return "checked",
     |                  NULL to return as bool, "xml" to return xml-valid.
     |
     |  @return mutli   See @param3.
     */
    function checked($value, $compare = true, $print = true){
        $checked = false;
        if(is_array($compare) && in_array($value, $compare, true)){
            $checked = true;
        } else if(!is_array($compare) && (string) $value === (string) $compare){
            $checked = true;
        }

        if($print === false){
            return ($checked)? 'checked': '';
        } else if($print === true){
            echo ($checked)? 'checked': '';
        } else if($print === "xml"){
            echo ($checked)? 'checked="checked"': '';
        } else {
            return $checked;
        }
    }

    /*
     |  HELPER :: IS SELECTED
     |  @since  0.8.4
     |
     |  @param  string  The option field value.
     |  @param  multi   The selected option(s) as string or as array.
     |  @param  multi   TRUE to print "selected", FALSE to return "selected",
     |                  NULL to return as bool, "xml" to return xml-valid.
     |
     |  @return mutli   See @param3.
     */
    function selected($value, $compare = true, $print = true){
        $selected = false;
        if(is_array($compare) && in_array($value, $compare, true)){
            $selected = true;
        } else if(!is_array($compare) && (string) $value === (string) $compare){
            $selected = true;
        }

        if($print === false){
            return ($selected)? 'selected': '';
        } else if($print === true){
            echo ($selected)? 'selected': '';
        } else if($print === "xml"){
            echo ($selected)? 'selected="selected"': '';
        } else {
            return $selected;
        }
    }

    /*
     |  HELPER :: IS DISABLED
     |  @since  0.8.4
     |
     |  @param  string  The option field value.
     |  @param  multi   The disabled option(s) as string or as array.
     |  @param  multi   TRUE to print "disabled", FALSE to return "disabled",
     |                  NULL to return as bool, "xml" to return xml-valid.
     |
     |  @return mutli   See @param3.
     */
    function disabled($value, $compare = true, $print = true){
        $disabled = false;
        if(is_array($compare) && in_array($value, $compare, true)){
            $disabled = true;
        } else if(!is_array($compare) && (string) $value === (string) $compare){
            $disabled = true;
        }

        if($print === false){
            return ($disabled)? 'disabled': '';
        } else if($print === true){
            echo ($disabled)? 'disabled': '';
        } else if($print === "xml"){
            echo ($disabled)? 'disabled="disabled"': '';
        } else {
            return $disabled;
        }
    }

    /*
     |  HELPER :: IS READONLY
     |  @since  0.8.4
     |
     |  @param  string  The option field value.
     |  @param  multi   The readonly option(s) as string or as array.
     |  @param  multi   TRUE to print "readonly", FALSE to return "readonly",
     |                  NULL to return as bool, "xml" to return xml-valid.
     |
     |  @return mutli   See @param3.
     */
    function readonly($value, $compare = true, $print = true){
        $readonly = false;
        if(is_array($compare) && in_array($value, $compare, true)){
            $readonly = true;
        } else if(!is_array($compare) && (string) $value === (string) $compare){
            $readonly = true;
        }

        if($print === false){
            return ($readonly)? 'readonly': '';
        } else if($print === true){
            echo ($readonly)? 'readonly': '';
        } else if($print === "xml"){
            echo ($readonly)? 'readonly="readonly"': '';
        } else {
            return $readonly;
        }
    }

    /*
     |  HELPER :: ODD OR EVEN
     |  @since  0.8.4
     |
     |  @return string  "odd" or "even".
     */
    function odd_even(){
        static $odd = true;
        return ($odd = !$odd) ? "even": "odd";
    }
    function even_odd(){
        return odd_even();
    }

    /*
     |  SECURITY :: HTML ENCODE
     |  @since  0.8.4
     |
     |  @param  string  The HTML to encode.
     |
     |  @return string  A HTML-encoded string.
     */
    function html_encode($string){
        if(defined("ENT_HTML5")){
            return htmlentities($string, ENT_QUOTES | ENT_HTML5 | ENT_IGNORE, DEFAULT_CHARSET);
        }
        return htmlentities($string, ENT_QUOTES | ENT_HTML401 | ENT_IGNORE, DEFAULT_CHARSET);
    }

    /*
     |  SECURITY :: HTML DECODE
     |  @since  0.8.4
     |
     |  @param  string  The HTML-encoded string.
     |
     |  @return string  A HTML-decoded string.
     */
    function html_decode($string){
        if(defined("ENT_HTML5")){
            return html_entity_decode($string, ENT_QUOTES | ENT_HTML5, DEFAULT_CHARSET);
        }
        return html_entity_decode($string, ENT_QUOTES | ENT_HTML401, DEFAULT_CHARSET);
    }

    /*
     |  SECURITY :: EXPERIMENTAL XSS FILTER
     |  @since  0.8.4
     |
     |  @param  string  The STRING to clean.
     |  @param  bool    TRUE to escape HTML too, FALSE to do it not.
     |
     |  @return string  The XSS-cleaned STRING.
     */
    function remove_xss($data, $html = false){
        if(is_array($data)){
            foreach($data AS $key => $value){
                $data[$key] = remove_xss($value, $html);
            }
        } else if(is_string($data)){
            if($html === true){
                $flag = defined("ENT_HTML5")? (ENT_QUOTES | ENT_HTML5): (ENT_QUOTES | ENT_HTML401);

                $data = htmlspecialchars(trim($data), $flag, "UTF-8");
                $data = preg_replace("#<(script|style)[^>]*?>.*?</\\1>#si", "", $data);
                $data = trim(strip_tags($data));
                $data = htmlspecialchars(trim($data), $flag, "UTF-8");
            }

            // @source  https://gist.github.com/mbijon/1098477
            $data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
            $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
            $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
            $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
            $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on)[^>]*+>#iu', '$1>', $data);
            $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
            $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
            $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
            $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
            $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
            $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
            $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
            $old_data = $data;
            while($old_data !== $data){
                $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title)[^>]*+>#i', '', $data);
            }
        }
        return $data;
    }

    /*
     |  SECURITY :: ESCAPE BASIC CHARACTERS
     |  @since  0.8.4
     |
     |  @param  string  The string to escape.
     |  @param  string  The type of escaping.
     |
     |  @return string  The escapred string.
     */
    function escape($string, $type = "html"){
        if($type == "url"){
            if(preg_match("#^(?:(?:https?|ftp):{1})\/\/[^\"\s\\\\]*.[^\"\s\\\\]*$#iu", $data, $match)){
                return $match[0];
            }
            return 'javascript:void(0)';
        }

        switch($type){
            case "style":
                $replace = array(
                    "<" => "&lt;", ">" => "&gt;", "\"" => "&quot;", "'" => "&apos;", "``" => "&grave;",
                    "(" => "&lpar;", ")" => "&rpar;", "&" => "&amp;", "\\\\" => "&bsol;"
                );
                break;
            case "script":
                $replace = array(
                    "<" => "&lt;", ">" => "&gt;", "\"" => "&quot;", "'" => "&apos;", "\\\\" => "&bsol;",
                    "%" => "&percnt;", "&" => "&amp;"
                );
                break;
            case "attribute":
                $replace = array("\"" => "&quot;", "'" => "&apos;", "``" => "&grave;");
                break;
            case "javascript":
                $replace = array(
                    "'" => '\\\'', '"' => '\"', '\\' => '\\\\', "\n" => '\n', "\r" => '\r', "\t" => '\t',
                    chr(12) => '\f', chr(11) => '\v', chr(8) => '\b', '</' => '\u003c\u002F',
                );
                break;
            default:
                $replace = array("<" => "&lt;", ">" => "&gt;");
                break;
        }
        return stripslashes(strtr($string, $replace));
    }
