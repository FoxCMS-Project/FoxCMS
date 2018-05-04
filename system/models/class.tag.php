<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/models/class.tag.php
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

    class Tag extends Record{
        const TABLE = "tag";

        /*
         |  FINDER :: FIND BY NAME
         |  @since  0.8.4
         |
         |  @param  multi   A single tag name as STRING, multiple as ARRAY.
         |
         |  @return multi   A single obejct instance, multiple as ARRAY or FALSE.
         */
        static public function findByName($name){
            if(is_string($name)){
                return self::findOne(array(
                    "where"     => "name = :name",
                    "values"    => array(":name" => $name)
                ));
            }
            if(is_array($name)){
                $where = array();
                $prepare = array();
                for($i = 0; $i < count($name); $i++){
                    $where[] = ":name{$i}";
                    $prepare[":name{$i}"] = $name[$i];
                }

                return self::find(array(
                    "where"     => "name IN (".implode(",", $where).")",
                    "values"    => $prepare
                ));
            }
            return false;
        }


        /*
         |  DATA VARs
         */
        public $id;
        public $name;
        public $count;

        /*
         |  HOOK :: AFTER SAVE
         |  @since  0.8.4
         */
        public function afterSave(){
            if($this->count === 0){
                return $this->delete();
            }
            return true;
        }
    }
