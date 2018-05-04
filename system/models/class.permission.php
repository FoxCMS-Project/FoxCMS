<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/models/class.permission.php
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

    class Permission extends Record{
        const TABLE = "permission";

        /*
         |  GLOBAL VARs
         */
        static private $permissions = false;

        /*
         |  GET PERMISSIONS
         |  @since  0.8.4
         |
         |  @param  bool    TRUE to get just the IDs, FALSE to get all instanced.
         |
         |  @return array   An array with all IDs or Objects.
         */
        static public function getPermissions($ids = false){
            if(!self::$permissions){
                $perms = self::find();
                foreach($perms AS $perm){
                    self::$permissions[$perm->id] = $perm;
                }
            }
            return ($ids)? array_keys(self::$permissions): array_values(self::$permissions);
        }

        /*
         |  FINDER :: FIND BY ID
         |  @since  0.8.4
         |
         |  @param  int     The respective permission ID.
         |
         |  @return multi   The permission obejct on success, FALSE on failure.
         */
        static public function findById($id){
            $keys = self::getPermissions(true);
            if(!in_array($id, $keys)){
                return false;
            }
            return self::$permissions[$id];
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
                    $where[] = ":name{$i}"
                    $prepare[":name{$i}"] = $name[$i];
                }

                return self::find(array(
                    "where"     => "name IN (".implode.(", ", $where)")",
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

        /*
         |  TO STRING
         |  @since  0.8.4
         */
        public function __toString(){
            return $this->name;
        }

        /*
         |  RESET PERMISSION FOLDER
         |  @since  0.8.4
         */
        public function beforeSave(){
            self::$permissions = false;
            return true;
        }
    }
