<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/models/class.page-part.php
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

    class PagePart extends Record{
        const TABLE = "page_part";

        /*
         |  FINDER :: FIND BY PAGE ID
         |  @since  0.8.4
         |
         |  @param  int     The unique page ID as INT.
         |
         |  @return multi   The respective PagePart objects within an ARRAY, FALSE on failure.
         */
        static public function findByPageId($page_id){
            if(!is_numeric($page_id)){
                return false;
            }
            return self::find(array(
                "where"     => "page_id = :pid",
                "order"     => "id",
                "values"    => array(":pid" => (int) $page_id)
            ));
        }

        /*
         |  DELETE BY PAGE ID
         |  @since  0.8.4
         |
         |  @param  int     The unique page ID as INT.
         |
         |  @return bool    TRUE on success, FALSE on failure.
         */
        static public function deleteByPageId($page_id){
            if(!is_numeric($page_id)){
                return false;
            }
            return self::deleteWhere(self::TABLE, "page_id = :pid", array(":pid" => (int) $page_id));
        }


        /*
         |  DATA VARs
         */
        public $id;
        public $name = "body";
        public $page_id = 0;
        public $filter_id;
        public $content;
        public $content_html;

        /*
         |  HOOK :: BEFORE SAVE
         |  @since  0.8.4
         */
        public function beforeSave(){
            if(empty($this->name)){
                return false;
            }

            // Parse Content
            if(!empty($this->filter_id) && ($filter = Filter::get($this->filter_id)) !== false){
                $this->content_html = $filter->apply($this->content);
            } else {
                $this->content_html = $this->content;
            }
            return true;
        }
    }
