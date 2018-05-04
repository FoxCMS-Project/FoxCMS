<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/models/class.user.php
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

    class User extends Record{
        const TABLE = "user";

        /*
         |  FINDER :: BY COLUMN
         |  @since  0.8.4
         |
         |  @param  string  The respective column name.
         |  @param  multi   The column value.
         |  @param  bool    TRUE to find just one, FALSE to find all.
         |
         |  @return multi   The User object on success, multiple as ARRAY, or FALSE on failure.
         */
        static public function findBy($column, $value, $single = true){
            $inst = new User();
            if(!is_string($column) || !in_array($column, $inst->getColumns())){
                return false;
            }
            if($single){
                return self::findOne(array(
                    "where"     => "{$column} = :value",
                    "values"    => array(":value" => $value)
                ));
            }
            return self::find(array(
                "where"     => "{$column} = :value",
                "values"    => array(":value" => $value)
            ));
        }

        /*
         |  FINDER :: All
         |  @since  0.8.4
         |
         |  @param  array   An array with respective find parameters.
         |
         |  @return multi   Multiple User Objects as ARRAY, FALSE on failure.
         */
        static public function findAll($args = NULL){
            return self::find($args);
        }

        /*
         |  DATA VARs
         */
        public $id = 0;
        public $username = "";
        public $name = "";
        public $email = "";

        public $password;
        public $salt;
        public $language;

        public $cookie;
        public $session;
        public $last_login;
        public $last_failure;
        public $failure_count;

        public $created_on;
        public $updated_on;
        public $created_by;
        public $updated_by;

        /*
         |  GET ROLES
         |  @since  0.8.4
         */
        public function roles(){
            if(!is_numeric($this->id) || $this->id <= 0){
                return array();
            }
            if(!($roles = Role::findByUserId($this->id))){
                return array();
            }

            $return = array();
            foreach($roles AS $role){
                $return[$role->id] = $role->name;
            }
            return $return;
        }

        /*
         |  OVERWRITE GET COLUMNS
         |  @since  0.8.4
         */
        public function getColumns(){
            return array(
                "id", "username", "name", "email", "password", "salt", "language", "cookie",
                "session", "last_login", "last_failure", "failure_count", "created_on",
                "updated_on", "created_by", "updated_by"
            );
        }

        /*
         |  HOOK :: BEFORE INSERT
         |  @since  0.8.4
         */
        public function beforeInsert(){
            if(empty($this->password)){
                return false;
            }
            if(!starts_with($this->password, "$")){
                $this->salt = "fox";
                $this->password = AuthUser::hashPassword($this->password);
            }

            $this->username = strtolower($this->username);
            if(strlen($this->username) < 3){
                return false;
            }
            if(preg_match("#[^a-z0-9_-]#", $this->username) || !preg_match("#^[a-z_]#", $this->username)){
                return false;
            }
            if(($this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL)) === false){
                return false;
            }

            if(Record::existsIn("User", "username = :user", array(":user" => $data["username"]))){
                return false;
            }
            if(Record::existsIn("User", "email = :email", array(":email" => $data["email"]))){
                return false;
            }

            $this->created_by = AuthUser::getID();
            $this->created_on = date("Y-m-d H:i:s");
            $this->last_login = date("Y-m-d H:i:s", 0);
            $this->last_failure = date("Y-m-d H:i:s", 0);
            $this->failure_count = 0;
            return true;
        }

        /*
         |  HOOK :: BEFORE UPDATE
         |  @since  0.8.4
         */
        public function beforeUpdate(){
            if(empty($this->password)){
                return false;
            }
            if($this->salt == "fox" && !starts_with($this->password, "$")){
                $this->password = AuthUser::hashPassword($this->password);
            }

            if(User::findById($this->id)->username != $this->username){
                return false;
            }
            if(($this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL)) === false){
                return false;
            }

            $this->updated_by = AuthUser::getID();
            $this->updated_on = date("Y-m-d H:i:s");
            return true;
        }
    }
