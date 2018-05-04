<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/models/class.user-role.php
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

    class UserRole extends Record{
        const TABLE = "user_role";

        /*
         |  SET ROLES FOR
         |  @since  0.8.4
         |
         |  @param  int     The unique user ID as INT or the user OBJECT.
         |  @param  multi   The single role ID as INT, multiple as ARRAY,
         |                  The role_name => role_id ARRAY pairs (Wolf CMS),
         |                  The single role object, multiple as ARRAY.
         |  @param  bool    TRUE to append the roles, FALSE to completely replace them
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function setRolesFor($user, $roles, $append = false){
            if(is_a($user, "User")){
                $user = $user->id;
            }
            if(!is_numeric($user) || $user <= 0){
                return false;
            }

            if(is_a($roles, "Role") || is_numeric($roles)){
                $roles = array($roles);
            }
            if(!is_array($roles)){
                return false;
            }

            // Delete Roles
            if(!$append){
                Record::deleteWhere("UserRole", "user_id = :uid", array(":uid" => (int) $user));
            }

            // Add Roles
            foreach($roles AS $role => $role_id){
                if(is_a($role_id, "Role")){
                    $role_id = $role_id->id;
                }
                if(!is_numeric($role_id)){
                    continue;
                }
                Record::insert("user_role", array("user_id" => ":uid", "role_id" => ":rid"), array(
                    ":uid"  => $user,
                    ":rid"  => $role_id
                ));
            }
            return true;
        }

        /*
         |  DATA VARs
         */
        public $user_id = false;
        public $role_id = false;
    }
