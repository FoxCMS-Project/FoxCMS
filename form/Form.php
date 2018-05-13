<?php
/*
 |  A Form builder class.
 |  @file       ./Form.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.2.0 [0.2.0] - Beta
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */

    class Form{
        /*
         |  THE GLOBAL ATTRIBUTEs
         */
        static protected $globals = array(
            "accesskey", "class", "contenteditable", "contentmenu", "data-*", "dir",
            "draggable", "dropzone", "form*", "hidden", "id", "lang", "name", "spellcheck",
            "style", "tabindex", "title", "translate"
        );

        /*
         |  THE AVAILABLE STATEs
         */
        static protected $states = array(
            "autofocus" => array("input", "textarea", "select", "button"),
            "checked"   => array("input"),
            "disabled"  => array("input", "textarea", "select", "option", "button"),
            "multiple"  => array("input", "select"),
            "readonly"  => array("input", "textarea"),
            "required"  => array("input", "textarea", "select"),
            "selected"  => array("option"),
        );

        /*
         |  THE AVAILABLE ATTRIBUTEs
         */
        static protected $attributes = array(
            "accept"        => array("input"),
            "align"         => array("input"),
            "alt"           => array("input"),
            "autocomplete"  => array("input"),
            "cols"          => array("textarea"),
            "dirname"       => array("input", "textarea"),
            "height"        => array("input"),
            "list"          => array("input"),
            "max"           => array("input"),
            "maxlength"     => array("input", "textarea"),
            "min"           => array("input"),
            "pattern"       => array("input"),
            "placeholder"   => array("input", "textarea"),
            "rows"          => array("textarea"),
            "size"          => array("input", "select"),
            "src"           => array("input"),
            "step"          => array("input"),
            "type"          => array("input", "button"),
            "value"         => array("input", "option", "button"),
            "width"         => array("input"),
            "wraps"         => array("textarea")
        );

        /*
         |  THE AVAILABLE INPUT TYPEs
         */
        static protected $types = array(
            "button", "checkbox", "color", "date", "datetime-local", "email", "file",
            "hidden", "image", "month", "number", "password", "radio", "range",
            "reset", "search", "submit", "tel", "text", "time", "url", "week"
        );

        /*
         |  THE CHRONOLOGICAL ORDER
         */
        static protected $ordered = array(
            "type", "id", "name", "value", "size", "maxlength", "min", "max", "step",
            "src", "alt", "accept", "list", "pattern", "placeholder", "cols", "rows",
            "wraps", "width", "height", "align", "class", "style", "dir", "dirname",
            "autocomplete", "form*", "data-*", "hidden", "contenteditable", "accesskey",
            "tabindex", "draggable", "dropzone", "lang", "spellcheck", "title",
            "translate", "autofocus", "multiple", "selected", "checked", "readonly",
            "disabled", "required"
        );

        /*
         |  ORDER ATTRIBUTEs
         |  @since  0.2.0
         */
        static protected function order($attributes){
            $form = array_search("form*", self::$ordered);
            $data = array_search("data-*", self::$ordered);

            // Loop
            $fixnum = 0;
            $ordered = array();
            foreach($attributes AS $key => $value){
                if(strpos($key, "form") === 0){
                    $number = $form;
                } else if(strpos($key, "data-") === 0){
                    $number = $data;
                } else if(($number = array_search($key, self::$ordered)) === false){
                    $number = count(self::$ordered)-7;
                }
                if($number >= $form || $number >= $data){
                    $number += $fixnum;
                }
                $ordered[] = $number;
            }
            array_multisort($ordered, SORT_NUMERIC, $attributes);
            return $attributes;
        }

        /*
         |  BUILD FIELD
         |  @since  0.2.0
         |
         |  @param  string  The TAG element name as STRING.
         |  @param  array   The attributes and configuration ARRAY.
         |
         |  @return string  The formatted element(s) as STRING.
         */
        static protected function build($tag, $attributes){
            $tag = strtolower($tag);

            // Get String

            switch($tag){
                // <input /> Fields
                case "input":
                    if(!isset($attributes["type"]) || empty($attributes["type"])){
                        return false;
                    }
                    if(!in_array($attributes["type"], self::$types)){
                        return false;
                    }
                    $type = $attributes["type"];
                    unset($attributes["type"]);

                    // String
                    if(in_array($type, array("radio", "checkbox"))){
                        $string = "<:tag type=\"{$type}\" :attr :state /> :label\n";
                    } else {
                        $string = ":label\n<:tag type=\"{$type}\" :attr :state />\n";
                    }

                    // Values
                    if(in_array($type, array("radio", "checkbox")) && isset($attributes["values"])){
                        $values = $attributes["values"];
                        unset($attributes["values"]);

                        $return = array();
                        foreach($values AS $id => $value){
                            if(strpos($id, $attributes["name"]) !== 0){
                                $id = $attributes["name"] . "-" . $id;
                            }

                            $return[] = self::build("input", array_merge($attributes, array(
                                "id"    => $id,
                                "type"  => $type,
                                "value" => $value,
                                "label" => $value
                            )));
                        }
                        return implode("\n", $return);
                    }
                    break;

                // <button>, <option>, <textarea> fields
                case "button":          //@fallthrough
                case "option":          //@fallthrough
                case "textarea":
                    $string = ":label <:tag :attr :state>:value</:tag>";
                    break;

                // <select> fields
                case "select":
                    $string = "<:tag :attr :state>\n:options</:tag>";

                    // Loop Options
                    $options = array();
                    if(isset($attributes["values"])){
                        $values = $attributes["values"];
                        unset($attributes["values"]);

                        foreach($values AS $id => $value){
                            if(strpos($id, $attributes["name"]) !== 0){
                                $id = $attributes["name"] . "-" . $id;
                            }

                            $selected = false;
                            if(isset($attrbutes["selected"]) && in_array($value, $attrbutes["selected"])){
                                $selected = true;
                            }
                            $options[] = self::build("option", array_filter(array_merge($attributes, array(
                                "id"        => $id,
                                "name"      => false,
                                "type"      => "option",
                                "value"     => $value,
                                "selected"  => $selected
                            ))));
                        }
                    }
                    $options = implode("\n", array_map("trim", $options));
                    break;

                default:
                    return false;
            }
            $attr = array();
            $state = array();

            // Check ID
            $attributes["id"] = preg_replace("/\s+/", "", trim($attributes["id"]));
            $attributes["id"] = preg_replace("/[^a-zA-Z0-9_-]+/", "", $attributes["id"]);

            // Check Label
            if(isset($attributes["label"]) && $attributes["label"]){
                if(!is_string($attributes["label"])){
                    $attributes["label"] = $attributes["name"];
                }
                $label = "<label for=\"{$attributes["id"]}\">{$attributes["label"]}</label>";
            }

            // Loop attributes
            foreach($attributes AS $key => $value){
                if(array_key_exists($key, self::$states)){
                    if(in_array($tag, self::$states[$key]) && $value){
                        if(isset($attributes["config:xml"]) && $attributes["config:xml"]){
                            $state[$key] = "{$key}=\"{$key}\"";
                        } else {
                            $state[$key] = "{$key}";
                        }
                    }
                    continue;
                }

                $value = htmlentities(strip_tags($value));
                if(in_array($key, self::$globals) || strpos($key, "form") === 0 || strpos($key, "data-") === 0){
                    $attr[$key] = "{$key}=\"{$value}\"";
                    continue;
                }
                if(array_key_exists($key, self::$attributes)){
                    if(in_array($tag, self::$attributes[$key])){
                        $attr[$key] = "{$key}=\"{$value}\"";
                    }
                    continue;
                }
            }

            // Replace and Return
            if(isset($attributes["config:order"]) && $attributes["config:order"]){
                $attr = self::order($attr);
            }
            $data = array(
                ":tag"      => $tag,
                ":attr"     => implode(" ", $attr),
                ":state"    => implode(" ", $state),
                ":label"    => isset($label)? $label: "",
                ":value"    => isset($attributes["value"])? $attributes["value"]: "",
                ":options"  => isset($options)? $options: "",
            );
            $string = strtr($string, $data);
            return str_replace(array("  />", " >"), array(" />", ">"), $string);
        }

        /*
         |  GENERATE INPUT FIELD
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  NEW FUNCTION CALL
         |  @param  string  The name attribute for the <input /> field.
         |  @param  array   The attributes as key => value pairs and some additional
         |                  config settings (starts with config:) for this <input /> field.
         |                      config:xml      (bool) Create a XML-valid attributes string.
         |                      config:order    (bool) Order the attributes string.
         |
         |  DEPRECATED FUNCTION CALL
         |  @param  string  The name attribute for the <input /> field.
         |  @param  string  The unique ID attribute for the <input /> field.
         |  @param  string  The type attribute for the <input /> field.
         |  @param  bool    TRUE to set an label, FALSE to do it not.
         |  @param  string  The placeholder attribute for the <input /> field.
         |  @param  bool    TRUE to set the required attribute, FALSE to do it not.
         |  @param  bool    TRUE to set the autofocus, FALSE to do it not.
         |  @param  bool    TRUE to set the autocomplete, FALSE to do it not.
         |  @param  array   Some additional attribute => value pairs.
         |
         |  @return string  The HTML 5 compliant <input /> field.
         */
        static public function input($name, $options = array()){
            if(func_num_args() == 2 && is_array($options)){
                $args = $options;
                $args["name"] = $name;
            } else {
                $deprecated = array(
                    "name", "id", "type", "label", "placeholder", "required",
                    "autofocus", "autocomplete", "options"
                );

                $args = array_combine($deprecated, array_pad(func_get_args(), 9, NULL));
                if(is_array($args["options"]) && !empty($args["options"])){
                    $args = array_merge($args["options"], $args);
                }
                unset($args["options"]);
            }

            // Check Basics
            if(!isset($args["id"]) ||!is_string($args["id"])){
                $args["id"] = $args["name"];
            }
            if(!isset($args["type"]) || !is_string($args["type"])){
                $args["type"] = "text";
            }
            if(isset($args["autocomplete"]) && !is_null($args["autocomplete"])){
                $args["autocomplete"] = $args["autocomplete"]? "on": "off";
            }

            // Build and Return field
            return self::build("input", array_filter($args));
        }

        /*
         |  GENERATE TEXTAREA FIELD
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  NEW FUNCTION CALL
         |  @param  string  The name attribute for the <textarea /> field.
         |  @param  array   The attributes as key => value pairs and some additional
         |                  config settings (starts with config:) for this <textarea /> field.
         |                      config:xml      (bool) Create a XML-valid attributes string.
         |                      config:order    (bool) Order the attributes string.
         |
         |  DEPRECATED FUNCTION CALL
         |  @param  string  The name attribute for the <textarea> field.
         |  @param  string  The unique ID attribute for the <textarea> field.
         |  @param  string  The rows attribute for the <textarea> field.
         |  @param  string  The text / value for the <textarea> field.
         |  @param  bool    TRUE to set an label, FALSE to do it not.
         |  @param  string  The placeholder attribute for the <textarea> field.
         |  @param  bool    TRUE to set the required attribute, FALSE to do it not.
         |  @param  bool    TRUE to set the autofocus, FALSE to do it not.
         |  @param  array   Some additional attribute => value pairs.
         |
         |  @return string  The HTML 5 compliant <textarea /> field.
         */
        static public function textarea($name, $options = array()){
            if(func_num_args() == 2 && is_array($options)){
                $args = $options;
                $args["name"] = $name;
            } else {
                $deprecated = array(
                    "name", "id", "rows", "value", "label", "placeholder", "required",
                    "autofocus", "options"
                );

                $args = array_combine($deprecated, array_pad(func_get_args(), 9, NULL));
                if(is_array($args["options"]) && !empty($args["options"])){
                    $args = array_merge($args["options"], $args);
                }
                unset($args["options"]);
            }

            // Check Basics
            if(!isset($args["id"]) ||!is_string($args["id"])){
                $args["id"] = $args["name"];
            }

            // Build and Return field
            return self::build("textarea", array_filter($args));
        }

        /*
         |  GENERATE RADIO FIELDs
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  NEW FUNCTION CALL
         |  @param  string  The name attribute for the <input /> field.
         |  @param  array   The respective values for the radio fields.
         |  @param  array   The attributes as key => value pairs and some additional
         |                  config settings (starts with config:) for this <input /> field.
         |                      config:xml      (bool) Create a XML-valid attributes string.
         |                      config:order    (bool) Order the attributes string.
         |
         |  DEPRECATED FUNCTION CALL
         |  @param  string  The name attribute for the <input /> field.
         |  @param  array   The respective values for the radio <input /> fields.
         |  @param  bool    TRUE to set the required attribute, FALSE to do it not.
         |  @param  bool    TRUE to set the autofocus, FALSE to do it not.
         |  @param  array   Some additional attribute => value pairs.
         |
         |  @return string  The HTML 5 compliant <input /> field.
         */
        static public function radio($name, $values = array(), $options = array()){
            if(func_num_args() == 3 && is_array($options)){
                $args = $options;
                $args["name"] = $name;
                $args["values"] = $values;
            } else {
                $deprecated = array("name", "values", "required", "autofocus", "options");

                $args = array_combine($deprecated, array_pad(func_get_args(), 5, NULL));
                if(is_array($args["options"]) && !empty($args["options"])){
                    $args = array_merge($args["options"], $args);
                }
                unset($args["options"]);
            }

            // Check Basics
            if(!isset($args["id"]) ||!is_string($args["id"])){
                $args["id"] = $args["name"];
            }
            $args["type"] = "radio";
            $attr["label"] = true;

            // Build and Return field
            return self::build("input", array_filter($args));
        }

        /*
         |  GENERATE CHECKBOX FIELDs
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  NEW FUNCTION CALL
         |  @param  string  The name attribute for the <input /> field.
         |  @param  array   The respective values for the checkbox fields.
         |  @param  array   The attributes as key => value pairs and some additional
         |                  config settings (starts with config:) for this <input /> field.
         |                      config:xml      (bool) Create a XML-valid attributes string.
         |                      config:order    (bool) Order the attributes string.
         |
         |  DEPRECATED FUNCTION CALL
         |  @param  string  The name attribute for the <input /> field.
         |  @param  array   The respective values for the checkbox <input /> fields.
         |  @param  bool    TRUE to set the required attribute, FALSE to do it not.
         |  @param  bool    TRUE to set the autofocus, FALSE to do it not.
         |  @param  array   Some additional attribute => value pairs.
         |
         |  @return string  The HTML 5 compliant <input /> field.
         */
        static public function checkbox($name, $values = array(), $options = array()){
            if(func_num_args() == 3 && is_array($options)){
                $args = $options;
                $args["name"] = $name;
                $args["values"] = $values;
            } else {
                $deprecated = array("name", "values", "required", "autofocus", "options");

                $args = array_combine($deprecated, array_pad(func_get_args(), 5, NULL));
                if(is_array($args["options"]) && !empty($args["options"])){
                    $args = array_merge($args["options"], $args);
                }
                unset($args["options"]);
            }

            // Check Basics
            if(!isset($args["id"]) ||!is_string($args["id"])){
                $args["id"] = $args["name"];
            }
            $args["type"] = "checkbox";
            $attr["label"] = true;

            // Build and Return field
            return self::build("input", array_filter($args));
        }
        static public function box($name){
            return call_user_func_array(array("Form", "checkbox"), func_get_args());
        }

        /*
         |  GENERATE SELECT / DROPDOWN FIELDs
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  NEW FUNCTION CALL
         |  @param  string  The name attribute for the <select> field.
         |  @param  array   The respective values for the option fields.
         |  @param  array   The attributes as key => value pairs and some additional
         |                  config settings (starts with config:) for this <select /> field.
         |                      config:xml      (bool) Create a XML-valid attributes string.
         |                      config:order    (bool) Order the attributes string.
         |
         |  DEPRECATED FUNCTION CALL
         |  @param  string  The name attribute for the <select> field.
         |  @param  array   The respective values for the <option> fields.
         |  @param  bool    TRUE to set the required attribute, FALSE to do it not.
         |  @param  bool    TRUE to set the autofocus, FALSE to do it not.
         |  @param  array   Some additional attribute => value pairs.
         |
         |  @return string  The HTML 5 compliant <select> field.
         */
        static public function select($name, $values = array(), $options = array()){
            if(func_num_args() == 3 && is_array($options)){
                $args = $options;
                $args["name"] = $name;
                $args["values"] = $values;
            } else {
                $deprecated = array(
                    "name", "values", "multiple", "selected", "size", "required",
                    "autofocus", "options"
                );

                $args = array_combine($deprecated, array_pad(func_get_args(), 8, NULL));
                if(is_array($args["options"]) && !empty($args["options"])){
                    $args = array_merge($args["options"], $args);
                }
                unset($args["options"]);
            }

            // Check Basics
            if(!isset($args["id"]) || !is_string($args["id"])){
                $args["id"] = $args["name"];
            }
            if(isset($args["selected"]) && $args["selected"]){
                if(is_string($args["selected"])){
                    $args["selected"] = array($args["selected"]);
                }
                if(!is_array($args["selected"])){
                    unset($args["selected"]);
                }
            }

            // Build and Return field
            return self::build("select", array_filter($args));
        }
        static public function dropdown($name){
            return call_user_func_array(array("Form", "select"), func_get_args());
        }

        /*
         |  GENERATE BUTTON FIELD
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  NEW FUNCTION CALL
         |  @param  string  The name attribute for the <button> field.
         |  @param  array   The attributes as key => value pairs and some additional
         |                  config settings (starts with config:) for this <button> field.
         |                      config:xml      (bool) Create a XML-valid attributes string.
         |                      config:order    (bool) Order the attributes string.
         |
         |  DEPRECATED FUNCTION CALL
         |  @param  string  The name attribute for the <button> field.
         |  @param  string  The type attribute for the <button> field.
         |  @param  array   Some additional attribute => value pairs.
         |
         |  @return string  The HTML 5 compliant <button> field.
         */
        static public function button($name, $options = array()){
            if(func_num_args() == 2 && is_array($options)){
                $args = $options;
                $args["name"] = $name;
            } else {
                $deprecated = array("name", "type", "options");

                $args = array_combine($deprecated, array_pad(func_get_args(), 3, NULL));
                if(is_array($args["options"]) && !empty($args["options"])){
                    $args = array_merge($args["options"], $args);
                }
                unset($args["options"]);
            }

            // Check Basics
            if(!isset($args["id"]) || !is_string($args["id"])){
                $args["id"] = $args["name"];
            }
            if(!isset($args["value"]) || !is_string($args["value"])){
                $args["value"] = $args["name"];
            }

            // Build and Return field
            return self::build("button", array_filter($args));
        }
    }
