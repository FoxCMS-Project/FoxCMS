<?php
/*
 |  FoxCMS Skeleton Plugin
 |  @file       ./skeleton/index.php
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
        "id"            => "skeleton",
        "title"         => __("Skeleton"),
        "description"   => __("Provides a basic plugin implementation. (try enabling it!)"),
        "version"       => "1.2.0",
        "license"       => "GPLv3",
        "author"        => "Martijn van der Kleijn / SamBrishes",
        "website"       => "https://www.foxcms.org/plugins/skeleton",
        "update_url"    => "https://www.foxcms.org/plugins/skeleton/update",
        "require_fox"   => "0.8.4"
    ));

    // Enabled Plugin Handle
    if(Plugin::isEnabled("skeleton")){
        Plugin::addController("skeleton", __("Skeleton"), "admin_view", false);
    }
