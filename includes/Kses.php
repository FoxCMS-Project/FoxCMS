<?php
/*
 |  The 'KSES Strips Evil Scripts' (X)HTML Filter
 |  @file       ./Kses.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.3.0 [0.3.0] - Stable
 |
 |  @license    GNU GPL v2
 |  @copyright  Copyright © 2017 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2002 - 2017 Ulf Harnhammar
 |              https://sourceforge.net/projects/kses
 */
/*
 |  Copyright (C) 2018 Ulf Harnhammar AND SamBrishes
 |
 |  This program is free software; you can redistribute it and/or modify it under the
 |  terms of the GNU General Public License as published by the Free Software Foundation;
 |  either version 2 of the License, or (at your option) any later version.
 |
 |  This program is distributed in the hope that it will be useful, but WITHOUT ANY
 |  WARRANTY; without even the implied warranty of ERCHANTABILITY or FITNESS FOR A
 |  MPARTICULAR PURPOSE. See the GNU General Public License for more details.
 |
 |  You should have received a copy of the GNU General Public License along with this
 |  program; if not, visit http://www.gnu.org/licenses/gpl.html or write to Free Software
 |  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
/*
 |  This class has been written with the following scripts / informations:
 |
 |  -   https://sourceforge.net/projects/kses
 |  -   https://core.trac.wordpress.org/browser/tags/4.9.5/src/wp-includes/kses.php
 */

    class Kses{
        const VERSION = "0.3.0";

        /*
         |  INSTANCE VARc
         */
        public $string;
        public $html = array();
        public $styles = array();
        public $schemes = array();

        /*
         |  CONSTRUCTOR
         |  @since  0.3.0
         |
         |  @param  string  The respective string to clean.
         |  @param  multi   The HTML Tags -> Allowed Attributes ARRAY pairs,
         |                   or 'post' for the default defined post array,
         |                   or 'excerpt' for the default defined excerpt array,
         |                   or NULL to remove all HTML elements and attributes.
         |  @param  multi   The allowed URL schemes / protocols as ARRAY,
         |                   or NULL to use the default schemes / protocols.
         |  @param  multi   The allowed style CSS attributes as ARRAY,
         |                   or NULL to use the default style CSS attributes.
         */
        public function __construct($string, $html = NULL, $schemes = NULL, $styles = NULL){
            if($html == "post" || $html == "excerpt"){
                $html = $this->defaults[$html];
            }
            if(empty($html) && !is_array($html)){
                $html = array();
            }
            if(empty($styles) && !is_array($styles)){
                $styles = $this->default_styles;
            }
            if(empty($schemes) && !is_array($schemes)){
                $schemes = array(
                    "http", "https", "ftp", "mailto", "irc", "feed", "svn", "telnet"
                );
            }

            // Filter
            if(defined("FOXCMS") && class_exists("Event", false)){
                $html    = Event::applyFilter("kses_filter_html", array($html));
                $schemes = Event::applyFilter("kses_filter_schemes", array($schemes));
                $styles  = Event::applyFilter("kses_filter_styles", array($styles));
            }

            // Set Data
            $this->string  = $string;
            $this->html    = $this->arrayLC($html);
            $this->styles  = $this->arrayLC($styles);
            $this->schemes = $schemes;
        }

        /*
         |  CORE :: FILTER STRING
         |  @since  0.3.0
         |
         |  @param  multi   The string to filter, or NULL to use the instance string.
         |
         |  @return string  The striped / cleaned string, which just contains the allowed
         |                  HTML tags / attributes.
         */
        public function filter($string = NULL){
            $string = is_string($string)? $string: $this->string;

            $string = $this->noNULL($string);
            $string = $this->normalizeEntities($string);
            $string = $this->hooks($string);

            $regex = '/(<!--.*?(-->|$))|(<[^>]*(>|$)|>)/';
            return preg_replace_callback($regex, array($this, "_split"), $string);
        }

        /*
         |  CORE :: ENABLE HOOKs
         |  @since  0.3.0
         |
         |  @param  string  The string to hook.
         |
         |  @return string  The filtered string.
         */
        private function hooks($string){
            if(!defined("FOXCMS") || !class_exists("Event", false)){
                return $string;
            }
            $filter = array($string, $this->html, $this->schemes);
            return Event::applyFilter("kses_filter", $filter);
        }


        ##
        ##  HELPER METHODs
        ##

        /*
         |  HELPER :: CONVERT ALL KEYS INTO LOWERCASE
         |  @since  0.3.0
         |
         |  @param  array   The respective array to convert.
         |
         |  @return array   The converted array.
         */
        public function arrayLC($array){
            $return = array();
            foreach($array AS $key => $val){
                $key = strtolower($key);
                if(is_array($val)){
                    $return[$key] = $this->arrayLC($val);
                } else {
                    $return[$key] = $val;
                }
            }
            return $return;
        }

        /*
         |  HELPER :: VALIDATE UNICODE
         |  @since  0.3.0
         |  @source https://core.trac.wordpress.org/browser/tags/4.9.5/src/wp-includes/kses.php
         |
         |  @param  int     The unicode value.
         |
         |  @return bool    TRUE if the unicode value is valid, FALSE if not.
         */
        public function validateUnicode($unicode){
            return (
                $unicode == 0x9 || $unicode == 0xa || $unicode == 0xd ||
                ($unicode >= 0x20 && $unicode <= 0xd7ff) ||
                ($unicode >= 0xe000 && $unicode <= 0xfffd) ||
                ($unicode >= 0x10000 && $unicode <= 0x10ffff)
            );
        }

        /*
         |  HELPER :: DECODE ENTITIES
         |  @since  0.3.0
         |
         |  @param  string  The respective string to convert.
         |
         |  @return string  The converted string.
         */
        public function decodeEntities($string){
            $string = preg_replace_callback('/&#([0-9]+);/', function($matches){
                return chr($matches[1]);
            }, $string);
            return preg_replace_callback('/&#[Xx]([0-9A-Fa-f]+);/', function($matches){
                return chr(hexdec($matches[1]));
            }, $string);
        }

        /*
         |  HELPER :: PARSE ATTIRBUTES LIST
         |  @since  0.3.0
         |
         |  @param  string  The attributes string, without tag name and <>.
         |
         |  @return array   The filtered / well-formatted attributes array.
         */
        public function parseAttr($attributes){
            $uris = array(
                "src", "href", "cite", "data", "xmlns", "profile", "classid", "usemap",
                "codebase", "longdesc", "action"
            );

            // Loop
            $mode = 0;          // Current Loop Function
            $attr = "";         // Current Attribute Name
            $array = array();   // All attributes.
            while(strlen($attributes) !== 0){
                $error = true;

                switch($mode){
                    // Fetch the Attribute Name
                    case 0:
                        if(preg_match('/^([a-zA-Z\-\:]+)/', $attributes, $match)){
                            $mode++;
                            $attr = $match[1];
                            $error = false;
                            $attributes = preg_replace('/^([a-zA-Z\-\:]+)/', '', $attributes);
                        }
                        break;

                    // Fetch the Equal Sign
                    case 1:
                        if(preg_match('/^(\s*=\s*)/', $attributes)){
                            $mode++;
                            $error = false;
                            $attributes = preg_replace('/^(\s*=\s*)/', '', $attributes);
                            break;
                        }
                        if(preg_match('/^(\s+)/', $attributes)){
                            $mode = 0;
                            $error = false;
                            if(!array_key_exists($attr, $array)){
                                $array[$attr] = array(
                                    "name"  => $attr,
                                    "value" => "",
                                    "whole" => $attr,
                                    "vless" => true,
                                    "quote" => NULL
                                );
                            }
                            $attributes = preg_replace('/^(\s+)/', '', $attributes);
                            break;
                        }
                        break;

                    // Fetch the Attribute Value
                    case 2:
                        $regex = '#^(^"([^"]*)"|^\'([^\']*)\'|^([^\s"\']+))(\s+|$)#';
                        if(preg_match($regex, $attributes, $match)){
                            $mode = 0;
                            $error = false;
                            if(!array_key_exists($attr, $array)){
                                if(in_array($attr, $uris)){
                                    $match[2] = $this->filterProtocol($match[2]);
                                }
                                $quote = substr($match[1], 0, 1);
                                $array[$attr] = array(
                                    "name"  => $attr,
                                    "value" => $match[2],
                                    "whole" => "{$attr}={$match[1]}",
                                    "vless" => false,
                                    "quote" => in_array($quote, array('\'', '"'))? $quote: NULL
                                );
                            }
                            $attributes = preg_replace($regex, "", $attributes);
                        }
                        break;
                }
                if($error){
                    $mode = 0;
                    $regex = '#^("[^"]*("|$)|\'[^\']*(\'|$)|\S)*\s*#';
                    $attributes = preg_replace($regex, '', $attributes);
                }
            }

            // Catch Last passed Non-Value Attribute
            if($mode == 1){
                $array[] = array(
                    "name"  => $attr,
                    "value" => "",
                    "whole" => $attr,
                    "vless" => true,
                    "quote" => NULL
                );
            }
            return $array;
        }


        ##
        ##  CLEANER METHODs
        ##

        /*
         |  CLEANER :: REMOVES INVALID AND NULL CHARACTERS
         |  @since  0.3.0
         |
         |  @param  string  The string to clean.
         |
         |  @return string  The cleaned string.
         */
        private function noNULL($string){
            $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $string);
            return preg_replace('/\\\\+0+/', '', $string);
        }

        /*
         |  CLEANER :: STRIPS SLASES FROM QUOTES
         |  @since  0.3.0
         |
         |  @param  string  The string to strip slashes.
         |
         |  @return string  The slashes-striped string.
         */
        private function stripslashes($string){
            return preg_replace('/\\\\"/', '"', $string);
        }

        /*
         |  CLEANER :: NORMALIZE ENTITIES
         |  @since  0.3.0
         |
         |  @param  string  The string to normalize entities.
         |
         |  @return string  The entities-normalized string.
         */
        private function normalizeEntities($string){
            $string = str_replace('&', '&amp;', $string);
            $string = preg_replace_callback('/&amp;([A-Za-z]{2,8}[0-9]{0,2});/',
                array($this, "_entities1"), $string);
            $string = preg_replace_callback('/&amp;#(0*[0-9]{1,7});/',
                array($this, "_entities2"), $string);
            $string = preg_replace_callback('/&amp;#[Xx](0*[0-9A-Fa-f]{1,6});/',
                array($this, "_entities3"), $string);
            return str_replace('&amp;amp;', '&amp;', $string);
        }

        /*
         |  CLEANER :: FILTER PROTOCOL
         |  @since  0.3.0
         |
         |  @param  string  The respective string to filter.
         |
         |  @return string  The filteres string on SUCCESS, an empty STRINg otherwise.
         */
        private function filterProtocol($string){
            $string = preg_replace('/\xad+/', '', $this->noNULL($string));

            $count = 0;
            $regex = '/^((&[^;]*;|[\sA-Za-z0-9])*)(:|&#58;|&#[Xx]3[Aa];)\s*/';
            do{
                $compare = $string;
                $string = preg_replace_callback($regex, array($this, "_protocol"), $string);
            } while($compare != $string && ++$count < 6);

            // Return
            if($compare == $string){
                return $string;
            }
            return "";
        }

        /*
         |  CLEANER :: FILTER ATTRIBUTE
         |  @since  0.3.0
         |
         |  @param  string  The respective value to check.
         |  @param  bool    TRUE if the attribute has a value, FALSE if not.
         |  @param  string  The respective type / key of filtering.
         |  @param  string  The respective value to compare.
         |
         |  @return bool    TRUE if the value is valid, FALSE if not.
         */
        private function filterAttr($value, $vless, $key, $compare){
            switch(strtolower($key)){
                case 'maxlen':
                    if(strlen($value) > $compare){
                        return false;
                    }
                    break;
                case 'minlen':
                    if(strlen($value) < $compare){
                        return false;
                    }
                    break;
                case 'maxval':
                    if(!preg_match('/^\s{0,6}[0-9]{1,6}\s{0,6}$/', $value) || $value > $compare){
                        return false;
                    }
                    break;
                case 'minval':
                    if(!preg_match('/^\s{0,6}[0-9]{1,6}\s{0,6}$/', $value) || $value < $compare){
                        return false;
                    }
                    break;
                case 'valueless':
                    if(($compare == "y" || $compare == true) && !$vless){
                        return false;
                    }
                    break;
            }
            return true;
        }

        /*
         |  CLEANER :: FILTER STYLE
         |  @since  0.3.0
         |  @source https://core.trac.wordpress.org/browser/tags/4.9.5/src/wp-includes/kses.php
         |
         |  @param  string  The respective style attribute value.
         |
         |  @return string  The filtered string on SUCCESS, an empty STRING otherwise.
         */
        private function filterStyle($value){
            $value = str_replace(array("\n","\r","\t"), "", $this->noNULL($value));
            if(empty($value) || preg_match('#[\\\\(&=}]|/\*#', $value)){
                return "";
            }

            $styles = array_map("trim", explode(";", $value));
            $return = array();
            foreach($styles AS $style){
                if(empty($style)){
                    continue;
                }
                if(strpos($style, ":") === false){
                    continue;
                }

                list($key, $value) = explode(":", $style, 2);
                if(!in_array($key, $this->styles) || empty($value)){
                    continue;
                }
                $return[] = "{$key}:{$value}";
            }
            return implode(";", $return) . ";";
        }


        ##
        ##  INTERNAL CALLBACKs
        ##

        /*
         |  CALLBACK :: VALIDATE AND NORMALIZE NAMED ENTITIES
         |  @since  0.3.0
         |
         |  @param  array   The matches array from `preg_replace_callback()`.
         |                   See method `normalizeEntities()`
         |
         |  @return string  The correctly encoded entity!
         */
        private function _entities1($matches){
            if(empty($matches[1])){
                return "";
            }
            if(array_key_exists($matches[1], $this->entities)){
                return "&{$matches[1]};";
            }
            return "&amp;{$matches[1]};";
        }

        /*
         |  CALLBACK :: VALIDATE AND NORMALIZE DECIMAL ENTITIES
         |  @since  0.3.0
         |
         |  @param  array   The matches array from `preg_replace_callback`.
         |                   See method `normalizeEntities()`
         |
         |  @return string  The correctly encoded entity!
         */
        private function _entities2($matches){
            if(empty($matches[1])){
                return "";
            }

            // Validate Unicode
            if($this->validateUnicode($matches[1])){
                $matches[1] = str_pad(ltrim($matches[1], "0"), 3, "0", STR_PAD_LEFT);
                return "&#{$matches[1]};";
            }
            return "&amp;#{$matches[1]};";
        }

        /*
         |  CALLBACK :: VALIDATE AND NORMALIZE HEXADECIMAL ENTITIES
         |  @since  0.3.0
         |
         |  @param  array   The matches array from `preg_replace_callback`.
         |                   See method `normalizeEntities()`
         |
         |  @return string  The correctly encoded entity!
         */
        private function _entities3($matches){
            if(empty($matches[1])){
                return "";
            }

            // Validate Unicode
            if($this->validateUnicode(hexdec($matches[1]))){
                return "&#x".ltrim($matches[1], "0").";";
            }
            return "&amp;#x{$matches[1]};";
        }

        /*
         |  CALLBACK :: VALIDATE AND NORMALIZE ELMENT
         |  @since  0.3.0
         |
         |  @param  array   The matches array from `preg_replace_callback`,
         |                   See method `filter()`
         |
         |  @return string  The striped / cleaned string, which just contains the allowed
         |                  HTML tags / attributes.
         */
        private function _split($string){
            $string = $this->stripslashes($string[0]);

            // Matched a ">" character
            if(substr($string, 0, 1) != "<"){
                return "&gt;";
            }

            // Matched a HTML comment
            if(substr($string, 0, 4) == "<!--"){
                $string = str_replace(array("<!--", "-->"), "", $string);
                $string = $this->filter($string);
                if($string == ""){
                    return "";
                }
                return "<!--" . trim($string, "-") . "-->";
            }

            // Seriously malformed Element
            if(!preg_match('#^<\s*(/\s*)?([a-zA-Z0-9]+)([^>]*)>?$#', $string, $matches)){
                return "";
            }
            $slash = trim($matches[1]);
            $element = $matches[2];
            $attributes = $matches[3];

            // Not-Allowed HTML Element
            if(!isset($this->html[strtolower($element)])){
                return "";
            }

            // Matched Closing HTML Element
            if($slash !== ""){
                return "<{$slash}{$element}>";
            }

            // Filter Attributes
            return $this->_attr("{$slash}{$element}", $attributes);
        }

        /*
         |  CALLBACK :: VALIDATE AND NORMALIZE ATTRIBUTEs
         |  @since  0.3.0
         |
         |  @param  string  The respective HTML element as STRING.
         |  @param  string  The respective attributes STIRNG.
         |
         |  @return string  The striped / cleaned string, which just contains the allowed
         |                  HTML tags / attributes.
         */
        private function _attr($element, $attributes){
            if(!is_string($element) || !is_string($attributes)){
                return "";
            }
            $xhtml = (preg_match('#\s*/\s*$#', $attributes))? " /": "";

            // HTML Element couldn't found
            if(!isset($this->html[strtolower($element)])){
                return "";
            }
            $current = $this->html[strtolower($element)];

            // No attributes allowed
            if(count($current) == 0){
                return "<{$element}{$xhtml}>";
            }

            // Split and Loop Attributes
            $return = array();
            foreach($this->parseAttr($attributes) AS $attr => $data){
                $attr = strtolower($attr);
                if(isset($current[$attr])){
                    $curarg = $current[$attr];
                } else if(isset($this->globals[$attr])){
                    $curarg = $this->globals[$attr];
                } else {
                    continue;
                }

                // Filter Style
                if($attr == "style" && !$data["vless"]){
                    $data["value"] = $this->filterStyle($data["value"]);
                    if(empty($data["value"])){
                        continue;
                    }
                    $data["whole"] = "{$data["name"]}={$data["quote"]}{$data["value"]}{$data["quote"]}";
                }

                // Add Attribute
                if(!is_array($curarg)){
                    $return[] = $data["whole"];
                } else {
                    $error = false;
                    foreach($curarg AS $key => $value){
                        if(!$this->filterAttr($data["value"], $data["vless"], $key, $value)){
                            $error = true;
                        }
                    }
                    if(!$error){
                        $return[] = $data["whole"];
                    }
                }
            }

            // Prepare Attributes and Return
            $return = implode(" ", $return);
            $return = preg_replace('/[<>]/', '', $return);
            return "<{$element} {$return}{$xhtml}>";
        }

        /*
         |  CALLBACK :: VALIDATE AND NORMALIZE PROTOCOL
         |  @since  0.3.0
         |
         |  @param  string  The respective attribute value.
         |
         |  @return string  The cleaned / validated attribute value or an empty SRING.
         */
        private function _protocol($string){
            $string = $this->decodeEntities($string[0]);
            $string = $this->noNULL(preg_replace('/\s/', '', $string));
            $string = strtolower(preg_replace('/\xad+/', '', $string));

            foreach($this->schemes AS $scheme){
                if(strtolower($scheme) == $string){
                    return "{$string}:";
                }
            }
            return "";
        }


        /*
         |  GLOBAL VARs
         */
        public $globals = array(
            "class"         => true,
            "dir"           => true,
            "id"            => true,
            "lang"          => true,
            "style"         => true,
            "title"         => true,
            "xml:lang"      => true
        );
        public $entities = array(
            "amp"     => "#38"  , "lt"      => "#60"  , "gt"      => "#62"  ,
            "Agrave"  => "#192" , "Aacute"  => "#193" , "Acirc"   => "#194" ,
            "Atilde"  => "#195" , "Auml"    => "#196" , "Aring"   => "#197" ,
            "AElig"   => "#198" , "Ccedil"  => "#199" , "Egrave"  => "#200" ,
            "Eacute"  => "#201" , "Ecirc"   => "#202" , "Euml"    => "#203" ,
            "Igrave"  => "#204" , "Iacute"  => "#205" , "Icirc"   => "#206" ,
            "Iuml"    => "#207" , "ETH"     => "#208" , "Ntilde"  => "#209" ,
            "Ograve"  => "#210" , "Oacute"  => "#211" , "Ocirc"   => "#212" ,
            "Otilde"  => "#213" , "Ouml"    => "#214" , "Oslash"  => "#216" ,
            "Ugrave"  => "#217" , "Uacute"  => "#218" , "Ucirc"   => "#219" ,
            "Uuml"    => "#220" , "Yacute"  => "#221" , "THORN"   => "#222" ,
            "szlig"   => "#223" , "agrave"  => "#224" , "aacute"  => "#225" ,
            "acirc"   => "#226" , "atilde"  => "#227" , "auml"    => "#228" ,
            "aring"   => "#229" , "aelig"   => "#230" , "ccedil"  => "#231" ,
            "egrave"  => "#232" , "eacute"  => "#233" , "ecirc"   => "#234" ,
            "euml"    => "#235" , "igrave"  => "#236" , "iacute"  => "#237" ,
            "icirc"   => "#238" , "iuml"    => "#239" , "eth"     => "#240" ,
            "ntilde"  => "#241" , "ograve"  => "#242" , "oacute"  => "#243" ,
            "ocirc"   => "#244" , "otilde"  => "#245" , "ouml"    => "#246" ,
            "oslash"  => "#248" , "ugrave"  => "#249" , "uacute"  => "#250" ,
            "ucirc"   => "#251" , "uuml"    => "#252" , "yacute"  => "#253" ,
            "thorn"   => "#254" , "yuml"    => "#255" , "nbsp"    => "#160" ,
            "iexcl"   => "#161" , "cent"    => "#162" , "pound"   => "#163" ,
            "curren"  => "#164" , "yen"     => "#165" , "brvbar"  => "#166" ,
            "sect"    => "#167" , "uml"     => "#168" , "copy"    => "#169" ,
            "ordf"    => "#170" , "laquo"   => "#171" , "not"     => "#172" ,
            "shy"     => "#173" , "reg"     => "#174" , "macr"    => "#175" ,
            "deg"     => "#176" , "plusmn"  => "#177" , "sup2"    => "#178" ,
            "sup3"    => "#179" , "acute"   => "#180" , "micro"   => "#181" ,
            "para"    => "#182" , "cedil"   => "#184" , "sup1"    => "#185" ,
            "ordm"    => "#186" , "raquo"   => "#187" , "frac14"  => "#188" ,
            "frac12"  => "#189" , "frac34"  => "#190" , "iquest"  => "#191" ,
            "times"   => "#215" , "divide"  => "#247" , "forall"  => "#8704",
            "part"    => "#8706", "exist"   => "#8707", "empty"   => "#8709",
            "nabla"   => "#8711", "isin"    => "#8712", "notin"   => "#8713",
            "ni"      => "#8715", "prod"    => "#8719", "sum"     => "#8721",
            "minus"   => "#8722", "lowast"  => "#8727", "radic"   => "#8730",
            "prop"    => "#8733", "infin"   => "#8734", "ang"     => "#8736",
            "and"     => "#8743", "or"      => "#8744", "cap"     => "#8745",
            "cup"     => "#8746", "int"     => "#8747", "there4"  => "#8756",
            "sim"     => "#8764", "cong"    => "#8773", "asymp"   => "#8776",
            "ne"      => "#8800", "equiv"   => "#8801", "le"      => "#8804",
            "ge"      => "#8805", "sub"     => "#8834", "sup"     => "#8835",
            "nsub"    => "#8836", "sube"    => "#8838", "supe"    => "#8839",
            "oplus"   => "#8853", "otimes"  => "#8855", "perp"    => "#8869",
            "sdot"    => "#8901", "Alpha"   => "#913" , "Beta"    => "#914" ,
            "Gamma"   => "#915" , "Delta"   => "#916" , "Epsilon" => "#917" ,
            "Zeta"    => "#918" , "Eta"     => "#919" , "Theta"   => "#920" ,
            "Iota"    => "#921" , "Kappa"   => "#922" , "Lambda"  => "#923" ,
            "Mu"      => "#924" , "Nu"      => "#925" , "Xi"      => "#926" ,
            "Omicron" => "#927" , "Pi"      => "#928" , "Rho"     => "#929" ,
            "Sigma"   => "#931" , "Tau"     => "#932" , "Upsilon" => "#933" ,
            "Phi"     => "#934" , "Chi"     => "#935" , "Psi"     => "#936" ,
            "Omega"   => "#937" , "alpha"   => "#945" , "beta"    => "#946" ,
            "gamma"   => "#947" , "delta"   => "#948" , "epsilon" => "#949" ,
            "zeta"    => "#950" , "eta"     => "#951" , "theta"   => "#952" ,
            "iota"    => "#953" , "kappa"   => "#954" , "lambda"  => "#955" ,
            "mu"      => "#956" , "nu"      => "#957" , "xi"      => "#958" ,
            "omicron" => "#959" , "pi"      => "#960" , "rho"     => "#961" ,
            "sigmaf"  => "#962" , "sigma"   => "#963" , "tau"     => "#964" ,
            "upsilon" => "#965" , "phi"     => "#966" , "chi"     => "#967" ,
            "psi"     => "#968" , "omega"   => "#969" , "thetasym"=> "#977" ,
            "upsih"   => "#978" , "piv"     => "#982" , "OElig"   => "#338" ,
            "oelig"   => "#339" , "Scaron"  => "#352" , "scaron"  => "#353" ,
            "Yuml"    => "#376" , "fnof"    => "#402" , "circ"    => "#710" ,
            "tilde"   => "#732" , "ensp"    => "#8194", "emsp"    => "#8195",
            "thinsp"  => "#8201", "zwnj"    => "#8204", "zwj"     => "#8205",
            "lrm"     => "#8206", "rlm"     => "#8207", "ndash"   => "#8211",
            "mdash"   => "#8212", "lsquo"   => "#8216", "rsquo"   => "#8217",
            "sbquo"   => "#8218", "ldquo"   => "#8220", "rdquo"   => "#8221",
            "bdquo"   => "#8222", "dagger"  => "#8224", "Dagger"  => "#8225",
            "bull"    => "#8226", "hellip"  => "#8230", "permil"  => "#8240",
            "prime"   => "#8242", "Prime"   => "#8243", "lsaquo"  => "#8249",
            "rsaquo"  => "#8250", "oline"   => "#8254", "euro"    => "#8364",
            "trade"   => "#8482", "larr"    => "#8592", "uarr"    => "#8593",
            "rarr"    => "#8594", "darr"    => "#8595", "harr"    => "#8596",
            "crarr"   => "#8629", "lceil"   => "#8968", "rceil"   => "#8969",
            "lfloor"  => "#8970", "rfloor"  => "#8971", "loz"     => "#9674",
            "spades"  => "#9824", "clubs"   => "#9827", "hearts"  => "#9829",
            "diams"   => "#9830"
        );
        public $defaults = array(
            "post"  => array(
                "a"             => array(
                    "href"          => true,
                    "rel"           => true,
                    "rev"           => true,
                    "name"          => true,
                    "target"        => true,
                ),
                "abbr"          => array(),
                "acronym"       => array(),
                "address"       => array(),
                "area"          => array(
                    "alt"           => true,
                    "coords"        => true,
                    "href"          => true,
                    "nohref"        => true,
                    "shape"         => true,
                    "target"        => true,
                ),
                "article"       => array(
                    "align"         => true,
                ),
                "aside"         => array(
                    "align"         => true,
                ),
                "audio"         => array(
                    "autoplay"      => true,
                    "controls"      => true,
                    "loop"          => true,
                    "muted"         => true,
                    "preload"       => true,
                    "src"           => true,
                ),
                "b"             => array(),
                "bdo"           => array(),
                "big"           => array(),
                "blockquote"    => array(
                    "cite"          => true,
                ),
                "br"            => array(),
                "button"        => array(
                    "disabled"      => true,
                    "name"          => true,
                    "type"          => true,
                    "value"         => true,
                ),
                "caption"       => array(
                    "align"         => true,
                ),
                "cite"          => array(),
                "code"          => array(),
                "col"           => array(
                    "align"         => true,
                    "char"          => true,
                    "charoff"       => true,
                    "span"          => true,
                    "valign"        => true,
                    "width"         => true,
                ),
                "colgroup"      => array(
                    "align"         => true,
                    "char"          => true,
                    "charoff"       => true,
                    "span"          => true,
                    "valign"        => true,
                    "width"         => true,
                ),
                "dd"            => array(),
                "del"           => array(
                    "datetime"      => true,
                ),
                "details"       => array(
                    "align"         => true,
                    "open"          => true,
                ),
                "dfn"           => array(),
                "div"           => array(
                    "align"         => true,
                ),
                "dl"            => array(),
                "dt"            => array(),
                "em"            => array(),
                "fieldset"      => array(),
                "figcaption"    => array(
                    "align"         => true,
                ),
                "figure"        => array(
                    "align"         => true,
                ),
                "font"          => array(
                    "color"         => true,
                    "face"          => true,
                    "size"          => true,
                ),
                "footer"        => array(
                    "align"         => true,
                ),
                "form"          => array(
                    "action"        => true,
                    "accept"        => true,
                    "accept-charset"=> true,
                    "enctype"       => true,
                    "method"        => true,
                    "name"          => true,
                    "target"        => true,
                ),
                "h1"            => array(
                    "align"         => true,
                ),
                "h2"            => array(
                    "align"         => true,
                ),
                "h3"            => array(
                    "align"         => true,
                ),
                "h4"            => array(
                    "align"         => true,
                ),
                "h5"            => array(
                    "align"         => true,
                ),
                "h6"            => array(
                    "align"         => true,
                ),
                "header"        => array(
                    "align"         => true,
                ),
                "hgroup"        => array(
                    "align"         => true,
                ),
                "hr"            => array(
                    "align"         => true,
                    "noshade"       => true,
                    "size"          => true,
                    "width"         => true,
                ),
                "i"             => array(),
                "img"           => array(
                    "alt"           => true,
                    "align"         => true,
                    "border"        => true,
                    "height"        => true,
                    "hspace"        => true,
                    "longdesc"      => true,
                    "vspace"        => true,
                    "src"           => true,
                    "usemap"        => true,
                    "width"         => true,
                ),
                "ins"           => array(
                    "datetime"      => true,
                    "cite"          => true,
                ),
                "kbd"           => array(),
                "label"         => array(
                    "for"           => true,
                ),
                "legend"        => array(
                    "align"         => true,
                ),
                "li"            => array(
                    "align"         => true,
                    "value"         => true,
                ),
                "map"           => array(
                    "name"          => true,
                ),
                "mark"          => array(),
                "menu"          => array(
                    "type"          => true,
                ),
                "nav"           => array(
                    "align"         => true,
                ),
                "ol"            => array(
                    "start"         => true,
                    "type"          => true,
                    "reversed"      => true,
                ),
                "p"         => array(
                    "align"         => true,
                ),
                "pre"           => array(
                    "width"         => true,
                ),
                "q"         => array(
                    "cite"      => true,
                ),
                "s"         => array(),
                "samp"          => array(),
                "section"       => array(
                    "align"         => true,
                ),
                "small"         => array(),
                "span"          => array(
                    "align"         => true,
                ),
                "strike"        => array(),
                "strong"        => array(),
                "sub"           => array(),
                "summary"       => array(
                    "align"         => true,
                ),
                "sup"           => array(),
                "table"         => array(
                    "align"     => true,
                    "bgcolor"       => true,
                    "border"        => true,
                    "cellpadding"   => true,
                    "cellspacing"   => true,
                    "rules"         => true,
                    "summary"       => true,
                    "width"         => true,
                ),
                "tbody"         => array(
                    "align"         => true,
                    "char"          => true,
                    "charoff"       => true,
                    "valign"        => true,
                ),
                "td"            => array(
                    "abbr"          => true,
                    "align"         => true,
                    "axis"          => true,
                    "bgcolor"       => true,
                    "char"          => true,
                    "charoff"       => true,
                    "colspan"       => true,
                    "headers"       => true,
                    "height"        => true,
                    "nowrap"        => true,
                    "rowspan"       => true,
                    "scope"         => true,
                    "valign"        => true,
                    "width"         => true,
                ),
                "textarea"      => array(
                    "cols"          => true,
                    "rows"          => true,
                    "disabled"      => true,
                    "name"          => true,
                    "readonly"      => true,
                ),
                "tfoot"         => array(
                    "align"         => true,
                    "char"          => true,
                    "charoff"       => true,
                    "valign"        => true,
                ),
                "th"            => array(
                    "abbr"          => true,
                    "align"         => true,
                    "axis"          => true,
                    "bgcolor"       => true,
                    "char"          => true,
                    "charoff"       => true,
                    "colspan"       => true,
                    "headers"       => true,
                    "height"        => true,
                    "nowrap"        => true,
                    "rowspan"       => true,
                    "scope"         => true,
                    "valign"        => true,
                    "width"         => true,
                ),
                "thead"         => array(
                    "align"         => true,
                    "char"          => true,
                    "charoff"       => true,
                    "valign"        => true,
                ),
                "title"         => array(),
                "tr"            => array(
                    "align"         => true,
                    "bgcolor"       => true,
                    "char"          => true,
                    "charoff"       => true,
                    "valign"        => true,
                ),
                "track"         => array(
                    "default"       => true,
                    "kind"          => true,
                    "label"         => true,
                    "src"           => true,
                    "srclang"       => true,
                ),
                "tt"            => array(),
                "u"             => array(),
                "ul"            => array(
                    "type"      => true,
                ),
                "var"           => array(),
                "video"         => array(
                    "autoplay"      => true,
                    "controls"      => true,
                    "height"        => true,
                    "loop"          => true,
                    "muted"         => true,
                    "poster"        => true,
                    "preload"       => true,
                    "src"           => true,
                    "width"         => true,
                )
            ),
            "excerpt"   => array(
                "a"             => array(
                    "href"          => true,
                    "rel"           => true,
                    "rev"           => true,
                    "name"          => true,
                    "target"        => true,
                ),
                "abbr"          => array(),
                "acronym"       => array(),
                "b"             => array(),
                "blockquote"    => array(
                    "cite"          => true,
                ),
                "cite"          => array(),
                "code"          => array(),
                "del"           => array(
                    "datetime"      => true,
                ),
                "em"            => array(),
                "i"             => array(),
                "q"             => array(
                    "cite"          => true,
                ),
                "s"             => array(),
                "strike"        => array(),
                "strong"        => array()
            )
        );
        public $default_styles = array(
            // General
            "clear", "float", "cursor", "display", "position", "direction", "overflow",
            "box-sizing", "border-spacing", "border-collapse", "caption-side", "vertical-align",
            "list-style", "list-style-type", "list-style-image", "list-style-position",

            // Size & Margin & Padding
            "height", "min-height", "max-height", "width", "min-width", "max-width",
            "margin", "margin-top", "margin-left", "margin-right", "margin-bottom",
            "padding", "padding-top", "padding-left", "padding-right", "padding-bottom",

            // Background
            "background", "background-color", "background-image", "background-arrachment",
            "background-position", "background-size", "background-origin", "background-repeat",
            "border", "border-width", "border-color", "border-style",

            // Border
            "border-top", "border-top-color", "border-top-style", "border-top-width",
            "border-left", "border-left-color", "border-left-style", "border-left-width",
            "border-right", "border-right-color", "border-right-style", "border-right-width",
            "border-bottom", "border-bottom-color", "border-bottom-style", "border-bottom-width",

            // Font
            "color", "font", "font-family", "font-size", "font-style", "font-variant",
            "font-weight", "letter-spacing", "line-height", "text-decoration", "text-indent",
            "text-align",
        );
    }

    /*
     |  CORE :: KSES / FILTER AN STRING
     |  @since  0.3.0
     |
     |  @param  string  The respective string to clean.
     |  @param  multi   The HTML Tags -> Allowed Attributes ARRAY pairs,
     |                   or 'post' for the default defined post array,
     |                   or 'excerpt' for the default defined excerpt array,
     |                   or NULL to remove all HTML elements and attributes.
     |  @param  multi   The allowed URL schemes / protocols as ARRAY,
     |                   or NULL to use the default schemes / protocols.
     |  @param  multi   The allowed style CSS attributes as ARRAY,
     |                   or NULL to use the default style CSS attributes.
     |
     |  @return string  The striped / cleaned string, which just contains the allowed
     |                  HTML tags / attributes.
     */
    function kses($string, $html = NULL, $schemes = NULL, $styles = NULL){
        $kses = new Kses($string, $html, $schemes, $styles);
        return $kses->filter();
    }

    /*
     |  CORE :: KSES VERSION NUMBER
     |  @since  0.3.0
     |
     |  @return string  The used kses version number.
     */
    function kses_version(){
        return Kses::VERSION;
    }
