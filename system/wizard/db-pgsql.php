<?php
/*
 |  FoxCMS      Content Management Simplified <www.foxcms.org>
 |  @file       ./system/wizard/db-pgsql.php
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
    if(!defined("FOX_WIZARD_PGSQL")){ die(); }

    /*
     |  INSTALL THE DATABASE
     |  @since  0.8.4
     */
    function wizard_db_install($db){
        $prefix = DB_PREFIX;

        /*
         |  NEW CONFIG TABLE (replaces setting and plugin_settings)
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}config(
            id BIGSERIAL PRIMARY KEY,
            name VARCHAR(64) NOT NULL,
            value TEXT NOT NULL,
            type VARCHAR(64) NOT NULL DEFAULT 'unknown',
            CONSTRAINT config_type_name UNIQUE (type,name)
        );");

        /*
         |  NEW CONFIG CRON TABLE (replaces cron)
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}config_cron(
            id BIGSERIAL PRIMARY KEY,
            cron VARCHAR(64) NOT NULL,
            status VARCHAR(30) NOT NULL,
            callback VARCHAR(100) NOT NULL,
            interval INTEGER NOT NULL DEFAULT 60,
            starttime TIMESTAMP NULL,
            nextcall TIMESTAMP NULL,
            lastcall TIMESTAMP NULL,
            CONSTRAINT cron_callback UNIQUE (cron,callback)
        );");

        /*
         |  NEW SECURE TOKEN TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}config_token(
            id BIGSERIAL PRIMARY KEY,
            token VARCHAR(64) NULL DEFAULT NULL,
            secure VARCHAR(128) NULL DEFAULT NULL,
            nonce VARCHAR(100) NULL DEFAULT NULL,
            userhash VARCHAR(200) NULL DEFAULT NULL,
            created_on TIMESTAMP NOT NULL,
            valid_until TIMESTAMP NOT NULL,
            CONSTRAINT user_nonce UNIQUE (userhash,nonce)
        );");

        /*
         |  OLD LAYOUT TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}layout(
            id BIGSERIAL PRIMARY KEY,
            name VARCHAR(100) DEFAULT NULL,
            content TEXT NULL,
            content_type VARCHAR(80) DEFAULT NULL,
            position INTEGER DEFAULT NULL,
            created_on TIMESTAMP DEFAULT NULL,
            updated_on TIMESTAMP DEFAULT NULL,
            created_by BIGINT DEFAULT NULL,
            updated_by BIGINT DEFAULT NULL,
            CONSTRAINT layout_name UNIQUE (name)
        );");

        /*
         |  OLD PAGE TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}page(
            id BIGSERIAL PRIMARY KEY,
            slug VARCHAR(100) NOT NULL DEFAULT '',
            title VARCHAR(255) DEFAULT NULL,
            breadcrumb VARCHAR(160) DEFAULT NULL,
            keywords VARCHAR(255) DEFAULT NULL,
            description TEXT NULL,
            position INTEGER DEFAULT NULL,
            parent_id BIGINT NULL,
            layout_id BIGINT NULL,
            status_id BIGINT NULL,
            behavior_id VARCHAR(25) NOT NULL DEFAULT '',
            is_protected SMALLINT NULL DEFAULT '0',
            needs_login SMALLINT NULL DEFAULT '2',
            created_on TIMESTAMP DEFAULT NULL,
            updated_on TIMESTAMP DEFAULT NULL,
            published_on TIMESTAMP DEFAULT NULL,
            created_by BIGINT DEFAULT NULL,
            updated_by BIGINT DEFAULT NULL,
            published_by BIGINT DEFAULT NULL,
            valid_until TIMESTAMP NULL
        );");

        /*
         |  OLD PAGE PART TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}page_part(
            id BIGSERIAL PRIMARY KEY,
            name VARCHAR(100) DEFAULT NULL,
            page_id BIGINT NOT NULL,
            filter_id VARCHAR(25) DEFAULT NULL,
            content TEXT NULL,
            content_html TEXT NULL,
            CONSTRAINT page_part_name UNIQUE (name)
        );");

        /*
         |  OLD PAGE TAG TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}page_tag(
            page_id BIGINT NOT NULL,
            tag_id BIGINT NOT NULL,
            CONSTRAINT page_tag UNIQUE (page_id,tag_id)
        );");

        /*
         |  OLD PERMISSION TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}permission(
            id BIGSERIAL PRIMARY KEY,
            name VARCHAR(25) NOT NULL,
            CONSTRAINT permission_name UNIQUE (name)
        );");

        /*
         |  OLD ROLE TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}role(
            id BIGSERIAL PRIMARY KEY,
            name VARCHAR(25) NOT NULL,
            CONSTRAINT role_name UNIQUE (name)
        );");

        /*
         |  OLD SNIPPET TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}snippet(
            id BIGSERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL DEFAULT '',
            filter_id VARCHAR(25) DEFAULT NULL,
            content TEXT NULL,
            content_html TEXT NULL,
            position INTEGER DEFAULT NULL,
            created_on TIMESTAMP DEFAULT NULL,
            updated_on TIMESTAMP DEFAULT NULL,
            created_by BIGINT DEFAULT NULL,
            updated_by BIGINT DEFAULT NULL,
            CONSTRAINT snippet_name UNIQUE (name)
        );");

        /*
         |  OLD TAG TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}tag(
            id BIGSERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL DEFAULT '',
            count INTEGER NOT NULL,
            CONSTRAINT tag_name UNIQUE (name)
        );");

        /*
         |  OLD USER TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}user(
            id BIGSERIAL PRIMARY KEY,
            username VARCHAR(64) NOT NULL,
            name VARCHAR(100) DEFAULT NULL,
            email VARCHAR(255) DEFAULT NULL,
            password TEXT NOT NULL,
            salt TEXT NOT NULL,
            language VARCHAR(10) DEFAULT 'en',
            cookie VARCHAR(100) DEFAULT NULL,
            session VARCHAR(100) DEFAULT NULL,
            last_login TIMESTAMP DEFAULT NULL,
            last_failure TIMESTAMP DEFAULT NULL,
            failure_count INTEGER DEFAULT NULL,
            created_on TIMESTAMP DEFAULT NULL,
            updated_on TIMESTAMP DEFAULT NULL,
            created_by BIGINT DEFAULT NULL,
            updated_by BIGINT DEFAULT NULL,
            COnSTRAINT username UNIQUE (username),
            CONSTRAINT uc_email UNIQUE (email)
        );");

        /*
         |  OLD USER ROLE TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}user_role(
            user_id BIGINT NOT NULL,
            role_id BIGINT NOT NULL,
            CONSTRAINT role_to_user UNIQUE (user_id,role_id)
        );");

        /*
         |  OLD ROLE PERMISSION TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}role_permission(
            role_id BIGINT NOT NULL,
            permission_id BIGINT NOT NULL,
            CONSTRAINT permission_to_role UNIQUE (role_id,permission_id)
        );");
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
        $prefix = TABLE_PREFIX;

        /*
         |  NEW CONFIG TABLE (replaces setting and plugin_settings)
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}config(
            id BIGSERIAL PRIMARY KEY,
            name VARCHAR(64) NOT NULL,
            value TEXT NOT NULL,
            type VARCHAR(64) NOT NULL DEFAULT 'unknown',
            CONSTRAINT config_type_name UNIQUE (type,name)
        );");

        /*
         |  NEW CONFIG CRON TABLE (replaces cron)
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}config_cron(
            id BIGSERIAL PRIMARY KEY,
            cron VARCHAR(64) NOT NULL,
            status VARCHAR(30) NOT NULL,
            callback VARCHAR(100) NOT NULL,
            interval INTEGER NOT NULL DEFAULT 60,
            starttime TIMESTAMP NULL,
            nextcall TIMESTAMP NULL,
            lastcall TIMESTAMP NULL,
            CONSTRAINT cron_callback UNIQUE (cron,callback)
        );");

        /*
         |  NEW SECURE TOKEN TABLE
         */
        $db->exec("CREATE TABLE IF NOT EXISTS {$prefix}config_token(
            id BIGSERIAL PRIMARY KEY,
            token VARCHAR(64) NULL DEFAULT NULL,
            secure VARCHAR(128) NULL DEFAULT NULL,
            nonce VARCHAR(100) NULL DEFAULT NULL,
            userhash VARCHAR(200) NULL DEFAULT NULL,
            created_on TIMESTAMP NOT NULL,
            valid_until TIMESTAMP NOT NULL,
            CONSTRAINT user_nonce UNIQUE (userhash,nonce)
        );");

        /*
         |  OLD LAYOUT TABLE
         */
        $db->exec("ALTER TABLE {$prefix}layout ALTER COLUMN id BIGSERIAL PRIMARY KEY;");
        $db->exec("ALTER TABLE {$prefix}layout ALTER COLUMN content TEXT NULL;");
        $db->exec("ALTER TABLE {$prefix}layout ALTER COLUMN created_by_id BIGINT DEFAULT NULL;");
        $db->exec("ALTER TABLE {$prefix}layout ALTER COLUMN updated_by_id BIGINT DEFAULT NULL;");
        $db->exec("ALTER TABLE {$prefix}layout RENAME COLUMN created_by_id TO created_by;");
        $db->exec("ALTER TABLE {$prefix}layout RENAME COLUMN updated_by_id TO updated_by;");

        /*
         |  OLD PAGE TABLE
         */
        $db->exec("ALTER TABLE {$prefix}page ALTER COLUMN id BIGSERIAL PRIMARY KEY;");
        $db->exec("ALTER TABLE {$prefix}page ALTER COLUMN parent_id BIGINT NULL;");
        $db->exec("ALTER TABLE {$prefix}page ALTER COLUMN layout_id BIGINT NULL;");
        $db->exec("ALTER TABLE {$prefix}page ALTER COLUMN status_id BIGINT NULL;");
        $db->exec("ALTER TABLE {$prefix}page ALTER COLUMN created_by_id BIGINT DEFAULT NULL;");
        $db->exec("ALTER TABLE {$prefix}page ALTER COLUMN updated_by_id BIGINT DEFAULT NULL;");
        $db->exec("ALTER TABLE {$prefix}page RENAME COLUMN created_by_id TO created_by;");
        $db->exec("ALTER TABLE {$prefix}page RENAME COLUMN updated_by_id TO updated_by;");
        $db->exec("ALTER TABLE {$prefix}page ADD COLUMN published_by BIGINT DEFAULT NULL");

        /*
         |  OLD PAGE PART TABLE
         */
        $db->exec("ALTER TABLE {$prefix}page_part ALTER COLUMN id BIGSERIAL PRIMARY KEY;");
        $db->exec("ALTER TABLE {$prefix}page_part ALTER COLUMN page_id BIGSERIAL;");

        /*
         |  OLD PAGE TAG TABLE
         */
        $db->exec("ALTER TABLE {$prefix}page_tag ALTER COLUMN page_id BIGSERIAL;");
        $db->exec("ALTER TABLE {$prefix}page_tag ALTER COLUMN tag_id BIGSERIAL;");

        /*
         |  OLD PERMISSION TABLE
         */
        $db->exec("ALTER TABLE {$prefix}permission ALTER COLUMN id BIGSERIAL PRIMARY KEY;");

        /*
         |  OLD ROLE TABLE
         */
        $db->exec("ALTER TABLE {$prefix}role ALTER COLUMN id BIGSERIAL PRIMARY KEY;");

        /*
         |  OLD SNIPPET TABLE
         */
        $db->exec("ALTER TABLE {$prefix}snippet ALTER COLUMN id BIGSERIAL PRIMARY KEY;");
        $db->exec("ALTER TABLE {$prefix}snippet ALTER COLUMN created_by_id BIGINT DEFAULT NULL;");
        $db->exec("ALTER TABLE {$prefix}snippet ALTER COLUMN updated_by_id BIGINT DEFAULT NULL;");
        $db->exec("ALTER TABLE {$prefix}snippet RENAME COLUMN created_by_id TO created_by;");
        $db->exec("ALTER TABLE {$prefix}snippet RENAME COLUMN updated_by_id TO updated_by;");

        /*
         |  OLD TAG TABLE
         */
        $db->exec("ALTER TABLE {$prefix}tag ALTER COLUMN id BIGSERIAL PRIMARY KEY;");

        /*
         |  OLD USER TABLE
         */
        $db->exec("ALTER TABLE {$prefix}user ALTER COLUMN id BIGSERIAL PRIMARY KEY;");
        $db->exec("ALTER TABLE {$prefix}user ALTER COLUMN username VARCHAR(64) NOT NULL;");
        $db->exec("ALTER TABLE {$prefix}user ALTER COLUMN password TEXT NOT NULL;");
        $db->exec("ALTER TABLE {$prefix}user ALTER COLUMN salt TEXT NOT NULL;");
        $db->exec("ALTER TABLE {$prefix}user ALTER COLUMN language VARCHAR(10) DEFAULT 'en';");
        $db->exec("ALTER TABLE {$prefix}user ALTER COLUMN created_by_id BIGINT DEFAULT NULL;");
        $db->exec("ALTER TABLE {$prefix}user ALTER COLUMN updated_by_id BIGINT DEFAULT NULL;");
        $db->exec("ALTER TABLE {$prefix}user RENAME COLUMN created_by_id TO created_by;");
        $db->exec("ALTER TABLE {$prefix}user RENAME COLUMN updated_by_id TO updated_by;");
        $db->exec("ALTER TABLE {$prefix}user ADD COLUMN cookie VARCHAR(100) DEFAULT NULL;");
        $db->exec("ALTER TABLE {$prefix}user ADD COLUMN session VARCHAR(100) DEFAULT NULL;");

        /*
         |  OLD USER ROLE TABLE
         */
        $db->exec("ALTER TABLE {$prefix}user_role ALTER COLUMN user_id BIGSERIAL NOT NULL;");
        $db->exec("ALTER TABLE {$prefix}user_role ALTER COLUMN role_id BIGSERIAL NOT NULL;");

        /*
         |  OLD ROLE PERMISSION TABLE
         */
        $db->exec("ALTER TABLE {$prefix}role_permission ALTER COLUMN role_id BIGSERIAL NOT NULL;");
        $db->exec("ALTER TABLE {$prefix}role_permission ALTER COLUMN permission_id BIGSERIAL NOT NULL;");
    }
