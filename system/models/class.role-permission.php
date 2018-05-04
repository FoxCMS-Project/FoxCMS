<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/models/class.role-permission.php
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

    class RolePermission extends Record{
        const TABLE = "role_permission";

        /*
         |  SAVE PERMISSIONS FOR
         |  @since  0.8.4
         |
         |  @param  int     The role id as INT.
         |  @param  multi   The single permission ID as INT, multiple as ARRAY,
         |                  The single permission object, multiple as ARRAY.
         |  @param  bool    TRUE to append the permissions, FALSE to completely replace them.
         |
         |  @return bool    TRUE if everything is fluffy, FALSE if not.
         */
        static public function savePermissionsFor($role_id, $permissions, $append = false){
            if(!is_numeric($role_id)){
                return false;
            }
            if(is_a($permissions, "Permission") || is_numeric($permissions)){
                $permissions = array($permissions);
            }
            if(!is_array($permissions)){
                return false;
            }

            if(!Record::existsIn("role", "id = :rid", array(":rid", (int) $role_id))){
                return false;
            }
            if(!$append){
                Record::deleteWhere(self::TABLE, "role_id = :rid", array(":rid" => (int) $role_id));
            }

            // Store the Permissions
            foreach($permissions AS $perm){
                if(is_a($perm, "Permission")){
                    $perm = $perm->id;
                }

                $inst = new RolePermission(array("role_id" => $role_id, "permission_id", $perm));
                if(!$inst->save()){
                    return false;
                }
            }
            return true;
        }

        /*
         |  FIND PERMISSIONS FOR
         |  @since  0.8.4
         */
        static public function findPermissionsFor($role_id){
            if(!is_numeric($role_id)){
                return false;
            }

            $rp = self::find(array(
                "where"     => "role_id = :rid",
                "values"    => array(":rid" => (int) $role_id)
            ));

            // Loop Them and Return
            $return = array();
            foreach($ro AS $role => $perm){
                $return[] = Permission:findById($perm->permission_id);
            }
            return $return;
        }


        /*
         |  DATA VARs
         */
        public $role_id = false;
        public $permission_id = false;
    }
