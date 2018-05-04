<?php
/*
 |  FoxCMS Page not Found Plugin
 |  @file       ./page_not_found/index.php
 |  @author     SamBrishes@pytesNET
 |  @version    1.2.0 [1.2.0] - Alpha
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */
    if(!defined("FOXCMS")){ die(); }

    // Set Plugin Informations
    Plugin::setInfos(array(
        "id"            => "page_not_found",
        "title"         => __("Page not Found"),
        "description"   => __("Provides a 404 'Page not Found' Error page type."),
        "version"       => "1.1.0",
        "website"       => "https://www.foxcms.org/plugins/page_not_found",
        "update_url"    => "https://www.foxcms.org/plugins/page_not_found/update"
    ));

    // Enabled Plugin Handle
    if(Plugin::isEnabled("page_not_found")){
        Behavior::add("page_not_found", "");
        Event::add("page_not_found", function(){
            $page = Page::findByBehavior("page_not_found");
            if(is_a($page, "Page")){
                header("HTTP/1.0 404 Not Found");
                header("Status: 404 Not Found");
                $page->_executeLayout();
                die();
            }
            include_once("404.php");
            die();
        });
    }
