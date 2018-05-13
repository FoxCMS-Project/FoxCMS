<?php
/*
 |  A Gravatar Helper class.
 |  @file       ./Gravatar.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.2.0 [0.2.0] - Beta
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */

    class Gravatar{
        const BASEURL = "https://www.gravatar.com/";

        /*
         |  HELPER :: GET EMAIL HASH
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The eMail Address to hash.
         |
         |  @return string  The eMail Address Hash or FALSE on failure.
         */
        static public function hash($email){
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                return false;
            }
            return md5(strtolower(trim($email)));
        }

        /*
         |  GET GRAVATAR / PROFILE URL
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The plain Gravatar eMail address.
         |  @param  string  The type of URL you want to get: 'image' or 'profile'.
         |  @param  array   The respective options for the Gravatar Call:
         |                      GRAVATARs:
         |                          'size'      The size in px [1 - 2048]
         |                          'default'   The default image to use [404 | mm | identicon | monsterid | wavatar]
         |                                      Or the respective file URL, if you want to use a local default.
         |                          'rating'    The maximum rating [g | pg | r | x]
         |                      PROFILEs
         |                          'format'    The respective format to return [json, xml, php, vcf, qr]
         |
         |  @return multi   The URL string of the garavatar / profile on success, FALSE on failure.
         */
        static public function url($email, $type = "image", $options = array()){
            if(($hash = self::hash($email)) == false){
                return false;
            }

            // Get Profile URL
            if($type === "profile"){
                $format = "json";
                $formats = array("json", "xml", "php", "vcf", "qr");
                if(isset($options["format"]) && in_array($options["format"], $formats)){
                    $format = $options["format"];
                }
                return self::BASEURL . htmlspecialchars(urlencode($hash . "." . $format));
            }

            // Get Gravatar URL
            $query = array("s" => "32", "d" => "mm", "r" => "g");
            $options = array_merge(array(
                "size"      => "32",
                "default"   => "mm",
                "rating"    => "g"
            ), $options);

            // Get Size
            if($options["size"] > 1 && $options["size"] < 2048){
                $query["s"] = htmlspecialchars(urlencode($options["size"]));
            }

            // Get Defaults
            if(in_array($options["default"], array("404", "mm", "identicon", "monsterid", "wavatar"))){
                $query["d"] = htmlspecialchars(urlencode($options["default"]));
            } else if(strpos($options["default"], "http") === 0){
                $query["d"] = "404";
            }

            // Get Ratings
            if(in_array($options["rating"],  array("g", "pg", "r", "x"))){
                $query["r"] = htmlspecialchars(urlencode($options["rating"]));
            }

            // Build URL
            $url  = self::BASEURL . "avatar/";
            $url .= htmlspecialchars($hash) . "?" . http_build_query($query);

            // Check 404
            if($query["d"] == "404"){
                if(!function_exists("curl_version")){
                    $curl = curl_init($url);
                    curl_setopt($curl, CURLOPT_HEADER, true);
                    curl_setopt($curl, CURLOPT_NOBODY, true);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($curl, CURLOPT_MAXREDIRS, 2);
                    curl_exec($curl);
                    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    curl_close($curl);

                    if($status != 200){
                        if($options["default"] == "404"){
                            return false;
                        }
                        $url = $options["default"];
                    }
                } else {
                    $headers = @get_headers($url);
                    if(!$headers || (is_array($headers) && !stripos($headers[0], "200 OK"))){
                        if($options["default"] == "404"){
                            return false;
                        }
                        $url = $options["default"];
                    }
                }
            }

            // Return
            return $url;
        }
        static public function profile($email, $options = array()){
            return self::url($email, "profile", $options);
        }

        /*
         |  BUILD AND IMAGE TAG
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  string  The plain Gravatar eMail address.
         |  @param  int     The size of the gravatar between 1 and 2048. (in pixel)
         |  @param  string  The default image to use [404 | mm | identicon | monsterid | wavatar]
         |                  or the respective file URL, if you want to use a local default.
         |  @param  string  The maximum rating to use [g | pg | r | x].
         |  @param  array   Some additional name => value attribute pairs for the IMG element.
         |
         |  @return multi   The image element with all attributes or FALSE on failure.
         */
        static public function image($email, $size = 32, $default = "mm", $rating = "g", $attr = array()){
            $options = array("size" => $size, "default" => $default, "rating" => $rating);
            if(($url = self::url($email, "image", $options)) == false){
                if(strpos($default, "http") !== 0){
                    return false;
                }
                $url = $default;
            }

            $string = array();
            if(is_array($attr) && !empty($attr)){
                foreach($attr AS $key => $value){
                    if(in_array($key, array("width", "height", "src"))){
                        continue;
                    }
                    $string[] = "{$key}=\"".htmlentities(strip_tags($value))."\"";
                }
            }
            $string = implode(" ", $string) . (!empty($string)? " ": "");
            return '<img src="'.$url.'" width="'.$size.'" height="'.$size.'" '.$string.'/>';
        }
        static public function img($email, $attr = array(), $size = "32", $default = "mm", $rating = "g", $deprecated = false){
            return self::image($email, $size, $default, $rating, $attr);
        }
    }
