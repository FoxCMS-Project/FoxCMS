<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/controllers/class.user-controller.php
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

    class UserController extends Controller{
        /*
         |  CONSTRUCTOR
         |  @since  0.8.4
         */
        public function __construct(){
            if(!AuthUser::isLoggedIn()){
                return redirect(get_url("login"));
            }
            if(!AuthUser::hasPermission("user_view")){
                Flash::set("error", __("You don't have the permission to access this page!"));
                if(Setting::get("default-tab") == "user"){
                    return redirect(get_url("page"));
                }
                return redirect(get_url());
            }
            $this->setLayout("backend");
            $this->assignToLayout("sidebar", new View("user/sidebar"));
        }

        /*
         |  ACTION :: INDEX
         |  @since  0.8.4
         */
        public function index(){
            $page = Event::applyFilter("controller-user-index", "user/index");
            $this->display($page, array(
                "users"     => User::findAll()
            ));
        }

        /*
         |  ACTION :: ADD
         |  @since  0.8.4
         */
        public function add(){
            $user = new User("post_data", array());
            $user->language = DEFAULT_LANGUAGE;
            if(!AuthUser::hasPermission("user_add")){
                Flash::set("error", __("You don't have the permission to add a new User!"));
                return redirect(get_url("user"));
            }

            // Post
            if(request_method() == "POST" && isset($_POST["user"])){
                return $this->_store("add", $user, $_POST["user"]);
            }

            // Display
            $page = Event::applyFilter("controller-user-add", "user/edit");
            $this->display($page, array(
                "user"      => $user,
                "roles"     => Record::findAllFrom("Role"),
                "token"     => SecureToken::generateToken("user/add"),
                "action"    => "add"
            ));
        }

        /*
         |  ACTION :: EDIT
         |  @since  0.8.4
         */
        public function edit($id){
            if(($user = User::findById($id)) === false){
                Flash::set("error", __("The passed User couldn't be found!"));
                return redirect(get_url("user"));
            }
            if(!AuthUser::hasPermission("user_edit")){
                Flash::set("error", __("You don't have the permission to edit this User!"));
                return redirect(get_url("user"));
            }

            // Post
            if(request_method() == "POST" && isset($_POST["user"])){
                return $this->_store("edit", $user, $_POST["user"]);
            }

            // Display
            $page = Event::applyFilter("controller-user-edit", "user/edit");
            $this->display($page, array(
                "user"      => $user,
                "roles"     => Record::findAllFrom("Role"),
                "token"     => SecureToken::generateToken("user/edit/{$user->id}"),
                "action"    => "edit"
            ));
        }

        /*
         |  ACTION :: EDIT
         |  @since  0.8.4
         */
        public function delete($id){
            if(($user = User::findById($id)) === false){
                Flash::set("error", __("The passed User couldn't be found!"));
                return redirect(get_url("user"));
            }
            if(!AuthUser::hasPermission("user_delete")){
                Flash::set("error", __("You don't have the permission to delete this User!"));
                return redirect(get_url("user"));
            }

            // Check Token
            if(!isset($_GET["token"]) || !SecureToken::validateToken($_GET["token"], "user/delete/{$user->id}")){
                Flash::set("error", __("The passed token is invalid or expired!"));
                return redirect(get_url("user"));
            }

            // Delete User
            if($id == 1){
                Flash::set("error", __("The passed User cannot be deleted!"));
                return redirect(get_url("user"));
            }
            if(!$user->delete()){
                Flash::set("error", __("An unknown error is occured while deleting the User!"));
                return redirect(get_url("user"));
            }
            Event::apply("user_after_delete", $user);

            // Return
            Flash::set("success", __("The User ':name' has been deleted successfully!", array(":name" => $user->username)));
            return redirect(get_url("user"));
        }

        /*
         |  INTERNAL :: VALIDATA DATA
         |  @since  0.8.4
         */
        private function _validate($data, $type = "add"){
            if($type == "add" || ($type == "edit" && !empty($data["password"]))){
                if(strlen($data["password"]) < 6 || $data["password"] !== $data["confirm"]){
                    Flash::set("error", __("The Password need at least 6 characters and MUST be equal with the Password Confirm field!"));
                    return false;
                }
                unset($data["confirm"]);
            } else {
                unset($data["password"], $data["confirm"]);
            }

            // Validata Username
            $data["username"] = strtolower($data["username"]);
            if(strlen($data["username"]) < 3 || ($action == "add" && $data["username"] == $data["password"])){
                Flash::set("error", __("The Username need at least 6 characters and MUST start with a-z or _ (underscore)!"));
                return false;
            }
            if(preg_match("#[^a-z0-9_-]#", $data["username"]) || !preg_match("#^[a-z_]#", $data["username"])){
                Flash::set("error", __("The Username MUST start with a-z or _ and MUST only consist of a-z, 0-9, _ and -!"));
                return false;
            }

            // Validate eMail
            if(($data["email"] = filter_var($data["email"], FILTER_SANITIZE_EMAIL)) === false){
                Flash::set("error", __("The eMail seems to be invalid or empty!"));
                return false;
            }

            // Sanitize / Escape
            if(!empty($data["name"])){
                $data["name"] = preg_replace("#[^a-zA-Z0-9\'\"\&\^\.\_\,\-\s]#i", "", $data["name"]);
            }
            if(!array_key_exists($data["language"], I18n::getLanguages())){
                $data["language"] = "en";
            }

            // Existence
            if($type == "add"){
                if(Record::existsIn("User", "username = :user", array(":user" => $data["username"]))){
                    Flash::set("error", __("The Username is already on use, please choose another one!"));
                    return false;
                }
                if(Record::existsIn("User", "email = :email", array(":email" => $data["email"]))){
                    Flash::set("error", __("The eMail Address is already on use, please choose another one!"));
                    return false;
                }
            }
            return $data;
        }

        /*
         |  HANDLE :: STORE USER TO DB
         |  @since  0.8.4
         */
        private function _store($action, $user, $data){
            $url = "user/{$action}" . (($action == "edit")? "/{$user->id}": "");
            Flash::set("post_data", $data, "both");

            // Check Token
            if(!isset($_POST["token"]) || !SecureToken::validateToken($_POST["token"], $url)){
                Flash::set("error", __("The passed token is invalid or expired!"));
                return redirect(get_url($url));
            }

            // Validata Data
            if(($data = $this->_validate($data, $action)) === false){
                return redirect(get_url($url));
            }

            // Set Data
            $user->setFromData($data);
            if(isset($data["password"])){
                $user->salt = "fox";
                $user->password = AuthUser::hashPassword($user->password);
            }

            // Store Data
            if(!$user->save()){
                Flash::set("error", __("An unknown error is occured while storing the User!"));
                return redirect(get_url($url));
            }
            Event::apply("user_after_add", $user);

            // Store Roles
            if(!empty($_POST["user_role"])){
                if(!UserRole::setRolesFor($user->id, $_POST["user_role"])){
                    Flash::set("error", __("The passed User Roles couldn't be assigned to the user!"));
                }
            }

            // Return
            Flash::set("success", __("The User has been successfully stored!"));
            return redirect(get_url("user"));
        }
    }
