<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/models/class.role.php
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

    class Role extends Record{
        const TABLE = "role";

        /*
         |  FINDER :: FIND BY NAMEs
         |  @since  0.8.4
         |
         |  @param  multi   A single name name as STRING, multiple as ARRAY.
         |
         |  @return multi   The Role object on success, FALSE on failure.
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
                    "where"     => "name IN (" . implode(", ", $where) . ")",
                    "values"    => $prepare
                ));
            }
            return false;
        }

        /*
         |  FINDER :: BY USER ID
         |  @since  0.8.4
         |
         |  @param  multi   The user ID as INT or the user object.
         |
         |  @return multi   The role objects within an array, an empty array if the user
         |                  isn't assigned to any role, or FALSE on failure.
         */
        static public function findByUserId($user_id){
            if(is_a($user_id, "User")){
                $user_id = $user_id->id;
            }
            if(!is_numeric($user_id)){
                return false;
            }

            // Get Roles
            $roles = UserRole::find(array(
                "where"     => "user_id = :uid",
                "values"    => array(":uid" => (int) $user_id)
            ));
            if(count($roles) <= 0){
                return array();
            }

            // Fetch Roles
            $return = array();
            foreach($roles AS $role){
                $return[] = Role::findById($role->role_id);
            }
            return $return;
        }


        /*
         |  DATA VARs
         */
        public $id;
        public $name;
        public $permissions = false;

        /*
         |  TO STRING
         |  @since  0.8.4
         */
        public function __toString(){
            return $this->name;
        }

        /*
         |  OVERWRITE GET COLUMNS FUNCTION
         |  @since  0.8.4
         */
        public function getColumns(){
            return array("id", "name");
        }

        /*
         |  GET ALL PERMISSIONS
         |  @since  0.8.4
         |
         |  @return array   All permissions within an array.
         */
        public function permissions(){
            if(!$this->permissions){
                $this->permissions = array();
                foreach(RolePermission::findPermissionsFor($this->id) AS $perm){
                    $this->permissions[$perm->name] = $perm;
                }
            }
            return $this->permissions;
        }

        /*
         |  ROLE HAS PERMISSION
         |  @since  0.8.4
         |
         |  @param  multi   A single permission as STRING, multiple comma-separated or as ARRAY.
         |  @param  bool    TRUE if the current user needs all permissions, FALSE if one is enough.
         |
         |  @return bool    TRUE if the user has all permissions, FALSE if not.
         */
        public function hasPermission($perms, $all = true){
            if(is_string($perms)){
                $perms = array_filter(array_map("trim", explode(",", $perms)));
            }
            if(!is_array($perms)){
                return false;
            }

            $list = $this->permissions();
            foreach($perms AS &$perm){
                if(in_array($perm, $list)){
                    if(!$all){
                        return true;
                    }
                    $perm = NULL;
                }
            }
            return (count(array_filter($perms)) == 0);
        }
    }
