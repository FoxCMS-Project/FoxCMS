<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/wizard/db-sqlite.php
 |  @author     SamBrishes@pytesNET
 |  @version    0.8.4 [0.8.4] - Alpha
 |
 |  @license    GNU GPL v3
 |  @copyright  Copyright © 2015 - 2018 SamBrishes, pytesNET <pytes@gmx.net>
 |
 |  @history    Copyright © 2009 - 2015 Martijn van der Kleijn <martijn.niji@gmail.com>
 |              Copyright © 2008 - 2009 Philippe Archambault <philippe.archambault@gmail.com>
 */
    if(!defined("FOXCMS") || (defined("FOXCMS") && FOXCMS !== "wizard")){ die(); }
    if(!defined("FOX_WIZARD_SQLITE")){ die(); }

    /*
     |  INSTALL THE DATABASE
     |  @since  0.8.4
     */
    function wizard_db_install($db){
        $prefix = DB_PREFIX;

        /*
         |  NEW CONFIG TABLE (replaces setting and plugin_settings)
         */
        $db->exec("CREATE TABLE IF NOT EXISTS config(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value TEXT NULL,
            type TEXT NOT NULL DEFAULT 'unknown'
        );");
        $db->exec("CREATE UNIQUE INDEX config_type_name ON config (type,name);");

        /*
         |  NEW CONFIG CRON TABLE (replaces cron)
         */
        $db->exec("CREATE TABLE IF NOT EXISTS config_cron(
            id INTEGER PRIMARY KEY AUTOINCREMENt,
            cron TEXT NOT NULL,
            status TEXT NOT NULL,
            callback TEXT NOT NULL,
            interval INTEGER NOT NULL DEFAULT 60,
            starttime INTEGER NULL,
            nextcall INTEGER NULL,
            lastcall INTEGER NULL
        );");
        $db->exec("CREATE UNIQUE INDEX cron_callback ON config_cron (cron,callback);");

        /*
         |  NEW SECURE TOKEN TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS config_token(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            token TEXT NULL DEFAULT NULL,
            secure TEXT NULL DEFAULT NULL,
            nonce TEXT NULL DEFAULT NULL,
            userhash TEXT NULL DEFAULT NULL,
            created_on INTEGER NOT NULL,
            valid_until INTEGER NOT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX user_nonce ON config_token (userhash,nonce);");

        /*
         |  OLD LAYOUT TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS layout(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT DEFAULT NULL,
            content TEXT NULL,
            content_type TEXT NULL,
            position INTEGER DEFAULT NULL,
            created_on INTEGER DEFAULT NULL,
            updated_on INTEGER DEFAULT NULL,
            created_by INTEGER DEFAULT NULL,
            updated_by INTEGER DEFAULT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX layout_name ON layout (name);");

         /*
          |  OLD PAGE TABLE
          */
         $db->exec("CREATE TABLE IF NOT EXISTS page(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT NOT NULL DEFAULT '',
            title TEXT DEFAULT NULL,
            breadcrumb TEXT DEFAULT NULL,
            keywords TEXT DEFAULT NULL,
            description TEXT NULL,
            position INTEGER DEFAULT NULL,
            parent_id INTEGER NULL,
            layout_id INTEGER NULL,
            status_id INTEGER NULL,
            behavior_id TEXT NOT NULL DEFAULT '',
            is_protected INTEGER NOT NULL DEFAULT '0',
            needs_login INTEGER NOT NULL DEFAULT '2',
            created_on INTEGER DEFAULT NULL,
            updated_on INTEGER DEFAULT NULL,
            published_on INTEGER DEFAULT NULL,
            created_by INTEGER DEFAULT NULL,
            updated_by INTEGER DEFAULT NULL,
            published_by INTEGER DEFAULT NULL,
            valid_until INTEGER NULL
        );");

        /*
         |  OLD PAGE PART TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS page_part(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT DEFAULT NULL,
            page_id INTEGER NOT NULL,
            filter_id TEXT DEFAULT NULL,
            content TEXT NULL,
            content_html TEXT NULL
        );");

        /*
         |  OLD PAGE TAG TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS page_tag(
            page_id INTEGER NOT NULL,
            tag_id INTEGER NOT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX tag_to_page ON page_tag (page_id,tag_id);");

        /*
         |  OLD PERMISSION TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS permission(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX permission_name ON permission (name);");

        /*
         |  OLD ROLE TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS role(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX role_name ON role (name);");

        /*
         |  OLD SNIPPET TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS snippet(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL DEFAULT '',
            filter_id TEXT DEFAULT NULL,
            content TEXT NULL,
            content_html TEXT NULL,
            position INTEGER DEFAULT NULL,
            created_on INTEGER DEFAULT NULL,
            updated_on INTEGER DEFAULT NULL,
            created_by INTEGER DEFAULT NULL,
            updated_by INTEGER DEFAULT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX snippet_name ON snippet (name);");

        /*
         |  OLD TAG TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS tag(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL DEFAULT '',
            count INTEGER NOT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX tag_name ON tag (name);");

        /*
         |  OLD USER TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS user(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            name TEXT DEFAULT NULL,
            email TEXT DEFAULT NULL,
            password TEXT NOT NULL,
            salt TEXT NOT NULL,
            language TEXT DEFAULT 'en',
            cookie TEXT DEFAULT NULL,
            session TEXT DEFAULT NULL,
            last_login INTEGER DEFAULT NULL,
            last_failure INTEGER DEFAULT NULL,
            failure_count INTEGER DEFAULT NULL,
            created_on INTEGER DEFAULT NULL,
            updated_on INTEGER DEFAULT NULL,
            created_by INTEGER DEFAULT NULL,
            updated_by INTEGER DEFAULT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX user_username ON user (username);");
        $db->exec("CREATE UNIQUE INDEX user_email ON user (email);");

        /*
         |  OLD USER ROLE TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS user_role(
            user_id INTEGER NOT NULL,
            role_id INTEGER NOT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX role_to_user ON user_role (user_id,role_id);");

        /*
         |  OLD ROLE PERMISSION TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS role_permission(
            role_id INTEGER NOT NULL,
            permission_id INTEGER NOT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX permission_to_role ON role_permission (role_id,permission_id);");
    }

    /*
     |  UPGRADE THE DATABASE
     |  @since  0.8.4
     */
    function wizard_db_upgrade($db){
        // Future Stuff :3
    }

    /*
     |  MIGRATE THE DATABASE
     |  @since  0.8.4
     */
    function wizard_db_migrate($db){
        /*
         |  NEW CONFIG TABLE (replaces setting and plugin_settings)
         */
        $db->exec("CREATE TABLE IF NOT EXISTS config(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value TEXT NULL,
            type TEXT NOT NULL DEFAULT 'unknown'
        );");
        $db->exec("CREATE UNIQUE INDEX config_type_name ON config (type,name);");

        /*
         |  NEW CONFIG CRON TABLE (replaces cron)
         */
        $db->exec("CREATE TABLE IF NOT EXISTS config_cron(
            id INTEGER PRIMARY KEY AUTOINCREMENt,
            cron TEXT NOT NULL,
            status TEXT NOT NULL,
            callback TEXT NOT NULL,
            interval INTEGER NOT NULL DEFAULT 60,
            starttime INTEGER NULL,
            nextcall INTEGER NULL,
            lastcall INTEGER NULL
        );");
        $db->exec("CREATE UNIQUE INDEX cron_callback ON config_cron (cron,callback);");

        /*
         |  NEW SECURE TOKEN TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS config_token(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            token TEXT NULL DEFAULT NULL,
            secure TEXT NULL DEFAULT NULL,
            nonce TEXT NULL DEFAULT NULL,
            userhash TEXT NULL DEFAULT NULL,
            created_on INTEGER NOT NULL,
            valid_until INTEGER NOT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX user_nonce ON config_token (userhash,nonce);");

        /*
         |  OLD LAYOUT TABLE
         */
        $merge = array(
            "name" => "name", "content" => "content", "content_type" => "content_type",
            "position" => "position", "created_on" => "created_on", "updated_on" => "updated_on",
            "created_by" => "created_by_id", "updated_by" => "updated_by_id"
        );
        $db->beginTransaction();
        $db->exec("ALTER TABLE layout RENAME TO old_layout;");
        $db->exec("CREATE TABLE IF NOT EXISTS layout(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT DEFAULT NULL,
            content TEXT NULL,
            content_type TEXT NULL,
            position INTEGER DEFAULT NULL,
            created_on INTEGER DEFAULT NULL,
            updated_on INTEGER DEFAULT NULL,
            created_by INTEGER DEFAULT NULL,
            updated_by INTEGER DEFAULT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX layout_name ON layout (name);");
        $db->exec("INSERT INTO layout (".implode(",", array_keys($merge)).") SELECT ".implode(",", array_values($merge))." FROM old_layout;");
        $db->exec("DROP TABLE old_layout;");
        $db->commit();

        /*
         |  OLD PAGE TABLE
         */
        $merge = array(
            "slug" => "slug", "title" => "title", "breadcrumb" => "breadcrumb",
            "keywords" => "keywords", "description" => "description", "position" => "position",
            "parent_id" => "parent_id", "layout_id" => "layout_id", "status_id" => "status_id",
            "behavior_id" => "behavior_id", "is_protected" => "is_protected",
            "needs_login" => "needs_login", "created_on" => "created_on", "updated_on" => "updated_on",
            "published_on" => "published_on", "created_by" => "created_by_id",
            "updated_by" => "updated_by_id", "published_by" => "created_by_id", "valid_until" => "valid_until"
        );
        $db->beginTransaction();
        $db->exec("ALTER TABLE page RENAME TO old_page;");
        $db->exec("CREATE TABLE IF NOT EXISTS page(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT NOT NULL DEFAULT '',
            title TEXT DEFAULT NULL,
            breadcrumb TEXT DEFAULT NULL,
            keywords TEXT DEFAULT NULL,
            description TEXT NULL,
            position INTEGER DEFAULT NULL,
            parent_id INTEGER NULL,
            layout_id INTEGER NULL,
            status_id INTEGER NULL,
            behavior_id TEXT NOT NULL DEFAULT '',
            is_protected INTEGER NOT NULL DEFAULT '0',
            needs_login INTEGER NOT NULL DEFAULT '2',
            created_on INTEGER DEFAULT NULL,
            updated_on INTEGER DEFAULT NULL,
            published_on INTEGER DEFAULT NULL,
            created_by INTEGER DEFAULT NULL,
            updated_by INTEGER DEFAULT NULL,
            published_by INTEGER DEFAULT NULL,
            valid_until INTEGER NULL
        );");
        $db->exec("INSERT INTO page (".implode(",", array_keys($merge)).") SELECT ".implode(",", array_values($merge))." FROM old_page;");
        $db->exec("DROP TABLE old_page;");
        $db->commit();

        /*
         |  OLD PAGE PART TABLE
         */
        $merge = array(
            "name" => "name", "filter_id" => "filter_id", "content" => "content",
            "content_html" => "content_html", "page_id" => "page_id"
        );
        $db->beginTransaction();
        $db->exec("ALTER TABLE page_part RENAME TO old_page_part;");
        $db->exec("CREATE TABLE IF NOT EXISTS page_part(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT DEFAULT NULL,
            page_id INTEGER NOT NULL,
            filter_id TEXT DEFAULT NULL,
            content TEXT NULL,
            content_html TEXT NULL
        );");
        $db->exec("INSERT INTO page_part (".implode(",", array_keys($merge)).") SELECT ".implode(",", array_values($merge))." FROM old_page_part;");
        $db->exec("DROP TABLE old_page_part;");
        $db->commit();

        /*
         |  OLD PAGE TAG TABLE
         */
        $merge = array(
            "page_id" => "page_id", "tag_id" => "tag_id"
        );
        $db->beginTransaction();
        $db->exec("ALTER TABLE page_tag RENAME TO old_page_tag;");
        $db->exec("CREATE TABLE IF NOT EXISTS page_tag(
            page_id INTEGER NOT NULL,
            tag_id INTEGER NOT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX tag_to_page ON page_tag (page_id,tag_id);");
        $db->exec("INSERT INTO page_tag (".implode(",", array_keys($merge)).") SELECT ".implode(",", array_values($merge))." FROM old_page_tag;");
        $db->exec("DROP TABLE old_page_tag;");
        $db->commit();

        /*
         |  OLD PERMISSION TABLE
         */
        $merge = array("name" => "name");
        $db->beginTransaction();
        $db->exec("ALTER TABLE permission RENAME TO old_permission;");
        $db->exec("CREATE TABLE IF NOT EXISTS permission(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX permission_name ON permission (name);");
        $db->exec("INSERT INTO permission (".implode(",", array_keys($merge)).") SELECT ".implode(",", array_values($merge))." FROM old_permission;");
        $db->exec("DROP TABLE old_permission;");
        $db->commit();

        /*
         |  OLD ROLE TABLE
         */
        $merge = array("name" => "name");
        $db->beginTransaction();
        $db->exec("ALTER TABLE role RENAME TO old_role;");
        $db->exec("CREATE TABLE IF NOT EXISTS role(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX role_name ON role (name);");
        $db->exec("INSERT INTO role (".implode(",", array_keys($merge)).") SELECT ".implode(",", array_values($merge))." FROM old_role;");
        $db->exec("DROP TABLE old_role;");
        $db->commit();

        /*
         |  OLD LAYOUT TABLE
         */
        $merge = array(
            "name" => "name", "filter_id" => "filter_id", "content" => "content",
            "content_html" => "content_html", "position" => "position", "created_on" => "created_on",
            "updated_on" => "updated_on", "created_by_id" => "created_by", "updated_by" => "updated_by_id"
        );
        $db->beginTransaction();
        $db->exec("ALTER TABLE snippet RENAME TO old_snippet;");
        $db->exec("CREATE TABLE IF NOT EXISTS snippet(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL DEFAULT '',
            filter_id TEXT DEFAULT NULL,
            content TEXT NULL,
            content_html TEXT NULL,
            position INTEGER DEFAULT NULL,
            created_on INTEGER DEFAULT NULL,
            updated_on INTEGER DEFAULT NULL,
            created_by INTEGER DEFAULT NULL,
            updated_by INTEGER DEFAULT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX snippet_name ON snippet (name);");
        $db->exec("INSERT INTO snippet (".implode(",", array_keys($merge)).") SELECT ".implode(",", array_values($merge))." FROM old_snippet;");
        $db->exec("DROP TABLE old_snippet;");
        $db->commit();

        /*
         |  OLD TAG TABLE
         */
        $merge = array("name" => "name", "count" => "count");
        $db->beginTransaction();
        $db->exec("ALTER TABLE tag RENAME TO old_tag;");
        $db->exec("CREATE TABLE IF NOT EXISTS tag(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL DEFAULT '',
            count INTEGER NOT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX tag_name ON tag (name);");
        $db->exec("INSERT INTO tag (".implode(",", array_keys($merge)).") SELECT ".implode(",", array_values($merge))." FROM old_tag;");
        $db->exec("DROP TABLE old_tag;");
        $db->commit();

        /*
         |  OLD USER TABLE
         */
        $merge = array(
            "username" => "username", "name" => "name", "email" => "email", "password" => "password",
            "salt" => "salt", "language" => "language", "last_login" => "last_login",
            "last_failure" => "last_failure", "failure_count" => "failure_count", "created_on" => "created_on",
            "updated_on" => "updated_on", "created_by" => "created_by_id", "updated_by" => "updated_by_id"
        );
        $db->beginTransaction();
        $db->exec("ALTER TABLE user RENAME TO old_user;");
        $db->exec("CREATE TABLE IF NOT EXISTS user(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            name TEXT DEFAULT NULL,
            email TEXT DEFAULT NULL,
            password TEXT NOT NULL,
            salt TEXT NOT NULL,
            language TEXT DEFAULT 'en',
            cookie TEXT DEFAULT NULL,
            session TEXT DEFAULT NULL,
            last_login INTEGER DEFAULT NULL,
            last_failure INTEGER DEFAULT NULL,
            failure_count INTEGER DEFAULT NULL,
            created_on INTEGER DEFAULT NULL,
            updated_on INTEGER DEFAULT NULL,
            created_by INTEGER DEFAULT NULL,
            updated_by INTEGER DEFAULT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX user_username ON user (username);");
        $db->exec("CREATE UNIQUE INDEX user_email ON user (email);");
        $db->exec("INSERT INTO user (".implode(",", array_keys($merge)).") SELECT ".implode(",", array_values($merge))." FROM old_user;");
        $db->exec("DROP TABLE old_user;");
        $db->commit();

        /*
         |  OLD USER ROLE TABLE
         */
        $merge = array("user_id" => "user_id", "role_id" => "role_id");
        $db->beginTransaction();
        $db->exec("ALTER TABLE user_role RENAME TO old_user_role;");
        $db->exec("CREATE TABLE IF NOT EXISTS user_role(
            user_id INTEGER NOT NULL,
            role_id INTEGER NOT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX role_to_user ON user_role (user_id,role_id);");
        $db->exec("INSERT INTO user_role (".implode(",", array_keys($merge)).") SELECT ".implode(",", array_values($merge))." FROM old_user_role;");
        $db->exec("DROP TABLE old_user_role;");
        $db->commit();

        /*
         |  OLD ROLE PERMISSION TABLE
         */
        $merge = array("user_id" => "user_id", "role_id" => "role_id");
        $db->beginTransaction();
        $db->exec("ALTER TABLE role_permission RENAME TO old_role_permission;");
        $db->exec("CREATE TABLE IF NOT EXISTS role_permission(
            role_id INTEGER NOT NULL,
            permission_id INTEGER NOT NULL
        );");
        $db->exec("CREATE UNIQUE INDEX permission_to_role ON role_permission (role_id,permission_id);");
        $db->exec("INSERT INTO role_permission (".implode(",", array_keys($merge)).") SELECT ".implode(",", array_values($merge))." FROM old_role_permission;");
        $db->exec("DROP TABLE old_role_permission;");
        $db->commit();
    }
