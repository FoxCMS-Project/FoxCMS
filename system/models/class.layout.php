<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/models/class.layout.php
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

    class Layout extends Record{
        const TABLE = "layout";

        /*
         |  FINDER :: FIND ALL
         |  @since  0.8.4
         |
         |  @param  array   An array with respective find parameters.
         |
         |  @return multi   Multiple Layout Objects as ARRAY, FALSE on failure.
         */
        static public function findAll($args = NULL){
            return self::find($args);
        }

        /*
         |  FINDER :: FIND BY NAME
         |  @since  0.8.4
         |
         |  @param  multi   A single permission name as STRING, multiple as ARRAY.
         |
         |  @return multi   The Permission object on success, FALSE on failure.
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
                    "where"     => "name IN (".implode(", ", $where).")",
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
        public $content;
        public $content_type;
        public $position;

        public $created_on;
        public $updated_on;
        public $created_by;
        public $updated_by;

        /*
         |  OVERWRITE GET COLUMNS
         |  @since  0.8.4
         */
        public function getColumns(){
            return array(
                "id", "name", "content", "content_type", "position", "created_on",
                "updated_on", "created_by", "updated_by"
            );
        }

        /*
         |  IS USED
         |  @since  0.8.4
         */
        public function isUsed(){
            return Record::countFrom("Page", "layout_id = :lid", array(":lid" => $this->id));
        }

        /*
         |  HOOK :: BEFORE SAVE
         |  @since  0.8.4
         */
        public function beforeSave(){
            if(empty($this->name)){
                return false;
            }
            $this->name = remove_xss(strip_tags($this->name), true);
            $this->content_type = remove_xss(strip_tags($this->content_type), true);
            return true;
        }

        /*
         |  HOOK :: BEFORE INSERT
         |  @since  0.8.4
         */
        public function beforeInsert(){
            $this->created_by = AuthUser::getID();
            $this->created_on = date("Y-m-d H:i:s");
            return true;
        }

        /*
         |  HOOK :: BEFORE ZPDATE
         |  @since  0.8.4
         */
        public function beforeUpdate(){
            $this->updated_by = AuthUser::getID();
            $this->updated_on = date("Y-m-d H:i:s");
            return true;
        }
    }
