<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/controllers/class.setting-controller.php
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

    class SettingController extends Controller{
        /*
         |  CONSTRUCTOR
         |  @since  0.8.4
         */
        public function __construct(){
            if(!AuthUser::isLoggedIn()){
                return redirect(get_url("login"));
            }
            if(!AuthUser::hasPermission("admin_edit")){
                Flash::set("error", __("You don't have the permission to access this page!"));
                if(Setting::get("default-tab") == "setting"){
                    return redirect(get_url("page"));
                }
                return redirect(get_url());
            }
            $this->setLayout("backend");
        }

        /*
         |  ACTION :: INDEX
         |  @since  0.8.4
         */
        public function index(){
            if(request_method() == "POST"){
                return $this->_saveSettings();
            }

            $page = Event::applyFilter("controller-setting-index", "setting/index");
            $this->display($page, array(
                "token"     => SecureToken::generateToken("setting"),
                "config"    => Setting::$settings
            ));
        }

        /*
         |  ACTION :: ACTIVATE PLUGIN
         |  @since  0.8.4
         |
         |  @param  string  The respective / unique Plugin ID as STRING.
         */
        public function activate_plugin($plugin){
            Plugin::activate($plugin);
            Event::apply("plugin_after_enable", $plugin);
        }

        /*
         |  ACTION :: DEACTIVATE PLUGIN
         |  @since  0.8.4
         |
         |  @param  string  The respective / unique Plugin ID as STRING.
         */
        public function deactivate_plugin($plugin){
            Plugin::deactivate($plugin);
            Event::apply("plugin_after_disable", $plugin);
        }

        /*
         |  ACTION :: UNINSTALL PLUGIN
         |  @since  0.8.4
         |
         |  @param  string  The respective / unique Plugin ID as STRING.
         */
        public function uninstall_plugin($plugin){
            Plugin::uninstall($plugin);
            Event::apply("plugin_after_uninstall", $plugin);
        }

        /*
         |  HANDLE :: SAVE SETTINGs
         |  @since  0.8.4
         */
        public function _saveSettings(){
            $data = $_POST["setting"];

            // Check Token
            if(!isset($_POST["token"]) || !SecureToken::validateToken($_POST["token"], "setting")){
                Flash::set("error", __("The passed token is invalid or expired!"));
                return redirect(get_url("setting"));
            }

            // Validate Data
            if(!isset($data["default-allow-html"]) || $data["default-allow-html"] !== "on"){
                $data["default-allow-html"] = "off";
            }
            if($data["default-allow-html"] == "on"){
                use_helper("Kses");
                $data["site-title"] = kses(trim($data["site-title"]), array(
                    "img"       => array("src" => array()),
                    "abbr"      => array("title" => array()),
                    "acronym"   => array("title" => array()),
                    "b"         => array(),
                    "blockquote"=> array("cite"=> array()),
                    "br"        => array(),
                    "code"      => array(),
                    "em"        => array(),
                    "i"         => array(),
                    "p"         => array(),
                    "strike"    => array(),
                    "strong"    => array()
                ));
            } else {
                $data["site-title"] = remove_xss(strip_tags($data["site-title"]), true);
            }

            // Save and Return
            Setting::saveFromData($data);
            Flash::set("success", __("The Settings have been successfully saved!"));
            return redirect(get_url("setting") . "#settings");
        }
    }
