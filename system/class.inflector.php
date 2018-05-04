<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/class.inflector.php
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
     |  The Inflector class allows you to reformat and adapt STRINGs. View the single
     |  methods for more informations.
     */
    class Inflector{
        /*
         |  TABLEIZE A STRING
         |  @since  0.1.0
         |  @exmpl  "like_this_dear_reader" becomes "LikeThisDearReader"
         |
         |  @param  string  The string, which should be camelized.
         |  @param  multi   A single glue between the words as STRING, multiple as ARRAY.
         |
         |  @return string  The camelized STRING.
         */
        static public function tableize($string, $chars = "_"){
            $string = str_replace($chars, " ", $string);
            return str_replace(" ", "", ucwords($string));
        }
        static public function camelize($string, $chars = "_"){
            return self::tableize($string, $chars);
        }

        /*
         |  UNDERSCORE A STRING
         |  @since  0.1.0
         |  @exmpl  "LikeThisDearReader" becomes "like_this_dear_reader"
         |
         |  @param  string  The string, which should be camelized.
         |  @param  string  The single glue between the words.
         |
         |  @return string  The underscored STRING.
         */
        static public function underscore($string, $glue = "_"){
            $string = preg_replace("#(?<=\\w)([A-Z])#", "{$glue}\\1", $string);
            return strtolower($string);
        }
        static public function minuscore($string){
            return self::underscore($string, "-");
        }
        static public function classify($string){
            return self::underscore($string, "_");
        }

        /*
         |  HUMANIZE A STRING
         |  @since  0.1.0
         |  @exmpl  "like_this_dear_reader" becomes "Like this dear reader"
         |          "LikeThisDearReader" becomes "Like this dear reader"
         |
         |  @param  string  The string, which should be camelized.
         |  @param  multi   A single glue between the words as STRING, multiple as ARRAY.
         |  @param  string  TRUE to use ucWords, FALSE to use ucFirst.
         |
         |  @return string  A humanized STRING.
         */
        static public function humanize($string, $glue = "_", $ucwords = false){
            $string = str_replace($glue, " ", self::underscore($string));
            return ($ucwords)? ucwords(strtolower($string)): ucfirst(strtolower($string));
        }
    }
