<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/models/class.user-permission.php
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

    class UserPermission extends Record{
        const TABLE = "user_permission";

        /*
         |  SET PERMISSIONS FOR
         |  @since  0.8.4
         |
         |  @param  int     The unique user ID as INT or the user OBJECT.
         |  @param  multi   The single permission ID as INT, multiple as ARRAY,
         |                  The permission_name => permission_id ARRAY pairs (Wolf CMS),
         |                  The single permission object, multiple as ARRAY.
         |  @param  bool    TRUE to append the roles, FALSE to completely replace them
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function setPermissionsFor($user_id, $permissions, $append = false){
            if(is_a($user, "User")){
                $user = $user->id;
            }
            if(!is_numeric($user) || $user <= 0){
                return false;
            }

            if(is_a($permissions, "Role") || is_numeric($permissions)){
                $permissions = array($permissions);
            }
            if(!is_array($permissions)){
                return false;
            }

            // Delete Permissions
            if(!$append){
                Record::deleteWhere(self::TABLE, "user_id = :uid", array(":uid" => (int) $user_id));
            }

            // Add Permissions
            foreach($permissions AS $perm => $perm_id){
                if(is_a($perm_id, "Permission")){
                    $perm_id = $perm_id->id;
                }
                if(!is_numeric($perm_id)){
                    continue;
                }
                Record::insert(self::TABLE, array("user_id" => ":uid", "permission_id" => ":pid"), array(
                    ":uid"  => $user_id,
                    ":pid"  => $perm_id
                ));
            }
            return true;
        }

        /*
         |  DATA VARs
         */
        public $user_id = false;
        public $permission_id = false;
    }
