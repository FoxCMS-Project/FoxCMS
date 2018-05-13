<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/controllers/class.theme-controller.php
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

    class ThemeController extends Controller{
        /*
         |  CONSTRUCTOR
         |  @since  0.8.4
         */
        public function __construct(){
            if(!AuthUser::isLoggedIn()){
                return redirect(get_url("login"));
            }
            if(!AuthUser::hasPermission("theme_view")){
                Flash::set("error", __("You don't have the permission to access this page!"));
                if(Setting::get("default-tab") == "theme"){
                    return redirect(get_url("page"));
                }
                return redirect(get_url());
            }
            $this->setLayout("backend");
            $this->assignToLayout("sidebar", new View("theme/sidebar"));
        }

        /*
         |  ACTION :: INDEX
         |  @since  0.8.4
         */
        public function index(){
            $page = Event::applyFilter("controller-theme-index", "theme/index");
            $this->display($page, array(
                "themes"   => Theme::findAll()
            ));
        }

    }
