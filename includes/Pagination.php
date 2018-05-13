<?php
/*
 |  A Pagination helper class
 |  @file       ./Pagination.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.2.0 [0.2.0] - Beta
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */

    class Pagination{
        /*
         |  INSTANCE VARs
         */
        public $options = array(
            "url"               => NULL,
            "step"              => 10,
            "count"             => 50,
            "limit"             => 3,
            "current"           => 1,
            "container"         => '<div class=":class">:pagination</div>',
            "first-item"        => '<a href=":link" title="First Page" class=":class">First Page</a>',
            "prev-item"         => '<a href=":link" title="Previous Page" class=":class">Previous Page</a>',
            "current-item"      => '<span title="Current Page" class=":class">:num</span>',
            "number-item"       => '<a href=":link" title="Page :num" class=":class">:num</a>',
            "next-item"         => '<a href=":link" title="Next Page" class=":class">Next Page</a>',
            "last-item"         => '<a href=":link" title="Last Page" class=":class">Last Page</a>'
        );

        /*
         |  CONSTRUCTOR
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  array   The respective options to build the pagination:
         |                      "url"               The base url like 'http://www.example.org?page='
         |                      "step"              The step / posts per pages to calculate the number of links
         |                      "count"             The total number of pages.
         |                      "limit"             The maximum number of links to render before/after the current page.
         |                      "current"           The current page / pagination link number.
         |                      "container"         The container element string or FALSE to disable the container.
         |                      "first-item"        The "First Page" element string or FALSE to disable this element.
         |                      "next-item"         The "Next Page" element string or FALSE to disable this element.
         |                      "current-item"      The "Current Page" element string or FALSE to disable this element.
         |                      "number-item"       The "Page x" element string or FALSE to disable this element.
         |                      "prev-item"         The "Previous Page" element string or FALSE to disable this element.
         |                      "last-item"         The "Last Page" element string or FALSE to disable this element.
         |
         |                  Use the following replacement-keys in your element strings:
         |                      :pagination         Just adds the pagination within the container.
         |                      :num                The 'current-rendered' pagination number.
         |                      :link               The 'current-rendered' pagination link.
         |                      :class              The 'current-rendered' pagination class names.
         */
        public function __construct($options = array()){
            if(is_array($options) && count($options) > 0){
                $options = $this->deprecated($options);
                $options = array_merge($this->options, $options);

                if(is_null($options["url"])){
                    $https = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
                    $url = "http" . ($https? "s": "") . "://" . $_SERVER["SERVER_NAME"];
                    $options["url"] = $url . $_SERVER["SCRIPT_NAME"] . "?page=";
                }
                $this->options = $options;
            }
        }

        /*
         |  CONSTRUCTOR
         |  @since  0.2.0
         |
         |  @param  array   The options array.
         |
         |  @return array   The converted options array.
         */
        private function deprecated($options){
            $return = array();

            // General
            if(isset($options["base_url"])){
                $return["url"] = $options["base_url"];
            }
            if(isset($options["per_page"])){
                $return["step"] = $options["per_page"];
            }
            if(isset($options["total_rows"])){
                $return["count"] = $options["total_rows"];
            }
            if(isset($options["num_links"])){
                $return["limit"] = $options["num_links"];
            }
            if(isset($options["cur_page"])){
                $return["current"] = $options["cur_page"];
            }

            // Elements
            $elements = array(
                "full" => "container", "first" => "first-item", "last" => "last-item",
                "cur" => "cur-item", "next" => "next-item", "prev" => "prev-item",
                "num" => "number-item",
            );
            foreach($elements AS $old => $new){
                if(isset($options["{$old}_tag_open"])){
                    $return[$new] = $options["{$old}_tag_open"];

                    if($new == "container"){
                        $return[$new] .= ":pagination";
                    } else if(isset($options["{$old}_link"])){
                        $return[$new] .= $options["{$old}_link"];
                    } else {
                        $strings = array(
                            "first" => "First Page", "prev" => "Previous Page",
                            "next"  => "Next Page", "last" => "Last Page"
                        );
                        $return[$new] .= isset($strings[$old])? $strings[$old]: ":num";
                    }

                    if(isset($options["{$old}_tag_close"])){
                        $return[$new] .= $options["{$old}_tag_close"];
                    }
                } else if(isset($options["{$old}_link"])){
                    $return[$new] = '<a href=":link" title="'.$options["{$old}_link"].'">'.$options["{$old}_link"].'</a>';
                }
            }

            // Return
            return $return;
        }

        /*
         |  CONSTRUCTOR
         |  @since  0.1.0
         |  @update 0.2.0
         |
         |  @param  bool    TRUE to render the output, FALSE to return it as STRING.
         |
         |  @return multi   FALSE on failure, the rendered output or void.
         */
        public function render($display = false){
            if($this->options["count"] == 0 || $this->options["count"] <= $this->options["step"]){
                return false;
            }
            $current = $this->options["current"];

            // Total Number of Pagination-Links
            $links = ceil($this->options["count"] / $this->options["step"]);
            if($links <= 1){
                return false;
            }

            // Calculate Start and Stop
            if($current - $this->options["limit"] > 0){
                $start = $current - $this->options["limit"] + 1;
            } else {
                $start = 1;
            }
            if($current + $this->options["limit"] < $links){
                $stop = $current + $this->options["limit"];
            } else {
                $stop = $links;
            }

            // Render
            $render = array();

            // First Item
            if(!empty($this->options["first-item"]) && $current > 1){
                $render[] = strtr($this->options["first-item"], array(
                    ":num"      => "1",
                    ":link"     => $this->options["url"] . "1",
                    ":class"    => "first-item item-1"
                ));
            }

            // Previous Item
            if(!empty($this->options["prev-item"]) && $current > 1){
                $render[] = strtr($this->options["prev-item"], array(
                    ":num"      => ($current-1),
                    ":link"     => $this->options["url"] . ($current-1),
                    ":class"    => "prev-item item-" . ($current-1)
                ));
            }

            // Loop Pages
            if(!empty($this->options["number-item"])){
                for($page = $start - 1; $page <= $stop; $page++){
                    if($page <= 0){
                        continue;
                    }
                    if(!empty($this->options["current-item"]) && $current == $page){
                        $item = $this->options["current-item"];
                        $class = "current-item item-{$page}";
                    } else {
                        $item = $this->options["number-item"];
                        $class = "number-item item-{$page}";
                    }
                    $render[] = strtr($item, array(
                        ":num" => $page, ":link" => $this->options["url"] . $page, ":class" => $class
                    ));
                }
            }

            // Next Item
            if(!empty($this->options["next-item"]) && $current < $links){
                $render[] = strtr($this->options["next-item"], array(
                    ":num"      => ($current+1),
                    ":link"     => $this->options["url"] . ($current+1),
                    ":class"    => "next-item item-" . ($current+1)
                ));
            }

            // Last Item
            if(!empty($this->options["last-item"]) && $current < $links){
                $render[] = strtr($this->options["last-item"], array(
                    ":num"      => $links,
                    ":link"     => $this->options["url"] . $links,
                    ":class"    => "last-item item-{$links}"
                ));
            }

            // Display or Return
            if(!empty($this->options["container"])){
                $render = strtr($this->options["container"], array(
                    ":class"        => "pagination pagination-links",
                    ":pagination"   => "\n\t" . implode("\n\t", $render) . "\n"
                ));
            } else {
                $render =  implode("\n", $render);
            }
            if(!$display){
                return $render;
            }
            print($render);
        }
        public function createLinks($display = false){
            return $this->render($display);
        }
    }
