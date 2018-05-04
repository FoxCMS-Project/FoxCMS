<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/class.events.php
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
     |    THE EVENT CLASS REPLACES THE OBSERVER CLASS
     */
    class Event{
        /*
         |    GLOBAL VARs
         */
        static protected $events = array();
        static protected $called = array();
        static protected $filters = array();
        static protected $current = NULL;

        /*
         |  HELPER :: GENERATE AN EVENT / FILTER ID
         |  @since    0.1.0
         |
         |  @param  multi   The callback function / array.
         |
         |  @return string  A callback function STRING.
         */
        static private function id($callback){
            if(is_array($callback)){
                if(is_object($callback[0])){
                    return get_class($callback[0])."-".$callback[1];
                }
                return $callback[0]."::".$callback[1];
            } else if(is_a($callback, "Closure")){
                $callback = "#closure_" . time();
            }
            return $callback;
        }

        /*
         |  HELPER :: ORDER AN ARRAY
         |  @since    0.1.0
         |
         |  @param  array   The callback array.
         |
         |  @return array   The ordered callback array.
         */
        static private function order($array){
            $sorted = array();
            foreach($array AS $key => $data){
                if($data["position"] < 0){
                    $sort[] = count($array) + count($sorted);
                    continue;
                }
                $sort[] = $data["position"];
            }
            array_multisort($sort, SORT_ASC, $array);
            return $array;
        }

        /*
         |  GET THE CURRENT FILTER / EVENT
         |  @since    0.1.0
         |
         |  @return    multi    The current event / filter or NULL if none.
         */
        static public function current(){
            return self::$current;
        }

        /*
         |  CHECK IF AN EVENT HAS ALREADY BEEN CALLED
         |  @since    0.1.0
         |
         |  @return    bool    TRUE if the event has already been called, FALSE if not.
         */
        static public function called($event){
            if(in_array($event, self::$called)){
                return true;
            }
            return false;
        }

        /*
         |    ADD AN EVENT
         |    @since    0.1.0
         |
         |    @param    string    The event name.
         |    @param    callb.    The callback function.
         |    @param    int        The position of the callback (-1 to add it at the end).
         |    @param    int        The number of parameters to be passed (0 to pass all available arguments).
         |                    (If the event is called with less arguments, NULL will be passed instead).
         |    @param    bool    TRUE to add as event, FALSE to add as filter (You can also use "filter()").
         |
         |    @return    multi    The callback id on success, FALSE on failure.
         */
        static public function add($event, $callback, $pos = -1, $args = 0, $action = true){
            if(!is_string($event) || !is_callable($callback)){
                return false;
            }
            if(!is_integer($pos)){
                $pos = -1;
            }
            if(!is_integer($args)){
                $args = -1;
            }

            // Get Array
            if($action !== true){
                $add = &self::$filters;
            } else {
                $add = &self::$events;
            }

            // Add Array
            if(!isset($add[$event])){
                $add[$event] = array();
            }
            $id = self::id($callback);

            $add[$event][$id] = array(
                "callback"    => $callback,
                "position"    => $pos,
                "arguments"   => $args
            );
            return $id;
        }
        static public function addFilter($event, $callback, $pos = -1, $args = -1){
            return self::add($event, $callback, $pos, $args, false);
        }

        /*
         |    GET EVENT ARRAY
         |    @since    0.1.0
         |
         |    @param    string    The event name.
         |    @param    bool    TRUE to get an event array, FALSE to get an filter array.
         |                    (You can also use "getFilters()").
         |
         |    @return    array    An array with all events.
         */
        static public function get($event, $action = true){
            if($action !== true){
                return (isset(self::$filters[$event])? self::$filters[$event]: array());
            } else {
                return (isset(self::$events[$event])? self::$events[$event]: array());
            }
        }
        static public function getFilters($event){
            return self::get($event, false);
        }

        /*
         |    REMOVE AN SINGLE EVENT
         |    @since    0.1.0
         |
         |    @param    string    The event name.
         |    @param    callb.    The callback function.
         |    @param    bool    TRUE to remove an event, FALSE to remove an filter
         |                    (You can also use "removeFilter()").
         |
         |    @return    bool    TRUE if the single event could be removed, FALSE if not.
         */
        static public function remove($event, $callback, $action = true){
            if(is_callable($callback)){
                $callback = self::id($callback);
            }
            if(!is_string($event) || !is_string($callback)){
                return false;
            }

            // Get Array
            if($action !== true){
                $rem = &self::$filters;
            } else {
                if(in_array($event, self::$called)){
                    return false;
                }
                $rem = &self::$events;
            }

            // Test and Remove
            if(!isset($rem[$event])){
                return false;
            }
            if(!isset($rem[$event][$callback])){
                return false;
            }
            unset($rem[$event][$callback]);
            return true;
        }
        static public function removeFilter($event, $callback){
            return self::remove($event, $callback, false);
        }

        /*
         |    CLEAR AN COMPLETE EVENT ARRAY
         |    @since    0.1.0
         |
         |    @param    string    The event name.
         |    @param    bool    TRUE to clear an event, FALSE to clear an filter
         |                    (You can also use "clearFilter()").
         |
         |    @return    bool    TRUE if the event could be cleared, FALSE if not.
         */
        static public function clear($event, $action = true){
            if(!is_string($event)){
                return false;
            }

            // Get Array
            if($action !== true){
                $clear = &self::$filters;
            } else {
                if(in_array($event, self::$called)){
                    return false;
                }
                $clear = &self::$events;
            }

            // Clear Array
            if(!isset($clear[$event])){
                return false;
            }
            unset($clear[$event]);
            return true;
        }
        static public function clearFilter($event, $callback){
            return self::clear($event, false);
        }

        /*
         |    APPLY AN EVENT
         |    @since    0.1.0
         |
         |    @param    string    The event name.
         |    @param    multi    A single argument or multiple inside an array.
         |                    You need at least one argument, if $event is false.
         |    @param    bool    TRUE to apply an event, FALSE to apply an filter
         |                    (You can also use "applyFilter()").
         |
         |    @return    multi    ($action = true)? returns TRUE on success and FALSE on failure.
         |                    ($action = false)? returns the first (filtered) argument or FALSE on failure.
         */
        static public function apply($event, $args = array(), $action = true){
            if(!is_string($event)){
                return false;
            }
            if(!is_array($args)){
                $args = array($args);
            }

            // Get Array
            if($action !== true){
                if(count($args) === 0){
                    return false;
                }
                $apply = self::$filters;
            } else {
                if(in_array($event, self::$called)){
                    return false;
                }
                $apply = self::$events;
            }

            // Call Events
            if(isset($apply[$event]) && !empty($apply[$event])){
                $apply = self::order($apply[$event]);
                self::$current = $event;
                foreach($apply AS $cb){
                    $pass = $args;

                    if($cb["arguments"] > 0){
                        if(count($pass) > $cb["arguments"]){
                            $pass = array_slice($pass, 0, $cb["arguments"]);
                        } else if(count($pass) < $cb["arguments"]){
                            $pass = array_pad($pass, $cb["arguments"], NULL);
                        }
                    }

                    if($event !== true){
                        $args[0] = call_user_func_array($cb["callback"], $pass);
                    } else {
                        call_user_func_array($cb["callback"], $pass);
                    }
                }
                self::$current = NULL;
            }

            // Return
            if($action !== true){
                return $args[0];
            }
            self::$called[] = $event;
            return true;
        }
        static public function applyFilter($event, $args){
            return self::apply($event, $args, false);
        }
    }
