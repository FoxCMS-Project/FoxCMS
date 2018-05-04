<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/models/class.auth-user.php
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

    class AuthUser{
        /*
         |  GLOBAL VARs
         */
        static protected $isAdmin = false;
        static protected $isLoggedIn = false;

        static protected $currentUser = false;
        static protected $currentRoles = array();
        static protected $currentUserID = 0;

        /*
         |  INIT FUNCTION
         |  @since  0.8.4
         */
        static public function init(){
            if(($user = self::validateCookie()) === false){
                if(($user = self::validateSession()) === false){
                    self::logout();
                    return false;
                }
            }

            if(is_a($user, "Record")){
                self::$currentUser = $user;
                self::$currentRoles = $user->roles();
                self::$currentUserID = $user->id;
                self::$isAdmin = self::inRole("administrator");
                self::$isLoggedIn = true;
            }
            return true;
        }
        static public function load(){
            return self::init();
        }

        /*
         |  COOKIE :: BAKE A NEW COOKIE
         |  @since  0.8.4
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static private function bakeCookie($user){
            $user->cookie = md5($_SERVER["HTTP_USER_AGENT"] . SecureToken::getIP());
            if($user->save()){
                $cookie = http_build_query(array(
                    "expire"    => "now",
                    "nonce"     => $user->cookie,
                    "secret"    => SecureToken::generateToken($user->cookie, $user, time()+COOKIE_LIFE)
                ));
                setcookie(COOKIE_KEY, $cookie, time()+COOKIE_LIFE, "/", NULL, in_array(HTTPS_MODE, array("always", "frontend")), COOKIE_HTTP);
                return true;
            }
            return false;
        }

        /*
         |  COOKIE :: EAT THAT ONE COOKIE
         |  @since  0.8.4
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static private function eatCookie(){
            if(!isset($_COOKIE[COOKIE_KEY])){
                return false;
            }
            setcookie(COOKIE_KEY, false, time()-3600, "/", NULL, in_array(HTTPS_MODE, array("always", "frontend")), COOKIE_HTTP);
            return true;
        }

        /*
         |  COOKIE :: CRUMBLE THAT ONE COOKIE
         |  @since  0.8.4
         |
         |  @param  string  The cookie data STRING.
         |
         |  @return array   The cookie crumbs on success, an empty array otherwise.
         */
        static public function explodeCookie($cookie){
            if(!is_string($cookie)){
                return array();
            }
            $cookie = explode('&', $_COOKIE[COOKIE_KEY]);

            $return = array();
            foreach($cookie AS $crumb){
                list($key, $value) = array_pad(explode("=", $crumb), 2, NULL);
                $return[$key] = $value;
            }
            return $return;
        }

        /*
         |  COOKIE :: VALIDATE THAT ONE COOKIE
         |  @since  0.8.4
         |
         |  @return multi   The User Record object on success, FALSE on failure.
         */
        static private function validateCookie(){
            if(!isset($_COOKIE[COOKIE_KEY])){
                return false;
            }

            // Get Cookie
            $cookie = self::explodeCookie($_COOKIE[COOKIE_KEY]);
            if(!isset($cookie["nonce"]) && !isset($cookie["secret"])){
                return false;
            }

            // Validate User and token
            if(($user = User::findBy("cookie", $cookie["nonce"])) === false){
                return false;
            }
            if(!SecureToken::validateToken($cookie["secret"], $user->cookie, $user)){
                return false;
            }
            return $user;
        }

        /*
         |  SESSION :: BAKE A NEW SESSION
         |  @since  0.8.4
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static private function bakeSession($user){
            $user->session = md5($_SERVER["HTTP_USER_AGENT"] . session_id());
            if($user->save()){
                $_SESSION[SESSION_KEY] = array(
                    "expire"    => "now",
                    "nonce"     => $user->session,
                    "secret"    => SecureToken::generateToken($user->session, $user, time()+SESSION_LIFE)
                );
                return true;
            }
            return false;
        }

        /*
         |  SESSION :: VALIDATE THAT ONE SESSION
         |  @since  0.8.4
         |
         |  @return multi   The User Record object on success, FALSE on failure.
         */
        static private function validateSession(){
            if(!isset($_SESSION[SESSION_KEY])){
                return false;
            }

            // Get Session
            $session = $_SESSION[SESSION_KEY];
            if(!isset($session["nonce"]) || !isset($session["secret"])){
                return false;
            }

            // Validate User and token
            if(($user = User::findBy("session", $session["nonce"])) === false){
                return false;
            }
            if(!SecureToken::validateToken($session["secret"], $user->session, $user)){
                return false;
            }
            return $user;
        }

        /*
         |  CURRENT :: IS LOGGED IN
         |  @since  0.8.4
         |
         |  @return bool    TRUE if ther user is logged in, FALSE if not.
         */
        static public function isLoggedIn(){
            return self::$isLoggedIn;
        }

        /*
         |  CURRENT :: GET CURRENT USER
         |  @since  0.8.4
         |
         |  @return multi   The currest User object or false.
         */
        static public function getUser(){
            return self::$currentUser;
        }
        static public function getRecord(){
            deprecated("AuthUser::getRecord", "AuthUser::getUser");
            return self::getUser();
        }

        /*
         |  CURRENT :: GET CURRENT USER ID
         |  @since  0.8.4
         |
         |  @return int     The current user ID or 0.
         */
        static public function getID(){
            return self::$currentUserID;
        }

        /*
         |  CURRENT :: GET CURRENT USER NAME
         |  @since  0.8.4
         |
         |  @return multi   The current username as STRING or false.
         */
        static public function getUserName(){
            return (self::$currentUser)? self::$currentUser->username: false;
        }

        /*
         |  CURRENT :: GET CURRENT USER PERMISSIONS
         |  @since  0.8.4
         |
         |  @return array   The current user permissions or an empty ARRAY.
         */
        static public function getPermissions(){
            $return = array();
            foreach(self::$currentRoles as $role){
                $return = array_merge($return, $role->permissions());
            }
            return $return;
        }

        /*
         |  CURRENT :: CHECK CURRENT PERMISSIONS
         |  @since  0.8.4
         |
         |  @param  multi   A single permission as STRING, multiple comma-separated or as ARRAY.
         |  @param  bool    TRUE if the current user needs all permissions, FALSE if one is enough.
         |
         |  @return bool    TRUE if the user has all permissions, FALSE if not.
         */
        static public function hasPermission($perms = false, $all = true){
            if($perms === false || self::getID() == 1){
                return true;
            }

            if(is_string($perms)){
                $perms = array_filter(array_map("trim", explode(",", $perms)));
            }
            if(!is_array($perms)){
                return false;
            }

            $list = self::getPermissions();
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

        /*
         |  CURRENT :: CHECK CURRENT ROLEs
         |  @since  0.8.4
         |
         |  @param  multi   A single role as STRING, multiple comma-separated or as ARRAY.
         |  @param  bool    TRUE if the current user needs to be in all roles, FALSE if one is enough.
         |
         |  @return bool    TRUE if the user in (all) roles, FALSE if not.
         */
        static public function inRole($roles, $all = true){
            if($roles == false){
                return true;
            }

            if(is_string($roles)){
                $roles = array_filter(array_map("trim", explode(",", $roles)));
            }
            if(!is_array($roles)){
                return false;
            }

            foreach($roles AS &$role){
                if(in_array($role, self::$currentRoles)){
                    if(!$all){
                        return true;
                    }
                    $role = NULL;
                }
            }
            return (count(array_filter($roles)) == 0);
        }
        static public function hasRole($roles, $all = true){
            return self::inRole($roles, $all);
        }

        /*
         |  SECURITY :: GENERATE UNIQUE SALT
         |  @since  0.3.0
         |
         |  @param  int     The salt length in INTEGER.
         |
         |  @return string  The random-generated salt in the respective length.
         */
        static public function generateSalt($bytes = 32){
            $number = ceil($bytes/2);
            $random = bin2hex(openssl_random_pseudo_bytes($number));
            return substr($random, 0, $bytes);
        }

        /*
         |  SECURITY :: HASH A USER PASSWORD
         |  @since  0.3.0
         |
         |  @param  string  The plain user password.
         |  @param  int     The algorithmic cost that should be used.
         |  @param  string  Use an own salt (not recommended!).
         |  @param  bool    TRUE to use the FOX_ID as pepper, FALSE to do it not.
         |
         |  @return string  The hashed user password.
         */
        static public function hashPassword($password, $cost = 10, $salt = NULL, $pepper = true){
            if(!$salt){
                $salt = self::generateSalt(22);
            }
            if($pepper){
                $password = FOX_ID . $password;
            }

            // Password Hash (PHP >= 5.3.7)
            if(function_exists("password_hash")){
                return password_hash($password, PASSWORD_BCRYPT, array(
                    "cost"  => $cost
                ));
            }

            // Crypt (PHP < 5.3.7)
            if(version_compare(PHP_VERSION, "5.3.7", "<")){
                return crypt($password, '$2a$'.$cost.'$'.$salt.'$');
            }
            return crypt($password, '$2y$'.$cost.'$'.$salt.'$');
        }
        static public function generateHashedPassword($password, $salt){
            deprecated("AuthUser::generateHashedPassword", "AuthUser::hashPassword");
            return self::hashPassword($password, 10, $salt, false);
        }

        /*
         |  SECURITY :: VALIDATE A PASSWORD
         |  @since  0.3.0
         |
         |  @param  object  The User Record object.
         |  @param  string  The plain password to complae.
         |
         |  @return bool    TRUE if everything is fluffy, FALSE if not.
         */
        static public function validatePassword($user, $password){
            if(!is_a($user, "User") || empty($user->salt)){
                return false;   // Pre 0.7.0 method is too unsecure!
            }

            // Wolf CMS
            if($user->salt !== "fox"){
                use_helper("Hash");
                $hash = new Crypt_Hash("sha512");

                $compare = bin2hex($hash->hash($password . $user->salt));
                if(function_exists("hash_equals")){
                    $result = hash_equals($user->password, $compare);
                } else {
                    $result = compare($user->password, $compare);
                }

                if($result){
                    // Update Wolf CMS password to Fox CMS
                    $user->salt = "fox";
                    $user->password = self::hashPassword($password);
                    $user->save();
                    return true;
                }
                return false;
            }

            // Fox CMS
            if(function_exists("password_verify")){
                return password_verify(FOX_ID . $password, $user->password);
            }
            list($temp, $algo, $cost, $hash) = explode("$", $user->password);
            return compare($user->password, self::hashPassword($password, $cost, substr($hash, 0, 22), true));
        }

        /*
         |  ACTION :: LOGIN
         |  @since  0.8.4
         |
         |  @param  string  The username or usermail address.
         |  @param  string  The plain user password.
         |  @param  bool    TRUE to set a cookie, FALSE to do it not.
         |  @param  null    Deprecated parameter.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function login($data, $password, $cookie = false, $deprecated = NULL){
            if(self::$isLoggedIn){
                return false;
            }

            // Find User
            if(filter_var($data, FILTER_VALIDATE_EMAIL) !== false){
                $user = User::findBy("email", $data);
            } else {
                $user = User::findBy("username", strtolower($data));
            }
            if(!is_a($user, "User")){
                return false;
            }

            // Login Protection
            if(LOGIN_PROTECTION && $user->failure_count >= LOGIN_PROTECTION_ATTEMPTS){
                $last = mk_time($user->last_failure);
                $multi = 1;
                if(LOGIN_PROTECTION_EXP){
                    $multi = ceil($user->failure_count / LOGIN_PROTECTION_ATTEMPTS);
                }
                if($last < (time() + (LOGIN_PROTECTION_TIME * $multi))){
                    return false;
                }
            }

            // Check Password
            if(!self::validatePassword($user, $password)){
                $user->last_failure  = date("Y-m-d H:i:s");
                $user->failure_count = $user->failure_count+1;
                $user->save();
                return false;
            }

            // Set User Data
            if($cookie && !self::bakeCookie($user)){
                return false;
            } else if(!$cookie && !self::bakeSession($user)){
                return false;
            }
            $user->last_login    = date("Y-m-d H:i:s");
            $user->last_failure  = date("Y-m-d H:i:s", 0);
            $user->failure_count = 0;
            $user->save();

            // Set Class Vars
            self::$currentUser = $user;
            self::$currentRoles = $user->roles();
            self::$currentUserID = $user->id;
            self::$isAdmin = self::inRole("administrator");
            self::$isLoggedIn = true;
            return true;
        }
        static public function forceLogin($username, $set_cookie = false){
            return false;
        }

        /*
         |  ACTION :: LOGOUT
         |  @since  0.8.4
         |
         |  @return bool    TRUE if there was a user to log out, FALSE if not.
         */
        static public function logout(){
            if(empty(self::$currentUserID) || !is_a(self::$currentUser, "User")){
                return false;
            }
            self::$currentUser->cookie = NULL;
            self::$currentUser->session = NULL;
            self::$currentUser->save();

            // Clear Storage
            self::$isAdmin = false;
            self::$isLoggedIn = false;
            self::$currentUser = false;
            self::$currentRoles = array();
            self::$currentUserID = 0;

            // Eat Cookie Session
            self::eatCookie();
            unset($_SESSION[SESSION_KEY]);
            return true;
        }
    }
