<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/controllers/class.login-controller.php
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

    class LoginController extends Controller{
        /*
         |  HELPER :: SANITIZE REDIRECT
         |  @since  0.8.4
         */
        static public function sanitizeRedirect($redirect){
            if(empty($redirect)){
                return "";
            }
            return filter_var($redirect, FILTER_SANITIZE_URL);
        }


        /*
         |  ACTION :: INDEX
         |  @since  0.8.4
         */
        public function index(){
            $redirect = trim(Flash::get("redirect", Flash::get("HTTP_REFERER", get_url())));
            if(($redirect = filter_var($redirect, FILTER_SANITIZE_URL)) == false){
                $redirect = get_url();
            }

            // Redirect if logged in
            if(AuthUser::isLoggedIn()){
                return redirect($redirect);
            }

            // Wolf CMS Hook
            Event::apply("login_required", $redirect);

            // Display Login Page
            $page = Event::applyFilter("controller-login-index", "login/login");
            $this->display($page, array(
                "username"  => Flash::get("username", NULL),
                "redirect"  => $redirect
            ));
        }

        /*
         |  ACTION :: INDEX
         |  @since  0.8.4
         */
        public function login(){
            $redirect = trim(Flash::get("redirect", Flash::get("HTTP_REFERER", get_url())));
            if(($redirect = filter_var($redirect, FILTER_SANITIZE_URL)) == false){
                $redirect = get_url();
            }

            // Wolf CMS Hook
            Event::apply("login_requested", $redirect);

            // Redirect if logged in
            if(AuthUser::isLoggedIn()){
                return redirect($redirect);
            }

            // Check Login Request
            if(request_method() == "POST" && isset($_POST["login"])){
                $login = $_POST["login"];
                $data = array(
                    "user"  => isset($login["username"])? $login["username"]: "",
                    "pass"  => isset($login["password"])? $login["password"]: "",
                    "login" => isset($login["remember"])
                );
                Flash::set("username", $data["user"], "both");

                // Login
                if(AuthUser::login($data["user"], $data["pass"], $data["login"])){
                    Event::apply("admin_login_success", $data["user"]);
                    if(isset($_POST["redirect_to"])){
                        if(($data = filter_var($_POST["redirect_to"], FILTER_SANITIZE_URL)) !== false){
                            $redirect = $data;
                        }
                    }
                    return redirect($redirect);
                } else {
                    Event::apply("admin_login_failed", $data["user"]);
                    Flash::set("error", __("Login failed! Please check your Username and Password!"));
                }
            }
            return redirect(get_url("login/index"));
        }

        /*
         |  ACTION :: LOGOUT
         |  @since  0.8.4
         */
        public function logout(){
            if(!isset($_GET["token"])){
                return redirect(get_url("login/index"));
            }

            // Check token
            if(!SecureToken::validateToken($_GET["token"], "login/logout")){
                Flash::set("error", __("The passed token is invalid or expired!"));
                return redirect(get_url());
            }

            // Wolf CMS Action
            Event::apply("logout_requested");

            // Logout
            $username = AuthUser::getUserName();
            if(!AuthUser::logout()){
                Flash::set("error", __("The logout action couldn't performed successfully!"));
                return redirect(get_url());
            }

            // Wolf CMS Action
            Event::apply("admin_after_logout", $username);
            return redirect(get_url("login/index"));
        }

        /*
         |  ACTION :: FORGOT
         |  @since  0.8.4
         */
        public function forgot(){
            if(AuthUser::isLoggedIn()){
                return redirect(get_url());
            }

            // Display Forgot Page
            $page = Event::applyFilter("controller-login-forgot", "login/forgot");
            $this->display($page, array(
                "email"     => Flash::get("email", NULL),
            ));
        }

        /*
         |  ACTION :: REMEMBER
         |  @since  0.8.4
         */
        public function _remember(){
            if(request_mode() !== "POST" || !isset($_POST["forgot"]["email"])){
                return redirect(get_url("login/index"));
            }

            // Check eMail
            if(($email = filter_var($_POST["forgot"]["email"], FILTER_SANITIZE_EMAIL)) == false){
                Flash::set("error", __("The passed eMail seems to be invalid!"));
                return redirect(get_url("login/forgot"));
            }

            // Check User
            if(($user = User::findBy("email", $email)) == false){
                Flash::set("error", __("The passed eMail doesn't match to any Account!"));
                return redirect(get_url("login/forgot"));
            }

            // Create new Password
            $password = AuthUser::generateRandom();
            $user->oassword == AuthUser::hashPassword($password);
            $user->save();

            $email = new eMail();
            $email->from(Setting::get("site-email"), Setting::get("site-title"));
            $email->to($user->email, $user->name);
            $email->subject(__("Your new Password on ") . Setting::get("site-title"));
            $email->message($message);
            $email->send();

            Flash::set("success", __("An eMail has been sent with a temporary new password!"));
            return redirect(get_url("login"));
        }
    }
