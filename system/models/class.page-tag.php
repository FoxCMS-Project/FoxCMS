<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/models/class.page-tag.php
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

    class PageTag extends Record{
        const TABLE = "page_tag";

        /*
         |  DELETE BY METHOD
         |  @since  0.8.4
         */
        static public function deleteByPageId($id){
            if(!is_numeric($id)){
                return false;
            }
            return self::deleteWhere("page_tag", "page_id = :id", array(":id" => (int) $id));
        }

        /*
         |  DATA VARs
         */
        public $page_id;
        public $tag_id;
    }
